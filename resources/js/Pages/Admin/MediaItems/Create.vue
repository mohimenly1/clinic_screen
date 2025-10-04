<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    files: [],
});

const fileInput = ref(null);

const submit = () => {
    form.post(route('admin.media-items.store'), {
        onSuccess: () => {
            form.reset();
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};
</script>

<template>
    <Head title="رفع ملفات جديدة" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">رفع ملفات جديدة</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div>
                                <!-- **تحديث**: تم تحديث النص ليشمل الصوتيات -->
                                <label for="files" class="block font-medium text-sm text-gray-700">اختر الملفات (صور، فيديوهات، أو صوتيات)</label>
                                <input
                                    type="file"
                                    @input="form.files = $event.target.files"
                                    id="files"
                                    ref="fileInput"
                                    class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                                    required
                                    multiple
                                >
                                <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="mt-2 w-full">
                                    {{ form.progress.percentage }}%
                                </progress>
                                <div v-if="form.errors.files" class="text-sm text-red-600 mt-2">{{ form.errors.files }}</div>
                            </div>

                            <div v-if="form.files.length > 0" class="mt-4 border-t pt-4">
                                <h3 class="font-medium text-gray-800">الملفات المختارة:</h3>
                                <ul class="mt-2 list-disc list-inside text-sm text-gray-600">
                                    <li v-for="file in form.files" :key="file.name">{{ file.name }} ({{ (file.size / 1024 / 1024).toFixed(2) }} MB)</li>
                                </ul>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <button type="submit" :disabled="form.processing || form.files.length === 0" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    رفع {{ form.files.length }} ملف
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

