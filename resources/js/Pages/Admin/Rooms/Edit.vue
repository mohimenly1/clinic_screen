<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    room: Object,
    floors: Array,
});

const form = useForm({
    floor_id: props.room.floor_id,
    name: props.room.name,
    room_number: props.room.room_number || '',
    room_type: props.room.room_type,
    map_x: props.room.map_x,
    map_y: props.room.map_y,
    description: props.room.description || '',
    is_active: props.room.is_active,
});

const roomTypes = [
    { value: 'clinic', label: 'عيادة' },
    { value: 'pharmacy', label: 'صيدلية' },
    { value: 'lab', label: 'مختبر' },
    { value: 'reception', label: 'استقبال' },
    { value: 'restroom', label: 'دورة مياه' },
    { value: 'elevator', label: 'مصعد' },
    { value: 'stairs', label: 'سلالم' },
    { value: 'other', label: 'أخرى' },
];

const submit = () => {
    form.put(route('admin.rooms.update', props.room.id));
};
</script>

<template>
    <Head title="تعديل الغرفة" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">تعديل الغرفة</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ room.name }}</p>
                </div>
            </div>
        </template>

        <div class="max-w-2xl">
            <div class="bg-white shadow rounded-lg p-6">
                <form @submit.prevent="submit">
                    <!-- Floor -->
                    <div class="mb-6">
                        <label for="floor_id" class="block text-sm font-medium text-gray-700 mb-2">
                            الطابق <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="floor_id"
                            v-model="form.floor_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">اختر الطابق</option>
                            <option v-for="floor in floors" :key="floor.id" :value="floor.id">
                                {{ floor.name }} ({{ floor.floor_number }})
                            </option>
                        </select>
                        <div v-if="form.errors.floor_id" class="mt-1 text-sm text-red-600">{{ form.errors.floor_id }}</div>
                    </div>

                    <!-- Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            اسم الغرفة <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                    </div>

                    <!-- Room Number -->
                    <div class="mb-6">
                        <label for="room_number" class="block text-sm font-medium text-gray-700 mb-2">
                            رقم الغرفة
                        </label>
                        <input
                            id="room_number"
                            v-model="form.room_number"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                        <div v-if="form.errors.room_number" class="mt-1 text-sm text-red-600">{{ form.errors.room_number }}</div>
                    </div>

                    <!-- Room Type -->
                    <div class="mb-6">
                        <label for="room_type" class="block text-sm font-medium text-gray-700 mb-2">
                            نوع الغرفة <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="room_type"
                            v-model="form.room_type"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option v-for="type in roomTypes" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                        <div v-if="form.errors.room_type" class="mt-1 text-sm text-red-600">{{ form.errors.room_type }}</div>
                    </div>

                    <!-- Map Coordinates -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            موقع الغرفة في الخريطة (نسبة مئوية 0-100)
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="map_x" class="block text-xs text-gray-600 mb-1">X (أفقي)</label>
                                <input
                                    id="map_x"
                                    v-model.number="form.map_x"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.1"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                            <div>
                                <label for="map_y" class="block text-xs text-gray-600 mb-1">Y (عمودي)</label>
                                <input
                                    id="map_y"
                                    v-model.number="form.map_y"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.1"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                        </div>
                        <div v-if="form.errors.map_x" class="mt-1 text-sm text-red-600">{{ form.errors.map_x }}</div>
                        <div v-if="form.errors.map_y" class="mt-1 text-sm text-red-600">{{ form.errors.map_y }}</div>
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
                        ></textarea>
                        <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
                    </div>

                    <!-- Is Active -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <span class="mr-2 text-sm text-gray-700">نشطة (ستظهر في الخريطة)</span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4">
                        <Link
                            :href="route('admin.rooms.index')"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            إلغاء
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50"
                        >
                            {{ form.processing ? 'جاري الحفظ...' : 'حفظ التغييرات' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

