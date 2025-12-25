<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { hasPermission } from '@/utils/permissions';

defineProps({
    roles: Array,
});

const { flash } = usePage().props;

// الصلاحيات
const canCreateRoles = computed(() => hasPermission('create_roles'));
const canEditRoles = computed(() => hasPermission('edit_roles'));
const canDeleteRoles = computed(() => hasPermission('delete_roles'));

const deleteRole = (roleId, roleName) => {
    if (confirm(`هل أنت متأكد من حذف الدور "${roleName}"؟`)) {
        router.delete(route('admin.roles.destroy', roleId));
    }
};
</script>

<template>
    <Head title="إدارة الأدوار" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">إدارة الأدوار</h2>
                    <p class="mt-1 text-sm text-gray-600">إدارة الأدوار والصلاحيات في النظام</p>
                </div>
                <Link 
                    v-if="canCreateRoles"
                    :href="route('admin.roles.create')" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 hover:shadow-xl"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span>إضافة دور جديد</span>
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
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">كيفية استخدام الأدوار</h3>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">1.</span>
                                <span>الأدوار هي مجموعات من الصلاحيات التي يمكن تعيينها للمستخدمين</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">2.</span>
                                <span>قم بإنشاء أدوار جديدة مثل "مدير الشاشات" أو "مدير الأطباء" واختر الصلاحيات المناسبة</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">3.</span>
                                <span>بعد إنشاء الدور، يمكنك تعيينه للمستخدمين من صفحة إدارة المستخدمين</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">4.</span>
                                <span>الأدوار النظامية لا يمكن حذفها أو تعديلها</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Roles Grid -->
            <div v-if="roles.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="role in roles" 
                    :key="role.id"
                    class="group bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-200 overflow-hidden"
                >
                    <div class="p-6">
                        <!-- Role Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                        {{ role.display_name }}
                                    </h3>
                                    <span v-if="role.is_system" class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded">
                                        نظام
                                    </span>
                                </div>
                                <p v-if="role.description" class="text-sm text-gray-600 mb-3">{{ role.description }}</p>
                                <p class="text-xs text-gray-500 font-mono">{{ role.name }}</p>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="flex items-center gap-4 mb-4 text-sm">
                            <div class="flex items-center gap-1 text-gray-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="font-semibold">{{ role.users_count }}</span>
                                <span>مستخدم</span>
                            </div>
                            <div class="flex items-center gap-1 text-gray-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span class="font-semibold">{{ role.permissions_count }}</span>
                                <span>صلاحية</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div v-if="canEditRoles || canDeleteRoles" class="flex items-center gap-2 pt-4 border-t border-gray-200">
                            <Link 
                                v-if="canEditRoles"
                                :href="route('admin.roles.edit', role.id)"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg"
                                :class="{ 'opacity-50 cursor-not-allowed': role.is_system }"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                تعديل
                            </Link>
                            <button
                                v-if="canDeleteRoles && !role.is_system"
                                @click="deleteRole(role.id, role.display_name)"
                                class="px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-all duration-200 shadow-md hover:shadow-lg"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12 bg-white rounded-xl shadow-md border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">لا توجد أدوار</h3>
                <p class="mt-1 text-sm text-gray-500">ابدأ بإنشاء دور جديد.</p>
                <div class="mt-6">
                    <Link 
                        v-if="canCreateRoles"
                        :href="route('admin.roles.create')" 
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-medium shadow-md hover:from-indigo-700 hover:to-purple-700 transition-all duration-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        إضافة دور جديد
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

