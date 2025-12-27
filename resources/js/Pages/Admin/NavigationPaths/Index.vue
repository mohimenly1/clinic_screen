<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    paths: Array,
});

const { flash } = usePage().props;

const formatTime = (seconds) => {
    if (!seconds) return '-';
    if (seconds < 60) return `${seconds} ثانية`;
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return secs > 0 ? `${minutes} د ${secs} ث` : `${minutes} دقيقة`;
};
</script>

<template>
    <Head title="مسارات التنقل" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">مسارات التنقل</h2>
                    <p class="mt-1 text-sm text-gray-600">إدارة مسارات التنقل بين الغرف</p>
                </div>
                <Link 
                    :href="route('admin.navigation-paths.create')" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 hover:shadow-xl"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span>إضافة مسار جديد</span>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash Messages -->
            <div v-if="flash.success" class="bg-green-50 border-r-4 border-green-500 text-green-800 p-4 rounded-lg shadow-sm">
                <p class="font-medium">{{ flash.success }}</p>
            </div>

            <!-- Paths Table -->
            <div v-if="paths.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">من</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إلى</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التعليمات</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الوقت/المسافة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="path in paths" :key="path.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ path.from_room.name }}</div>
                                <div class="text-sm text-gray-500">{{ path.from_room.floor_name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ path.to_room.name }}</div>
                                <div class="text-sm text-gray-500">{{ path.to_room.floor_name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-xs truncate">{{ path.directions }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div v-if="path.estimated_time_seconds">{{ formatTime(path.estimated_time_seconds) }}</div>
                                <div v-if="path.distance_meters">{{ path.distance_meters }} م</div>
                                <span v-if="!path.estimated_time_seconds && !path.distance_meters" class="text-gray-400">-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="route('admin.navigation-paths.edit', path.id)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        تعديل
                                    </Link>
                                    <Link
                                        :href="route('admin.navigation-paths.destroy', path.id)"
                                        method="delete"
                                        as="button"
                                        class="text-red-600 hover:text-red-900"
                                        onclick="return confirm('هل أنت متأكد من حذف هذا المسار؟')"
                                    >
                                        حذف
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">لا توجد مسارات</h3>
                    <p class="text-gray-500 mb-6">ابدأ بإنشاء مسار جديد للتنقل بين الغرف</p>
                    <Link 
                        :href="route('admin.navigation-paths.create')" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        إنشاء مسار جديد
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

