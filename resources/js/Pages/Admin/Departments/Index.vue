<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    departments: Array,
});

const { flash } = usePage().props;
</script>

<template>
    <Head title="الأقسام الطبية" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">الأقسام الطبية</h2>
                <Link :href="route('admin.departments.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <span>إضافة قسم جديد</span>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="flash.success" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-md" role="alert">
                    <p>{{ flash.success }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">اسم القسم</th>
                                <th scope="col" class="px-6 py-3 text-left">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="department in departments" :key="department.id" class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ department.name }}</td>
                                <td class="px-6 py-4 text-left">
                                    <Link :href="route('admin.departments.edit', department.id)" class="font-medium text-blue-600 hover:underline">تعديل</Link>
                                    <Link :href="route('admin.departments.destroy', department.id)" method="delete" as="button" class="font-medium text-red-600 hover:underline ltr:ml-4 rtl:mr-4" onclick="return confirm('هل أنت متأكد؟')">حذف</Link>
                                </td>
                            </tr>
                             <tr v-if="departments.length === 0">
                                <td class="px-6 py-4 text-center text-gray-500" colspan="2">لا توجد أقسام بعد.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
