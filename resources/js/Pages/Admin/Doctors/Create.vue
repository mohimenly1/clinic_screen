<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    departments: Array,
});

const form = useForm({
    name: '',
    department_id: '',
    photo: null,
});

const photoPreview = ref(null);

const updatePhotoPreview = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    form.photo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const submit = () => {
    form.post(route('admin.doctors.store'));
};
</script>

<template>
    <Head title="إضافة طبيب جديد" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">إضافة طبيب جديد</h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label for="name">اسم الطبيب</label>
                            <input id="name" type="text" v-model="form.name" class="mt-1 block w-full" required>
                            <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
                        </div>
                        <div>
                            <label for="department_id">القسم</label>
                            <select id="department_id" v-model="form.department_id" class="mt-1 block w-full" required>
                                <option value="" disabled>اختر قسماً</option>
                                <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                            </select>
                            <div v-if="form.errors.department_id" class="text-sm text-red-500">{{ form.errors.department_id }}</div>
                        </div>
                        <div>
                            <label for="photo">صورة الطبيب</label>
                            <input id="photo" type="file" @change="updatePhotoPreview" class="mt-1 block w-full">
                             <div v-if="photoPreview" class="mt-4">
                                <img :src="photoPreview" class="w-24 h-24 rounded-full object-cover">
                            </div>
                            <div v-if="form.errors.photo" class="text-sm text-red-500">{{ form.errors.photo }}</div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-gray-800 text-white rounded-md">حفظ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
