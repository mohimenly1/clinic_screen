<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    playlist: Object,
    mediaItems: Array,
    playlistMediaIds: Array,
});

const form = useForm({
    name: props.playlist.name,
    media_items: props.playlistMediaIds, // ابدأ بتحديد العناصر الموجودة بالفعل
});

const submit = () => {
    form.put(route('admin.playlists.update', props.playlist.id));
};
</script>

<template>
    <Head :title="`تعديل: ${playlist.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">تعديل قائمة التشغيل: {{ playlist.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <!-- حقل اسم القائمة -->
                        <div>
                            <label for="name" class="block font-medium text-sm text-gray-700">اسم القائمة</label>
                            <input
                                type="text"
                                v-model="form.name"
                                id="name"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                required
                            />
                             <div v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</div>
                        </div>

                        <!-- اختيار الوسائط -->
                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-gray-900">اختر الوسائط لهذه القائمة</h3>
                            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                <div v-for="item in mediaItems" :key="item.id" class="relative">
                                    <label :for="`media_${item.id}`" class="cursor-pointer">
                                        <div class="border-2 rounded-lg overflow-hidden" :class="{'border-indigo-500': form.media_items.includes(item.id)}">
                                            <img v-if="item.file_type === 'image'" :src="item.url" class="w-full h-32 object-cover">
                                            <video v-if="item.file_type === 'video'" :src="item.url" class="w-full h-32 object-cover" muted loop></video>
                                        </div>
                                        <input
                                            type="checkbox"
                                            :id="`media_${item.id}`"
                                            :value="item.id"
                                            v-model="form.media_items"
                                            class="sr-only"
                                        >
                                    </label>
                                </div>
                            </div>
                            <div v-if="form.errors.media_items" class="text-sm text-red-600 mt-2">{{ form.errors.media_items }}</div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-gray-800 text-white rounded-md">
                                حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
