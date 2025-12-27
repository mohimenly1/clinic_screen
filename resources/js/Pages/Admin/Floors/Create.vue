<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    floor_number: 0,
    map_image: null,
    description: '',
    display_order: 0,
});

const submit = () => {
    form.post(route('admin.floors.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="إضافة طابق جديد" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">إضافة طابق جديد</h2>
                    <p class="mt-1 text-sm text-gray-600">إنشاء طابق جديد مع خريطة الطابق</p>
                </div>
            </div>
        </template>

        <div class="max-w-2xl">
            <div class="bg-white shadow rounded-lg p-6">
                <form @submit.prevent="submit">
                    <!-- Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            اسم الطابق <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="مثال: الطابق الأول"
                        />
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                    </div>

                    <!-- Floor Number -->
                    <div class="mb-6">
                        <label for="floor_number" class="block text-sm font-medium text-gray-700 mb-2">
                            رقم الطابق <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="floor_number"
                            v-model.number="form.floor_number"
                            type="number"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="0 للأرضي، 1 للأول، -1 للبدروم"
                        />
                        <p class="mt-1 text-sm text-gray-500">0 للأرضي، الأرقام الموجبة للطوابق العلوية، السالبة للبدروم</p>
                        <div v-if="form.errors.floor_number" class="mt-1 text-sm text-red-600">{{ form.errors.floor_number }}</div>
                    </div>

                    <!-- Map Image -->
                    <div class="mb-6">
                        <label for="map_image" class="block text-sm font-medium text-gray-700 mb-2">
                            صورة خريطة الطابق
                        </label>
                        <input
                            id="map_image"
                            type="file"
                            accept="image/jpeg,image/png,image/jpg"
                            @input="form.map_image = $event.target.files[0]"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                        <p class="mt-1 text-sm text-gray-500">صورة PNG أو JPG تمثل خريطة الطابق</p>
                        <div v-if="form.errors.map_image" class="mt-1 text-sm text-red-600">{{ form.errors.map_image }}</div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            الوصف
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="وصف الطابق (اختياري)"
                        ></textarea>
                        <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
                    </div>

                    <!-- Display Order -->
                    <div class="mb-6">
                        <label for="display_order" class="block text-sm font-medium text-gray-700 mb-2">
                            ترتيب العرض
                        </label>
                        <input
                            id="display_order"
                            v-model.number="form.display_order"
                            type="number"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="0"
                        />
                        <p class="mt-1 text-sm text-gray-500">يستخدم لترتيب الطوابق في القائمة</p>
                        <div v-if="form.errors.display_order" class="mt-1 text-sm text-red-600">{{ form.errors.display_order }}</div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4">
                        <Link
                            :href="route('admin.floors.index')"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            إلغاء
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50"
                        >
                            {{ form.processing ? 'جاري الحفظ...' : 'حفظ' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

