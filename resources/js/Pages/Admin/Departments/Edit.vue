<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    department: Object
});

const form = useForm({
    name: props.department.name,
});

const submit = () => {
    form.put(route('admin.departments.update', props.department.id));
};
</script>

<template>
    <Head title="تعديل القسم" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">تعديل القسم</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">اسم القسم</label>
                                <input v-model="form.name" type="text" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <div v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-gray-800 text-white rounded-md">
                                    تحديث
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
