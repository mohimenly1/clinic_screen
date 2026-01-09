<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue';
import { initSpeechRecognition, processVoiceCommand } from '@/utils/voiceCommands';
import RoomListView from '@/Components/RoomListView.vue';

const props = defineProps({
    screen: Object,
    mediaItems: Array,
    departments: Array,
    backgroundAudioUrl: String,
    initialBroadcastItem: Object,
    floors: Array,
});

const broadcastItem = ref(props.initialBroadcastItem);
const audioPlayer = ref(null);
const isMuted = ref(true);
const currentIndex = ref(0);
const isVisible = ref(false);

// **جديد**: استخدام متغير محلي للوسائط لتمكين التحديث الفوري
const localMediaItems = ref(props.mediaItems);

const currentItem = computed(() => localMediaItems.value?.[currentIndex.value] || null);
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
        currentIndex.value = (currentIndex.value + 1) % (localMediaItems.value.length || 1);
        isVisible.value = true;
    }, 500);
};

// **جديد**: دالة لبدء أو إعادة تشغيل المشغل
const startPlayer = () => {
    if (localMediaItems.value.length > 0 && !broadcastItem.value) {
        currentIndex.value = 0; // ابدأ دائمًا من العنصر الأول عند التحديث
        isVisible.value = true;
        scheduleNext();
    } else {
        isVisible.value = false; // إخفاء المشغل إذا كانت القائمة الجديدة فارغة
    }
};


const scheduleNext = () => {
    // إيقاف أي مؤقتات سابقة لضمان عدم التداخل
    clearTimeout(window.playerTimeout);
    if (currentItem.value?.type === 'image') {
        window.playerTimeout = setTimeout(nextItem, currentItem.value.duration * 1000);
    }
};

const notification = ref(null);
const showInquiry = ref(false);
const showMap = ref(false);
const inquiryStep = ref('departments');
const selectedDepartment = ref(null);
const selectedDoctor = ref(null);
let inactivityTimer = null;

const openMap = () => {
    showMap.value = true;
};

const closeMap = () => {
    showMap.value = false;
};

// Voice Recognition
const isListening = ref(false);
const speechRecognition = ref(null);
const voiceCommandFeedback = ref(null);
const currentRecognitionLang = ref('en-US'); // اللغة الحالية للتعرف الصوتي
const translatedDays = {
    Saturday: 'السبت', Sunday: 'الأحد', Monday: 'الإثنين',
    Tuesday: 'الثلاثاء', Wednesday: 'الأربعاء', Thursday: 'الخميس', Friday: 'الجمعة'
};

// ترتيب الأيام حسب الأسبوع
const dayOrder = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// دالة لترتيب المواعيد حسب الأيام
const sortedSchedules = computed(() => {
    if (!selectedDoctor.value || !selectedDoctor.value.schedules) return [];

    const schedules = [...selectedDoctor.value.schedules];
    return schedules.sort((a, b) => {
        const indexA = dayOrder.indexOf(a.day_of_week);
        const indexB = dayOrder.indexOf(b.day_of_week);
        return indexA - indexB;
    });
});

// دالة لتجميع المواعيد حسب اليوم
const schedulesByDay = computed(() => {
    if (!sortedSchedules.value.length) return [];

    const grouped = {};
    sortedSchedules.value.forEach(schedule => {
        if (!grouped[schedule.day_of_week]) {
            grouped[schedule.day_of_week] = [];
        }
        grouped[schedule.day_of_week].push(schedule);
    });

    // ترتيب حسب dayOrder
    return dayOrder.map(day => ({
        day,
        dayLabel: translatedDays[day],
        schedules: grouped[day] || []
    })).filter(item => item.schedules.length > 0);
});

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

// Voice Recognition Functions
const startVoiceRecognition = async () => {
    if (!speechRecognition.value) {
        voiceCommandFeedback.value = { type: 'error', message: 'المتصفح لا يدعم التعرف الصوتي' };
        setTimeout(() => voiceCommandFeedback.value = null, 4000);
        return;
    }

    if (isListening.value) {
        stopVoiceRecognition();
        return;
    }

    // التحقق من الاتصال بالإنترنت
    if (!navigator.onLine) {
        voiceCommandFeedback.value = { type: 'error', message: 'يحتاج التعرف الصوتي إلى اتصال بالإنترنت. يرجى التحقق من الاتصال والمحاولة مرة أخرى.' };
        setTimeout(() => voiceCommandFeedback.value = null, 4000);
        return;
    }

    // طلب إذن الوصول للميكروفون
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        // إذا حصلنا على الإذن، نتوقف عن بث الصوت مباشرة (سنستخدمه لاحقاً)
        stream.getTracks().forEach(track => track.stop());
    } catch (error) {
        console.error('Microphone permission error:', error);
        isListening.value = false;

        if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
            voiceCommandFeedback.value = {
                type: 'error',
                message: 'تم رفض الوصول للميكروفون. يرجى السماح بالوصول في إعدادات المتصفح ثم حاول مرة أخرى.'
            };
        } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
            voiceCommandFeedback.value = {
                type: 'error',
                message: 'لم يتم العثور على ميكروفون. يرجى التأكد من وجود ميكروفون متصل.'
            };
        } else {
            voiceCommandFeedback.value = {
                type: 'error',
                message: 'حدث خطأ في الوصول للميكروفون. يرجى المحاولة مرة أخرى.'
            };
        }

        setTimeout(() => voiceCommandFeedback.value = null, 5000);
        return;
    }

    try {
        isListening.value = true;
        const langMessage = currentRecognitionLang.value.startsWith('ar')
            ? 'أنا أستمع... (يمكنك قول اسم القسم مثل: "قسم العظام" أو اسم الطبيب مباشرة)'
            : 'أنا أستمع... (يمكنك قول اسم القسم أو اسم الطبيب)';
        voiceCommandFeedback.value = {
            type: 'listening',
            message: langMessage
        };

        speechRecognition.value.onresult = (event) => {
            // محاولة الحصول على أفضل نتيجة من البدائل المتاحة
            let transcript = '';
            let confidence = 0;

            // البحث في جميع النتائج والبدائل للحصول على أفضل تطابق
            for (let i = 0; i < event.results.length; i++) {
                for (let j = 0; j < event.results[i].length; j++) {
                    const alternative = event.results[i][j];
                    if (alternative.confidence > confidence) {
                        transcript = alternative.transcript;
                        confidence = alternative.confidence;
                    }
                }
            }

            // إذا لم نجد نتيجة بثقة عالية، نستخدم النتيجة الأولى
            if (!transcript && event.results[0] && event.results[0][0]) {
                transcript = event.results[0][0].transcript;
            }

            console.log('Speech recognition result:', transcript, 'Confidence:', confidence);
            voiceCommandFeedback.value = { type: 'processing', message: `تم التعرف على: ${transcript}` };

            // معالجة الأمر
            const result = processVoiceCommand(transcript, props.departments);

            if (result.type === 'doctor' && result.data) {
                // فتح الطبيب مباشرة
                // أولاً نفتح واجهة الاستعلامات إذا لم تكن مفتوحة
                if (!showInquiry.value) {
                    showInquiry.value = true;
                }
                // ثم نختار القسم أولاً ثم الطبيب
                setTimeout(() => {
                    selectDepartment(result.data.department);
                    setTimeout(() => {
                        selectDoctor(result.data.doctor);
                        voiceCommandFeedback.value = { type: 'success', message: `تم فتح بيانات الدكتور ${result.data.doctor.name}` };
                        setTimeout(() => voiceCommandFeedback.value = null, 3000);
                    }, 300);
                }, 500);
            } else if (result.type === 'department' && result.data) {
                // فتح القسم تلقائياً
                // أولاً نفتح واجهة الاستعلامات إذا لم تكن مفتوحة
                if (!showInquiry.value) {
                    showInquiry.value = true;
                }
                // ثم نختار القسم
                setTimeout(() => {
                    selectDepartment(result.data);
                    voiceCommandFeedback.value = { type: 'success', message: `تم فتح قسم ${result.data.name}` };
                    setTimeout(() => voiceCommandFeedback.value = null, 3000);
                }, 500);
            } else {
                voiceCommandFeedback.value = { type: 'error', message: 'لم أتمكن من فهم الأمر. حاول مرة أخرى.' };
                setTimeout(() => voiceCommandFeedback.value = null, 3000);
            }

            isListening.value = false;
        };

        speechRecognition.value.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            isListening.value = false;

            let errorMessage = 'حدث خطأ. حاول مرة أخرى.';

            switch (event.error) {
                case 'no-speech':
                    errorMessage = 'لم يتم اكتشاف أي صوت. حاول مرة أخرى.';
                    break;
                case 'not-allowed':
                    errorMessage = 'تم رفض الوصول للميكروفون. يرجى السماح بالوصول في إعدادات المتصفح.';
                    break;
                case 'network':
                    errorMessage = 'خطأ في الاتصال بالإنترنت. يرجى التحقق من الاتصال والمحاولة مرة أخرى.';
                    break;
                case 'audio-capture':
                    errorMessage = 'لم يتم العثور على ميكروفون. يرجى التحقق من وجود ميكروفون متصل.';
                    break;
                case 'aborted':
                    // تم الإلغاء بواسطة المستخدم أو النظام - لا نعرض رسالة خطأ
                    return;
                case 'service-not-allowed':
                    // إذا كانت اللغة الحالية هي العربية، نجرب الإنجليزية كبديل
                    if (currentRecognitionLang.value.startsWith('ar')) {
                        console.log('Arabic not supported, trying English...');
                        currentRecognitionLang.value = 'en-US';
                        if (speechRecognition.value) {
                            speechRecognition.value.lang = 'en-US';
                            // إعادة المحاولة بعد قليل
                            setTimeout(() => {
                                voiceCommandFeedback.value = {
                                    type: 'listening',
                                    message: 'العربية غير مدعومة. أستمع بالإنجليزية... (مثل: "bones", "internal medicine")'
                                };
                                isListening.value = true;
                                speechRecognition.value.start();
                            }, 500);
                            return; // لا نعرض رسالة خطأ، سنحاول مرة أخرى
                        }
                    }
                    errorMessage = 'خدمة التعرف الصوتي غير متاحة. يرجى التأكد من: 1) استخدام متصفح Chrome أو Edge 2) الاتصال بالإنترنت 3) الموقع يعمل على HTTPS';
                    break;
                default:
                    errorMessage = `حدث خطأ: ${event.error}. يرجى المحاولة مرة أخرى.`;
            }

            voiceCommandFeedback.value = { type: 'error', message: errorMessage };
            setTimeout(() => voiceCommandFeedback.value = null, 4000);
        };

        speechRecognition.value.onend = () => {
            isListening.value = false;
        };

        speechRecognition.value.start();
    } catch (error) {
        console.error('Error starting speech recognition:', error);
        isListening.value = false;
        voiceCommandFeedback.value = { type: 'error', message: 'حدث خطأ. حاول مرة أخرى.' };
        setTimeout(() => voiceCommandFeedback.value = null, 3000);
    }
};

const stopVoiceRecognition = () => {
    if (speechRecognition.value && isListening.value) {
        speechRecognition.value.stop();
        isListening.value = false;
        voiceCommandFeedback.value = null;
    }
};

onMounted(() => {
    startPlayer(); // بدء المشغل عند تحميل الصفحة

    // تهيئة Speech Recognition
    const recognition = initSpeechRecognition();
    if (recognition) {
        // محاولة استخدام اللغة العربية - نبدأ بـ 'ar'
        try {
            recognition.lang = 'ar';
            currentRecognitionLang.value = 'ar';
        } catch (e) {
            // إذا فشلت، نجرب 'ar-EG' أو 'ar-XA'
            try {
                recognition.lang = 'ar-EG';
                currentRecognitionLang.value = 'ar-EG';
            } catch (e2) {
                // كحل أخير، نستخدم الإنجليزية
                recognition.lang = 'en-US';
                currentRecognitionLang.value = 'en-US';
            }
        }
        speechRecognition.value = recognition;
    } else {
        console.warn('Speech Recognition not supported in this browser');
    }

    if (window.Echo) {
        // الاستماع للأحداث العامة
        console.log("Connecting to public 'displays' channel...");
        window.Echo.channel('displays')
            .listen('.AppointmentApproaching', (event) => {
                console.log('Real-time event received: AppointmentApproaching', event);
                notification.value = event;
                setTimeout(() => notification.value = null, 15000);
            })
            .listen('.BroadcastMedia', (event) => {
                console.log('Real-time event received: BroadcastMedia', event);
                broadcastItem.value = event.mediaItem;
            })
            .listen('.StopBroadcast', (event) => {
                console.log('Real-time event received: StopBroadcast', event);
                broadcastItem.value = null;
            });

        // **جديد**: الاستماع للأحداث الخاصة بهذه الشاشة
        if (props.screen.screen_code) {
             console.log(`Connecting to private 'displays.${props.screen.screen_code}' channel...`);
             window.Echo.channel(`displays.${props.screen.screen_code}`)
                .listen('.ScreenContentUpdated', (event) => {
                    console.log('Real-time event received: ScreenContentUpdated', event);
                    localMediaItems.value = event.mediaItems;
                    startPlayer(); // إعادة تشغيل المشغل بالمحتوى الجديد
                });
        }

        window.Echo.connector.pusher.connection.bind('state_change', function(states) {
            console.log("Pusher connection state changed from", states.previous, "to", states.current);
        });
    } else {
        console.error("Laravel Echo not found. Real-time features will not work.");
    }
});

onUnmounted(() => {
    stopVoiceRecognition();
    if (speechRecognition.value) {
        speechRecognition.value.abort();
    }
});

watch(currentItem, (newItem) => {
    if (newItem?.type === 'image' && !broadcastItem.value) {
        scheduleNext();
    }
});

watch(broadcastItem, (newVal, oldVal) => {
    if (newVal === null && oldVal !== null) {
        console.log("Broadcast ended. Restarting regular playlist...");
        startPlayer();
    }
});
</script>

<template>
    <audio ref="audioPlayer" v-if="backgroundAudioUrl" :src="backgroundAudioUrl" loop :muted="isMuted"></audio>
    <div class="fixed inset-0 bg-black w-full h-full overflow-hidden flex items-center justify-center font-sans">
        <div :style="containerStyle" class="relative">
            <!-- مشغل الإعلانات - مع إزاحة عند فتح الاستعلامات -->
            <transition name="fade">
                <div 
                    v-if="currentItem && isVisible && !broadcastItem" 
                    :key="currentItem.id" 
                    class="absolute inset-0 transition-all duration-500 ease-in-out"
                    :class="{
                        'translate-x-[-20%] scale-90 opacity-60': showInquiry,
                        'translate-x-0 scale-100 opacity-100': !showInquiry
                    }"
                >
                    <img v-if="currentItem.type === 'image'" :src="currentItem.url" class="w-full h-full object-cover"/>
                    <video v-else-if="currentItem.type === 'video'" :src="currentItem.url" class="w-full h-full object-cover" autoplay muted playsinline @ended="nextItem"></video>
                </div>
            </transition>
            <div 
                v-if="!currentItem && !showInquiry && !broadcastItem" 
                class="w-full h-full flex items-center justify-center text-white text-2xl transition-all duration-500 ease-in-out"
                :class="{
                    'translate-x-[-20%] scale-90 opacity-60': showInquiry,
                    'translate-x-0 scale-100 opacity-100': !showInquiry
                }"
            >
                لا يوجد محتوى لعرضه.
            </div>
            <transition name="fade">
                <div v-if="broadcastItem" class="absolute inset-0 z-40">
                    <img v-if="broadcastItem.type === 'image'" :src="broadcastItem.url" class="w-full h-full object-cover"/>
                    <video v-else-if="broadcastItem.type === 'video'" :src="broadcastItem.url" class="w-full h-full object-cover" autoplay muted playsinline loop></video>
                </div>
            </transition>

            <!-- شريط الأدوات الجانبي -->
            <div v-if="!broadcastItem" class="absolute left-4 top-1/2 -translate-y-1/2 z-30 flex flex-col gap-3">
                <!-- زر التحكم الصوتي -->
                <button
                    @click="startVoiceRecognition"
                    class="group relative bg-white/10 backdrop-blur-md hover:bg-white/20 rounded-full p-4 shadow-xl border border-white/20 transition-all duration-300 hover:scale-110"
                    :class="{'bg-red-500/80 border-red-400 animate-pulse': isListening, 'bg-green-500/80 border-green-400': !isListening && speechRecognition}"
                >
                    <svg v-if="!isListening" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                    </svg>
                    <!-- Tooltip -->
                    <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50">
                        {{ isListening ? 'جارٍ الاستماع... (اضغط للتوقف)' : speechRecognition ? 'التحكم الصوتي - اضغط للتحدث' : 'التعرف الصوتي غير مدعوم' }}
                    </span>
                </button>

                <!-- زر الاستعلامات - تصميم ملفت مع تأثير pulse -->
                <button
                    @click="openInquiry"
                    class="group relative rounded-full p-4 shadow-2xl transition-all duration-300 hover:scale-110 inquiry-button"
                    :class="{
                        'bg-gradient-to-br from-orange-500 via-red-500 to-pink-500 border-2 border-white/30': !showInquiry,
                        'bg-gradient-to-br from-blue-500 to-purple-500 border-2 border-blue-300': showInquiry
                    }"
                >
                    <!-- تأثير Pulse/Animation -->
                    <div v-if="!showInquiry" class="absolute inset-0 rounded-full bg-gradient-to-br from-orange-400 via-red-400 to-pink-400 animate-ping opacity-75"></div>
                    <div v-if="!showInquiry" class="absolute inset-0 rounded-full bg-gradient-to-br from-orange-500 via-red-500 to-pink-500 animate-pulse"></div>
                    
                    <!-- أيقونة الاستعلامات -->
                    <div class="relative z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white drop-shadow-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <!-- مربع حواري يظهر ويختفي -->
                    <div v-if="!showInquiry" class="absolute right-full mr-4 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-300 animate-bounce-slow">
                        <div class="relative">
                            <!-- السهم -->
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-0 h-0 border-t-8 border-t-transparent border-b-8 border-b-transparent border-r-8 border-r-orange-500"></div>
                            <!-- المربع الحواري -->
                            <div class="bg-gradient-to-br from-orange-500 to-red-500 text-white px-4 py-2 rounded-lg shadow-xl whitespace-nowrap mr-2">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="font-bold text-sm">استفسر هنا!</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tooltip العادي -->
                    <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-20">
                        الاستعلامات
                    </span>
                </button>

                <!-- زر الصوت (إذا كان هناك موسيقى خلفية) -->
                <button
                    v-if="backgroundAudioUrl"
                    @click="toggleMute"
                    class="group relative bg-white/10 backdrop-blur-md hover:bg-white/20 rounded-full p-4 shadow-xl border border-white/20 transition-all duration-300 hover:scale-110"
                >
                    <svg v-if="isMuted" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 14l4-4m0 0l-4-4m4 4H7" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                    <!-- Tooltip -->
                    <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                        {{ isMuted ? 'تشغيل الصوت' : 'إيقاف الصوت' }}
                    </span>
                </button>

                <!-- زر الخريطة التفاعلية -->
                <button
                    @click="openMap"
                    class="group relative bg-white/10 backdrop-blur-md hover:bg-white/20 rounded-full p-4 shadow-xl border border-white/20 transition-all duration-300 hover:scale-110"
                    :class="{'bg-green-500/80 border-green-400': showMap}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    <!-- Tooltip -->
                    <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                        خريطة العيادة التفاعلية
                    </span>
                </button>
            </div>

            <!-- قائمة الغرف مع الواقع المعزز -->
            <transition name="fade">
                <RoomListView
                    v-if="showMap"
                    :floors="props.floors || []"
                    @close="closeMap"
                />
            </transition>

            <!-- نافذة الاستعلامات - تصميم عصري مع موضع في منتصف الشاشة -->
            <transition name="modal">
                <div v-if="showInquiry && !broadcastItem" class="absolute inset-0 z-40 flex items-center justify-center p-4">
                    <!-- Overlay -->
                    <div @click="resetInactivityTimer" class="absolute inset-0 bg-gradient-to-br from-black/60 via-black/70 to-black/80 backdrop-blur-sm"></div>

                    <!-- Modal Content - موضع في منتصف الشاشة (أسفل قليلاً) -->
                    <div class="relative w-full max-w-6xl bg-gradient-to-br from-white via-blue-50/30 to-purple-50/30 rounded-3xl shadow-2xl overflow-hidden border border-white/20 backdrop-blur-xl transform translate-y-8">
                        <!-- Header Bar -->
                        <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 p-6 flex items-center justify-between shadow-lg">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-3xl font-bold text-white">
                                        <span v-if="inquiryStep === 'departments'">اختر القسم</span>
                                        <span v-else-if="inquiryStep === 'doctors'">{{ selectedDepartment.name }}</span>
                                        <span v-else>{{ selectedDoctor.name }}</span>
                                    </h2>
                                    <p class="text-white/80 text-sm mt-1">استعلام سريع عن معلومات الأطباء والمواعيد</p>
                                </div>
                            </div>
                            <!-- زر الإغلاق في الأعلى -->
                            <button
                                @click="closeInquiry"
                                class="group relative h-12 w-12 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center transition-all duration-200 backdrop-blur-sm hover:scale-110"
                            >
                                <svg class="h-6 w-6 text-white group-hover:rotate-90 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Content Area -->
                        <div class="flex-1 overflow-y-auto p-6 bg-white/40 backdrop-blur-sm min-h-0 max-h-[calc(100vh-250px)]">
                            <!-- Departments View -->
                            <div v-if="inquiryStep === 'departments'" class="space-y-6">
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                                    <div
                                        v-for="department in departments"
                                        :key="department.id"
                                        @click="selectDepartment(department)"
                                        class="group relative bg-gradient-to-br from-white to-blue-50/50 rounded-2xl p-6 cursor-pointer transition-all duration-300 hover:scale-105 hover:shadow-xl border border-blue-100 hover:border-indigo-300"
                                    >
                                        <div class="flex flex-col items-center text-center space-y-4">
                                            <div class="h-20 w-20 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow">
                                                <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-800 group-hover:text-indigo-600 transition-colors">
                                                {{ department.name }}
                                            </h3>
                                        </div>
                                        <!-- Hover Effect -->
                                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/0 to-purple-500/0 group-hover:from-indigo-500/5 group-hover:to-purple-500/5 rounded-2xl transition-all duration-300"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Doctors View -->
                            <div v-if="inquiryStep === 'doctors'" class="space-y-6">
                                <div class="flex items-center justify-between mb-4">
                                    <button
                                        @click="goBack"
                                        class="flex items-center gap-2 px-4 py-2 bg-white/80 hover:bg-white rounded-xl text-gray-700 font-medium transition-all duration-200 hover:scale-105 shadow-md"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                        </svg>
                                        <span>رجوع</span>
                                    </button>
                                    <div class="text-sm text-gray-600 bg-white/60 px-4 py-2 rounded-xl font-medium">
                                        {{ selectedDepartment.doctors.length }} طبيب
                                    </div>
                                </div>
                                <div class="max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                        <div
                                            v-for="doctor in selectedDepartment.doctors"
                                            :key="doctor.id"
                                            @click="selectDoctor(doctor)"
                                            class="group relative bg-gradient-to-br from-white to-blue-50/50 rounded-2xl p-6 cursor-pointer transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl border-2 border-blue-100 hover:border-indigo-400 hover:from-indigo-50 hover:to-purple-50/50"
                                        >
                                            <!-- Doctor Icon -->
                                            <div class="flex justify-center mb-4">
                                                <div class="relative">
                                                    <div class="h-24 w-24 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-xl group-hover:shadow-2xl transition-all duration-300 group-hover:scale-110">
                                                        <svg class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                    </div>
                                                    <!-- Badge -->
                                                    <div class="absolute -bottom-2 -right-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg border-2 border-white">
                                                        دكتور
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Doctor Name -->
                                            <div class="text-center space-y-2">
                                                <h3 class="text-xl font-bold text-gray-800 group-hover:text-indigo-600 transition-colors leading-tight">
                                                    {{ doctor.name }}
                                                </h3>
                                                <!-- Schedules Count -->
                                                <div v-if="doctor.schedules && doctor.schedules.length > 0" class="flex items-center justify-center gap-2 text-sm text-gray-600">
                                                    <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <span>{{ doctor.schedules.length }} مواعيد</span>
                                                </div>
                                                <div v-else class="text-xs text-gray-400 italic">
                                                    لا توجد مواعيد
                                                </div>
                                            </div>

                                            <!-- Hover Arrow -->
                                            <div class="absolute top-4 left-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <div class="bg-indigo-500 text-white rounded-full p-2">
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Details View -->
                            <div v-if="inquiryStep === 'details'" class="h-full flex flex-col">
                                <!-- Header with Back Button -->
                                <div class="flex items-center justify-between mb-4 flex-shrink-0">
                                    <button
                                        @click="goBack"
                                        class="flex items-center gap-2 px-4 py-2 bg-white/80 hover:bg-white rounded-xl text-gray-700 font-medium transition-all duration-200 hover:scale-105 shadow-md"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                        </svg>
                                        <span>رجوع</span>
                                    </button>
                                    <div class="text-sm text-gray-600 bg-white/60 px-4 py-2 rounded-xl font-medium">
                                        {{ selectedDoctor.schedules?.length || 0 }} موعد
                                    </div>
                                </div>

                                <!-- Scrollable Content -->
                                <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                                    <div class="bg-gradient-to-br from-white via-blue-50/30 to-purple-50/30 rounded-2xl p-4 shadow-xl border border-purple-100">
                                        <!-- Doctor Header - Compact -->
                                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-purple-200">
                                            <!-- Doctor Icon - Smaller -->
                                            <div class="flex-shrink-0">
                                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-md">
                                                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                </div>
                                            </div>

                                            <!-- Doctor Info - Compact -->
                                            <div class="flex-1 min-w-0">
                                                <h2 class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-1 truncate">
                                                    {{ selectedDoctor.name }}
                                                </h2>
                                                <div class="inline-flex items-center gap-1.5 px-2 py-1 bg-indigo-100 rounded-lg">
                                                    <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                    </svg>
                                                    <span class="text-xs font-semibold text-indigo-700 truncate">{{ selectedDepartment.name }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Schedules - Compact Table Style -->
                                        <div v-if="schedulesByDay && schedulesByDay.length > 0" class="space-y-2">
                                            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2 mb-2">
                                                <svg class="h-4 w-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span>المواعيد المتاحة</span>
                                            </h3>

                                            <!-- Compact Schedule List -->
                                            <div class="space-y-1.5">
                                                <div v-for="dayGroup in schedulesByDay" :key="dayGroup.day" class="space-y-1">
                                                    <!-- Day Header - Compact -->
                                                    <div class="flex items-center gap-2 px-2 py-1 bg-gradient-to-r from-indigo-500/90 to-purple-500/90 rounded-lg">
                                                        <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        <span class="text-white font-semibold text-sm">{{ dayGroup.dayLabel }}</span>
                                                        <span class="text-white/80 text-xs">({{ dayGroup.schedules.length }})</span>
                                                    </div>

                                                    <!-- Schedules for this Day - Compact Row -->
                                                    <div v-for="schedule in dayGroup.schedules" :key="schedule.id" class="bg-white/80 rounded-lg px-3 py-2 border border-purple-100 hover:border-purple-300 transition-colors">
                                                        <div class="flex items-center justify-between gap-3 text-sm">
                                                            <!-- Left: Time -->
                                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                                <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                <span class="font-semibold text-gray-800 whitespace-nowrap">
                                                                    {{ schedule.start_time.substring(0, 5) }} - {{ schedule.end_time.substring(0, 5) }}
                                                                </span>
                                                            </div>

                                                            <!-- Right: Clinic & Floor - Compact -->
                                                            <div class="flex items-center gap-2 flex-wrap">
                                                                <div class="flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                                                    </svg>
                                                                    <span>{{ schedule.clinic_number }}</span>
                                                                </div>
                                                                <div class="flex items-center gap-1 px-2 py-0.5 bg-purple-100 text-purple-700 rounded text-xs font-medium">
                                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                                                    </svg>
                                                                    <span>ط. {{ schedule.floor }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- No Schedules -->
                                        <div v-else class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                                            <div class="flex items-center justify-center gap-2 text-yellow-800">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <p class="text-sm font-medium">لا توجد مواعيد متاحة حالياً</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Voice Command Feedback -->
            <transition name="slide-fade">
                <div v-if="voiceCommandFeedback" class="absolute top-20 left-1/2 -translate-x-1/2 z-50 px-6 py-4 rounded-xl shadow-2xl backdrop-blur-md"
                     :class="{
                         'bg-green-500/90 text-white': voiceCommandFeedback.type === 'success',
                         'bg-red-500/90 text-white': voiceCommandFeedback.type === 'error',
                         'bg-blue-500/90 text-white': voiceCommandFeedback.type === 'listening',
                         'bg-yellow-500/90 text-white': voiceCommandFeedback.type === 'processing'
                     }">
                    <div class="flex items-center gap-3">
                        <svg v-if="voiceCommandFeedback.type === 'listening'" class="h-6 w-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                        <svg v-else-if="voiceCommandFeedback.type === 'success'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else-if="voiceCommandFeedback.type === 'error'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-semibold text-lg">{{ voiceCommandFeedback.message }}</span>
                    </div>
                </div>
            </transition>

            <!-- Voice Command Feedback -->
            <transition name="slide-fade">
                <div v-if="voiceCommandFeedback" class="absolute top-20 left-1/2 -translate-x-1/2 z-50 px-6 py-4 rounded-xl shadow-2xl backdrop-blur-md"
                     :class="{
                         'bg-green-500/90 text-white': voiceCommandFeedback.type === 'success',
                         'bg-red-500/90 text-white': voiceCommandFeedback.type === 'error',
                         'bg-blue-500/90 text-white': voiceCommandFeedback.type === 'listening',
                         'bg-yellow-500/90 text-white': voiceCommandFeedback.type === 'processing'
                     }">
                    <div class="flex items-center gap-3">
                        <svg v-if="voiceCommandFeedback.type === 'listening'" class="h-6 w-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                        <svg v-else-if="voiceCommandFeedback.type === 'success'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else-if="voiceCommandFeedback.type === 'error'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-semibold text-lg">{{ voiceCommandFeedback.message }}</span>
                    </div>
                </div>
            </transition>

            <!-- إشعارات المواعيد -->
            <transition name="slide-fade">
                <div v-if="notification" class="absolute inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
                     <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full flex items-center p-6 space-x-6 rtl:space-x-reverse transform transition-all">
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

/* Modal Animation */
.modal-enter-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.modal-leave-active {
    transition: all 0.3s ease-in;
}
.modal-enter-from {
    opacity: 0;
    transform: scale(0.9) translateY(40px);
}
.modal-leave-to {
    opacity: 0;
    transform: scale(0.95) translateY(20px);
}

/* Inquiry Button Animation */
@keyframes bounce-slow {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

.animate-bounce-slow {
    animation: bounce-slow 2s ease-in-out infinite;
}

.inquiry-button {
    position: relative;
    overflow: visible;
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 10px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #6366f1, #8b5cf6);
    border-radius: 10px;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #4f46e5, #7c3aed);
}
</style>
