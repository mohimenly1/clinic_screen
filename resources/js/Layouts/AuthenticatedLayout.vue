<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import SidebarLink from '@/Components/SidebarLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { hasPermission } from '@/utils/permissions';

const page = usePage();
const showingMobileMenu = ref(false);

const user = computed(() => page.props.auth.user);

const isActive = (pattern) => {
    return route().current(pattern);
};

const navigation = computed(() => {
    const allNavItems = [
        {
            name: 'لوحة التحكم',
            href: 'dashboard',
            icon: 'dashboard',
            active: isActive('dashboard'),
            permission: 'view_dashboard',
        },
        {
            name: 'إدارة الشاشات',
            href: 'admin.screens.index',
            icon: 'screen',
            active: isActive('admin.screens.*'),
            permission: 'view_screens',
        },
        {
            name: 'مكتبة الوسائط',
            href: 'admin.media-items.index',
            icon: 'media',
            active: isActive('admin.media-items.*'),
            permission: 'view_media',
        },
        {
            name: 'قوائم التشغيل',
            href: 'admin.playlists.index',
            icon: 'playlist',
            active: isActive('admin.playlists.*'),
            permission: 'view_playlists',
        },
        {
            name: 'أقسام العيادة',
            href: 'admin.departments.index',
            icon: 'department',
            active: isActive('admin.departments.*'),
            permission: 'view_departments',
        },
        {
            name: 'الأطباء',
            href: 'admin.doctors.index',
            icon: 'doctor',
            active: isActive('admin.doctors.*'),
            permission: 'view_doctors',
        },
        {
            name: 'الطوابق',
            href: 'admin.floors.index',
            icon: 'screen',
            active: isActive('admin.floors.*'),
            permission: 'view_screens',
        },
        {
            name: 'الغرف',
            href: 'admin.rooms.index',
            icon: 'screen',
            active: isActive('admin.rooms.*'),
            permission: 'view_screens',
        },
        {
            name: 'مسارات التنقل',
            href: 'admin.navigation-paths.index',
            icon: 'screen',
            active: isActive('admin.navigation-paths.*'),
            permission: 'view_screens',
        },
        {
            name: 'البث العام',
            href: 'admin.broadcast.index',
            icon: 'broadcast',
            active: isActive('admin.broadcast.*'),
            permission: 'view_broadcast',
        },
        {
            name: 'إدارة المستخدمين',
            href: 'admin.users.index',
            icon: 'users',
            active: isActive('admin.users.*'),
            permission: 'view_users',
        },
    ];

    // تصفية العناصر بناءً على الصلاحيات
    return allNavItems.filter(item => hasPermission(item.permission));
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile menu overlay -->
        <div
            v-if="showingMobileMenu"
            class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
            @click="showingMobileMenu = false"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 right-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0',
                showingMobileMenu ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-6 bg-gradient-to-r from-indigo-600 to-purple-600">
                <Link :href="route('dashboard')" class="flex items-center space-x-3 space-x-reverse">
                    <ApplicationLogo class="h-8 w-8 text-white" />
                    <span class="text-white font-bold text-lg">نظام الشاشات</span>
                </Link>
                <button
                    @click="showingMobileMenu = false"
                    class="lg:hidden text-white hover:text-gray-200"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="mt-8 px-4 space-y-2 overflow-y-auto h-[calc(100vh-12rem)] pb-4">
                <SidebarLink
                    v-for="item in navigation"
                    :key="item.name"
                    :href="route(item.href)"
                    :active="item.active"
                    :icon="item.icon"
                >
                    {{ item.name }}
                </SidebarLink>
            </nav>

            <!-- User section at bottom -->
            <div class="absolute bottom-0 right-0 left-0 p-4 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center space-x-3 space-x-reverse">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="lg:pr-64">
            <!-- Top header -->
            <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <!-- Mobile menu button -->
                    <button
                        @click="showingMobileMenu = true"
                        class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Page title (optional, can be removed if not needed) -->
                    <div class="flex-1 lg:flex-none"></div>

                    <!-- User dropdown -->
                    <div class="relative">
                        <Dropdown align="left" width="48">
                            <template #trigger>
                                <button class="flex items-center space-x-3 space-x-reverse text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 rounded-full">
                                    <span class="hidden sm:block text-gray-700 font-medium">{{ user.name }}</span>
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    الملف الشخصي
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="text-red-600"
                                >
                                    تسجيل الخروج
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Page Heading (optional slot) -->
            <header
                v-if="$slots.header"
                class="bg-white shadow-sm"
            >
                <div class="px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="py-6">
                <div class="px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
