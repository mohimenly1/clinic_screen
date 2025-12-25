<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { hasPermission } from '@/utils/permissions';

const props = defineProps({
    users: Object,
});

const { flash } = usePage().props;

const deleteUser = (userId, userName) => {
    if (confirm(`هل أنت متأكد من حذف المستخدم "${userName}"؟`)) {
        router.delete(route('admin.users.destroy', userId));
    }
};

const paginationLinks = computed(() => props.users?.links || []);

// الصلاحيات
const canViewRoles = computed(() => hasPermission('manage_roles'));
const canCreateUsers = computed(() => hasPermission('create_users'));
const canEditUsers = computed(() => hasPermission('edit_users'));
const canDeleteUsers = computed(() => hasPermission('delete_users'));
</script>

<template>
    <Head title="إدارة المستخدمين" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">إدارة المستخدمين</h2>
                    <p class="mt-1 text-sm text-gray-600">إدارة المستخدمين وصلاحياتهم في النظام</p>
                </div>
                <div v-if="canViewRoles || canCreateUsers" class="flex gap-3">
                    <Link 
                        v-if="canViewRoles"
                        :href="route('admin.roles.index')" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-lg font-semibold shadow-lg hover:from-gray-700 hover:to-gray-800 transition-all duration-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        <span>إدارة الأدوار</span>
                    </Link>
                    <Link 
                        v-if="canCreateUsers"
                        :href="route('admin.users.create')" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 hover:shadow-xl"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        <span>إضافة مستخدم جديد</span>
                    </Link>
                </div>
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

            <div v-if="flash.error" class="bg-red-50 border-r-4 border-red-500 text-red-800 p-4 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 ml-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-medium">{{ flash.error }}</p>
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
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">كيفية استخدام إدارة المستخدمين</h3>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">1.</span>
                                <span>قم بإنشاء مستخدمين جدد وتعيين أدوارهم من خلال زر "إضافة مستخدم جديد"</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">2.</span>
                                <span>يمكنك تعيين عدة أدوار لكل مستخدم لتحديد صلاحياته في النظام</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">3.</span>
                                <span>استخدم "إدارة الأدوار" لإنشاء أدوار جديدة وتحديد الصلاحيات لكل دور</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">4.</span>
                                <span>المستخدمون الذين لديهم دور "مدير" لديهم جميع الصلاحيات تلقائياً</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-indigo-500 to-purple-600">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-right text-sm font-bold text-white">
                                    #
                                </th>
                                <th scope="col" class="px-6 py-4 text-right text-sm font-bold text-white">
                                    الاسم الكامل
                                </th>
                                <th scope="col" class="px-6 py-4 text-right text-sm font-bold text-white">
                                    اسم المستخدم
                                </th>
                                <th scope="col" class="px-6 py-4 text-right text-sm font-bold text-white">
                                    البريد الإلكتروني
                                </th>
                                <th scope="col" class="px-6 py-4 text-right text-sm font-bold text-white">
                                    الحالة
                                </th>
                                <th scope="col" class="px-6 py-4 text-right text-sm font-bold text-white">
                                    الأدوار
                                </th>
                                <th scope="col" class="px-6 py-4 text-right text-sm font-bold text-white">
                                    تاريخ الإنشاء
                                </th>
                                <th scope="col" class="px-6 py-4 text-center text-sm font-bold text-white">
                                    الإجراءات
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(user, index) in users.data" :key="user.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ users.from + index }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold shadow-md">
                                            {{ user.name.charAt(0) }}
                                        </div>
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ user.name }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-mono text-gray-900 bg-gray-100 px-3 py-1 rounded-lg inline-block">
                                        {{ user.username }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600">
                                        {{ user.email || '—' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="user.is_admin" class="px-3 py-1 inline-flex items-center gap-1 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold rounded-full shadow-md">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        مدير
                                    </span>
                                    <span v-else class="px-3 py-1 inline-flex items-center gap-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full">
                                        مستخدم
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="user.roles.length > 0" class="flex flex-wrap gap-1">
                                        <span 
                                            v-for="role in user.roles" 
                                            :key="role.id"
                                            class="px-2 py-1 bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-700 text-xs font-medium rounded border border-indigo-200"
                                        >
                                            {{ role.display_name }}
                                        </span>
                                    </div>
                                    <span v-else class="text-xs text-gray-400">لا توجد أدوار</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(user.created_at).toLocaleDateString('ar-SA') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div v-if="canEditUsers || canDeleteUsers" class="flex items-center justify-center gap-2">
                                        <Link 
                                            v-if="canEditUsers"
                                            :href="route('admin.users.edit', user.id)"
                                            class="inline-flex items-center gap-1 px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md"
                                            title="تعديل"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            <span class="hidden md:inline">تعديل</span>
                                        </Link>
                                        <button
                                            v-if="canDeleteUsers"
                                            @click="deleteUser(user.id, user.name)"
                                            class="inline-flex items-center gap-1 px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-sm hover:shadow-md"
                                            title="حذف"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span class="hidden md:inline">حذف</span>
                                        </button>
                                    </div>
                                    <span v-else class="text-gray-400 text-xs">لا توجد صلاحيات</span>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">لا يوجد مستخدمون</h3>
                                        <p class="text-gray-500 mb-4">ابدأ بإنشاء مستخدم جديد.</p>
                                        <Link 
                                            :href="route('admin.users.create')" 
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-medium shadow-md hover:from-indigo-700 hover:to-purple-700 transition-all duration-200"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                            </svg>
                                            إضافة مستخدم جديد
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.links && users.links.length > 3" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            عرض
                            <span class="font-semibold">{{ users.from }}</span>
                            إلى
                            <span class="font-semibold">{{ users.to }}</span>
                            من
                            <span class="font-semibold">{{ users.total }}</span>
                            مستخدم
                        </div>
                        <div class="flex items-center gap-2">
                            <Link
                                v-for="(link, index) in paginationLinks"
                                :key="index"
                                :href="link.url || '#'"
                                v-html="link.label"
                                :class="[
                                    'px-4 py-2 text-sm font-medium rounded-lg transition-all',
                                    link.active
                                        ? 'bg-indigo-600 text-white shadow-md'
                                        : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                                    !link.url
                                        ? 'opacity-50 cursor-not-allowed pointer-events-none'
                                        : 'hover:shadow-md'
                                ]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
