<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const features = ref([
    {
        icon: '📺',
        title: 'شاشات ذكية تفاعلية',
        description: 'نظام متكامل لإدارة وعرض المحتوى على شاشات العيادات بشكل احترافي وسلس',
    },
    {
        icon: '🎬',
        title: 'إدارة الوسائط المتقدمة',
        description: 'رفع وإدارة الصور والفيديوهات والصوتيات بسهولة مع دعم قوائم التشغيل المخصصة',
    },
    {
        icon: '⚡',
        title: 'بث فوري مباشر',
        description: 'تحديث المحتوى على جميع الشاشات فوراً عبر تقنية WebSocket بدون الحاجة لإعادة التشغيل',
    },
    {
        icon: '👨‍⚕️',
        title: 'نظام الاستعلامات',
        description: 'عرض معلومات الأطباء والأقسام وجداول المواعيد بشكل تفاعلي وسهل',
    },
    {
        icon: '🔔',
        title: 'إشعارات ذكية',
        description: 'تنبيهات فورية عند اقتراب مواعيد الأطباء لتحسين تجربة المرضى',
    },
    {
        icon: '🗺️',
        title: 'تجربة الواقع المعزز',
        description: 'جولات تفاعلية للعيادات باستخدام تقنية AR لعرض الصور والوصول للمكان بسهولة',
    },
]);

const stats = ref([
    { label: 'شاشات نشطة', value: '∞', suffix: '+' },
    { label: 'وسائط محملة', value: '∞', suffix: '+' },
    { label: 'أطباء مسجلين', value: '∞', suffix: '+' },
    { label: 'رضا العملاء', value: '100', suffix: '%' },
]);

const isScrolled = ref(false);

onMounted(() => {
    window.addEventListener('scroll', () => {
        isScrolled.value = window.scrollY > 50;
    });
});
</script>

<template>
    <Head title="نظام إدارة الشاشات الإعلانية - Clinic Screen" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
        <!-- Navigation -->
        <nav 
            :class="[
                'fixed top-0 left-0 right-0 z-50 transition-all duration-300',
                isScrolled ? 'bg-white/95 backdrop-blur-md shadow-lg' : 'bg-transparent'
            ]"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center space-x-2 rtl:space-x-reverse">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <span class="text-white text-xl font-bold">CS</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">Clinic Screen</span>
                    </div>
                    
                    <div v-if="canLogin" class="flex items-center space-x-4 rtl:space-x-reverse">
                        <Link
                            v-if="$page.props.auth?.user"
                            :href="route('dashboard')"
                            class="px-4 py-2 text-gray-700 hover:text-blue-600 transition-colors font-medium"
                        >
                            لوحة التحكم
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-4 py-2 text-gray-700 hover:text-blue-600 transition-colors font-medium"
                            >
                                تسجيل الدخول
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl font-medium"
                            >
                                ابدأ الآن
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto w-full">
                <div class="text-center px-4">
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-gray-900 mb-6 leading-tight px-4">
                        نظام إدارة
                        <span class="inline-block bg-gradient-to-r from-blue-600 via-blue-500 to-purple-600 bg-clip-text text-transparent" style="-webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                            الشاشات الإعلانية
                        </span>
                        <br />
                        للعيادات الطبية
                    </h1>
                    <p class="text-xl md:text-2xl text-gray-600 mb-8 max-w-3xl mx-auto leading-relaxed px-4">
                        حل متكامل وحديث لإدارة وعرض المحتوى على شاشات العيادات بشكل احترافي
                        مع ميزات تفاعلية متقدمة وتحكم فوري
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center px-4">
                        <Link
                            v-if="canRegister && !$page.props.auth?.user"
                            :href="route('register')"
                            class="px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-lg rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all shadow-xl hover:shadow-2xl font-semibold transform hover:scale-105"
                        >
                            ابدأ تجربتك المجانية
                        </Link>
                        <Link
                            v-if="canLogin && !$page.props.auth?.user"
                            :href="route('login')"
                            class="px-8 py-4 bg-white text-gray-900 text-lg rounded-xl hover:bg-gray-50 transition-all shadow-lg hover:shadow-xl font-semibold border-2 border-gray-200"
                        >
                            تسجيل الدخول
                        </Link>
                    </div>
                </div>

                <!-- Hero Image/Animation -->
                <div class="mt-16 relative px-4">
                    <div class="relative mx-auto max-w-5xl w-full">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-purple-400 rounded-3xl blur-3xl opacity-30"></div>
                        <div class="relative bg-white rounded-3xl shadow-2xl p-6 sm:p-8 border border-gray-100">
                            <div class="aspect-video bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl flex items-center justify-center">
                                <div class="text-center">
                                    <div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-blue-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg">
                                        <span class="text-5xl">📺</span>
                                    </div>
                                    <p class="text-gray-500 text-lg">معاينة الشاشة الإعلانية</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
                    <div v-for="stat in stats" :key="stat.label" class="text-center px-2">
                        <div class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-blue-600 via-blue-500 to-purple-600 bg-clip-text text-transparent mb-2 inline-block" style="-webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                            {{ stat.value }}{{ stat.suffix }}
                        </div>
                        <div class="text-gray-600 font-medium text-sm md:text-base">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto w-full">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                        مميزات النظام
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        حل شامل يوفر جميع الأدوات التي تحتاجها لإدارة شاشات العيادة بفعالية
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100"
                    >
                        <div class="text-5xl mb-4">{{ feature.icon }}</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">{{ feature.title }}</h3>
                        <p class="text-gray-600 leading-relaxed">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="py-20 bg-gradient-to-br from-blue-600 to-purple-600 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto w-full">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
                        كيف يعمل النظام؟
                    </h2>
                    <p class="text-xl text-blue-100 max-w-2xl mx-auto">
                        خطوات بسيطة لبدء استخدام النظام
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 text-center border border-white/20">
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-bold text-blue-600">
                            1
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">إنشاء حساب</h3>
                        <p class="text-blue-100">سجل حسابك مجاناً وابدأ في استخدام النظام</p>
                    </div>

                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 text-center border border-white/20">
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-bold text-blue-600">
                            2
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">إعداد الشاشات</h3>
                        <p class="text-blue-100">أضف شاشاتك وحدد المحتوى الذي تريد عرضه</p>
                    </div>

                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 text-center border border-white/20">
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-bold text-blue-600">
                            3
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">ابدأ العرض</h3>
                        <p class="text-blue-100">راقب شاشاتك وتحديثها فوراً من لوحة التحكم</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center w-full">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-3xl p-8 sm:p-12 shadow-2xl">
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                        جاهز للبدء؟
                    </h2>
                    <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                        انضم إلى العيادات التي تستخدم نظامنا لإدارة شاشاتها الإعلانية
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            v-if="canRegister && !$page.props.auth?.user"
                            :href="route('register')"
                            class="px-8 py-4 bg-white text-blue-600 text-lg rounded-xl hover:bg-gray-50 transition-all shadow-xl hover:shadow-2xl font-semibold transform hover:scale-105"
                        >
                            ابدأ الآن مجاناً
                        </Link>
                        <Link
                            v-if="canLogin && !$page.props.auth?.user"
                            :href="route('login')"
                            class="px-8 py-4 bg-white/10 backdrop-blur-md text-white text-lg rounded-xl hover:bg-white/20 transition-all border-2 border-white/30 font-semibold"
                        >
                            تسجيل الدخول
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-300 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto w-full">
                <div class="grid md:grid-cols-3 gap-8 mb-8">
                    <div>
                        <div class="flex items-center space-x-2 rtl:space-x-reverse mb-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                                <span class="text-white text-xl font-bold">CS</span>
                            </div>
                            <span class="text-xl font-bold text-white">Clinic Screen</span>
                        </div>
                        <p class="text-gray-400">
                            نظام متكامل لإدارة الشاشات الإعلانية للعيادات الطبية
                        </p>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-4">روابط سريعة</h4>
                        <ul class="space-y-2">
                            <li v-if="canLogin">
                                <Link :href="route('login')" class="hover:text-white transition-colors">
                                    تسجيل الدخول
                                </Link>
                            </li>
                            <li v-if="canRegister">
                                <Link :href="route('register')" class="hover:text-white transition-colors">
                                    إنشاء حساب
                                </Link>
                            </li>
                            <li v-if="$page.props.auth?.user">
                                <Link :href="route('dashboard')" class="hover:text-white transition-colors">
                                    لوحة التحكم
                                </Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-4">المميزات</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li>شاشات تفاعلية</li>
                            <li>بث فوري</li>
                            <li>إدارة متقدمة</li>
                            <li>دعم فني 24/7</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 pt-8 text-center text-gray-400">
                    <p>&copy; {{ new Date().getFullYear() }} Clinic Screen. جميع الحقوق محفوظة.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #2563eb, #9333ea);
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #1d4ed8, #7e22ce);
}
</style>
