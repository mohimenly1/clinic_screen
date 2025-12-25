<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    roles: Array,
});

const form = useForm({
    name: props.user.name,
    username: props.user.username,
    email: props.user.email || '',
    password: '',
    password_confirmation: '',
    roles: props.user.roles || [],
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id));
};
</script>

<template>
    <Head title="تعديل المستخدم" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">تعديل المستخدم</h2>
                    <p class="mt-1 text-sm text-gray-600">تعديل بيانات المستخدم وأدواره</p>
                </div>
                <Link 
                    :href="route('admin.users.index')" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    رجوع
                </Link>
            </div>
        </template>

        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <form @submit.prevent="submit">
                    <div class="p-6 space-y-6">
                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الأساسية</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        الاسم الكامل
                                    </label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                        :class="{ 'border-red-500': form.errors.name }"
                                    />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                                </div>

                                <div>
                                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                        اسم المستخدم <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="username"
                                        v-model="form.username"
                                        type="text"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-mono"
                                        :class="{ 'border-red-500': form.errors.username }"
                                    />
                                    <p v-if="form.errors.username" class="mt-1 text-sm text-red-600">{{ form.errors.username }}</p>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        البريد الإلكتروني (اختياري)
                                    </label>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                        :class="{ 'border-red-500': form.errors.email }"
                                    />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                                </div>

                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                        كلمة المرور الجديدة (اتركها فارغة إذا لم ترد تغييرها)
                                    </label>
                                    <input
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                        :class="{ 'border-red-500': form.errors.password }"
                                    />
                                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                        تأكيد كلمة المرور
                                    </label>
                                    <input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Roles Selection -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">الأدوار والصلاحيات</h3>
                            <p class="text-sm text-gray-600 mb-4">اختر الأدوار التي سيحصل عليها هذا المستخدم</p>
                            
                            <div v-if="roles.length > 0" class="space-y-3">
                                <label
                                    v-for="role in roles"
                                    :key="role.id"
                                    class="flex items-start gap-3 p-4 border border-gray-200 rounded-lg hover:border-indigo-300 hover:bg-indigo-50 transition-all cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :value="role.id"
                                        v-model="form.roles"
                                        class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                    />
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">{{ role.display_name }}</div>
                                        <div class="text-sm text-gray-500">{{ role.name }}</div>
                                    </div>
                                </label>
                            </div>
                            <p v-else class="text-sm text-gray-500 text-center py-4">
                                لا توجد أدوار متاحة. <Link :href="route('admin.roles.create')" class="text-indigo-600 hover:text-indigo-700 font-medium">أنشئ دوراً جديداً</Link>
                            </p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <Link 
                            :href="route('admin.users.index')" 
                            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            إلغاء
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">جاري الحفظ...</span>
                            <span v-else>حفظ التغييرات</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

