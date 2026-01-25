<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { hasPermission } from '@/utils/permissions';

const props = defineProps({
    visualMediaItems: Array,
    activeBroadcastId: Number,
});

const { flash } = usePage().props;

// الصلاحيات
const canManageBroadcast = computed(() => hasPermission('manage_broadcast'));

const selectedMediaId = ref(null);
const form = useForm({});

// متغيرات للتحكم في نافذة المعاينة
const isPreviewModalOpen = ref(false);
const itemToPreview = ref(null);

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

const startBroadcast = () => {
    if (!selectedMediaId.value) {
        alert('الرجاء اختيار ملف لبثه أولاً.');
        return;
    }
    form.post(route('admin.broadcast.store', { media_item_id: selectedMediaId.value }));
};

const stopBroadcast = () => {
    form.delete(route('admin.broadcast.destroy'));
};

const isBroadcasting = computed(() => props.activeBroadcastId !== null);
const currentlyBroadcastingItem = computed(() => {
    if (!isBroadcasting.value) return null;
    return props.visualMediaItems.find(item => item.id === props.activeBroadcastId);
});

</script>

<template>
    <Head title="البث العام" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">البث العام (تعميم محتوى موحد)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                 <div v-if="flash.success" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-md" role="alert">
                    <p>{{ flash.success }}</p>
                </div>

                <!-- حالة البث النشط -->
                <div v-if="isBroadcasting" class="bg-blue-100 border-l-4 border-blue-500 text-blue-800 p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-bold">بث مباشر نشط حاليًا</h3>
                    <p class="mt-2">يتم حاليًا عرض المحتوى التالي على جميع الشاشات. سيتجاوز هذا البث أي محتوى مجدول.</p>
                    <div class="mt-4 flex items-center gap-6">
                         <div class="w-32 h-32 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                             <img v-if="currentlyBroadcastingItem.type === 'image'" :src="currentlyBroadcastingItem.url" class="w-full h-full object-cover">
                             <video v-else :src="currentlyBroadcastingItem.url" class="w-full h-full object-cover" muted></video>
                         </div>
                         <button
                             v-if="canManageBroadcast"
                             @click="stopBroadcast"
                             :disabled="form.processing"
                             class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition"
                         >
                             إيقاف البث
                         </button>
                    </div>
                </div>

                <!-- اختيار المحتوى وبدء البث -->
                <div v-else class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-medium text-gray-900">1. اختر المحتوى المراد بثه</h3>
                    <div class="mt-4 grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3 max-h-80 overflow-y-auto p-2 border rounded-md">
                         <div v-for="item in visualMediaItems" :key="item.id"
                             class="group relative aspect-square rounded-md overflow-hidden cursor-pointer"
                             :class="{'ring-4 ring-indigo-500 ring-offset-2': selectedMediaId === item.id}">
                             <img v-if="item.type === 'image'" :src="item.url" class="w-full h-full object-cover">
                             <video v-else :src="item.url" class="w-full h-full object-cover" muted></video>

                             <!-- أيقونة الفيديو -->
                             <div v-if="item.type === 'video'" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-20">
                                 <svg class="w-10 h-10 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>
                             </div>

                             <!-- Overlay مع الأزرار عند hover -->
                             <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-all duration-300 flex items-center justify-center gap-2">
                                 <!-- زر المعاينة -->
                                 <button @click.stop="openPreview(item)" class="opacity-0 group-hover:opacity-100 transform group-hover:scale-100 scale-90 transition-all duration-300 p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                 </button>
                                 <!-- زر الاختيار -->
                                 <button @click.stop="selectedMediaId = item.id" class="opacity-0 group-hover:opacity-100 transform group-hover:scale-100 scale-90 transition-all duration-300 p-2 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                     <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                 </button>
                             </div>

                             <!-- علامة الاختيار عند التحديد -->
                             <div v-if="selectedMediaId === item.id" class="absolute inset-0 bg-indigo-500/60 flex items-center justify-center pointer-events-none">
                                 <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                             </div>
                         </div>
                    </div>
                    <div class="mt-6 border-t pt-6">
                         <h3 class="text-lg font-medium text-gray-900">2. ابدأ البث لجميع الشاشات</h3>
                         <button
                             v-if="canManageBroadcast"
                             @click="startBroadcast"
                             :disabled="form.processing || !selectedMediaId"
                             class="mt-4 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                         >
                             بدء البث الآن
                         </button>
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
                            <img v-if="itemToPreview && itemToPreview.type === 'image'" :src="itemToPreview.url" class="w-full h-full object-cover">
                            <video v-else-if="itemToPreview && itemToPreview.type === 'video'" :src="itemToPreview.url" class="w-full h-full object-cover" controls autoplay loop muted></video>
                        </div>
                    </div>
                    <!-- Portrait Preview -->
                    <div class="text-center">
                        <h4 class="font-semibold text-gray-600 mb-2">معاينة الشاشة الطولية (9:16)</h4>
                        <div class="bg-gray-800 p-2 rounded-lg shadow-inner h-96 aspect-[9/16] mx-auto">
                            <img v-if="itemToPreview && itemToPreview.type === 'image'" :src="itemToPreview.url" class="w-full h-full object-cover">
                            <video v-else-if="itemToPreview && itemToPreview.type === 'video'" :src="itemToPreview.url" class="w-full h-full object-cover" controls autoplay loop muted></video>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

