<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    visualMediaItems: Array,
    activeBroadcastId: Number,
});

const { flash } = usePage().props;

const selectedMediaId = ref(null);
const form = useForm({});

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
                         <button @click="stopBroadcast" :disabled="form.processing" class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                             إيقاف البث
                         </button>
                    </div>
                </div>

                <!-- اختيار المحتوى وبدء البث -->
                <div v-else class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-medium text-gray-900">1. اختر المحتوى المراد بثه</h3>
                    <div class="mt-4 grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3 max-h-80 overflow-y-auto p-2 border rounded-md">
                         <div v-for="item in visualMediaItems" :key="item.id" @click="selectedMediaId = item.id"
                             class="relative aspect-square rounded-md overflow-hidden cursor-pointer"
                             :class="{'ring-4 ring-indigo-500 ring-offset-2': selectedMediaId === item.id}">
                             <img v-if="item.type === 'image'" :src="item.url" class="w-full h-full object-cover">
                             <video v-else :src="item.url" class="w-full h-full object-cover" muted></video>
                              <div v-if="selectedMediaId === item.id" class="absolute inset-0 bg-indigo-500/60 flex items-center justify-center">
                                  <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                              </div>
                         </div>
                    </div>
                    <div class="mt-6 border-t pt-6">
                         <h3 class="text-lg font-medium text-gray-900">2. ابدأ البث لجميع الشاشات</h3>
                         <button @click="startBroadcast" :disabled="form.processing || !selectedMediaId" class="mt-4 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                             بدء البث الآن
                         </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

