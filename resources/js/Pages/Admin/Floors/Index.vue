<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    floors: Array,
});

const { flash } = usePage().props;
</script>

<template>
    <Head title="إدارة الطوابق" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">إدارة الطوابق</h2>
                    <p class="mt-1 text-sm text-gray-600">إدارة طوابق العيادة والخرائط</p>
                </div>
                <Link 
                    :href="route('admin.floors.create')" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 hover:shadow-xl"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span>إضافة طابق جديد</span>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash Message -->
            <div v-if="flash.success" class="bg-green-50 border-r-4 border-green-500 text-green-800 p-4 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 ml-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-medium">{{ flash.success }}</p>
                </div>
            </div>

            <div v-if="flash.error" class="bg-red-50 border-r-4 border-red-500 text-red-800 p-4 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 ml-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-medium">{{ flash.error }}</p>
                </div>
            </div>

            <!-- Floors Grid -->
            <div v-if="floors.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="floor in floors" 
                    :key="floor.id"
                    class="group bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-200 overflow-hidden"
                >
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-white mb-2">{{ floor.name }}</h3>
                                <div class="flex items-center gap-4 text-white/90 text-sm">
                                    <div class="flex items-center gap-1">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        <span class="font-semibold">{{ floor.rooms_count }}</span>
                                        <span>غرفة</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="font-semibold">#</span>
                                        <span>{{ floor.floor_number }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-2">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <div v-if="floor.map_image_url" class="mb-4">
                            <img :src="floor.map_image_url" :alt="floor.name" class="w-full h-32 object-cover rounded-lg">
                        </div>
                        <p v-if="floor.description" class="text-sm text-gray-600 mb-4">{{ floor.description }}</p>
                        
                        <!-- Footer Actions -->
                        <div class="flex items-center gap-2">
                            <Link 
                                :href="route('admin.floors.edit', floor.id)" 
                                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                تعديل
                            </Link>
                            <Link
                                :href="route('admin.floors.destroy', floor.id)"
                                method="delete"
                                as="button"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors"
                                onclick="return confirm('هل أنت متأكد من حذف الطابق؟ سيتم حذف جميع الغرف المرتبطة به.')"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">لا توجد طوابق</h3>
                    <p class="text-gray-500 mb-6">ابدأ بإنشاء طابق جديد لإدارة الخريطة</p>
                    <Link 
                        :href="route('admin.floors.create')" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        إنشاء طابق جديد
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

