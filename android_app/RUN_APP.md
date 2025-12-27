# دليل تشغيل التطبيق من VSCode

## المتطلبات الأساسية

1. **Java JDK 17+** - للتأكد:
   ```bash
   java -version
   ```

2. **Android SDK** - يجب تثبيت Android SDK Platform Tools
   ```bash
   # على macOS يمكن استخدام Homebrew
   brew install --cask android-platform-tools
   ```

3. **Gradle** - يمكن استخدام Gradle Wrapper المرفق (لا حاجة لتثبيت منفصل)

4. **Android Emulator** أو جهاز حقيقي متصل

## خطوات التشغيل

### 1. التأكد من إعداد Laravel Server

أولاً، تأكد أن Laravel يعمل على الـ IP المحلي:

```bash
# في مجلد المشروع Laravel
cd "/Users/sulimangzllal/Development/clinic screen/clink_screen"

# شغل Laravel على الـ IP المحلي (172.20.10.2)
php artisan serve --host=0.0.0.0 --port=8000
```

الآن Laravel متاح على: `http://172.20.10.2:8000`

### 2. إعداد Android Emulator

#### خيار أ: استخدام Android Studio Emulator
1. افتح Android Studio
2. Tools > Device Manager
3. أنشئ أو شغّل Emulator

#### خيار ب: استخدام Command Line Emulator
```bash
# سرد الأجهزة المتاحة
emulator -list-avds

# تشغيل محاكي محدد
emulator -avd <AVD_NAME>
```

#### خيار ج: استخدام جهاز حقيقي
1. فعّل Developer Options على الجهاز
2. فعّل USB Debugging
3. وصّل الجهاز بالكمبيوتر
4. تحقق من الاتصال:
   ```bash
   adb devices
   ```

### 3. بناء التطبيق (Build)

من مجلد `android_app`:

```bash
cd android_app

# بناء التطبيق (Debug)
./gradlew assembleDebug

# أو بناء APK مباشرة
./gradlew build
```

### 4. تثبيت التطبيق على المحاكي/الجهاز

```bash
# التأكد من الاتصال
adb devices

# تثبيت APK
adb install app/build/outputs/apk/debug/app-debug.apk

# أو استخدام Gradle مباشرة
./gradlew installDebug
```

### 5. تشغيل التطبيق

#### من Command Line:
```bash
# تشغيل التطبيق مباشرة
adb shell am start -n com.clinic.screen/.ui.main.MainActivity

# أو مع Screen Code
adb shell am start -n com.clinic.screen/.ui.main.MainActivity \
  --es SCREEN_CODE "SCREEN001"
```

#### من المحاكي/الجهاز:
افتح التطبيق "Clinic Screen" من قائمة التطبيقات

### 6. عرض Logs (للتطوير)

```bash
# عرض جميع logs
adb logcat

# عرض logs للتطبيق فقط
adb logcat | grep -i "clinic"

# أو تصفية حسب Tag
adb logcat -s MainActivity MainViewModel PusherService
```

## الإعدادات الحالية

### API Configuration
- **Base URL**: `http://172.20.10.2/api/v1/`
- **Laravel Server**: يجب أن يعمل على `http://172.20.10.2:8000`

### Screen Code
- **Default**: `SCREEN001` (من BuildConfig)
- **Via Intent**: يمكن تمرير Screen Code عبر Intent Extra

## تشغيل سريع (One-liner)

```bash
# من مجلد android_app
cd android_app && ./gradlew installDebug && adb shell am start -n com.clinic.screen/.ui.main.MainActivity
```

## استكشاف الأخطاء

### 1. Laravel غير متاح
```bash
# تحقق من Laravel
curl http://172.20.10.2:8000/api/v1/screens/SCREEN001
```

### 2. Emulator غير متصل
```bash
# تحقق من الاتصال
adb devices

# إعادة تشغيل ADB
adb kill-server
adb start-server
```

### 3. التطبيق لا يشتغل
```bash
# مسح البيانات وإعادة التثبيت
adb uninstall com.clinic.screen
./gradlew installDebug
```

### 4. مشاكل في الشبكة
- تأكد أن Emulator/PC على نفس الشبكة
- للـ Emulator، يمكن استخدام `10.0.2.2` بدلاً من `172.20.10.2` (خاص بالـ Android Emulator)

## ملاحظة مهمة: Android Emulator Network

إذا كنت تستخدم **Android Emulator**، يجب استخدام `10.0.2.2` بدلاً من `172.20.10.2` للوصول إلى localhost:

```kotlin
// في ApiConfig.kt - للـ Emulator فقط
const val BASE_URL = "http://10.0.2.2:8000/api/v1/"
```

أما إذا كنت تستخدم **جهاز حقيقي** على نفس الشبكة، استخدم:
```kotlin
const val BASE_URL = "http://172.20.10.2:8000/api/v1/"
```

## إضافة Scripts لتسهيل التشغيل

يمكنك إضافة scripts في `package.json` أو إنشاء ملفات shell scripts:

### build-and-run.sh
```bash
#!/bin/bash
cd android_app
./gradlew installDebug
adb shell am start -n com.clinic.screen/.ui.main.MainActivity
adb logcat -s MainActivity MainViewModel PusherService
```

### setup.sh
```bash
#!/bin/bash
# تشغيل Laravel على الـ IP المحلي
cd "/Users/sulimangzllal/Development/clinic screen/clink_screen"
php artisan serve --host=0.0.0.0 --port=8000 &
```

