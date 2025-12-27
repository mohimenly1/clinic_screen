# Clinic Screen Android App

تطبيق أندرويد لعرض الشاشات في العيادة مع دعم البث المباشر (Real-time) عبر Pusher.

## المميزات
- ✅ Full Screen Kiosk Mode
- ✅ Real-time updates عبر Pusher
- ✅ عرض الوسائط (صور وفيديو) باستخدام ExoPlayer
- ✅ Voice Recognition للتعرف الصوتي
- ✅ Inquiry System للاستعلامات
- ✅ Auto-refresh للوسائط عند التحديث

## التقنيات المستخدمة
- Kotlin
- MVVM Architecture
- Retrofit للـ API calls
- Pusher Android SDK للبث المباشر
- ExoPlayer للوسائط
- Android Speech Recognition API

## الإعدادات

### Pusher Configuration
```kotlin
PUSHER_APP_ID = "2059282"
PUSHER_KEY = "86253c368b61504804fa"
PUSHER_CLUSTER = "eu"
```

### API Base URL
قم بتعديل `BASE_URL` في `ApiConfig.kt` وفقاً لخادمك.

## البناء والتشغيل
1. افتح المشروع في Android Studio
2. حدد Build Variant: `release` للـ APK
3. Build > Build Bundle(s) / APK(s) > Build APK(s)

