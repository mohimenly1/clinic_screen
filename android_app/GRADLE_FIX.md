# إصلاح مشكلة Gradle Plugin

## المشكلة
```
Plugin [id: 'com.android.application'] was not found
```

## الحل

تم إصلاح الملفات التالية:

### 1. `build.gradle.kts` (الملف الرئيسي)
يحتوي على تعريفات الـ plugins مع الإصدارات:
```kotlin
plugins {
    id("com.android.application") version "8.2.0" apply false
    id("org.jetbrains.kotlin.android") version "1.9.20" apply false
}
```

### 2. `settings.gradle.kts`
تم إزالة `plugins` block منه (يجب أن يكون فقط في build.gradle.kts)

### 3. `app/build.gradle.kts`
يستخدم plugins بدون versions (لأنها معرفة في build.gradle.kts الرئيسي):
```kotlin
plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
    // ...
}
```

## الآن جرب:

```bash
cd android_app
./gradlew installDebug
```

إذا ظهرت أي أخطاء أخرى، أرسلها وسأصلحها.

