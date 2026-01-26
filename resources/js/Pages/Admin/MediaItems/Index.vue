<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, Transition } from 'vue';
import { hasPermission } from '@/utils/permissions';

const props = defineProps({
    mediaItems: Array,
});

const { flash } = usePage().props;

// الصلاحيات
const canCreateMedia = computed(() => hasPermission('create_media'));
const canScanMedia = computed(() => hasPermission('manage_media'));
const canDeleteMedia = computed(() => hasPermission('delete_media'));

// متغيرات للتحكم في نافذة المعاينة
const isPreviewModalOpen = ref(false);
const itemToPreview = ref(null);

// متغيرات للتحكم في التحديد والحذف الجماعي
const selectedItems = ref(new Set());
const isDeleting = ref(false);
const isDeletingSingle = ref(new Set()); // تتبع العناصر قيد الحذف الفردي
const selectAllMode = ref(false);

// متغيرات للتحكم في custom confirmation dialog
const showDeleteConfirm = ref(false);
const deleteConfirmData = ref({ type: 'multiple', count: 0, itemId: null });

// متغيرات لمعالجة المسح
const isScanning = ref(false);
const scanProgress = ref({ current: 0, total: 0, message: '' });
const scanModalOpen = ref(false);

// دالة لفتح نافذة المعاينة وتمرير العنصر المختار
const openPreview = (item) => {
    itemToPreview.value = item;
    isPreviewModalOpen.value = true;
};

// دالة لإغلاق نافذة المعاينة
const closePreview = () => {
    isPreviewModalOpen.value = false;
    itemToPreview.value = null;
};

// دالة لتحديد/إلغاء تحديد عنصر
const toggleSelect = (itemId) => {
    if (isDeleting.value || isDeletingSingle.value.has(itemId)) return; // منع التحديد أثناء الحذف

    if (selectedItems.value.has(itemId)) {
        selectedItems.value.delete(itemId);
    } else {
        selectedItems.value.add(itemId);
    }
    selectAllMode.value = false;
};

// دالة لتحديد/إلغاء تحديد الكل
const toggleSelectAll = () => {
    if (isDeleting.value || isDeletingSingle.value.size > 0) return; // منع التحديد أثناء الحذف

    selectAllMode.value = !selectAllMode.value;
    if (selectAllMode.value) {
        props.mediaItems.forEach(item => {
            if (!isDeletingSingle.value.has(item.id)) {
                selectedItems.value.add(item.id);
            }
        });
    } else {
        selectedItems.value.clear();
    }
};

// دالة لفتح dialog تأكيد الحذف الجماعي
const openDeleteConfirm = (type = 'multiple', itemId = null) => {
    if (type === 'multiple') {
        if (selectedItems.value.size === 0 || isDeleting.value) return;
        deleteConfirmData.value = {
            type: 'multiple',
            count: selectedItems.value.size,
            itemId: null
        };
    } else {
        deleteConfirmData.value = {
            type: 'single',
            count: 1,
            itemId: itemId
        };
    }
    showDeleteConfirm.value = true;
};

// دالة لإغلاق dialog التأكيد
const closeDeleteConfirm = () => {
    showDeleteConfirm.value = false;
    deleteConfirmData.value = { type: 'multiple', count: 0, itemId: null };
};

// دالة للحذف الجماعي
const deleteSelected = () => {
    if (selectedItems.value.size === 0 || isDeleting.value) return;

    isDeleting.value = true;

    router.post(route('admin.media-items.destroy-multiple'), {
        ids: Array.from(selectedItems.value)
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            selectedItems.value.clear();
            selectAllMode.value = false;
            isDeleting.value = false;
            closeDeleteConfirm();
        },
        onError: () => {
            isDeleting.value = false;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};

// دالة للحذف الفردي
const deleteSingleItem = (itemId) => {
    if (!itemId || isDeleting.value || isDeletingSingle.value.has(itemId)) return;

    isDeletingSingle.value.add(itemId);

    router.delete(route('admin.media-items.destroy', itemId), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            selectedItems.value.delete(itemId); // إزالة من التحديد إذا كان محدداً
            closeDeleteConfirm();
        },
        onError: () => {
            isDeletingSingle.value.delete(itemId);
        },
        onFinish: () => {
            isDeletingSingle.value.delete(itemId);
        }
    });
};

// دالة لتأكيد الحذف
const confirmDelete = () => {
    if (deleteConfirmData.value.type === 'multiple') {
        deleteSelected();
    } else {
        deleteSingleItem(deleteConfirmData.value.itemId);
    }
};

// computed للتحقق من وجود عناصر محددة
const hasSelectedItems = computed(() => selectedItems.value.size > 0);
const selectedCount = computed(() => selectedItems.value.size);

// دالة لبدء عملية المسح
const startScan = async () => {
    if (isScanning.value) return;

    // منع التنقل
    window.addEventListener('beforeunload', preventNavigation);

    isScanning.value = true;
    scanModalOpen.value = true;
    scanProgress.value = { current: 0, total: 0, message: 'جاري فحص الملفات...' };

    try {
        // الحصول على CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (!csrfToken) {
            throw new Error('CSRF token غير موجود. يرجى إعادة تحميل الصفحة.');
        }

        // الحصول على عدد الملفات (بدون execute)
        const formDataInit = new FormData();

        const initResponse = await fetch(route('admin.media-items.scan'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formDataInit,
            credentials: 'same-origin',
        });

        if (!initResponse.ok) {
            const errorText = await initResponse.text();
            let errorData;
            try {
                errorData = JSON.parse(errorText);
            } catch {
                errorData = { message: errorText || `HTTP error! status: ${initResponse.status}` };
            }
            throw new Error(errorData.message || `HTTP error! status: ${initResponse.status}`);
        }

        const initData = await initResponse.json();

        if (initData.total_files !== undefined) {
            scanProgress.value.total = initData.total_files;
            scanProgress.value.current = 0;

            if (initData.total_files === 0) {
                scanProgress.value.message = 'لا توجد ملفات جديدة للمعالجة.';
                setTimeout(() => {
                    window.removeEventListener('beforeunload', preventNavigation);
                    isScanning.value = false;
                    scanModalOpen.value = false;
                    scanProgress.value = { current: 0, total: 0, message: '' };
                }, 2000);
                return;
            }

            scanProgress.value.message = `تم العثور على ${initData.total_files} ملف. جاري المعالجة...`;

            // محاكاة التقدم (لأن المعالجة الفعلية تحدث في الخادم)
            const progressInterval = setInterval(() => {
                if (scanProgress.value.current < scanProgress.value.total) {
                    scanProgress.value.current = Math.min(
                        scanProgress.value.current + Math.ceil(scanProgress.value.total / 20),
                        scanProgress.value.total
                    );
                    scanProgress.value.message = `جاري معالجة الملف ${scanProgress.value.current} من ${scanProgress.value.total}...`;
                }
            }, 500);

            // بدء المعالجة الفعلية مع معامل execute
            try {
                const formData = new FormData();
                formData.append('execute', 'true');

                const processResponse = await fetch(route('admin.media-items.scan'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                    credentials: 'same-origin',
                });

                clearInterval(progressInterval);

                if (!processResponse.ok) {
                    const errorText = await processResponse.text();
                    let errorData;
                    try {
                        errorData = JSON.parse(errorText);
                    } catch {
                        errorData = { error: errorText || `HTTP error! status: ${processResponse.status}` };
                    }
                    throw new Error(errorData.error || errorData.message || `HTTP error! status: ${processResponse.status}`);
                }

                const processData = await processResponse.json();

                if (processData.success) {
                    scanProgress.value.current = scanProgress.value.total;
                    scanProgress.value.message = `تمت معالجة ${scanProgress.value.total} ملف بنجاح!`;

                    // إزالة منع التنقل فوراً
                    window.removeEventListener('beforeunload', preventNavigation);

                    // تحديث البيانات أولاً (في الخلفية)
                    router.reload({
                        only: ['mediaItems'],
                        preserveScroll: true,
                        onSuccess: () => {
                            // إخفاء الـ dialog بعد تحديث البيانات بنجاح
                            setTimeout(() => {
                                isScanning.value = false;
                                scanModalOpen.value = false;
                                scanProgress.value = { current: 0, total: 0, message: '' };
                            }, 800); // وقت قصير لإظهار رسالة النجاح
                        },
                        onError: () => {
                            // في حالة الخطأ، إخفاء الـ dialog أيضاً
                            setTimeout(() => {
                                isScanning.value = false;
                                scanModalOpen.value = false;
                                scanProgress.value = { current: 0, total: 0, message: '' };
                            }, 2000);
                        }
                    });
                } else {
                    scanProgress.value.message = `خطأ: ${processData.error || 'حدث خطأ غير متوقع'}`;
                    setTimeout(() => {
                        window.removeEventListener('beforeunload', preventNavigation);
                        isScanning.value = false;
                        scanModalOpen.value = false;
                        scanProgress.value = { current: 0, total: 0, message: '' };
                    }, 3000);
                }
            } catch (error) {
                clearInterval(progressInterval);
                throw error;
            }
        } else {
            throw new Error('فشل في الحصول على عدد الملفات. الاستجابة: ' + JSON.stringify(initData));
        }
    } catch (error) {
        console.error('Scan error:', error);
        scanProgress.value.message = `خطأ: ${error.message || 'حدث خطأ أثناء المعالجة'}`;
        setTimeout(() => {
            window.removeEventListener('beforeunload', preventNavigation);
            isScanning.value = false;
            scanModalOpen.value = false;
        }, 3000);
    }
};

// دالة لمنع التنقل أثناء المعالجة
const preventNavigation = (e) => {
    e.preventDefault();
    e.returnValue = 'جاري معالجة الملفات. هل أنت متأكد من أنك تريد المغادرة؟';
    return e.returnValue;
};
</script>

<template>
    <Head title="مكتبة الوسائط" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">مكتبة الوسائط</h2>
                <div v-if="canScanMedia || canCreateMedia" class="flex gap-3">
                    <button
                        v-if="canScanMedia"
                        @click="startScan"
                        :disabled="isScanning"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rtl:ml-2 ltr:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>{{ isScanning ? 'جاري المسح...' : 'مسح المجلد' }}</span>
                    </button>
                    <Link
                        v-if="canCreateMedia"
                        :href="route('admin.media-items.create')"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rtl:ml-2 ltr:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>رفع ملفات جديدة</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Flash Message -->
                <div v-if="flash.success" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-md" role="alert">
                    <p class="font-bold">نجاح</p>
                    <p>{{ flash.success }}</p>
                </div>
                <div v-if="flash.error" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-md" role="alert">
                    <p class="font-bold">خطأ</p>
                    <p>{{ flash.error }}</p>
                </div>

                <!-- Info Box about folder scanning -->
                <div class="mb-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded-md shadow-sm" role="alert">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 ml-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-semibold mb-1">💡 نصيحة: رفع الملفات الكبيرة</p>
                            <p class="text-sm">يمكنك نسخ الملفات مباشرة إلى مجلد <code class="bg-blue-100 px-1 rounded">storage/app/public/media/</code> ثم الضغط على زر "مسح المجلد" لإضافتها تلقائياً.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Toolbar for selection and bulk actions -->
                        <div v-if="mediaItems.length > 0 && canDeleteMedia" class="mb-4 flex items-center justify-between bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-4">
                                <!-- Select All Checkbox -->
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        :checked="selectAllMode && selectedItems.size === mediaItems.length"
                                        @change="toggleSelectAll"
                                        :disabled="isDeleting"
                                        class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                    <span class="text-sm font-medium text-gray-700">تحديد الكل</span>
                                </label>

                                <!-- Selected Count -->
                                <div v-if="hasSelectedItems" class="text-sm text-gray-600">
                                    تم تحديد <span class="font-bold text-indigo-600">{{ selectedCount }}</span> ملف
                                </div>
                            </div>

                            <!-- Delete Selected Button -->
                            <button
                                v-if="hasSelectedItems"
                                @click="openDeleteConfirm('multiple')"
                                :disabled="isDeleting || isScanning"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg v-if="isDeleting" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span>{{ isDeleting ? 'جاري الحذف...' : `حذف المحدد (${selectedCount})` }}</span>
                            </button>
                        </div>

                        <!-- Empty State -->
                        <div v-if="mediaItems.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">لا توجد ملفات</h3>
                            <p class="mt-1 text-sm text-gray-500">ابدأ برفع بعض الصور والفيديوهات.</p>
                        </div>

                        <!-- Media Grid -->
                        <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                            <div
                                v-for="item in mediaItems"
                                :key="item.id"
                                class="group relative aspect-square rounded-lg overflow-hidden shadow-md bg-gray-200 transition-all duration-200"
                                :class="{
                                    'ring-4 ring-indigo-500 ring-offset-2': selectedItems.has(item.id),
                                    'opacity-50 pointer-events-none': isDeleting || isDeletingSingle.has(item.id) || isScanning
                                }"
                            >
                                <!-- Selection Checkbox -->
                                <div v-if="canDeleteMedia" class="absolute top-2 left-2 z-20">
                                    <label class="cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :checked="selectedItems.has(item.id)"
                                            @change="toggleSelect(item.id)"
                                            :disabled="isDeleting || isDeletingSingle.has(item.id) || isScanning"
                                            class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 bg-white shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                    </label>
                                </div>

                                <img v-if="item.type === 'image'" :src="item.url" class="w-full h-full object-cover">
                                <video v-else-if="item.type === 'video'" :src="item.url" class="w-full h-full object-cover" muted></video>
                                <div v-if="item.type === 'video'" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-20">
                                    <svg class="w-10 h-10 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>
                                </div>

                                <!-- Overlay with actions -->
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-all duration-300 flex items-center justify-center gap-2">
                                    <!-- Preview Button -->
                                    <button
                                        @click="openPreview(item)"
                                        :disabled="isDeleting || isDeletingSingle.has(item.id) || isScanning"
                                        class="opacity-0 group-hover:opacity-100 transform group-hover:scale-100 scale-90 transition-all duration-300 p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </button>
                                    <!-- Delete Single Button (only if not in selection mode) -->
                                    <button
                                        v-if="canDeleteMedia && !hasSelectedItems"
                                        @click="openDeleteConfirm('single', item.id)"
                                        :disabled="isDeleting || isDeletingSingle.has(item.id) || isScanning"
                                        class="opacity-0 group-hover:opacity-100 transform group-hover:scale-100 scale-90 transition-all duration-300 p-2 bg-red-600 text-white rounded-full hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg v-if="isDeletingSingle.has(item.id)" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showDeleteConfirm" @click.self="closeDeleteConfirm" class="fixed inset-0 z-[90] bg-black bg-opacity-50 flex items-center justify-center p-4">
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="showDeleteConfirm" class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full relative">
                        <!-- Icon -->
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>

                        <!-- Title -->
                        <h3 class="text-lg font-bold text-gray-900 text-center mb-2">
                            تأكيد الحذف
                        </h3>

                        <!-- Message -->
                        <p class="text-sm text-gray-600 text-center mb-6">
                            <span v-if="deleteConfirmData.type === 'multiple'">
                                هل أنت متأكد من حذف <span class="font-bold text-red-600">{{ deleteConfirmData.count }}</span> ملف؟
                            </span>
                            <span v-else>
                                هل أنت متأكد من حذف هذا الملف؟
                            </span>
                            <br>
                            <span class="text-xs text-gray-500 mt-2 block">لا يمكن التراجع عن هذه العملية.</span>
                        </p>

                        <!-- Actions -->
                        <div class="flex gap-3 justify-end">
                            <button
                                @click="closeDeleteConfirm"
                                :disabled="isDeleting || (deleteConfirmData.type === 'single' && isDeletingSingle.has(deleteConfirmData.itemId))"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                إلغاء
                            </button>
                            <button
                                @click="confirmDelete"
                                :disabled="isDeleting || (deleteConfirmData.type === 'single' && isDeletingSingle.has(deleteConfirmData.itemId))"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                <svg v-if="isDeleting || (deleteConfirmData.type === 'single' && isDeletingSingle.has(deleteConfirmData.itemId))" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ isDeleting || (deleteConfirmData.type === 'single' && isDeletingSingle.has(deleteConfirmData.itemId)) ? 'جاري الحذف...' : 'حذف' }}</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>

        <!-- Preview Modal -->
        <div v-if="isPreviewModalOpen" @click.self="closePreview" class="fixed inset-0 z-50 bg-black bg-opacity-80 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-2xl p-6 max-w-4xl w-full relative">
                <button @click="closePreview" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                <h3 class="text-lg font-bold text-gray-800 mb-4">معاينة الوسائط</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <!-- Landscape Preview -->
                    <div class="text-center">
                        <h4 class="font-semibold text-gray-600 mb-2">معاينة الشاشة العرضية (16:9)</h4>
                        <div class="bg-gray-800 p-2 rounded-lg shadow-inner w-full aspect-video mx-auto">
                            <img v-if="itemToPreview.type === 'image'" :src="itemToPreview.url" class="w-full h-full object-cover">
                            <video v-else :src="itemToPreview.url" class="w-full h-full object-cover" controls autoplay loop muted></video>
                        </div>
                    </div>
                    <!-- Portrait Preview -->
                    <div class="text-center">
                        <h4 class="font-semibold text-gray-600 mb-2">معاينة الشاشة الطولية (9:16)</h4>
                        <div class="bg-gray-800 p-2 rounded-lg shadow-inner h-96 aspect-[9/16] mx-auto">
                            <img v-if="itemToPreview.type === 'image'" :src="itemToPreview.url" class="w-full h-full object-cover">
                            <video v-else :src="itemToPreview.url" class="w-full h-full object-cover" controls autoplay loop muted></video>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scan Progress Modal -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="scanModalOpen" class="fixed inset-0 z-[100] bg-black bg-opacity-90 flex items-center justify-center p-4">
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="scanModalOpen" class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full relative">
                <!-- Warning Message -->
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-md">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-yellow-400 ml-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-yellow-800">تحذير مهم</p>
                            <p class="text-xs text-yellow-700 mt-1">يرجى عدم إغلاق هذه الصفحة أو الانتقال إلى صفحة أخرى حتى تكتمل عملية المعالجة.</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Content -->
                <div class="text-center">
                    <!-- Spinner -->
                    <div class="mb-6 flex justify-center">
                        <svg class="animate-spin h-16 w-16 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <!-- Progress Text -->
                    <h3 class="text-xl font-bold text-gray-800 mb-2">جاري معالجة الملفات</h3>
                    <p class="text-gray-600 mb-6">{{ scanProgress.message }}</p>

                    <!-- Progress Counter -->
                    <div v-if="scanProgress.total > 0" class="mb-6">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <span class="text-3xl font-bold text-indigo-600">{{ scanProgress.current }}</span>
                            <span class="text-xl text-gray-400">/</span>
                            <span class="text-3xl font-bold text-gray-600">{{ scanProgress.total }}</span>
                        </div>
                        <p class="text-sm text-gray-500">ملف</p>
                    </div>

                    <!-- Progress Bar -->
                    <div v-if="scanProgress.total > 0" class="w-full bg-gray-200 rounded-full h-4 mb-4 overflow-hidden">
                        <div
                            class="bg-gradient-to-r from-indigo-500 to-purple-600 h-4 rounded-full transition-all duration-300 ease-out"
                            :style="{ width: `${(scanProgress.current / scanProgress.total) * 100}%` }"
                        ></div>
                    </div>

                    <!-- Percentage -->
                    <div v-if="scanProgress.total > 0" class="text-sm font-semibold text-gray-700">
                        {{ Math.round((scanProgress.current / scanProgress.total) * 100) }}%
                    </div>
                </div>
                    </div>
                </Transition>
            </div>
        </Transition>

    </AuthenticatedLayout>
</template>

