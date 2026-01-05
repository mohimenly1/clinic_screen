# دليل تشغيل التطبيق في Android Studio

## المشاكل الشائعة والحلول

### 1. مشكلة Gradle Sync

**الأعراض:**
- لا يمكن تشغيل التطبيق
- أخطاء في Gradle sync
- رسائل خطأ عن التبعيات (dependencies)

**الحل:**
1. افتح Android Studio
2. اضغط على `File` → `Sync Project with Gradle Files`
3. أو اضغط على أيقونة Sync في شريط الأدوات (🔄)
4. انتظر حتى تنتهي عملية Sync

### 2. مشكلة JDK/Java

**الأعراض:**
- خطأ: "Unable to locate Java Runtime"
- خطأ: "JDK not found"

**الحل:**
1. في Android Studio، اذهب إلى:
   - `File` → `Settings` (أو `Android Studio` → `Preferences` على Mac)
   - `Build, Execution, Deployment` → `Build Tools` → `Gradle`
2. في قسم `Gradle JDK`، اختر:
   - `jbr-17` أو `jbr-21` (JetBrains Runtime)
   - أو حدد مسار JDK الخاص بك (يجب أن يكون JDK 17 أو 21)
3. اضغط `Apply` و `OK`
4. أعد تشغيل Sync

### 3. مشكلة Android SDK

**الأعراض:**
- خطأ: "SDK location not found"
- خطأ عن عدم وجود Android SDK

**الحل:**
1. في Android Studio:
   - `File` → `Settings` → `Appearance & Behavior` → `System Settings` → `Android SDK`
2. تأكد من تثبيت:
   - Android SDK Platform 34
   - Android SDK Build-Tools
   - Android Emulator (إذا كنت تريد استخدام المحاكي)
3. اضغط `Apply` لتثبيت المكونات المفقودة

### 4. تنظيف وإعادة بناء المشروع (Clean & Rebuild)

إذا واجهت مشاكل غير واضحة:

1. `Build` → `Clean Project`
2. انتظر حتى ينتهي
3. `Build` → `Rebuild Project`
4. بعد الانتهاء، حاول تشغيل التطبيق مرة أخرى

### 5. إلغاء التخزين المؤقت لـ Gradle

**الحل:**
1. أغلق Android Studio
2. احذف المجلدات التالية:
   ```bash
   cd android_app
   rm -rf .gradle
   rm -rf app/build
   rm -rf build
   ```
3. افتح Android Studio مرة أخرى
4. `File` → `Invalidate Caches / Restart...`
5. اختر `Invalidate and Restart`

### 6. التحقق من ملف local.properties

تأكد من وجود ملف `local.properties` في مجلد `android_app`:

```properties
sdk.dir=/Users/YOUR_USERNAME/Library/Android/sdk
```

يمكنك العثور على مسار SDK من:
- Android Studio → `Settings` → `Android SDK` → `SDK Location`

### 7. تشغيل التطبيق

**طريقة 1: من Android Studio**
1. تأكد من أن Gradle Sync ناجح (لا توجد أخطاء حمراء)
2. اختر الجهاز (Emulator أو جهاز حقيقي متصل)
3. اضغط على زر `Run` (▶️) أو `Shift + F10`

**طريقة 2: من Terminal (للـ Debug APK)**
```bash
cd android_app
./gradlew assembleDebug
./gradlew installDebug  # لتثبيته على جهاز متصل
```

### 8. مشكلة في الإذونات (Permissions)

تم إصلاح مشكلة تكرار `RECORD_AUDIO` في `AndroidManifest.xml`.

إذا ظهرت مشاكل أخرى متعلقة بالإذونات، تأكد من أن جميع الإذونات المطلوبة موجودة في `AndroidManifest.xml`.

### 9. مشكلة في Keystore (للتجميع Release فقط)

إذا كنت تحاول تجميع نسخة Release وظهرت مشكلة في Keystore:
- هذا لا يؤثر على التشغيل العادي من Android Studio (Debug mode)
- للتجميع Release، انظر إلى `BUILD_APK.md`

### 10. التحقق من الأخطاء

**في Android Studio:**
- `Build` → `Make Project` - للتحقق من الأخطاء
- `View` → `Tool Windows` → `Build` - لعرض سجل البناء
- `View` → `Tool Windows` → `Run` - لعرض سجل التشغيل

**في Terminal:**
```bash
cd android_app
./gradlew build --stacktrace
```

## خطوات سريعة للتشخيص

1. ✅ تأكد من أن Android Studio محدث
2. ✅ تحقق من Gradle Sync (لا توجد أخطاء)
3. ✅ تحقق من JDK (Settings → Gradle → Gradle JDK)
4. ✅ تحقق من Android SDK (Settings → Android SDK)
5. ✅ Clean & Rebuild
6. ✅ تحقق من `local.properties`
7. ✅ تحقق من وجود جهاز/Emulator

## الحصول على المساعدة

إذا استمرت المشكلة:
1. انسخ رسالة الخطأ الكاملة
2. تحقق من `Build` → `Make Project` للأخطاء
3. تحقق من `View` → `Tool Windows` → `Build` للتفاصيل

