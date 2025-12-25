<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            screens: 0,
            media_items: 0,
            playlists: 0,
            doctors: 0,
        }),
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.is_admin || false);

const statsItems = [
    {
        name: 'الشاشات',
        route: 'admin.screens.index',
        icon: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        color: 'from-blue-500 to-blue-600',
        count: props.stats.screens,
    },
    {
        name: 'الوسائط',
        route: 'admin.media-items.index',
        icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
        color: 'from-purple-500 to-purple-600',
        count: props.stats.media_items,
    },
    {
        name: 'قوائم التشغيل',
        route: 'admin.playlists.index',
        icon: 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3',
        color: 'from-pink-500 to-pink-600',
        count: props.stats.playlists,
    },
    {
        name: 'الأطباء',
        route: 'admin.doctors.index',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        color: 'from-green-500 to-green-600',
        count: props.stats.doctors,
    },
];

const quickLinks = [
    {
        name: 'إضافة شاشة جديدة',
        route: 'admin.screens.create',
        description: 'إنشاء وإعداد شاشة عرض جديدة',
        icon: 'M12 6v6m0 0v6m0-6h6m-6 0H6',
        color: 'bg-blue-50 text-blue-600 hover:bg-blue-100',
    },
    {
        name: 'رفع وسائط',
        route: 'admin.media-items.create',
        description: 'إضافة صور أو فيديوهات جديدة',
        icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
        color: 'bg-purple-50 text-purple-600 hover:bg-purple-100',
    },
    {
        name: 'البث العام',
        route: 'admin.broadcast.index',
        description: 'بث محتوى لجميع الشاشات',
        icon: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
        color: 'bg-pink-50 text-pink-600 hover:bg-pink-100',
    },
];
</script>

<template>
    <Head title="لوحة التحكم" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">لوحة التحكم</h2>
                <p class="mt-1 text-sm text-gray-600">مرحباً بك في نظام إدارة شاشات العيادة</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats Grid (Only for Admin) -->
            <div v-if="isAdmin" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="stat in statsItems"
                    :key="stat.name"
                    :href="route(stat.route)"
                    class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition-all duration-300 hover:shadow-lg hover:ring-gray-300"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">{{ stat.name }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stat.count }}</p>
                        </div>
                        <div :class="`rounded-lg bg-gradient-to-r ${stat.color} p-3 shadow-lg`">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center">
                        <span class="text-sm text-gray-500 group-hover:text-indigo-600 transition-colors">عرض الكل</span>
                        <svg class="mr-2 h-4 w-4 text-gray-400 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </div>
                </Link>
            </div>

            <!-- Quick Actions -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="link in quickLinks"
                        :key="link.name"
                        :href="route(link.route)"
                        :class="`${link.color} rounded-xl p-6 transition-all duration-300 hover:scale-105 hover:shadow-md`"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold mb-2">{{ link.name }}</h4>
                                <p class="text-sm opacity-80">{{ link.description }}</p>
                            </div>
                            <svg class="h-8 w-8 flex-shrink-0 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="link.icon" />
                            </svg>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Welcome Card -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-8 shadow-xl">
                <div class="relative z-10">
                    <h3 class="text-2xl font-bold text-white mb-2">مرحباً بك في النظام!</h3>
                    <p class="text-indigo-100 mb-6 max-w-2xl">
                        يمكنك إدارة جميع الشاشات والوسائط والمحتوى من خلال هذه الواجهة. استخدم القائمة الجانبية للتنقل بين الأقسام المختلفة.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <Link
                            :href="route('admin.screens.index')"
                            class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-gray-50 transition-colors shadow-lg"
                        >
                            <svg class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            إدارة الشاشات
                        </Link>
                        <Link
                            :href="route('admin.media-items.index')"
                            class="inline-flex items-center px-6 py-3 bg-white/10 text-white border-2 border-white/30 rounded-lg font-semibold hover:bg-white/20 transition-colors"
                        >
                            <svg class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            مكتبة الوسائط
                        </Link>
                    </div>
                </div>
                <!-- Decorative circles -->
                <div class="absolute top-0 left-0 w-72 h-72 bg-white/10 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full translate-x-1/2 translate-y-1/2"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
