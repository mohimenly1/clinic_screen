<template>
    <Head :title="`تعديل صورة - ${room.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">تعديل الصورة</h2>
                    <p class="mt-1 text-sm text-gray-600">الغرفة: {{ room.name }}</p>
                </div>
                <Link 
                    :href="route('admin.rooms.images.index', room.id)" 
                    class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    ← العودة
                </Link>
            </div>
        </template>

        <div class="max-w-3xl mx-auto">
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-lg p-6 space-y-6">
                <!-- Current Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        الصورة الحالية
                    </label>
                    <img :src="image.image_url" alt="Current" class="max-w-full h-64 object-contain rounded-lg border border-gray-300" />
                </div>

                <!-- New Image Upload (Optional) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        استبدال الصورة (اختياري)
                    </label>
                    <input
                        type="file"
                        @change="handleFileChange"
                        accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    />
                    <p class="mt-1 text-sm text-gray-500">اتركه فارغاً للاحتفاظ بالصورة الحالية</p>
                    <div v-if="preview" class="mt-4">
                        <img :src="preview" alt="Preview" class="max-w-full h-64 object-contain rounded-lg border border-gray-300" />
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        الوصف
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="وصف الصورة (سيظهر على الصورة في الواقع المعزز)"
                    ></textarea>
                </div>

                <!-- AR Instructions -->
                <div>
                    <label for="ar_instructions" class="block text-sm font-medium text-gray-700 mb-2">
                        تعليمات التنقل
                    </label>
                    <input
                        id="ar_instructions"
                        v-model="form.ar_instructions"
                        type="text"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="مثال: اتجه يميناً، أو اصعد السلالم"
                    />
                </div>

                <!-- Display Order -->
                <div>
                    <label for="display_order" class="block text-sm font-medium text-gray-700 mb-2">
                        ترتيب العرض
                    </label>
                    <input
                        id="display_order"
                        v-model.number="form.display_order"
                        type="number"
                        min="0"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <!-- Is Active -->
                <div class="flex items-center">
                    <input
                        id="is_active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    />
                    <label for="is_active" class="mr-2 block text-sm text-gray-700">
                        تفعيل الصورة
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <Link
                        :href="route('admin.rooms.images.index', room.id)"
                        class="px-6 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        إلغاء
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 disabled:opacity-50"
                    >
                        <span v-if="form.processing">جاري الحفظ...</span>
                        <span v-else>حفظ التغييرات</span>
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    room: Object,
    image: Object,
});

const preview = ref(null);

const form = useForm({
    image: null,
    description: props.image.description || '',
    ar_instructions: props.image.ar_instructions || '',
    display_order: props.image.display_order,
    is_active: props.image.is_active,
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.put(route('admin.rooms.images.update', [props.room.id, props.image.id]), {
        forceFormData: true,
    });
};
</script>

