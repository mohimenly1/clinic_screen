<template>
    <Head :title="`صور ${room.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">صور {{ room.name }}</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        الطابق: {{ room.floor.name }} | رقم الغرفة: {{ room.room_number || 'غير محدد' }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link 
                        :href="route('admin.rooms.index')" 
                        class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        ← العودة
                    </Link>
                    <Link 
                        :href="route('admin.rooms.images.create', room.id)" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        إضافة صورة
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="bg-green-50 border-r-4 border-green-500 text-green-800 p-4 rounded-lg shadow-sm">
                <p class="font-medium">{{ $page.props.flash.success }}</p>
            </div>

            <!-- Images Grid -->
            <div v-if="images.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="(image, index) in images"
                    :key="image.id"
                    class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300"
                >
                    <div class="relative">
                        <img
                            :src="image.image_url"
                            :alt="image.description || `صورة ${index + 1}`"
                            class="w-full h-64 object-cover"
                        />
                        <div class="absolute top-2 right-2">
                            <span class="bg-purple-600 text-white px-3 py-1 rounded-full text-xs font-bold">
                                {{ index + 1 }}
                            </span>
                        </div>
                    </div>
                    <div class="p-4">
                        <p v-if="image.description" class="text-gray-700 mb-2 font-medium">
                            {{ image.description }}
                        </p>
                        <p v-if="image.ar_instructions" class="text-sm text-purple-600 mb-3">
                            📍 {{ image.ar_instructions }}
                        </p>
                        <div class="flex items-center justify-between">
                            <span :class="image.is_active ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                                {{ image.is_active ? 'نشطة' : 'غير نشطة' }}
                            </span>
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="route('admin.rooms.images.edit', [room.id, image.id])"
                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                >
                                    تعديل
                                </Link>
                                <Link
                                    :href="route('admin.rooms.images.destroy', [room.id, image.id])"
                                    method="delete"
                                    as="button"
                                    class="text-red-600 hover:text-red-900 text-sm font-medium"
                                    onclick="return confirm('هل أنت متأكد من حذف هذه الصورة؟')"
                                >
                                    حذف
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">لا توجد صور</h3>
                    <p class="text-gray-500 mb-6">ابدأ بإضافة صور للواقع المعزز لهذه الغرفة</p>
                    <Link 
                        :href="route('admin.rooms.images.create', room.id)" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        إضافة صورة
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    room: Object,
    images: Array,
});
</script>

