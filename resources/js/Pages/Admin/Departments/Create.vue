<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
});

const submit = () => {
    form.post(route('admin.departments.store'));
};
</script>

<template>
    <Head title="إضافة قسم جديد" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">إضافة قسم جديد</h2>
                    <p class="mt-1 text-sm text-gray-600">إنشاء قسم طبي جديد في النظام</p>
                </div>
                <Link 
                    :href="route('admin.departments.index')" 
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
                        <h3 class="font-semibold text-gray-900 mb-2">نصائح لإنشاء قسم جديد</h3>
                        <ul class="space-y-1 text-sm text-gray-700">
                            <li>• استخدم اسم القسم بشكل واضح ومباشر (مثل: "طب الأسنان"، "الجراحة العامة")</li>
                            <li>• بعد إنشاء القسم، يمكنك إضافة الأطباء وربطهم بهذا القسم</li>
                            <li>• سيظهر القسم في نظام الاستعلامات للزوار</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <form @submit.prevent="submit">
                    <div class="p-8 space-y-6">
                        <!-- Name Input -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                اسم القسم <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                type="text"
                                v-model="form.name"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                placeholder="مثال: طب الأسنان"
                                required
                                autofocus
                            />
                            <p class="mt-1 text-xs text-gray-500">اسم واضح ومباشر للقسم الطبي</p>
                            <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <Link 
                            :href="route('admin.departments.index')" 
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
                        >
                            إلغاء
                        </Link>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">جاري الإنشاء...</span>
                            <span v-else class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                إنشاء القسم
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
