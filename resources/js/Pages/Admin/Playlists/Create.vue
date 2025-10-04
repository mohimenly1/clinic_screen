<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
});

const submit = () => {
    form.post(route('admin.playlists.store'));
};
</script>

<template>
    <Head title="إنشاء قائمة تشغيل" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">إنشاء قائمة تشغيل جديدة</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">اسم القائمة</label>
                                <input
                                    type="text"
                                    v-model="form.name"
                                    id="name"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                <div v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-gray-800 text-white rounded-md">
                                    إنشاء
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
