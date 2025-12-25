<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    screen: Object,
    playlists: Array,
    visualMediaItems: Array,
    backgroundAudios: Array,
});

const form = useForm({
    _method: 'PUT',
    name: props.screen.name,
    screen_code: props.screen.screen_code,
    orientation: props.screen.orientation,
    resolution: props.screen.resolution || '',
    assignable_type: props.screen.assignment?.assignable_type || '',
    assignable_id: props.screen.assignment?.assignable_id || '',
    background_audio_id: props.screen.background_audio_id || '',
});

// دالة لاختيار محتوى مرئي
const selectMediaItem = (id) => {
    if (form.assignable_id === id) {
        form.assignable_id = '';
    } else {
        form.assignable_id = id;
    }
};

// دالة لاختيار ملف صوتي
const selectAudio = (id) => {
    if (form.background_audio_id === id) {
        form.background_audio_id = '';
    } else {
        form.background_audio_id = id;
    }
};

// الحصول على معلومات المحتوى المختار
const selectedContent = computed(() => {
    if (form.assignable_type === 'App\\Models\\Playlist' && form.assignable_id) {
        const playlist = props.playlists.find(p => p.id == form.assignable_id);
        return playlist ? { type: 'playlist', name: playlist.name } : null;
    } else if (form.assignable_type === 'App\\Models\\MediaItem' && form.assignable_id) {
        const media = props.visualMediaItems.find(m => m.id == form.assignable_id);
        return media ? { type: 'media', name: media.url.split('/').pop(), file_type: media.type } : null;
    }
    return null;
});

const submit = () => {
    if (!form.assignable_type) {
        form.assignable_id = '';
    }
    form.post(route('admin.screens.update', props.screen.id));
};

const getDisplayUrl = () => {
    return route('display.show', form.screen_code);
};
</script>

<template>
    <Head :title="'تعديل الشاشة: ' + form.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">تعديل الشاشة</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ form.name }}</p>
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
            <!-- Quick Preview Card -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">معاينة سريعة</h3>
                        <p class="text-indigo-100 text-sm mb-4">رابط عرض الشاشة:</p>
                        <a
                            :href="getDisplayUrl()"
                            target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            فتح في نافذة جديدة
                        </a>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-indigo-200">الكود</p>
                        <p class="text-2xl font-bold">{{ form.screen_code }}</p>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <form @submit.prevent="submit">
                    <!-- Screen Details Section -->
                    <div class="p-8 border-b border-gray-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-10 w-10 bg-indigo-500 rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">تفاصيل الشاشة</h3>
                                <p class="text-sm text-gray-600">المعلومات الأساسية للشاشة</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    اسم الشاشة <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                    required
                                />
                                <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
                            </div>

                            <div>
                                <label for="screen_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                    كود الشاشة <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="screen_code"
                                    type="text"
                                    v-model="form.screen_code"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                    required
                                />
                                <p class="mt-1 text-xs text-gray-500">يستخدم في رابط العرض</p>
                                <div v-if="form.errors.screen_code" class="mt-2 text-sm text-red-600">{{ form.errors.screen_code }}</div>
                            </div>

                            <div>
                                <label for="orientation" class="block text-sm font-semibold text-gray-700 mb-2">
                                    اتجاه الشاشة <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label
                                        :class="[
                                            'relative flex flex-col items-center p-4 border-2 rounded-xl cursor-pointer transition-all',
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
                                        <svg class="h-10 w-10 mb-2" :class="form.orientation === 'landscape' ? 'text-indigo-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                        <span :class="form.orientation === 'landscape' ? 'font-semibold text-indigo-700' : 'text-gray-700'">عرضي</span>
                                    </label>
                                    <label
                                        :class="[
                                            'relative flex flex-col items-center p-4 border-2 rounded-xl cursor-pointer transition-all',
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
                                        <svg class="h-10 w-10 mb-2" :class="form.orientation === 'portrait' ? 'text-indigo-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span :class="form.orientation === 'portrait' ? 'font-semibold text-indigo-700' : 'text-gray-700'">طولي</span>
                                    </label>
                                </div>
                                <div v-if="form.errors.orientation" class="mt-2 text-sm text-red-600">{{ form.errors.orientation }}</div>
                            </div>

                            <div>
                                <label for="resolution" class="block text-sm font-semibold text-gray-700 mb-2">
                                    الدقة (اختياري)
                                </label>
                                <input
                                    id="resolution"
                                    type="text"
                                    v-model="form.resolution"
                                    placeholder="مثال: 1920x1080"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                />
                                <p class="mt-1 text-xs text-gray-500">تنسيق: العرضxالارتفاع</p>
                                <div v-if="form.errors.resolution" class="mt-2 text-sm text-red-600">{{ form.errors.resolution }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Assignment Section -->
                    <div class="p-8 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-10 w-10 bg-purple-500 rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">تخصيص المحتوى</h3>
                                <p class="text-sm text-gray-600">اختر ما سيتم عرضه على هذه الشاشة</p>
                            </div>
                        </div>

                        <!-- Content Type Selection -->
                        <div class="mb-6">
                            <label for="assignable_type" class="block text-sm font-semibold text-gray-700 mb-3">
                                نوع المحتوى
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label
                                    :class="[
                                        'relative p-4 border-2 rounded-xl cursor-pointer transition-all',
                                        form.assignable_type === ''
                                            ? 'border-purple-500 bg-purple-50'
                                            : 'border-gray-300 hover:border-gray-400'
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.assignable_type"
                                        value=""
                                        class="sr-only"
                                    />
                                    <div class="text-center">
                                        <svg class="h-8 w-8 mx-auto mb-2" :class="form.assignable_type === '' ? 'text-purple-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        <p :class="form.assignable_type === '' ? 'font-semibold text-purple-700' : 'text-gray-700'">بدون محتوى</p>
                                    </div>
                                </label>
                                <label
                                    :class="[
                                        'relative p-4 border-2 rounded-xl cursor-pointer transition-all',
                                        form.assignable_type === 'App\\Models\\Playlist'
                                            ? 'border-purple-500 bg-purple-50'
                                            : 'border-gray-300 hover:border-gray-400'
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.assignable_type"
                                        value="App\Models\Playlist"
                                        class="sr-only"
                                    />
                                    <div class="text-center">
                                        <svg class="h-8 w-8 mx-auto mb-2" :class="form.assignable_type === 'App\\Models\\Playlist' ? 'text-purple-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                        </svg>
                                        <p :class="form.assignable_type === 'App\\Models\\Playlist' ? 'font-semibold text-purple-700' : 'text-gray-700'">قائمة تشغيل</p>
                                    </div>
                                </label>
                                <label
                                    :class="[
                                        'relative p-4 border-2 rounded-xl cursor-pointer transition-all',
                                        form.assignable_type === 'App\\Models\\MediaItem'
                                            ? 'border-purple-500 bg-purple-50'
                                            : 'border-gray-300 hover:border-gray-400'
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.assignable_type"
                                        value="App\Models\MediaItem"
                                        class="sr-only"
                                    />
                                    <div class="text-center">
                                        <svg class="h-8 w-8 mx-auto mb-2" :class="form.assignable_type === 'App\\Models\\MediaItem' ? 'text-purple-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p :class="form.assignable_type === 'App\\Models\\MediaItem' ? 'font-semibold text-purple-700' : 'text-gray-700'">ملف واحد</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Selected Content Preview -->
                        <div v-if="selectedContent" class="mb-6 p-4 bg-white rounded-lg border-2 border-purple-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg v-if="selectedContent.type === 'playlist'" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                        </svg>
                                        <svg v-else class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ selectedContent.name }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ selectedContent.type === 'playlist' ? 'قائمة تشغيل' : selectedContent.file_type === 'image' ? 'صورة' : 'فيديو' }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="form.assignable_type = ''; form.assignable_id = ''"
                                    class="p-2 text-gray-400 hover:text-red-600 transition-colors"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Playlist Selection -->
                        <div v-if="form.assignable_type === 'App\\Models\\Playlist'" class="space-y-4">
                            <label for="assignable_id_playlist" class="block text-sm font-semibold text-gray-700">
                                اختر قائمة التشغيل
                            </label>
                            <select
                                id="assignable_id_playlist"
                                v-model="form.assignable_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                            >
                                <option value="">-- اختر قائمة --</option>
                                <option v-for="playlist in playlists" :key="playlist.id" :value="playlist.id">
                                    {{ playlist.name }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500">سيتم عرض جميع الملفات في القائمة المختارة</p>
                        </div>

                        <!-- Media Item Selection -->
                        <div v-if="form.assignable_type === 'App\\Models\\MediaItem'" class="space-y-4">
                            <label class="block text-sm font-semibold text-gray-700">
                                اختر ملف وسائط واحد (مثبت)
                            </label>
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3 max-h-80 overflow-y-auto p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <div
                                    v-for="item in visualMediaItems"
                                    :key="item.id"
                                    @click="selectMediaItem(item.id)"
                                    class="group relative aspect-square rounded-lg overflow-hidden cursor-pointer border-2 transition-all"
                                    :class="form.assignable_id == item.id ? 'border-purple-500 ring-4 ring-purple-200' : 'border-gray-300 hover:border-purple-300'"
                                >
                                    <img
                                        v-if="item.type === 'image'"
                                        :src="item.url"
                                        class="w-full h-full object-cover"
                                    >
                                    <video
                                        v-else
                                        :src="item.url"
                                        class="w-full h-full object-cover"
                                        muted
                                    ></video>
                                    <div v-if="form.assignable_id == item.id" class="absolute inset-0 bg-purple-500/50 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="absolute bottom-1 right-1">
                                        <span
                                            class="px-2 py-0.5 text-xs font-semibold rounded text-white"
                                            :class="item.type === 'image' ? 'bg-green-500' : 'bg-purple-500'"
                                        >
                                            {{ item.type === 'image' ? 'صورة' : 'فيديو' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">سيتم تثبيت الملف المختار على الشاشة بشكل دائم</p>
                        </div>

                        <!-- Info Box -->
                        <div v-if="form.assignable_type" class="mt-6 bg-blue-50 border-r-4 border-blue-500 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-sm text-gray-700">
                                    <p class="font-semibold mb-1">💡 ملاحظة</p>
                                    <p v-if="form.assignable_type === 'App\\Models\\Playlist'">
                                        عند اختيار قائمة تشغيل، سيتم عرض جميع الملفات في القائمة بشكل متتالي. يمكنك تعديل محتوى القائمة من صفحة قوائم التشغيل.
                                    </p>
                                    <p v-else>
                                        عند اختيار ملف واحد، سيتم تثبيته على الشاشة بشكل دائم حتى تقوم بتغييره. مفيد للعروض الخاصة أو الإعلانات المهمة.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Background Audio Section -->
                    <div class="p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-10 w-10 bg-green-500 rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">الموسيقى التصويرية</h3>
                                <p class="text-sm text-gray-600">اختر ملف صوتي كخلفية موسيقية (اختياري)</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            <button
                                type="button"
                                @click="selectAudio('')"
                                :class="[
                                    'p-4 rounded-lg border-2 text-center transition-all',
                                    form.background_audio_id === ''
                                        ? 'border-green-500 bg-green-50 text-green-700 font-semibold'
                                        : 'border-gray-300 hover:border-gray-400 text-gray-700'
                                ]"
                            >
                                <svg class="h-8 w-8 mx-auto mb-2" :class="form.background_audio_id === '' ? 'text-green-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                <p class="text-sm">بدون موسيقى</p>
                            </button>
                            <button
                                type="button"
                                v-for="audio in backgroundAudios"
                                :key="audio.id"
                                @click="selectAudio(audio.id)"
                                :class="[
                                    'p-4 rounded-lg border-2 text-center transition-all truncate',
                                    form.background_audio_id == audio.id
                                        ? 'border-green-500 bg-green-50 text-green-700 font-semibold'
                                        : 'border-gray-300 hover:border-gray-400 text-gray-700'
                                ]"
                            >
                                <svg class="h-8 w-8 mx-auto mb-2" :class="form.background_audio_id == audio.id ? 'text-green-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                                <p class="text-xs truncate">{{ audio.file_name }}</p>
                            </button>
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
                            <span v-if="form.processing">جاري الحفظ...</span>
                            <span v-else class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                حفظ التغييرات
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
