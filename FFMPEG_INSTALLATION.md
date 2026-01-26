# تثبيت FFmpeg لتحسين الفيديو (اختياري)

## نظرة عامة
تم إضافة نظام تحسين تلقائي للفيديو عند الرفع. النظام يستخدم FFmpeg لإعادة ترميز الفيديو إلى صيغة متوافقة مع جميع المتصفحات.

**ملاحظة مهمة**: النظام يعمل بدون FFmpeg أيضاً! إذا لم يكن FFmpeg متاحاً، سيتم استخدام الملف الأصلي بدون تحسين.

## تثبيت FFmpeg (اختياري - للحصول على أفضل جودة)

### Windows (الطريقة السهلة - موصى بها)
1. قم بتحميل FFmpeg من: https://www.gyan.dev/ffmpeg/builds/
2. اختر "ffmpeg-release-essentials.zip"
3. استخرج الملفات إلى مجلد مثل `C:\ffmpeg`
4. أضف FFmpeg إلى PATH:
   - اضغط `Win + R` واكتب `sysdm.cpl` ثم اضغط Enter
   - أو: ابحث عن "Environment Variables" في قائمة Start
   - في "System variables"، ابحث عن "Path" واختر "Edit"
   - اضغط "New" وأضف: `C:\ffmpeg\bin`
   - اضغط "OK" لحفظ التغييرات
   - **مهم**: أعد تشغيل Command Prompt/PowerShell بعد إضافة PATH
5. تحقق من التثبيت:
   ```cmd
   ffmpeg -version
   ```
   
   **ملاحظة**: إذا لم يعمل الأمر، تأكد من:
   - إعادة تشغيل Terminal/Command Prompt بعد إضافة PATH
   - التحقق من أن `C:\ffmpeg\bin\ffmpeg.exe` موجود
   - إعادة تشغيل Laravel/PHP-FPM إذا كان يعمل كخدمة

### Windows (استخدام Chocolatey - أسهل)
```cmd
choco install ffmpeg
```

### Windows (استخدام Scoop)
```cmd
scoop install ffmpeg
```

### macOS
```bash
brew install ffmpeg
```

### Ubuntu/Debian
```bash
sudo apt update
sudo apt install ffmpeg
```

### التحقق من التثبيت
```bash
ffmpeg -version
```

## الميزات (عند وجود FFmpeg)
- إعادة ترميز الفيديو إلى H.264 Baseline Profile (متوافق مع جميع المتصفحات)
- إضافة faststart flag للتحميل التدريجي السريع
- تحسين الصوت باستخدام AAC
- الحصول على مدة الفيديو تلقائياً

## العمل بدون FFmpeg
- النظام يعمل بشكل طبيعي بدون FFmpeg
- سيتم استخدام الملفات الأصلية كما هي
- قد تواجه بعض المشاكل مع ملفات الفيديو غير المتوافقة
- الحل: إعادة ترميز الفيديو يدوياً قبل الرفع أو تثبيت FFmpeg

## نصائح لملفات الفيديو بدون FFmpeg
- استخدم صيغة MP4 مع H.264 codec
- استخدم Baseline Profile بدلاً من High Profile
- تأكد من أن الفيديو يحتوي على faststart flag
- يمكنك استخدام أدوات مثل HandBrake لإعادة الترميز يدوياً
