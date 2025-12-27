# تشغيل التطبيق من VSCode

## الملفات الرئيسية للمشروع:

### 1. نقطة البداية (Entry Point)
- **الملف**: `app/src/main/java/com/clinic/screen/ui/main/MainActivity.kt`
- **الوصف**: هذا هو الملف الرئيسي الذي يبدأ التطبيق
- **المسار الكامل**: `android_app/app/src/main/java/com/clinic/screen/ui/main/MainActivity.kt`

### 2. AndroidManifest.xml
- **الملف**: `app/src/main/AndroidManifest.xml`
- **الوصف**: يحدد Activity الرئيسي والإذونات

### 3. build.gradle.kts
- **الملف**: `android_app/app/build.gradle.kts`
- **الوصف**: إعدادات البناء والاعتمادات

## طرق التشغيل من VSCode:

### الطريقة 1: استخدام Tasks (موصى به)

1. **افتح Command Palette**: `Cmd+Shift+P` (Mac) أو `Ctrl+Shift+P` (Windows/Linux)
2. **اكتب**: `Tasks: Run Task`
3. **اختر أحد المهام**:
   - `Android: Start Emulator` - لفتح المحاكي
   - `Android: Build & Install` - لبناء وتثبيت التطبيق
   - `Android: Clean & Build` - لتنظيف وبناء التطبيق
   - `Android: Check Devices` - للتحقق من الأجهزة المتصلة
   - `Laravel: Start Server` - لبدء خادم Laravel

### الطريقة 2: استخدام Terminal في VSCode

افتح Terminal في VSCode (`Ctrl+`` أو `Cmd+``) ثم:

```bash
# 1. افتح المحاكي (إذا لم يكن مفتوحاً)
emulator -avd Pixel_7_API_34

# أو استخدم أول محاكي متاح:
emulator -avd $(emulator -list-avds | head -1)

# 2. انتظر حتى يظهر في adb devices
adb devices

# 3. اذهب إلى مجلد android_app
cd android_app

# 4. بناء وتثبيت التطبيق
./gradlew installDebug

# 5. تشغيل التطبيق
adb shell am start -n com.clinic.screen/.ui.main.MainActivity
```

### الطريقة 3: استخدام اختصارات لوحة المفاتيح

يمكنك إضافة اختصارات في VSCode:

1. `Cmd+K Cmd+S` (Mac) أو `Ctrl+K Ctrl+S` (Windows/Linux)
2. ابحث عن `Tasks: Run Task`
3. أضف اختصارك المفضل

## خطوات سريعة:

### أول مرة:
```bash
# 1. تأكد من أن المحاكي يعمل
adb devices

# 2. ابدأ خادم Laravel (في terminal منفصل)
php artisan serve

# 3. بناء وتثبيت التطبيق
cd android_app
./gradlew installDebug
```

### في كل مرة:
```bash
# فقط بناء وتثبيت
cd android_app
./gradlew installDebug
```

## نصائح:

1. **استخدم Terminal متعدد**: 
   - Terminal 1: Laravel Server
   - Terminal 2: Android Build

2. **راقب Logcat**:
   ```bash
   adb logcat | grep -i "clinic\|MainActivity"
   ```

3. **إعادة التشغيل السريع**:
   ```bash
   adb shell am force-stop com.clinic.screen
   adb shell am start -n com.clinic.screen/.ui.main.MainActivity
   ```

## الملفات المهمة:

- **MainActivity.kt**: نقطة البداية الرئيسية
- **MainViewModel.kt**: منطق الأعمال
- **PusherService.kt**: خدمة البث المباشر
- **ApiService.kt**: واجهة API
- **AndroidManifest.xml**: إعدادات التطبيق

## استكشاف الأخطاء:

### المحاكي لا يفتح:
```bash
# تحقق من المحاكيات المتاحة
emulator -list-avds

# افتح محاكي محدد
emulator -avd <اسم_المحاكي>
```

### التطبيق لا يثبت:
```bash
# تحقق من الأجهزة
adb devices

# إعادة تشغيل adb
adb kill-server
adb start-server
```

### خطأ في البناء:
```bash
cd android_app
./gradlew clean
./gradlew installDebug
```

