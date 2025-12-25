<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { hasPermission } from '@/utils/permissions';

defineProps({
    doctors: Array,
});

const { flash } = usePage().props;

// الصلاحيات
const canCreateDoctors = computed(() => hasPermission('create_doctors'));
const canEditDoctors = computed(() => hasPermission('edit_doctors'));
const canDeleteDoctors = computed(() => hasPermission('delete_doctors'));
</script>

<template>
    <Head title="الأطباء" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">إدارة الأطباء</h2>
                    <p class="mt-1 text-sm text-gray-600">إدارة بيانات الأطباء والمواعيد</p>
                </div>
                <Link 
                    v-if="canCreateDoctors"
                    :href="route('admin.doctors.create')" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 hover:shadow-xl"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span>إضافة طبيب جديد</span>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash Message -->
            <div v-if="flash.success" class="bg-green-50 border-r-4 border-green-500 text-green-800 p-4 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 ml-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-medium">{{ flash.success }}</p>
                </div>
            </div>

            <!-- Instructions Card -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">كيفية استخدام إدارة الأطباء</h3>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">1.</span>
                                <span>قم بإضافة طبيب جديد وربطه بأحد الأقسام الطبية</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">2.</span>
                                <span>يمكنك رفع صورة للطبيب (اختياري) لعرضها في نظام الاستعلامات</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">3.</span>
                                <span>بعد إضافة الطبيب، قم بإضافة مواعيده من صفحة التعديل</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Doctors Grid -->
            <div v-if="doctors.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="doctor in doctors" 
                    :key="doctor.id"
                    class="group bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-200 overflow-hidden"
                >
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-white mb-2">{{ doctor.name }}</h3>
                                <div class="flex items-center gap-4 text-white/90 text-sm">
                                    <div class="flex items-center gap-1">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span>{{ doctor.department_name }}</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Photo -->
                            <div class="bg-white/20 rounded-lg p-1">
                                <img 
                                    :src="doctor.photo_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(doctor.name) + '&background=ffffff&color=10b981&size=128'" 
                                    :alt="doctor.name"
                                    class="w-16 h-16 rounded-lg object-cover"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-4">
                        <!-- Schedules Info -->
                        <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg">
                            <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm text-gray-700">
                                <span class="font-semibold">{{ doctor.schedules_count }}</span> موعد
                            </span>
                        </div>

                        <!-- Footer Actions -->
                        <div v-if="canEditDoctors || canDeleteDoctors" class="flex items-center gap-2 pt-3 border-t border-gray-200">
                            <Link 
                                v-if="canEditDoctors"
                                :href="route('admin.doctors.edit', doctor.id)" 
                                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                تعديل
                            </Link>
                            <Link
                                v-if="canDeleteDoctors"
                                :href="route('admin.doctors.destroy', doctor.id)"
                                method="delete"
                                as="button"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors"
                                onclick="return confirm('هل أنت متأكد من حذف الطبيب؟ سيتم حذف جميع المواعيد المرتبطة به.')"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">لا يوجد أطباء</h3>
                    <p class="text-gray-500 mb-6">ابدأ بإضافة طبيب جديد لإدارة بياناته ومواعيده</p>
                    <Link 
                        v-if="canCreateDoctors"
                        :href="route('admin.doctors.create')" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        إضافة طبيب جديد
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
