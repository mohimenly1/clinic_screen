# دليل إعداد وتشغيل تطبيق Android

## الملفات المُنشأة

تم إنشاء بنية أساسية كاملة لتطبيق Android مع الدعم التالي:

### ✅ المميزات المُنفذة:
1. **Full Screen Kiosk Mode** - وضع الشاشة الكاملة
2. **Pusher Integration** - دعم البث المباشر
3. **Retrofit API Client** - للاتصال بـ API
4. **MVVM Architecture** - بنية معمارية حديثة
5. **Real-time Updates** - تحديثات فورية للوسائط

### 📁 البنية الأساسية:

```
android_app/
├── app/
│   ├── build.gradle.kts          # Dependencies
│   └── src/main/
│       ├── AndroidManifest.xml   # Permissions & Activities
│       └── java/com/clinic/screen/
│           ├── ClinicScreenApplication.kt
│           ├── data/
│           │   ├── api/
│           │   │   ├── ApiService.kt      # API endpoints
│           │   │   └── RetrofitClient.kt  # Retrofit setup
│           │   └── model/
│           │       └── ScreenData.kt      # Data models
│           ├── service/
│           │   └── PusherService.kt       # Pusher real-time
│           ├── ui/
│           │   └── main/
│           │       ├── MainActivity.kt    # Main screen
│           │       ├── MediaPlayerFragment.kt
│           │       └── viewmodel/
│           │           └── MainViewModel.kt
│           └── util/
│               └── ApiConfig.kt           # Configuration
├── build.gradle.kts
├── settings.gradle.kts
└── gradle.properties
```

## خطوات الإعداد

### 1. فتح المشروع في Android Studio

```bash
# افتح Android Studio
# File > Open > اختر مجلد android_app
```

### 2. تحديث الإعدادات

#### أ. تحديث API Base URL
في `ApiConfig.kt`:
```kotlin
const val BASE_URL = "https://your-domain.com/api/v1/"
```

#### ب. تحديث Screen Code (اختياري)
يمكن تمرير Screen Code عبر Intent:
```kotlin
val intent = Intent(this, MainActivity::class.java)
intent.putExtra("SCREEN_CODE", "SCREEN001")
startActivity(intent)
```

أو عبر Deep Link:
```
clinicscreen://display?code=SCREEN001
```

### 3. إضافة Dependencies المتبقية

المشروع يحتوي على جميع الـ dependencies الأساسية، لكن قد تحتاج:
- ExoPlayer setup (في MediaPlayerFragment)
- Glide setup (لتحميل الصور)
- UI Layouts (activity_main.xml, fragment_media_player.xml)

### 4. إنشاء Layouts

#### activity_main.xml
```xml
<?xml version="1.0" encoding="utf-8"?>
<FrameLayout xmlns:android="http://schemas.android.com/apk/res/android"
    android:layout_width="match_parent"
    android:layout_height="match_parent">
    
    <!-- Media Player Fragment -->
    <fragment
        android:id="@+id/mediaPlayerFragment"
        android:name="com.clinic.screen.ui.main.MediaPlayerFragment"
        android:layout_width="match_parent"
        android:layout_height="match_parent" />
    
    <!-- Inquiry Button (Sidebar) -->
    <ImageButton
        android:id="@+id/inquiryButton"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:layout_gravity="center|end"
        android:src="@drawable/ic_inquiry" />
</FrameLayout>
```

### 5. إضافة Strings & Resources

في `res/values/strings.xml`:
```xml
<resources>
    <string name="app_name">Clinic Screen</string>
</resources>
```

### 6. إنشاء Themes

في `res/values/themes.xml`:
```xml
<resources>
    <style name="Theme.ClinicScreen" parent="Theme.MaterialComponents.DayNight.NoActionBar">
        <item name="colorPrimary">@color/purple_500</item>
    </style>
    
    <style name="Theme.ClinicScreen.FullScreen" parent="Theme.ClinicScreen">
        <item name="android:windowFullscreen">true</item>
        <item name="android:windowNoTitle">true</item>
    </style>
</resources>
```

## Pusher Events

التطبيق يستمع للأحداث التالية:

### 1. Screen Content Updated
- **Channel**: `displays.{screen_code}`
- **Event**: `App\Events\ScreenContentUpdated`
- **Action**: تحديث قائمة الوسائط تلقائياً

### 2. Broadcast Media
- **Channel**: `displays`
- **Event**: `App\Events\BroadcastMedia`
- **Action**: عرض وسائط البث العام

### 3. Stop Broadcast
- **Channel**: `displays`
- **Event**: `App\Events\StopBroadcast`
- **Action**: إيقاف البث العام

## الخطوات التالية

1. ✅ إضافة ExoPlayer implementation في `MediaPlayerFragment`
2. ✅ إضافة Inquiry UI (استعلامات)
3. ✅ إضافة Voice Recognition
4. ✅ إضافة Background Audio Player
5. ✅ إضافة Error Handling & Retry Logic
6. ✅ اختبار Real-time Updates

## البناء (Build)

```bash
# Build APK
./gradlew assembleRelease

# Build Bundle (for Play Store)
./gradlew bundleRelease
```

## ملاحظات مهمة

1. **Pusher Event Names**: تأكد من أن أسماء الأحداث في `PusherService.kt` تطابق الأسماء في Laravel (مع `App\Events\` prefix)

2. **Full Screen Mode**: التطبيق مُعد للعمل في Kiosk Mode - تأكد من إعدادات الجهاز

3. **Network Security**: في Production، أزل `usesCleartextTraffic="true"` وأضف Network Security Config

4. **Permissions**: تأكد من طلب الأذونات في Runtime (Android 6.0+)

5. **Screen Code**: يمكن تمرير Screen Code عبر Intent أو استخدام القيمة الافتراضية من `BuildConfig`

