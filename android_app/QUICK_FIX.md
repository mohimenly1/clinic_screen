# إصلاح سريع لمشكلة Gradle

## ✅ تم الإصلاح

تم تحديث الملفات التالية:

1. **`build.gradle.kts`** (الملف الرئيسي) - يحتوي على جميع plugins مع versions
2. **`settings.gradle.kts`** - تم إزالة plugins block منه
3. **`app/build.gradle.kts`** - يستخدم plugins بدون versions

## الآن جرب:

```bash
cd android_app
./gradlew installDebug
```

## إذا ظهرت أخطاء أخرى:

### خطأ: "Could not find com.android.tools.build:gradle"
هذا يعني أن Gradle لا يستطيع الوصول إلى Google Maven. تأكد من:
- اتصال الإنترنت
- أن `settings.gradle.kts` يحتوي على `google()` repository

### خطأ: "Java Runtime not found"
قم بتثبيت Java JDK 17+:
```bash
# على macOS
brew install openjdk@17
```

### خطأ: "SDK location not found"
أنشئ ملف `local.properties` في مجلد `android_app`:
```properties
sdk.dir=/Users/YOUR_USERNAME/Library/Android/sdk
```

