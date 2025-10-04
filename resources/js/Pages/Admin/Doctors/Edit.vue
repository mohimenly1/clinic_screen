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

// ## جديد: بيانات الأيام للترجمة
const daysOfWeek = [
    { value: 'Sunday',    label: 'الأحد' },
    { value: 'Monday',    label: 'الاثنين' },
    { value: 'Tuesday',   label: 'الثلاثاء' },
    { value: 'Wednesday', label: 'الأربعاء' },
    { value: 'Thursday',  label: 'الخميس' },
    { value: 'Friday',    label: 'الجمعة' },
    { value: 'Saturday',  label: 'السبت' },
];

// ## جديد: دالة لترجمة اليوم
const translateDay = (englishDay) => {
    const day = daysOfWeek.find(d => d.value === englishDay);
    return day ? day.label : englishDay;
};

// فورم لإضافة موعد جديد
const scheduleForm = useForm({
    day_of_week: 'Sunday',
    start_time: '',
    end_time: '',
    clinic_number: '',
    floor: '',
});

const submitNewSchedule = () => {
    scheduleForm.post(route('admin.doctors.schedules.store', props.doctor.id), {
        preserveScroll: true,
        onSuccess: () => scheduleForm.reset(),
    });
};
</script>

<template>
    <Head title="تعديل بيانات طبيب" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">تعديل بيانات طبيب</h2>
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- قسم تحديث بيانات الطبيب -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submitDoctorDetails" class="p-6 space-y-6">
                        <h3 class="text-lg font-medium text-gray-900">البيانات الأساسية</h3>
                         <div>
                            <label for="name">اسم الطبيب</label>
                            <input id="name" type="text" v-model="form.name" class="mt-1 block w-full" required>
                            <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
                        </div>
                        <div>
                            <label for="department_id">القسم</label>
                            <select id="department_id" v-model="form.department_id" class="mt-1 block w-full" required>
                                <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                            </select>
                            <div v-if="form.errors.department_id" class="text-sm text-red-500">{{ form.errors.department_id }}</div>
                        </div>
                        <div>
                            <label for="photo">تغيير الصورة</label>
                            <input id="photo" type="file" @change="updatePhotoPreview" class="mt-1 block w-full">
                            <div class="mt-4">
                                <img :src="photoPreview || 'https://placehold.co/96x96/EFEFEF/AAAAAA?text=No+Image'" class="w-24 h-24 rounded-full object-cover">
                            </div>
                            <div v-if="form.errors.photo" class="text-sm text-red-500">{{ form.errors.photo }}</div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-gray-800 text-white rounded-md">تحديث البيانات</button>
                        </div>
                    </form>
                </div>

                <!-- قسم إدارة المواعيد -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-6">
                        <h3 class="text-lg font-medium text-gray-900">جدول المواعيد</h3>

                        <!-- جدول المواعيد الحالية -->
                        <div class="border rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-right">اليوم</th>
                                        <th class="px-4 py-2 text-right">من</th>
                                        <th class="px-4 py-2 text-right">إلى</th>
                                        <th class="px-4 py-2 text-right">العيادة</th>
                                        <th class="px-4 py-2 text-right">الطابق</th>
                                        <th class="px-4 py-2 text-right"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="schedule in doctor.schedules" :key="schedule.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-2">{{ translateDay(schedule.day_of_week) }}</td>
                                        <td class="px-4 py-2">{{ schedule.start_time }}</td>
                                        <td class="px-4 py-2">{{ schedule.end_time }}</td>
                                        <td class="px-4 py-2">{{ schedule.clinic_number }}</td>
                                        <td class="px-4 py-2">{{ schedule.floor }}</td>
                                        <td class="px-4 py-2">
                                            <Link :href="route('admin.schedules.destroy', schedule.id)" method="delete" as="button" class="text-red-600 hover:text-red-800">حذف</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="doctor.schedules.length === 0">
                                        <td colspan="6" class="text-center py-4 text-gray-500">لا توجد مواعيد مضافة لهذا الطبيب.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- فورم إضافة موعد جديد -->
                         <form @submit.prevent="submitNewSchedule" class="pt-6 border-t">
                            <h4 class="font-medium text-gray-800 mb-4">إضافة موعد جديد</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label>اليوم</label>
                                    <select v-model="scheduleForm.day_of_week" class="mt-1 block w-full" required>
                                        <option v-for="day in daysOfWeek" :key="day.value" :value="day.value">
                                            {{ day.label }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label>وقت البدء</label>
                                    <input type="time" v-model="scheduleForm.start_time" class="mt-1 block w-full" required>
                                </div>
                                 <div>
                                    <label>وقت الانتهاء</label>
                                    <input type="time" v-model="scheduleForm.end_time" class="mt-1 block w-full" required>
                                </div>
                                 <div>
                                    <label>رقم العيادة</label>
                                    <input type="text" v-model="scheduleForm.clinic_number" class="mt-1 block w-full" required>
                                </div>
                                 <div>
                                    <label>الطابق</label>
                                    <input type="text" v-model="scheduleForm.floor" class="mt-1 block w-full" required>
                                </div>
                            </div>
                             <div v-if="Object.keys(scheduleForm.errors).length > 0" class="mt-4 text-sm text-red-600">
                                <p v-for="error in scheduleForm.errors" :key="error">{{ error }}</p>
                            </div>
                            <div class="flex justify-end mt-4">
                                <button type="submit" :disabled="scheduleForm.processing" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">إضافة الموعد</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

