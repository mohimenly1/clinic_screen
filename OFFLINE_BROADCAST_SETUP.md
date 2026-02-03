# إعداد البث بدون إنترنت (Offline Broadcasting)

## نظرة عامة

تم إعداد النظام ليعمل مع **Laravel Reverb** - خادم WebSocket الرسمي من Laravel الذي يعمل بدون إنترنت. Reverb متوافق تماماً مع Laravel Echo و Laravel Broadcasting.

## المتطلبات

1. **PHP 8.2+**
2. **Laravel 12+**
3. **Composer**

## خطوات الإعداد

### 1. التأكد من تثبيت Reverb

```bash
composer require laravel/reverb
php artisan reverb:install
```

### 2. إعداد متغيرات البيئة

أضف/عدّل في ملف `.env`:

```env
# استخدام Reverb للبث المحلي (بدون إنترنت)
BROADCAST_DRIVER=reverb

# إعدادات Reverb
REVERB_APP_ID=local-app
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
```

أضف في ملف `.env` للـ frontend (أو في `vite.config.js`):

```env
VITE_BROADCAST_DRIVER=reverb
VITE_REVERB_APP_KEY=local-key
VITE_REVERB_HOST=127.0.0.1
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

### 3. تشغيل Reverb

```bash
php artisan reverb:start
```

أو باستخدام npm:

```bash
npm run reverb
```

### 4. تشغيل Laravel

في نافذة terminal منفصلة:

```bash
php artisan serve
```

### 5. تشغيل Vite (للتطوير)

في نافذة terminal ثالثة:

```bash
npm run dev
```

## الإعداد التلقائي

يمكنك تشغيل كل شيء معاً باستخدام:

### في Windows (Command Prompt):

```cmd
start cmd /k "php artisan reverb:start"
start cmd /k "php artisan serve"
start cmd /k "npm run dev"
```

### في Windows (PowerShell):

```powershell
Start-Process cmd -ArgumentList "/k php artisan reverb:start"
Start-Process cmd -ArgumentList "/k php artisan serve"
Start-Process cmd -ArgumentList "/k npm run dev"
```

### في macOS/Linux:

```bash
php artisan reverb:start & php artisan serve & npm run dev
```

## التحقق من العمل

1. افتح المتصفح على `http://127.0.0.1:8000`
2. افتح Developer Tools (F12)
3. انتقل إلى Console
4. يجب أن ترى رسالة اتصال WebSocket ناجحة

## إعدادات Reverb

ملف `config/reverb.php` يحتوي على الإعدادات التالية:

- **Port**: 8080 (افتراضي)
- **Host**: 0.0.0.0 (للسماح بالاتصالات من جميع العناوين)
- **App ID**: local-app
- **App Key**: local-key
- **App Secret**: local-secret

يمكنك تعديل هذه الإعدادات في ملف `.env`.

## تشغيل Reverb كخدمة Windows

لجعل Reverb يعمل تلقائياً عند بدء Windows:

### الطريقة 1: استخدام Task Scheduler

1. افتح **Task Scheduler**
2. أنشئ مهمة جديدة
3. في **General**:
   - Name: `Laravel Reverb WebSocket Server`
   - Run whether user is logged on or not
   - Run with highest privileges
4. في **Triggers**:
   - New Trigger: At startup
5. في **Actions**:
   - Action: Start a program
   - Program: `C:\path\to\php.exe`
   - Arguments: `artisan reverb:start`
   - Start in: `C:\path\to\your\project`
6. في **Conditions**:
   - Uncheck "Start the task only if the computer is on AC power"
7. احفظ المهمة

### الطريقة 2: استخدام NSSM (Non-Sucking Service Manager)

1. حمّل NSSM من: https://nssm.cc/download
2. استخرج الملفات
3. افتح Command Prompt كمسؤول
4. انتقل إلى مجلد NSSM
5. قم بتنفيذ:

```cmd
nssm install LaravelReverb "C:\path\to\php.exe" "C:\path\to\your\project\artisan" "reverb:start"
```

6. افتح Services (services.msc)
7. ابحث عن "LaravelReverb"
8. اضغط Start واختر "Automatic" في Startup type

## استكشاف الأخطاء

### المشكلة: Reverb لا يبدأ

**الحل:**
- تأكد من أن PHP مثبت ومتاح في PATH
- تأكد من أن Port 8080 غير مستخدم:
  ```cmd
  netstat -ano | findstr :8080
  ```
- إذا كان المستخدم، غيّر Port في `.env`:
  ```env
  REVERB_PORT=8081
  REVERB_SERVER_PORT=8081
  ```

### المشكلة: WebSocket لا يتصل

**الحل:**
1. تأكد من أن Reverb يعمل
2. تأكد من أن `BROADCAST_DRIVER=reverb` في `.env`
3. تأكد من أن `VITE_BROADCAST_DRIVER=reverb` في `.env`
4. أعد بناء assets:
   ```bash
   npm run build
   ```

### المشكلة: CORS Error

**الحل:**
- تأكد من أن `allowed_origins` في `config/reverb.php` يحتوي على `*` أو عنوان URL الخاص بك
- تأكد من أن `REVERB_HOST` في `.env` مضبوط بشكل صحيح

## العودة إلى Pusher (مع إنترنت)

إذا أردت العودة لاستخدام Pusher الحقيقي:

1. غيّر في `.env`:
   ```env
   BROADCAST_DRIVER=pusher
   ```

2. أضف مفاتيح Pusher الحقيقية:
   ```env
   PUSHER_APP_KEY=your-real-key
   PUSHER_APP_SECRET=your-real-secret
   PUSHER_APP_ID=your-real-app-id
   PUSHER_APP_CLUSTER=eu
   ```

3. أزل `VITE_BROADCAST_DRIVER=reverb` من `.env`

4. أعد بناء assets:
   ```bash
   npm run build
   ```

## الميزات

✅ يعمل بدون إنترنت  
✅ متوافق مع Laravel Echo  
✅ الحل الرسمي من Laravel  
✅ سهل الإعداد  
✅ لا يحتاج Node.js  
✅ يعمل على Windows و macOS و Linux  
✅ يدعم Redis للـ scaling  

## ملاحظات مهمة

1. **الأمان**: في بيئة الإنتاج، يجب تفعيل المصادقة في `routes/channels.php`
2. **الأداء**: Reverb يعمل بشكل جيد حتى 1000+ اتصال متزامن
3. **التخزين**: Reverb لا يحفظ البيانات، كل شيء في الذاكرة
4. **النسخ الاحتياطي**: لا حاجة لنسخ احتياطي لأن Reverb لا يخزن بيانات

## الدعم

إذا واجهت أي مشاكل، راجع:
- [Laravel Reverb Documentation](https://laravel.com/docs/reverb)
- [Laravel Broadcasting Documentation](https://laravel.com/docs/broadcasting)
