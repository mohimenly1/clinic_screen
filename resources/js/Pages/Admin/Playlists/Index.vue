<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { hasPermission } from '@/utils/permissions';

defineProps({
    playlists: Array,
});

const { flash } = usePage().props;

// الصلاحيات
const canCreatePlaylists = computed(() => hasPermission('create_playlists'));
const canEditPlaylists = computed(() => hasPermission('edit_playlists'));
const canDeletePlaylists = computed(() => hasPermission('delete_playlists'));
</script>

<template>
    <Head title="قوائم التشغيل" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">قوائم التشغيل</h2>
                    <p class="mt-1 text-sm text-gray-600">إدارة قوائم الوسائط الخاصة بالشاشات</p>
                </div>
                <Link
                    v-if="canCreatePlaylists"
                    :href="route('admin.playlists.create')"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 hover:shadow-xl"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>إنشاء قائمة جديدة</span>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash Message -->
            <div v-if="flash.success" class="bg-green-50 border-r-4 border-green-500 text-green-800 p-4 rounded-lg shadow-sm" role="alert">
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
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">كيفية استخدام قوائم التشغيل</h3>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">1.</span>
                                <span>قم بإنشاء قائمة تشغيل جديدة واختر لها اسماً واضحاً</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">2.</span>
                                <span>أضف الوسائط التي تريد عرضها في القائمة (صور، فيديوهات، أو صوتيات)</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">3.</span>
                                <span>اختر القائمة عند تعديل إعدادات الشاشة لتخصيص المحتوى المعروض</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold mt-0.5">4.</span>
                                <span>يمكن استخدام قائمة واحدة على عدة شاشات في نفس الوقت</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Playlists Grid -->
            <div v-if="playlists.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="playlist in playlists"
                    :key="playlist.id"
                    class="group bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-200 overflow-hidden"
                >
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-white mb-2">{{ playlist.name }}</h3>
                                <div class="flex items-center gap-4 text-white/90 text-sm">
                                    <div class="flex items-center gap-1">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                        </svg>
                                        <span class="font-semibold">{{ playlist.media_items_count }}</span>
                                        <span>ملف</span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-2">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-4">
                        <!-- Screens Section -->
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm font-semibold text-gray-700">الشاشات المرتبطة</span>
                            </div>

                            <div v-if="playlist.screens.length > 0" class="space-y-2">
                                <div
                                    v-for="screen in playlist.screens"
                                    :key="screen.id"
                                    class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg border border-gray-200"
                                >
                                    <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ screen.name }}</p>
                                        <p class="text-xs text-gray-500">كود: {{ screen.screen_code }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-400 italic py-2">
                                لا توجد شاشات مرتبطة
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div v-if="canEditPlaylists || canDeletePlaylists" class="pt-4 border-t border-gray-200 flex items-center gap-2">
                            <Link
                                v-if="canEditPlaylists"
                                :href="route('admin.playlists.edit', playlist.id)"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                تعديل
                            </Link>
                            <Link
                                v-if="canDeletePlaylists"
                                :href="route('admin.playlists.destroy', playlist.id)"
                                method="delete"
                                as="button"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors"
                                onclick="return confirm('هل أنت متأكد من حذف قائمة التشغيل؟ سيتم إزالة ارتباطها من جميع الشاشات.')"
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">لا توجد قوائم تشغيل</h3>
                    <p class="text-gray-500 mb-6">ابدأ بإنشاء قائمة تشغيل جديدة لإدارة محتوى الشاشات</p>
                    <Link
                        v-if="canCreatePlaylists"
                        :href="route('admin.playlists.create')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        إنشاء قائمة جديدة
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
