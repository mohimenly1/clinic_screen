<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    path: Object,
    rooms: Array,
});

const form = useForm({
    from_room_id: props.path.from_room_id,
    to_room_id: props.path.to_room_id,
    directions: props.path.directions,
    estimated_time_seconds: props.path.estimated_time_seconds,
    distance_meters: props.path.distance_meters,
});

const submit = () => {
    form.put(route('admin.navigation-paths.update', props.path.id));
};
</script>

<template>
    <Head title="تعديل المسار" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">تعديل المسار</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ path.from_room.name }} → {{ path.to_room.name }}</p>
                </div>
            </div>
        </template>

        <div class="max-w-2xl">
            <div class="bg-white shadow rounded-lg p-6">
                <form @submit.prevent="submit">
                    <!-- From Room -->
                    <div class="mb-6">
                        <label for="from_room_id" class="block text-sm font-medium text-gray-700 mb-2">
                            من غرفة <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="from_room_id"
                            v-model="form.from_room_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">اختر الغرفة المصدر</option>
                            <option v-for="room in rooms" :key="room.id" :value="room.id">
                                {{ room.label }}
                            </option>
                        </select>
                        <div v-if="form.errors.from_room_id" class="mt-1 text-sm text-red-600">{{ form.errors.from_room_id }}</div>
                    </div>

                    <!-- To Room -->
                    <div class="mb-6">
                        <label for="to_room_id" class="block text-sm font-medium text-gray-700 mb-2">
                            إلى غرفة <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="to_room_id"
                            v-model="form.to_room_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">اختر الغرفة الهدف</option>
                            <option v-for="room in rooms" :key="room.id" :value="room.id">
                                {{ room.label }}
                            </option>
                        </select>
                        <div v-if="form.errors.to_room_id" class="mt-1 text-sm text-red-600">{{ form.errors.to_room_id }}</div>
                    </div>

                    <!-- Directions -->
                    <div class="mb-6">
                        <label for="directions" class="block text-sm font-medium text-gray-700 mb-2">
                            تعليمات التنقل <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="directions"
                            v-model="form.directions"
                            rows="4"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        ></textarea>
                        <div v-if="form.errors.directions" class="mt-1 text-sm text-red-600">{{ form.errors.directions }}</div>
                    </div>

                    <!-- Estimated Time -->
                    <div class="mb-6">
                        <label for="estimated_time_seconds" class="block text-sm font-medium text-gray-700 mb-2">
                            الوقت المتوقع (بالثواني)
                        </label>
                        <input
                            id="estimated_time_seconds"
                            v-model.number="form.estimated_time_seconds"
                            type="number"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                        <div v-if="form.errors.estimated_time_seconds" class="mt-1 text-sm text-red-600">{{ form.errors.estimated_time_seconds }}</div>
                    </div>

                    <!-- Distance -->
                    <div class="mb-6">
                        <label for="distance_meters" class="block text-sm font-medium text-gray-700 mb-2">
                            المسافة (بالمتر)
                        </label>
                        <input
                            id="distance_meters"
                            v-model.number="form.distance_meters"
                            type="number"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                        <div v-if="form.errors.distance_meters" class="mt-1 text-sm text-red-600">{{ form.errors.distance_meters }}</div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4">
                        <Link
                            :href="route('admin.navigation-paths.index')"
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

