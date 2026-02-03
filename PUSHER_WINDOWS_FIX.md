# حل مشكلة Pusher في Windows

## المشكلة

عند استخدام ميزة البث العام في Windows، قد يظهر الخطأ التالي:

```
cURL error 6: Could not resolve host: api-eu.pusher.com
```

هذا يعني أن Windows لا يستطيع حل اسم المضيف `api-eu.pusher.com` إلى عنوان IP.

## الحلول

### الحل 1: إصلاح إعدادات DNS في Windows

1. **فتح إعدادات DNS:**
   - اضغط `Win + R`
   - اكتب `ncpa.cpl` واضغط Enter
   - انقر بزر الماوس الأيمن على اتصال الشبكة النشط
   - اختر "Properties" (خصائص)
   - اختر "Internet Protocol Version 4 (TCP/IPv4)"
   - انقر "Properties" (خصائص)

2. **تغيير إعدادات DNS:**
   - اختر "Use the following DNS server addresses"
   - DNS Preferred: `8.8.8.8` (Google DNS)
   - DNS Alternate: `8.8.4.4` (Google DNS)
   - أو استخدم:
     - `1.1.1.1` (Cloudflare DNS)
     - `1.0.0.1` (Cloudflare DNS)

3. **إعادة تشغيل الاتصال:**
   - انقر "OK" في جميع النوافذ
   - أعد تشغيل الكمبيوتر أو اقطع الاتصال وأعد الاتصال بالشبكة

### الحل 2: استخدام إعدادات Pusher مخصصة

إذا استمرت المشكلة، يمكنك إضافة إعدادات مخصصة في ملف `.env`:

```env
# Pusher Configuration
PUSHER_APP_KEY=86253c368b61504804fa
PUSHER_APP_SECRET=f9fa3b0472b020245ec8
PUSHER_APP_ID=2059282
PUSHER_APP_CLUSTER=eu

# إعدادات إضافية لحل مشاكل DNS (اختياري)
# PUSHER_HOST=api-eu.pusher.com
# PUSHER_PORT=443
# PUSHER_SCHEME=https

# حل DNS مخصص (استخدم IP مباشر)
# PUSHER_RESOLVE=api-eu.pusher.com:443:52.18.123.123
```

**ملاحظة:** للحصول على IP الحالي لـ `api-eu.pusher.com`، استخدم الأمر التالي في Command Prompt:

```cmd
nslookup api-eu.pusher.com
```

ثم استخدم العنوان IP في `PUSHER_RESOLVE`.

### الحل 3: إضافة استثناء في الجدار الناري

1. افتح "Windows Defender Firewall"
2. انقر "Allow an app or feature through Windows Defender Firewall"
3. انقر "Change Settings"
4. ابحث عن PHP أو أضف استثناء جديد لـ `php.exe`
5. تأكد من تفعيل "Private" و "Public"

### الحل 4: التحقق من إعدادات Proxy

إذا كنت تستخدم Proxy:

1. افتح "Internet Options" من Control Panel
2. انتقل إلى تبويب "Connections"
3. انقر "LAN settings"
4. تأكد من إعدادات Proxy الصحيحة

## التحقق من الحل

بعد تطبيق أي من الحلول أعلاه، اختبر الاتصال:

1. افتح Command Prompt كمسؤول
2. قم بتنفيذ:
   ```cmd
   ping api-eu.pusher.com
   ```
3. إذا نجح الـ ping، فالمشكلة محلولة

## الميزات المضافة

تم إضافة الميزات التالية في الكود:

1. **إعادة المحاولة التلقائية:** النظام يحاول إعادة البث تلقائياً حتى 3 مرات في حالة فشل الاتصال
2. **معالجة أخطاء محسنة:** رسائل خطأ واضحة للمستخدم
3. **إعدادات cURL محسنة:** إعدادات DNS cache و timeout محسنة

## ملاحظات إضافية

- إذا استمرت المشكلة، تأكد من أن Windows محدث
- تأكد من أن PHP cURL extension مثبت ويعمل بشكل صحيح
- في بعض الحالات، قد تحتاج إلى إعادة تشغيل خدمة Windows DNS Client
