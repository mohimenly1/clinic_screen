<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    doctors: Array,
});

const { flash } = usePage().props;
</script>

<template>
    <Head title="الأطباء" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">إدارة الأطباء</h2>
                <Link :href="route('admin.doctors.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <span>إضافة طبيب جديد</span>
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
                                <th scope="col" class="px-6 py-3">الصورة</th>
                                <th scope="col" class="px-6 py-3">اسم الطبيب</th>
                                <th scope="col" class="px-6 py-3">القسم</th>
                                <th scope="col" class="px-6 py-3 text-left">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="doctor in doctors" :key="doctor.id" class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <img :src="doctor.photo_url || 'https://placehold.co/40x40/EFEFEF/AAAAAA?text=No+Image'" alt="Doctor Photo" class="w-10 h-10 rounded-full object-cover">
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ doctor.name }}</td>
                                <td class="px-6 py-4">{{ doctor.department_name }}</td>
                                <td class="px-6 py-4 text-left">
                                    <Link :href="route('admin.doctors.edit', doctor.id)" class="font-medium text-blue-600 hover:underline">تعديل</Link>
                                    <Link :href="route('admin.doctors.destroy', doctor.id)" method="delete" as="button" class="font-medium text-red-600 hover:underline ltr:ml-4 rtl:mr-4" onclick="return confirm('هل أنت متأكد؟')">حذف</Link>
                                </td>
                            </tr>
                            <tr v-if="doctors.length === 0">
                                <td class="px-6 py-4 text-center text-gray-500" colspan="4">لا يوجد أطباء بعد.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
