<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    rooms: Array,
});

const { flash } = usePage().props;

const getRoomTypeLabel = (type) => {
    const labels = {
        clinic: 'عيادة',
        pharmacy: 'صيدلية',
        lab: 'مختبر',
        reception: 'استقبال',
        restroom: 'دورة مياه',
        elevator: 'مصعد',
        stairs: 'سلالم',
        other: 'أخرى',
    };
    return labels[type] || type;
};

const getRoomTypeColor = (type) => {
    const colors = {
        clinic: 'bg-purple-100 text-purple-700',
        pharmacy: 'bg-green-100 text-green-700',
        lab: 'bg-blue-100 text-blue-700',
        reception: 'bg-orange-100 text-orange-700',
        restroom: 'bg-gray-100 text-gray-700',
        elevator: 'bg-red-100 text-red-700',
        stairs: 'bg-amber-100 text-amber-700',
        other: 'bg-gray-100 text-gray-700',
    };
    return colors[type] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head title="إدارة الغرف" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">إدارة الغرف</h2>
                    <p class="mt-1 text-sm text-gray-600">إدارة غرف وعيادات الطوابق</p>
                </div>
                <Link 
                    :href="route('admin.rooms.create')" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 hover:shadow-xl"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span>إضافة غرفة جديدة</span>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash Messages -->
            <div v-if="flash.success" class="bg-green-50 border-r-4 border-green-500 text-green-800 p-4 rounded-lg shadow-sm">
                <p class="font-medium">{{ flash.success }}</p>
            </div>
            <div v-if="flash.error" class="bg-red-50 border-r-4 border-red-500 text-red-800 p-4 rounded-lg shadow-sm">
                <p class="font-medium">{{ flash.error }}</p>
            </div>

            <!-- Rooms Table -->
            <div v-if="rooms.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الغرفة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النوع</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الطابق</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الموقع</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="room in rooms" :key="room.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ room.name }}</div>
                                    <div v-if="room.room_number" class="text-sm text-gray-500">#{{ room.room_number }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full', getRoomTypeColor(room.room_type)]">
                                    {{ getRoomTypeLabel(room.room_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ room.floor.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span v-if="room.map_x !== null && room.map_y !== null">
                                    ({{ room.map_x }}, {{ room.map_y }})
                                </span>
                                <span v-else class="text-gray-400">غير محدد</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="room.is_active ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                                    {{ room.is_active ? 'نشطة' : 'غير نشطة' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="route('admin.rooms.edit', room.id)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        تعديل
                                    </Link>
                                    <Link
                                        :href="route('admin.rooms.destroy', room.id)"
                                        method="delete"
                                        as="button"
                                        class="text-red-600 hover:text-red-900"
                                        onclick="return confirm('هل أنت متأكد من حذف هذه الغرفة؟')"
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">لا توجد غرف</h3>
                    <p class="text-gray-500 mb-6">ابدأ بإنشاء غرفة جديدة</p>
                    <Link 
                        :href="route('admin.rooms.create')" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        إنشاء غرفة جديدة
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

