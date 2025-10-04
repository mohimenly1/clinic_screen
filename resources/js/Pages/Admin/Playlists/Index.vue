<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    playlists: Array,
});

const flash = usePage().props.flash;
</script>

<template>
    <Head title="قوائم التشغيل" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">قوائم التشغيل</h2>
                <Link :href="route('admin.playlists.create')" class="px-4 py-2 bg-gray-800 text-white rounded-md">
                    إنشاء قائمة جديدة
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                 <div v-if="flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ flash.success }}</span>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم</th>
                                    <th scope="col" class="relative px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="playlist in playlists" :key="playlist.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ playlist.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                        <Link :href="route('admin.playlists.edit', playlist.id)" class="text-indigo-600 hover:text-indigo-900">تعديل</Link>
                                        <Link
                                            :href="route('admin.playlists.destroy', playlist.id)"
                                            method="delete"
                                            as="button"
                                            class="text-red-600 hover:text-red-900 ml-4"
                                            onclick="return confirm('هل أنت متأكد؟')"
                                        >حذف</Link>
                                    </td>
                                </tr>
                                <tr v-if="playlists.length === 0">
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500" colspan="2">لا توجد قوائم تشغيل بعد.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
