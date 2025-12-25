<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    playlist: Object,
    mediaItems: Array,
    playlistMediaIds: Array,
});

const form = useForm({
    name: props.playlist.name,
    media_items: [...props.playlistMediaIds], // نسخ المصفوفة
});

const searchQuery = ref('');
const filterType = ref('all'); // all, image, video, audio

// الملفات المختارة في القائمة
const selectedItems = computed(() => {
    return props.mediaItems.filter(item => form.media_items.includes(item.id));
});

// الملفات المتاحة (غير المختارة)
const availableItems = computed(() => {
    let filtered = props.mediaItems.filter(item => !form.media_items.includes(item.id));

    // فلترة حسب البحث
    if (searchQuery.value) {
        filtered = filtered.filter(item =>
            item.file_path.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    // فلترة حسب النوع
    if (filterType.value !== 'all') {
        filtered = filtered.filter(item => item.file_type === filterType.value);
    }

    return filtered;
});

const toggleMediaItem = (itemId) => {
    const index = form.media_items.indexOf(itemId);
    if (index > -1) {
        form.media_items.splice(index, 1);
    } else {
        form.media_items.push(itemId);
    }
};

const removeFromPlaylist = (itemId) => {
    const index = form.media_items.indexOf(itemId);
    if (index > -1) {
        form.media_items.splice(index, 1);
    }
};

const submit = () => {
    form.put(route('admin.playlists.update', props.playlist.id));
};
</script>

<template>
    <Head :title="`تعديل: ${playlist.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">تعديل قائمة التشغيل</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ playlist.name }}</p>
                </div>
                <Link
                    :href="route('admin.playlists.index')"
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
            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="bg-green-50 border-r-4 border-green-500 text-green-800 p-4 rounded-lg">
                {{ $page.props.flash.success }}
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <form @submit.prevent="submit">
                    <!-- Playlist Name Section -->
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            اسم قائمة التشغيل
                        </label>
                        <input
                            type="text"
                            v-model="form.name"
                            id="name"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                            placeholder="أدخل اسم القائمة"
                            required
                        />
                        <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
                    </div>

                    <!-- Selected Items Section -->
                    <div class="p-6 border-b border-gray-200 bg-blue-50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">الملفات في هذه القائمة</h3>
                                    <p class="text-sm text-gray-600">{{ selectedItems.length }} ملف مختار</p>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Items Grid -->
                        <div v-if="selectedItems.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            <div
                                v-for="item in selectedItems"
                                :key="item.id"
                                class="group relative aspect-square rounded-lg overflow-hidden border-2 border-blue-500 shadow-md bg-gray-100"
                            >
                                <img
                                    v-if="item.file_type === 'image'"
                                    :src="item.url"
                                    class="w-full h-full object-cover"
                                >
                                <video
                                    v-else-if="item.file_type === 'video'"
                                    :src="item.url"
                                    class="w-full h-full object-cover"
                                    muted
                                    loop
                                ></video>
                                <div v-else class="w-full h-full flex items-center justify-center bg-gray-200">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                    </svg>
                                </div>

                                <!-- Remove Button -->
                                <button
                                    @click="removeFromPlaylist(item.id)"
                                    class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 shadow-lg"
                                    type="button"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                <!-- Type Badge -->
                                <div class="absolute bottom-2 right-2">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded text-white"
                                        :class="{
                                            'bg-green-500': item.file_type === 'image',
                                            'bg-purple-500': item.file_type === 'video',
                                            'bg-orange-500': item.file_type === 'audio'
                                        }"
                                    >
                                        {{ item.file_type === 'image' ? 'صورة' : item.file_type === 'video' ? 'فيديو' : 'صوت' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 bg-white rounded-lg border-2 border-dashed border-gray-300">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                            <p class="text-gray-500 font-medium">لا توجد ملفات في القائمة</p>
                            <p class="text-sm text-gray-400 mt-1">اختر الملفات من المكتبة أدناه</p>
                        </div>
                    </div>

                    <!-- Media Library Section -->
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 bg-indigo-500 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">مكتبة الوسائط</h3>
                                    <p class="text-sm text-gray-600">اختر الملفات لإضافتها للقائمة</p>
                                </div>
                            </div>
                        </div>

                        <!-- Search and Filter -->
                        <div class="mb-6 space-y-4">
                            <!-- Search -->
                            <div class="relative">
                                <input
                                    type="text"
                                    v-model="searchQuery"
                                    placeholder="ابحث عن ملف..."
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <!-- Filter Buttons -->
                            <div class="flex flex-wrap gap-2">
                                <button
                                    @click="filterType = 'all'"
                                    type="button"
                                    :class="[
                                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                        filterType === 'all'
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                >
                                    الكل ({{ mediaItems.length }})
                                </button>
                                <button
                                    @click="filterType = 'image'"
                                    type="button"
                                    :class="[
                                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                        filterType === 'image'
                                            ? 'bg-green-600 text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                >
                                    صور ({{ mediaItems.filter(m => m.file_type === 'image').length }})
                                </button>
                                <button
                                    @click="filterType = 'video'"
                                    type="button"
                                    :class="[
                                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                        filterType === 'video'
                                            ? 'bg-purple-600 text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                >
                                    فيديو ({{ mediaItems.filter(m => m.file_type === 'video').length }})
                                </button>
                                <button
                                    @click="filterType = 'audio'"
                                    type="button"
                                    :class="[
                                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                        filterType === 'audio'
                                            ? 'bg-orange-600 text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                >
                                    صوت ({{ mediaItems.filter(m => m.file_type === 'audio').length }})
                                </button>
                            </div>
                        </div>

                        <!-- Available Items Grid -->
                        <div v-if="availableItems.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            <div
                                v-for="item in availableItems"
                                :key="item.id"
                                @click="toggleMediaItem(item.id)"
                                class="group relative aspect-square rounded-lg overflow-hidden border-2 border-gray-300 cursor-pointer hover:border-indigo-500 hover:shadow-lg transition-all duration-200 bg-gray-100"
                            >
                                <img
                                    v-if="item.file_type === 'image'"
                                    :src="item.url"
                                    class="w-full h-full object-cover"
                                >
                                <video
                                    v-else-if="item.file_type === 'video'"
                                    :src="item.url"
                                    class="w-full h-full object-cover"
                                    muted
                                    loop
                                ></video>
                                <div v-else class="w-full h-full flex items-center justify-center bg-gray-200">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                    </svg>
                                </div>

                                <!-- Add Icon Overlay -->
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 flex items-center justify-center">
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                        <div class="h-12 w-12 bg-indigo-500 rounded-full flex items-center justify-center">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Type Badge -->
                                <div class="absolute bottom-2 right-2">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded text-white"
                                        :class="{
                                            'bg-green-500': item.file_type === 'image',
                                            'bg-purple-500': item.file_type === 'video',
                                            'bg-orange-500': item.file_type === 'audio'
                                        }"
                                    >
                                        {{ item.file_type === 'image' ? 'صورة' : item.file_type === 'video' ? 'فيديو' : 'صوت' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <p class="text-gray-500 font-medium">لا توجد نتائج</p>
                            <p class="text-sm text-gray-400 mt-1">جرب البحث بكلمات مختلفة أو غير الفلتر</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="p-6 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <span class="font-semibold">{{ form.media_items.length }}</span> ملف مختار في القائمة
                        </div>
                        <div class="flex gap-3">
                            <Link
                                :href="route('admin.playlists.index')"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
                            >
                                إلغاء
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing">جاري الحفظ...</span>
                                <span v-else class="flex items-center gap-2">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    حفظ التغييرات
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
