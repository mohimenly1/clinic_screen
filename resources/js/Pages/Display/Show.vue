<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
    screen: Object,
    mediaItems: Array,
    departments: Array,
    backgroundAudioUrl: String,
    initialBroadcastItem: Object, // **تصحيح**: استقبال بيانات البث الأولية
});

// **تصحيح**: تهيئة متغير البث بالقيمة الأولية من الخادم
const broadcastItem = ref(props.initialBroadcastItem);

// --- نظام الصوت ---
const audioPlayer = ref(null);
const isMuted = ref(true);

// --- نظام الإعلانات ---
const currentIndex = ref(0);
const isVisible = ref(false);
const currentItem = computed(() => props.mediaItems?.[currentIndex.value] || null);
const containerStyle = computed(() => {
    if (props.screen.resolution) {
        const [width, height] = props.screen.resolution.split('x').map(Number);
        if (width && height) return { height: '100%', aspectRatio: `${width} / ${height}` };
    }
    const aspectRatio = props.screen.orientation === 'portrait' ? '9 / 16' : '16 / 9';
    return { height: '100%', aspectRatio };
});

const nextItem = () => {
    isVisible.value = false;
    setTimeout(() => {
        currentIndex.value = (currentIndex.value + 1) % (props.mediaItems.length || 1);
        isVisible.value = true;
    }, 500);
};

const scheduleNext = () => {
    if (currentItem.value?.type === 'image') {
        setTimeout(nextItem, currentItem.value.duration * 1000);
    }
};

// --- نظام التنبيهات ---
const notification = ref(null);

// --- نظام الاستعلامات ---
const showInquiry = ref(false);
const inquiryStep = ref('departments');
const selectedDepartment = ref(null);
const selectedDoctor = ref(null);
let inactivityTimer = null;

const translatedDays = {
    Saturday: 'السبت', Sunday: 'الأحد', Monday: 'الإثنين',
    Tuesday: 'الثلاثاء', Wednesday: 'الأربعاء', Thursday: 'الخميس', Friday: 'الجمعة'
};

const toggleMute = () => {
    isMuted.value = !isMuted.value;
    if (audioPlayer.value) {
        audioPlayer.value.muted = isMuted.value;
        audioPlayer.value.play().catch(e => console.error("Audio play failed:", e));
    }
};

const resetInactivityTimer = () => {
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(closeInquiry, 15000);
};

const openInquiry = () => {
    showInquiry.value = true;
    resetInactivityTimer();
};

const closeInquiry = () => {
    showInquiry.value = false;
    inquiryStep.value = 'departments';
    // **تصحيح**: إعادة تعيين البيانات عند الإغلاق
    selectedDepartment.value = null;
    selectedDoctor.value = null;
    clearTimeout(inactivityTimer);
};

const selectDepartment = (department) => {
    selectedDepartment.value = department;
    inquiryStep.value = 'doctors';
    resetInactivityTimer();
};

const selectDoctor = (doctor) => {
    selectedDoctor.value = doctor;
    inquiryStep.value = 'details';
    resetInactivityTimer();
};

const goBack = () => {
    if (inquiryStep.value === 'details') inquiryStep.value = 'doctors';
    else if (inquiryStep.value === 'doctors') inquiryStep.value = 'departments';
    resetInactivityTimer();
};

onMounted(() => {
    if (props.mediaItems.length > 0 && !broadcastItem.value) {
        isVisible.value = true;
        scheduleNext();
    }
    // **تحديث**: إضافة سجلات تشخيصية للاتصال الفوري
    if (window.Echo) {
        console.log("Echo is available. Connecting to 'displays' channel...");
        window.Echo.channel('displays')
            .listen('AppointmentApproaching', (event) => {
                console.log('Real-time event received: AppointmentApproaching', event);
                notification.value = event;
                setTimeout(() => notification.value = null, 15000);
            })
            .listen('BroadcastMedia', (event) => {
                console.log('Real-time event received: BroadcastMedia', event);
                broadcastItem.value = event.mediaItem;
            })
            .listen('StopBroadcast', (event) => {
                console.log('Real-time event received: StopBroadcast', event);
                broadcastItem.value = null;
            });

        // للتشخيص المتقدم: مراقبة حالة الاتصال
        window.Echo.connector.pusher.connection.bind('state_change', function(states) {
            console.log("Pusher connection state changed from", states.previous, "to", states.current);
        });
    } else {
        console.error("Laravel Echo not found. Real-time features will not work.");
    }
});

watch(currentItem, (newItem) => {
    if (newItem?.type === 'image' && !broadcastItem.value) {
        scheduleNext();
    }
});
</script>

<template>
    <audio ref="audioPlayer" v-if="backgroundAudioUrl" :src="backgroundAudioUrl" loop :muted="isMuted"></audio>
    <div class="fixed inset-0 bg-black w-full h-full overflow-hidden flex items-center justify-center font-sans">
        <div :style="containerStyle" class="relative">
            <!-- مشغل الإعلانات -->
            <transition name="fade">
                <div v-if="currentItem && isVisible && !broadcastItem" :key="currentItem.id" class="absolute inset-0">
                    <img v-if="currentItem.type === 'image'" :src="currentItem.url" class="w-full h-full object-cover"/>
                    <video v-else-if="currentItem.type === 'video'" :src="currentItem.url" class="w-full h-full object-cover" autoplay muted playsinline @ended="nextItem"></video>
                </div>
            </transition>
            <div v-if="!currentItem && !showInquiry && !broadcastItem" class="w-full h-full flex items-center justify-center text-white text-2xl">
                لا يوجد محتوى لعرضه.
            </div>
            <transition name="fade">
                <div v-if="broadcastItem" class="absolute inset-0 z-40">
                    <img v-if="broadcastItem.type === 'image'" :src="broadcastItem.url" class="w-full h-full object-cover"/>
                    <video v-else-if="broadcastItem.type === 'video'" :src="broadcastItem.url" class="w-full h-full object-cover" autoplay muted playsinline loop></video>
                </div>
            </transition>
            <div v-if="!broadcastItem">
                <button @click="toggleMute" v-if="backgroundAudioUrl" class="absolute top-4 right-4 z-20 text-white rounded-full p-2 bg-black/40 hover:bg-black/60 transition">
                    <svg v-if="isMuted" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l4-4m0 0l-4-4m4 4H7" /></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /></svg>
                </button>
                <div v-if="!showInquiry" class="absolute bottom-5 right-5 z-20">
                    <button @click="openInquiry" class="bg-blue-600 text-white rounded-full shadow-lg py-4 px-6 text-xl font-bold hover:bg-blue-700 transition">
                        الاستعلامات
                    </button>
                </div>
            </div>
        </div>
        <transition name="inquiry-fade">
            <div v-if="showInquiry && !broadcastItem" @click.self="resetInactivityTimer" class="absolute inset-0 z-30 flex items-center justify-center p-4">
                <div :style="containerStyle" class="relative flex flex-col bg-gray-900/95 rounded-lg overflow-hidden">
                    <div class="flex-grow flex items-center justify-center overflow-y-auto p-8">
                        <div class="w-full">
                            <div v-if="inquiryStep === 'departments'">
                                <h2 class="text-4xl font-bold text-white text-center mb-8">اختر القسم</h2>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                                    <div v-for="department in departments" :key="department.id" @click="selectDepartment(department)"
                                        class="bg-white/10 text-white text-2xl font-semibold p-8 rounded-lg text-center cursor-pointer hover:bg-white/20 transition">
                                        {{ department.name }}
                                    </div>
                                </div>
                            </div>
                            <div v-if="inquiryStep === 'doctors'">
                                <h2 class="text-4xl font-bold text-white text-center mb-8">{{ selectedDepartment.name }}</h2>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                    <div v-for="doctor in selectedDepartment.doctors" :key="doctor.id" @click="selectDoctor(doctor)"
                                        class="bg-white/10 p-4 rounded-lg text-center cursor-pointer hover:bg-white/20 transition">
                                        <img :src="doctor.photo_url || 'https://placehold.co/128x128/EFEFEF/AAAAAA?text=No+Image'" class="w-32 h-32 rounded-full mx-auto object-cover mb-4 ring-4 ring-white/20">
                                        <p class="text-white text-xl font-semibold">{{ doctor.name }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="inquiryStep === 'details'">
                                <div class="flex flex-col md:flex-row items-center gap-8 text-center md:text-right">
                                    <img :src="selectedDoctor.photo_url || 'https://placehold.co/200x200/EFEFEF/AAAAAA?text=No+Image'" class="w-48 h-48 rounded-full object-cover ring-8 ring-white/20 flex-shrink-0">
                                    <div>
                                        <h2 class="text-5xl font-bold text-white">{{ selectedDoctor.name }}</h2>
                                        <p class="text-2xl text-gray-300 mt-2">{{ selectedDepartment.name }}</p>
                                        <div v-if="selectedDoctor.schedules.length > 0" class="mt-6 space-y-3">
                                            <div v-for="schedule in selectedDoctor.schedules" :key="schedule.id" class="text-xl text-white">
                                                <span class="font-bold">{{ translatedDays[schedule.day_of_week] }}:</span>
                                                <span> من {{ schedule.start_time.substring(0, 5) }} إلى {{ schedule.end_time.substring(0, 5) }}</span>
                                                <span class="mx-2">|</span>
                                                <span>العيادة: {{ schedule.clinic_number }}</span>
                                                <span class="mx-2">|</span>
                                                <span>الطابق: {{ schedule.floor }}</span>
                                            </div>
                                        </div>
                                        <p v-else class="text-xl text-gray-400 mt-6">لا توجد مواعيد متاحة لهذا الطبيب.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex-shrink-0 flex justify-between items-center h-20 px-8 pb-4">
                        <button v-if="inquiryStep !== 'departments'" @click="goBack" class="bg-gray-500 text-white rounded-full py-3 px-6 text-lg hover:bg-gray-600 transition">
                            رجوع
                        </button>
                        <div class="flex-grow"></div>
                        <button @click="closeInquiry" class="bg-red-600 text-white rounded-full py-3 px-6 text-lg hover:bg-red-700 transition">
                            إغلاق
                        </button>
                    </div>
                </div>
            </div>
        </transition>
        <transition name="slide-fade">
            <div v-if="notification" class="absolute inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
                 <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full flex items-center p-6 space-x-6 rtl:space-x-reverse">
                    <div class="flex-shrink-0">
                        <img class="h-32 w-32 rounded-full object-cover ring-4 ring-blue-500" :src="notification.photoUrl || 'https://placehold.co/128x128/EFEFEF/AAAAAA?text=No+Image'" alt="Doctor Photo">
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-blue-600 uppercase tracking-wider">تنبيه موعد</div>
                        <p class="mt-1 text-3xl font-bold text-gray-900">{{ notification.doctor.name }}</p>
                        <p class="mt-2 text-lg text-gray-600">
                            سيكون متواجدًا الآن في
                            <span class="font-bold text-gray-800">العيادة رقم {{ notification.schedule.clinic_number }}</span>
                            بالطابق
                            <span class="font-bold text-gray-800">{{ notification.schedule.floor }}</span>.
                        </p>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
<style>
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.inquiry-fade-enter-active, .inquiry-fade-leave-active { transition: opacity 0.3s ease; }
.inquiry-fade-enter-from, .inquiry-fade-leave-to { opacity: 0; }
.slide-fade-enter-active { transition: all 0.4s ease-out; }
.slide-fade-leave-active { transition: all 0.4s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateY(20px); opacity: 0; }
</style>

