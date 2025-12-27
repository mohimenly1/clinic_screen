# دليل إعداد Screen Code

## كيف يعمل النظام:

### 1. أول فتح للتطبيق:
- يفتح `SetupActivity` تلقائياً
- يطلب من المستخدم إدخال رقم الشاشة (Screen Code)
- يحفظ الرقم في `SharedPreferences`

### 2. المرات التالية:
- يتحقق من وجود Screen Code محفوظ
- إذا موجود، يذهب مباشرة إلى `MainActivity`
- إذا غير موجود، يظهر `SetupActivity` مرة أخرى

## كيفية إعادة تعيين Screen Code:

### من الكود:

```kotlin
import com.clinic.screen.ui.setup.SetupActivity

// مسح Screen Code المحفوظ
SetupActivity.clearScreenCode(context)

// الحصول على Screen Code المحفوظ
val screenCode = SetupActivity.getScreenCode(context)
```

### من ADB (للاختبار):

```bash
# مسح Screen Code المحفوظ
adb shell run-as com.clinic.screen pm clear com.clinic.screen

# أو حذف SharedPreferences يدوياً
adb shell run-as com.clinic.screen rm /data/data/com.clinic.screen/shared_prefs/clinic_screen_prefs.xml
```

## ملاحظات:

1. **Screen Code محفوظ محلياً** في `SharedPreferences`
2. **لا يتم طلب API** حتى يتم إدخال Screen Code
3. **يمكن إعادة التعيين** عن طريق مسح بيانات التطبيق
4. **يدعم RTL** للعربية

## لاختبار الإعداد:

```bash
# 1. شغل التطبيق (سيظهر SetupActivity)
adb shell am start -n com.clinic.screen/.ui.setup.SetupActivity

# 2. أو امسح البيانات وأعد التشغيل
adb shell pm clear com.clinic.screen
adb shell am start -n com.clinic.screen/.ui.setup.SetupActivity
```

