<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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

// دالة للحذف الجماعي
const deleteSelected = () => {
    if (selectedItems.value.size === 0 || isDeleting.value) return;

    if (!confirm(`هل أنت متأكد من حذف ${selectedItems.value.size} ملف؟`)) {
        return;
    }

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
        },
        onError: () => {
            isDeleting.value = false;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};

// computed للتحقق من وجود عناصر محددة
const hasSelectedItems = computed(() => selectedItems.value.size > 0);
const selectedCount = computed(() => selectedItems.value.size);
</script>

<template>
    <Head title="مكتبة الوسائط" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">مكتبة الوسائط</h2>
                <div v-if="canScanMedia || canCreateMedia" class="flex gap-3">
                    <Link
                        v-if="canScanMedia"
                        :href="route('admin.media-items.scan')"
                        method="post"
                        as="button"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rtl:ml-2 ltr:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>مسح المجلد</span>
                    </Link>
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
                                @click="deleteSelected"
                                :disabled="isDeleting"
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
                                    'opacity-50 pointer-events-none': isDeleting || isDeletingSingle.has(item.id)
                                }"
                            >
                                <!-- Selection Checkbox -->
                                <div v-if="canDeleteMedia" class="absolute top-2 left-2 z-20">
                                    <label class="cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :checked="selectedItems.has(item.id)"
                                            @change="toggleSelect(item.id)"
                                            :disabled="isDeleting || isDeletingSingle.has(item.id)"
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
                                        :disabled="isDeleting || isDeletingSingle.has(item.id)"
                                        class="opacity-0 group-hover:opacity-100 transform group-hover:scale-100 scale-90 transition-all duration-300 p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </button>
                                    <!-- Delete Single Button (only if not in selection mode) -->
                                    <Link
                                        v-if="canDeleteMedia && !hasSelectedItems"
                                        :href="route('admin.media-items.destroy', item.id)"
                                        method="delete"
                                        as="button"
                                        :disabled="isDeleting || isDeletingSingle.has(item.id)"
                                        @click="isDeletingSingle.add(item.id)"
                                        preserve-state
                                        preserve-scroll
                                        @finish="isDeletingSingle.delete(item.id)"
                                        class="opacity-0 group-hover:opacity-100 transform group-hover:scale-100 scale-90 transition-all duration-300 p-2 bg-red-600 text-white rounded-full hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                        onclick="if(!confirm('هل أنت متأكد؟')) { return false; }"
                                    >
                                        <svg v-if="isDeletingSingle.has(item.id)" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

    </AuthenticatedLayout>
</template>

