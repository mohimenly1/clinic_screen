<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    name: '',
    screen_code: '',
    orientation: 'landscape',
    resolution: '',
});

const submit = () => {
    form.post(route('admin.screens.store'));
};
</script>

<template>
    <Head title="إضافة شاشة جديدة" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">إضافة شاشة جديدة</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="name" value="اسم الشاشة" />
                                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="screen_code" value="كود الشاشة (مثال: 101)" />
                                <TextInput id="screen_code" type="text" class="mt-1 block w-full" v-model="form.screen_code" required />
                                <InputError class="mt-2" :message="form.errors.screen_code" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="orientation" value="اتجاه الشاشة" />
                                <select id="orientation" v-model="form.orientation" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="landscape">عرضي</option>
                                    <option value="portrait">طولي</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.orientation" />
                            </div>
                            
                            <div class="mt-4">
                                <InputLabel for="resolution" value="الدقة (اختياري، مثال: 1920x1080)" />
                                <TextInput id="resolution" type="text" class="mt-1 block w-full" v-model="form.resolution" />
                                <InputError class="mt-2" :message="form.errors.resolution" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    حفظ
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
