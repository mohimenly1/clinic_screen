<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    permissions: Object,
    categories: Array,
});

const form = useForm({
    name: '',
    display_name: '',
    description: '',
    permissions: [],
});

const selectedCategory = ref(null);

const togglePermission = (permissionId) => {
    const index = form.permissions.indexOf(permissionId);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(permissionId);
    }
};

const selectAllInCategory = (category) => {
    const categoryPermissions = props.permissions[category] || [];
    const categoryPermissionIds = categoryPermissions.map(p => p.id);
    const allSelected = categoryPermissionIds.every(id => form.permissions.includes(id));
    
    if (allSelected) {
        // إلغاء تحديد الكل
        form.permissions = form.permissions.filter(id => !categoryPermissionIds.includes(id));
    } else {
        // تحديد الكل
        categoryPermissionIds.forEach(id => {
            if (!form.permissions.includes(id)) {
                form.permissions.push(id);
            }
        });
    }
};

const submit = () => {
    form.post(route('admin.roles.store'));
};
</script>

<template>
    <Head title="إضافة دور جديد" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">إضافة دور جديد</h2>
                    <p class="mt-1 text-sm text-gray-600">إنشاء دور جديد وتحديد صلاحياته</p>
                </div>
                <Link 
                    :href="route('admin.roles.index')" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    رجوع
                </Link>
            </div>
        </template>

        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <form @submit.prevent="submit">
                    <div class="p-6 space-y-6">
                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الأساسية</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        اسم الدور (بالإنجليزية) <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        pattern="[a-z_]+"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-mono"
                                        placeholder="screen_manager"
                                        :class="{ 'border-red-500': form.errors.name }"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">استخدم أحرف صغيرة وشرطة سفلية فقط (مثل: screen_manager)</p>
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                                </div>

                                <div>
                                    <label for="display_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        الاسم المعروض <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="display_name"
                                        v-model="form.display_name"
                                        type="text"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                        placeholder="مدير الشاشات"
                                        :class="{ 'border-red-500': form.errors.display_name }"
                                    />
                                    <p v-if="form.errors.display_name" class="mt-1 text-sm text-red-600">{{ form.errors.display_name }}</p>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                        الوصف
                                    </label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                        placeholder="وصف مختصر للدور..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Permissions Selection -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">الصلاحيات</h3>
                            <p class="text-sm text-gray-600 mb-4">اختر الصلاحيات التي سيحصل عليها هذا الدور</p>
                            
                            <!-- Category Filter -->
                            <div v-if="categories.length > 0" class="mb-4 flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    @click="selectedCategory = null"
                                    :class="[
                                        'px-4 py-2 rounded-lg text-sm font-medium transition-all',
                                        selectedCategory === null 
                                            ? 'bg-indigo-600 text-white shadow-md' 
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                >
                                    الكل
                                </button>
                                <button
                                    v-for="category in categories"
                                    :key="category"
                                    type="button"
                                    @click="selectedCategory = category"
                                    :class="[
                                        'px-4 py-2 rounded-lg text-sm font-medium transition-all',
                                        selectedCategory === category 
                                            ? 'bg-indigo-600 text-white shadow-md' 
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                >
                                    {{ category }}
                                </button>
                            </div>

                            <!-- Permissions by Category -->
                            <div class="space-y-4 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                                <div 
                                    v-for="(perms, category) in permissions" 
                                    :key="category"
                                    v-show="!selectedCategory || selectedCategory === category"
                                    class="border border-gray-200 rounded-lg p-4"
                                >
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="font-semibold text-gray-900">{{ category }}</h4>
                                        <button
                                            type="button"
                                            @click="selectAllInCategory(category)"
                                            class="text-sm text-indigo-600 hover:text-indigo-700 font-medium"
                                        >
                                            {{ (perms || []).every(p => form.permissions.includes(p.id)) ? 'إلغاء تحديد الكل' : 'تحديد الكل' }}
                                        </button>
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            v-for="permission in perms"
                                            :key="permission.id"
                                            class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg hover:border-indigo-300 hover:bg-indigo-50 transition-all cursor-pointer"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="permission.id"
                                                v-model="form.permissions"
                                                class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                            />
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900">{{ permission.display_name }}</div>
                                                <div v-if="permission.description" class="text-xs text-gray-500 mt-1">{{ permission.description }}</div>
                                                <div class="text-xs text-gray-400 font-mono mt-1">{{ permission.name }}</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <span class="font-semibold">تم تحديد {{ form.permissions.length }} صلاحية</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <Link 
                            :href="route('admin.roles.index')" 
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
                            <span v-else>إنشاء الدور</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

