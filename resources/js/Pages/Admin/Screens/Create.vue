<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    screen_code: '',
    orientation: 'landscape',
    resolution: '',
});

const submit = () => {
    form.post(route('admin.screens.store'));
};
</script>

<template>
    <Head title="إضافة شاشة جديدة" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">إضافة شاشة جديدة</h2>
                    <p class="mt-1 text-sm text-gray-600">إنشاء شاشة عرض جديدة في النظام</p>
                </div>
                <Link 
                    :href="route('admin.screens.index')" 
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
                        <h3 class="font-semibold text-gray-900 mb-2">نصائح لإنشاء شاشة جديدة</h3>
                        <ul class="space-y-1 text-sm text-gray-700">
                            <li>• <strong>الكود:</strong> استخدم رقماً أو نصاً بسيطاً مثل "101" أو "lobby"</li>
                            <li>• <strong>الاتجاه:</strong> اختر "عرضي" للشاشات الأفقية و"طولي" للشاشات العمودية</li>
                            <li>• <strong>الدقة:</strong> اختيارية، مثال: "1920x1080" للشاشات عالية الدقة</li>
                            <li>• يمكنك تخصيص المحتوى بعد إنشاء الشاشة من صفحة التعديل</li>
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
                                اسم الشاشة <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                type="text"
                                v-model="form.name"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                placeholder="مثال: شاشة مدخل الطابق الأول"
                                required
                                autofocus
                            />
                            <p class="mt-1 text-xs text-gray-500">اسم وصفي للشاشة لتسهيل التعرف عليها</p>
                            <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
                        </div>

                        <!-- Screen Code -->
                        <div>
                            <label for="screen_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                كود الشاشة <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="screen_code"
                                type="text"
                                v-model="form.screen_code"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                placeholder="مثال: 101"
                                required
                            />
                            <p class="mt-1 text-xs text-gray-500">كود فريد يستخدم للوصول إلى الشاشة عبر الرابط</p>
                            <div v-if="form.errors.screen_code" class="mt-2 text-sm text-red-600">{{ form.errors.screen_code }}</div>
                        </div>

                        <!-- Orientation -->
                        <div>
                            <label for="orientation" class="block text-sm font-semibold text-gray-700 mb-2">
                                اتجاه الشاشة <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label 
                                    :class="[
                                        'relative flex flex-col items-center p-6 border-2 rounded-xl cursor-pointer transition-all',
                                        form.orientation === 'landscape' 
                                            ? 'border-indigo-500 bg-indigo-50' 
                                            : 'border-gray-300 hover:border-gray-400'
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.orientation"
                                        value="landscape"
                                        class="sr-only"
                                    />
                                    <svg class="h-12 w-12 mb-3" :class="form.orientation === 'landscape' ? 'text-indigo-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                    <span :class="form.orientation === 'landscape' ? 'font-semibold text-indigo-700' : 'text-gray-700'">عرضي</span>
                                    <span class="text-xs text-gray-500 mt-1">16:9</span>
                                </label>
                                <label 
                                    :class="[
                                        'relative flex flex-col items-center p-6 border-2 rounded-xl cursor-pointer transition-all',
                                        form.orientation === 'portrait' 
                                            ? 'border-indigo-500 bg-indigo-50' 
                                            : 'border-gray-300 hover:border-gray-400'
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.orientation"
                                        value="portrait"
                                        class="sr-only"
                                    />
                                    <svg class="h-12 w-12 mb-3" :class="form.orientation === 'portrait' ? 'text-indigo-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span :class="form.orientation === 'portrait' ? 'font-semibold text-indigo-700' : 'text-gray-700'">طولي</span>
                                    <span class="text-xs text-gray-500 mt-1">9:16</span>
                                </label>
                            </div>
                            <div v-if="form.errors.orientation" class="mt-2 text-sm text-red-600">{{ form.errors.orientation }}</div>
                        </div>

                        <!-- Resolution -->
                        <div>
                            <label for="resolution" class="block text-sm font-semibold text-gray-700 mb-2">
                                الدقة (اختياري)
                            </label>
                            <input
                                id="resolution"
                                type="text"
                                v-model="form.resolution"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                placeholder="مثال: 1920x1080"
                            />
                            <p class="mt-1 text-xs text-gray-500">تنسيق: العرضxالارتفاع (مثال: 1920x1080)</p>
                            <div v-if="form.errors.resolution" class="mt-2 text-sm text-red-600">{{ form.errors.resolution }}</div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <Link 
                            :href="route('admin.screens.index')" 
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
                                إنشاء الشاشة
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
