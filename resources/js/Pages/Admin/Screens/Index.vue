<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    screens: Object,
});

const { flash } = usePage().props;
</script>

<template>
    <Head title="إدارة الشاشات" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">إدارة الشاشات</h2>
                <Link :href="route('admin.screens.create')" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span>إضافة شاشة جديدة</span>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                 <div v-if="flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                    <span class="block sm:inline">{{ flash.success }}</span>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكود</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الاتجاه</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="screen in screens" :key="screen.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ screen.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ screen.screen_code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ screen.orientation === 'landscape' ? 'عرضي' : 'طولي' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                            <div class="flex items-center justify-start gap-x-4">
                                                <Link :href="route('admin.screens.edit', screen.id)" class="px-3 py-1 text-sm font-semibold text-sky-600 border border-sky-500 rounded-md hover:bg-sky-50 transition-colors">تعديل</Link>
                                                <Link
                                                    :href="route('admin.screens.destroy', screen.id)"
                                                    method="delete"
                                                    as="button"
                                                    class="px-3 py-1 text-sm font-semibold text-red-600 border border-red-500 rounded-md hover:bg-red-50 transition-colors"
                                                    onclick="return confirm('هل أنت متأكد من الحذف؟')"
                                                >
                                                    حذف
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="screens.length === 0">
                                        <td class="text-center py-12 text-gray-500" colspan="4">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium text-gray-900">لا توجد شاشات</h3>
                                            <p class="mt-1 text-sm text-gray-500">ابدأ بإضافة شاشتك الأولى.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

