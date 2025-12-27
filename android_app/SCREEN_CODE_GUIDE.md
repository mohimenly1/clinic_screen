# كيفية تحديد Screen Code للتطبيق

## المشكلة:
التطبيق يحتاج معرفة على أي شاشة يعمل (Screen Code) ليحمل المحتوى المناسب.

## الحلول:

### 1. استخدام Intent Extra (للاختبار)

```bash
# تشغيل التطبيق مع screen code محدد
adb shell am start -n com.clinic.screen/.ui.main.MainActivity --es SCREEN_CODE "SCREEN001"
```

### 2. استخدام Build Config (للإنتاج)

في `app/build.gradle.kts`:
```kotlin
defaultConfig {
    // ... other config
    buildConfigField("String", "DEFAULT_SCREEN_CODE", "\"SCREEN001\"")
}
```

ثم في `MainActivity.kt`:
```kotlin
screenCode = intent.getStringExtra("SCREEN_CODE") ?: 
            intent.data?.getQueryParameter("code") ?: 
            BuildConfig.DEFAULT_SCREEN_CODE
```

### 3. استخدام SharedPreferences (للتخزين المحلي)

```kotlin
val prefs = getSharedPreferences("app_prefs", MODE_PRIVATE)
screenCode = prefs.getString("screen_code", "SCREEN001") ?: "SCREEN001"
```

### 4. استخدام QR Code أو Deep Link

```bash
# Deep link example
adb shell am start -W -a android.intent.action.VIEW -d "clinicscreen://display?code=SCREEN001" com.clinic.screen
```

## الإعداد الحالي:

- **الافتراضي**: `SCREEN001`
- **من Intent**: `SCREEN_CODE` extra
- **من Deep Link**: `code` query parameter

## للاختبار:

```bash
# استخدم screen code محدد
adb shell am start -n com.clinic.screen/.ui.main.MainActivity --es SCREEN_CODE "YOUR_SCREEN_CODE"
```

## ملاحظة مهمة:

تأكد من:
1. ✅ Laravel server يعمل على `localhost:8000`
2. ✅ Screen Code موجود في قاعدة البيانات
3. ✅ Screen لديه محتوى (playlist أو media item)

