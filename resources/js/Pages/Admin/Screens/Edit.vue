<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

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
    // إذا كان العنصر المحدد هو نفسه، قم بإلغاء التحديد
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


const submit = () => {
    // التأكد من أن assignable_id يتم مسحه إذا لم يتم اختيار نوع
    if (!form.assignable_type) {
        form.assignable_id = '';
    }
    form.post(route('admin.screens.update', props.screen.id));
};
</script>

<template>
    <Head :title="'تعديل الشاشة: ' + form.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">تعديل الشاشة: {{ form.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-8 space-y-8">
                        <!-- قسم تفاصيل الشاشة -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">تفاصيل الشاشة</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name">اسم الشاشة</label>
                                    <input id="name" type="text" v-model="form.name" class="mt-1 block w-full" required>
                                </div>
                                <div>
                                    <label for="screen_code">كود الشاشة</label>
                                    <input id="screen_code" type="text" v-model="form.screen_code" class="mt-1 block w-full" required>
                                </div>
                                <div>
                                    <label for="orientation">اتجاه الشاشة</label>
                                    <select id="orientation" v-model="form.orientation" class="mt-1 block w-full" required>
                                        <option value="landscape">عرضي (Landscape)</option>
                                        <option value="portrait">طولي (Portrait)</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="resolution">الدقة (اختياري)</label>
                                    <input id="resolution" type="text" v-model="form.resolution" placeholder="مثال: 1920x1080" class="mt-1 block w-full">
                                </div>
                            </div>
                        </div>

                        <!-- قسم تخصيص المحتوى -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">تخصيص المحتوى</h3>
                            <div class="space-y-4">
                                <label for="assignable_type">نوع المحتوى</label>
                                <select id="assignable_type" v-model="form.assignable_type" class="block w-full md:w-1/2">
                                    <option value="">-- لا يوجد محتوى --</option>
                                    <option value="App\Models\Playlist">قائمة تشغيل</option>
                                    <option value="App\Models\MediaItem">ملف وسائط واحد (مثبت)</option>
                                </select>

                                <!-- اختيار قائمة التشغيل -->
                                <div v-if="form.assignable_type === 'App\\Models\\Playlist'">
                                    <label for="assignable_id_playlist">اختر قائمة التشغيل</label>
                                    <select id="assignable_id_playlist" v-model="form.assignable_id" class="mt-1 block w-full md:w-1/2">
                                        <option v-for="playlist in playlists" :key="playlist.id" :value="playlist.id">{{ playlist.name }}</option>
                                    </select>
                                </div>

                                <!-- اختيار ملف وسائط مثبت -->
                                <div v-if="form.assignable_type === 'App\\Models\\MediaItem'" class="mt-4">
                                    <label>اختر الملف المراد تثبيته</label>
                                    <div class="mt-2 grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-3 max-h-60 overflow-y-auto p-2 border rounded-md">
                                        <div v-for="item in visualMediaItems" :key="item.id" @click="selectMediaItem(item.id)"
                                             class="relative aspect-square rounded-md overflow-hidden cursor-pointer"
                                             :class="{'ring-4 ring-blue-500 ring-offset-2': form.assignable_id === item.id}">
                                            <img v-if="item.type === 'image'" :src="item.url" class="w-full h-full object-cover">
                                            <video v-else :src="item.url" class="w-full h-full object-cover" muted></video>
                                            <div v-if="form.assignable_id === item.id" class="absolute inset-0 bg-blue-500/50 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- قسم الموسيقى التصويرية -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">الموسيقى التصويرية (اختياري)</h3>
                             <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                 <button type="button" @click="selectAudio('')"
                                     class="p-4 rounded-md border text-center"
                                     :class="{'bg-blue-600 text-white border-blue-600': form.background_audio_id === ''}">
                                     بدون موسيقى
                                 </button>
                                 <button type="button" v-for="audio in backgroundAudios" :key="audio.id" @click="selectAudio(audio.id)"
                                     class="p-4 rounded-md border text-center truncate"
                                     :class="{'bg-blue-600 text-white border-blue-600': form.background_audio_id === audio.id}">
                                     {{ audio.file_name }}
                                 </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end p-6 bg-gray-50 border-t rounded-b-lg">
                        <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                            حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

