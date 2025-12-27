# Quick Start Guide - تشغيل سريع

## ⚠️ إعداد أولي: Gradle Wrapper

**إذا ظهرت رسالة `zsh: no such file or directory: ./gradlew`:**

قم بتشغيل:
```bash
cd android_app
./INSTALL_GRADLE_WRAPPER.sh
```

أو يدوياً:
```bash
cd android_app
mkdir -p gradle/wrapper
curl -L -o gradle/wrapper/gradle-wrapper.jar \
  https://raw.githubusercontent.com/gradle/gradle/v8.5.0/gradle/wrapper/gradle-wrapper.jar
chmod +x gradlew
```

## ⚡ التشغيل السريع (من VSCode Terminal)

### الخطوة 1: تشغيل Laravel Server

افتح Terminal في VSCode (Ctrl+` أو Cmd+`) وأدخل:

```bash
cd "/Users/sulimangzllal/Development/clinic screen/clink_screen"
php artisan serve --host=0.0.0.0 --port=8000
```

✅ يجب أن ترى: `Starting Laravel development server: http://0.0.0.0:8000`

### الخطوة 2: التحقق من المحاكي/الجهاز

```bash
# تحقق من الاتصال
adb devices
```

يجب أن ترى جهاز متصل مثل:
```
List of devices attached
emulator-5554    device
```

### الخطوة 3: بناء وتشغيل التطبيق

افتح Terminal جديد في VSCode (Split Terminal) وأدخل:

```bash
cd "/Users/sulimangzllal/Development/clinic screen/clink_screen/android_app"
./gradlew installDebug
```

### الخطوة 4: تشغيل التطبيق

```bash
# تشغيل التطبيق
adb shell am start -n com.clinic.screen/.ui.main.MainActivity --es SCREEN_CODE "SCREEN001"
```

### الخطوة 5: عرض Logs (اختياري)

```bash
adb logcat -s MainActivity MainViewModel PusherService
```

---

## 🎯 طريقة واحدة سريعة (Build & Run Script)

استخدم الـ script المرفق:

```bash
cd "/Users/sulimangzllal/Development/clinic screen/clink_screen/android_app"
./build-and-run.sh SCREEN001
```

---

## 📋 ملخص الأوامر

```bash
# 1. Terminal 1: Laravel Server
cd "/Users/sulimangzllal/Development/clinic screen/clink_screen"
php artisan serve --host=0.0.0.0 --port=8000

# 2. Terminal 2: Build & Run
cd "/Users/sulimangzllal/Development/clinic screen/clink_screen/android_app"
./gradlew installDebug
adb shell am start -n com.clinic.screen/.ui.main.MainActivity --es SCREEN_CODE "SCREEN001"

# 3. Terminal 3: Logs (اختياري)
adb logcat -s MainActivity MainViewModel PusherService
```

---

## 🔧 إعدادات API

✅ **تم تحديث `ApiConfig.kt`** لاستخدام:
- **Base URL**: `http://172.20.10.2/api/v1/`
- **ملاحظة**: إذا كنت تستخدم Android Emulator، غيّر إلى `http://10.0.2.2:8000/api/v1/`

---

## 🐛 استكشاف الأخطاء السريع

### Laravel غير متاح؟
```bash
curl http://172.20.10.2:8000/api/v1/screens/SCREEN001
```

### لا يوجد جهاز متصل؟
```bash
adb devices
adb kill-server && adb start-server
```

### التطبيق لا يعمل؟
```bash
adb uninstall com.clinic.screen
./gradlew clean
./gradlew installDebug
```

---

## 📝 ملاحظة: Android Emulator vs Real Device

### للـ Emulator:
في `ApiConfig.kt` استخدم:
```kotlin
const val BASE_URL = "http://10.0.2.2:8000/api/v1/"
```

### للجهاز الحقيقي (على نفس الشبكة):
في `ApiConfig.kt` استخدم:
```kotlin
const val BASE_URL = "http://172.20.10.2:8000/api/v1/"
```

