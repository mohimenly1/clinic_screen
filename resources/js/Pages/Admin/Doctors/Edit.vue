<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    doctor: Object,
    departments: Array,
});

// فورم لتحديث بيانات الطبيب الأساسية
const form = useForm({
    _method: 'PUT',
    name: props.doctor.name,
    department_id: props.doctor.department_id,
    photo: null,
});

const photoPreview = ref(props.doctor.photo_url);

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

const submitDoctorDetails = () => {
    form.post(route('admin.doctors.update', props.doctor.id));
};

// بيانات الأيام للترجمة
const daysOfWeek = [
    { value: 'Sunday',    label: 'الأحد' },
    { value: 'Monday',    label: 'الاثنين' },
    { value: 'Tuesday',   label: 'الثلاثاء' },
    { value: 'Wednesday', label: 'الأربعاء' },
    { value: 'Thursday',  label: 'الخميس' },
    { value: 'Friday',    label: 'الجمعة' },
    { value: 'Saturday',  label: 'السبت' },
];

const translateDay = (englishDay) => {
    const day = daysOfWeek.find(d => d.value === englishDay);
    return day ? day.label : englishDay;
};

// فورم لإضافة موعد جديد
const scheduleForm = useForm({
    day_of_week: 'Sunday',
    start_time: '',
    end_time: '',
    department_id: '',
    floor: '',
});

const submitNewSchedule = () => {
    scheduleForm.post(route('admin.doctors.schedules.store', props.doctor.id), {
        preserveScroll: true,
        onSuccess: () => {
            scheduleForm.reset();
            scheduleForm.day_of_week = 'Sunday';
            scheduleForm.department_id = '';
        },
    });
};
</script>

<template>
    <Head title="تعديل بيانات طبيب" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">تعديل بيانات الطبيب</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ form.name }}</p>
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
            <!-- Doctor Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-3">
                        <div class="h-10 w-10 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        البيانات الأساسية
                    </h3>
                </div>
                
                <form @submit.prevent="submitDoctorDetails" class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                required
                            />
                            <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                القسم <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="department_id"
                                v-model="form.department_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                required
                            >
                                <option v-for="department in departments" :key="department.id" :value="department.id">
                                    {{ department.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.department_id" class="mt-2 text-sm text-red-600">{{ form.errors.department_id }}</div>
                        </div>
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
                        
                        <div v-if="photoPreview" class="mt-4">
                            <div class="relative inline-block">
                                <img 
                                    :src="photoPreview" 
                                    alt="Preview" 
                                    class="w-32 h-32 rounded-2xl object-cover border-4 border-green-100 shadow-lg"
                                >
                                <div class="absolute -bottom-2 -right-2 bg-green-500 text-white rounded-full p-2 shadow-lg">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div v-if="form.errors.photo" class="mt-2 text-sm text-red-600">{{ form.errors.photo }}</div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">جاري التحديث...</span>
                            <span v-else class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                تحديث البيانات
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Schedules Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-3">
                            <div class="h-10 w-10 bg-purple-500 rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            جدول المواعيد
                        </h3>
                        <div class="text-sm text-gray-600 bg-white px-4 py-2 rounded-lg font-medium">
                            {{ doctor.schedules?.length || 0 }} موعد
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Existing Schedules -->
                    <div v-if="doctor.schedules && doctor.schedules.length > 0" class="space-y-3">
                        <div 
                            v-for="schedule in doctor.schedules" 
                            :key="schedule.id"
                            class="bg-gradient-to-r from-white to-gray-50 rounded-xl p-4 border-2 border-gray-100 hover:border-purple-300 transition-all"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="flex-shrink-0">
                                        <div class="h-12 w-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                                            <span class="text-white font-bold text-lg">{{ translateDay(schedule.day_of_week)[0] }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">اليوم</p>
                                            <p class="font-semibold text-gray-900">{{ translateDay(schedule.day_of_week) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">الوقت</p>
                                            <p class="font-semibold text-gray-900">{{ schedule.start_time.substring(0, 5) }} - {{ schedule.end_time.substring(0, 5) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">القسم</p>
                                            <div class="flex items-center gap-2">
                                                <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                                <p class="font-semibold text-gray-900">{{ schedule.clinic_number }}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">الطابق</p>
                                            <p class="font-semibold text-gray-900">{{ schedule.floor }}</p>
                                        </div>
                                    </div>
                                </div>
                                <Link
                                    :href="route('admin.schedules.destroy', schedule.id)"
                                    method="delete"
                                    as="button"
                                    class="ml-4 p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    onclick="return confirm('هل أنت متأكد من حذف هذا الموعد؟')"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div v-else class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
                        <svg class="h-12 w-12 text-yellow-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-gray-600 font-medium">لا توجد مواعيد مضافة لهذا الطبيب</p>
                    </div>

                    <!-- Add New Schedule Form -->
                    <div class="pt-6 border-t border-gray-200">
                        <h4 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            إضافة موعد جديد
                        </h4>
                        <form @submit.prevent="submitNewSchedule" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">اليوم</label>
                                    <select 
                                        v-model="scheduleForm.day_of_week" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                        required
                                    >
                                        <option v-for="day in daysOfWeek" :key="day.value" :value="day.value">
                                            {{ day.label }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">وقت البدء</label>
                                    <input 
                                        type="time" 
                                        v-model="scheduleForm.start_time" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">وقت الانتهاء</label>
                                    <input 
                                        type="time" 
                                        v-model="scheduleForm.end_time" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">القسم <span class="text-red-500">*</span></label>
                                    <select 
                                        v-model="scheduleForm.department_id" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                        required
                                    >
                                        <option value="" disabled>اختر القسم</option>
                                        <option v-for="department in departments" :key="department.id" :value="department.id">
                                            {{ department.name }}
                                        </option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">اختر القسم من القائمة</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">الطابق</label>
                                    <input 
                                        type="text" 
                                        v-model="scheduleForm.floor" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                        required
                                    />
                                </div>
                            </div>
                            
                            <div v-if="Object.keys(scheduleForm.errors).length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex items-center gap-2 text-red-800">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="font-medium">يوجد أخطاء في النموذج</p>
                                </div>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    <li v-for="error in scheduleForm.errors" :key="error">{{ error }}</li>
                                </ul>
                            </div>
                            
                            <div class="flex justify-end">
                                <button 
                                    type="submit" 
                                    :disabled="scheduleForm.processing"
                                    class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg font-semibold shadow-lg hover:from-green-700 hover:to-emerald-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="scheduleForm.processing">جاري الإضافة...</span>
                                    <span v-else class="flex items-center gap-2">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        إضافة الموعد
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

