<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
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
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">إضافة طبيب جديد</h2>
                    <p class="mt-1 text-sm text-gray-600">إضافة طبيب جديد للنظام</p>
                </div>
                <Link 
                    :href="route('admin.doctors.index')" 
                    class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    رجوع
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Info Card -->
            <div class="bg-blue-50 border-r-4 border-blue-500 rounded-lg p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 mb-2">نصائح لإضافة طبيب جديد</h3>
                        <ul class="space-y-1 text-sm text-gray-700">
                            <li>• أدخل اسم الطبيب كاملاً بشكل واضح</li>
                            <li>• اختر القسم الطبي المناسب للطبيب</li>
                            <li>• يمكنك رفع صورة للطبيب (اختياري) - ستنعكس في نظام الاستعلامات</li>
                            <li>• بعد إضافة الطبيب، يمكنك إضافة مواعيده من صفحة التعديل</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <form @submit.prevent="submit">
                    <div class="p-8 space-y-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                اسم الطبيب <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                type="text"
                                v-model="form.name"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                placeholder="مثال: د. محمد أحمد"
                                required
                                autofocus
                            />
                            <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                القسم الطبي <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="department_id"
                                v-model="form.department_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                required
                            >
                                <option value="" disabled>اختر قسماً</option>
                                <option v-for="department in departments" :key="department.id" :value="department.id">
                                    {{ department.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.department_id" class="mt-2 text-sm text-red-600">{{ form.errors.department_id }}</div>
                        </div>

                        <!-- Photo -->
                        <div>
                            <label for="photo" class="block text-sm font-semibold text-gray-700 mb-2">
                                صورة الطبيب (اختياري)
                            </label>
                            <input
                                id="photo"
                                type="file"
                                accept="image/*"
                                @change="updatePhotoPreview"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                            />
                            <p class="mt-1 text-xs text-gray-500">الصيغ المدعومة: JPG, PNG (أقصى حجم: 2MB)</p>
                            
                            <!-- Photo Preview -->
                            <div v-if="photoPreview" class="mt-4">
                                <div class="relative inline-block">
                                    <img 
                                        :src="photoPreview" 
                                        alt="Preview" 
                                        class="w-32 h-32 rounded-2xl object-cover border-4 border-indigo-100 shadow-lg"
                                    >
                                    <div class="absolute -bottom-2 -right-2 bg-indigo-500 text-white rounded-full p-2 shadow-lg">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="mt-4">
                                <div class="w-32 h-32 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 border-4 border-gray-200 flex items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div v-if="form.errors.photo" class="mt-2 text-sm text-red-600">{{ form.errors.photo }}</div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <Link 
                            :href="route('admin.doctors.index')" 
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
                        >
                            إلغاء
                        </Link>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">جاري الإضافة...</span>
                            <span v-else class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                إضافة الطبيب
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
