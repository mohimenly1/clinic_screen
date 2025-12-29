# دليل بناء APK للتطبيق

## الطريقة 1: بناء APK بدون توقيع (للاختبار)

### بناء APK Debug (للتطوير والاختبار)
```bash
cd android_app
./gradlew assembleDebug
```

الملف سيكون في: `app/build/outputs/apk/debug/app-debug.apk`

### بناء APK Release بدون توقيع
```bash
cd android_app
./gradlew assembleRelease
```

الملف سيكون في: `app/build/outputs/apk/release/app-release-unsigned.apk`

**ملاحظة**: APK غير الموقع لا يمكن تثبيته على الأجهزة إلا بعد تفعيل "مصادر غير معروفة" وقد لا يعمل على بعض الأجهزة.

---

## الطريقة 2: بناء APK موقّع (موصى به للتوزيع)

### الخطوة 1: إنشاء Keystore

```bash
cd android_app
keytool -genkey -v -keystore clinic-screen-release.jks -keyalg RSA -keysize 2048 -validity 10000 -alias clinic-screen-key
```

ستُطلب منك المعلومات التالية:
- كلمة مرور Keystore (احفظها جيداً!)
- كلمة مرور Key (يمكن أن تكون نفس كلمة مرور Keystore)
- معلومات الهوية (الاسم، المؤسسة، إلخ)

### الخطوة 2: إنشاء ملف keystore.properties

أنشئ ملف `android_app/keystore.properties`:

```properties
storeFile=clinic-screen-release.jks
storePassword=YOUR_KEYSTORE_PASSWORD
keyAlias=clinic-screen-key
keyPassword=YOUR_KEY_PASSWORD
```

**⚠️ مهم جداً**: أضف `keystore.properties` إلى `.gitignore` لحماية كلمات المرور!

### الخطوة 3: تحديث build.gradle.kts

تم تحديث `build.gradle.kts` تلقائياً لدعم التوقيع.

### الخطوة 4: بناء APK الموقّع

```bash
cd android_app
./gradlew assembleRelease
```

الملف الموقّع سيكون في: `app/build/outputs/apk/release/app-release.apk`

---

## الطريقة 3: بناء AAB (Android App Bundle) للتوزيع عبر Google Play

```bash
cd android_app
./gradlew bundleRelease
```

الملف سيكون في: `app/build/outputs/bundle/release/app-release.aab`

**ملاحظة**: AAB هو التنسيق المطلوب لرفع التطبيق على Google Play Store.

---

## معلومات الإصدار

يمكنك تحديث رقم الإصدار في `app/build.gradle.kts`:

```kotlin
defaultConfig {
    versionCode = 2  // قم بزيادة هذا الرقم مع كل إصدار
    versionName = "1.0.1"  // رقم الإصدار الذي يراه المستخدم
}
```

---

## تثبيت APK على الجهاز

### الطريقة 1: عبر USB
```bash
adb install app/build/outputs/apk/release/app-release.apk
```

### الطريقة 2: نقل الملف يدوياً
1. انقل ملف APK إلى الجهاز
2. افتح الملف على الجهاز
3. فعّل "مصادر غير معروفة" إذا طُلب منك
4. اضغط "تثبيت"

---

## نصائح مهمة

1. **احفظ Keystore بأمان**: إذا فقدت Keystore، لن تتمكن من تحديث التطبيق على Google Play
2. **اختبر APK قبل التوزيع**: تأكد من أن التطبيق يعمل على أجهزة مختلفة
3. **استخدم ProGuard للتقليل من الحجم**: فعّل `isMinifyEnabled = true` في production
4. **تحقق من الأذونات**: تأكد من أن جميع الأذونات المطلوبة موجودة في AndroidManifest.xml


