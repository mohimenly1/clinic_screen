# إعدادات PHP المطلوبة لتحسين الفيديو

## المشكلة
عند معالجة عدد كبير من ملفات الفيديو، قد تواجه خطأ:
```
PHP Fatal error: Maximum execution time of 60 seconds exceeded
```

## الحل

### الطريقة 1: تعديل php.ini (موصى بها)

1. ابحث عن ملف `php.ini`:
   - في XAMPP: `C:\xampp\php\php.ini`
   - في Laragon: `C:\laragon\bin\php\php-{version}\php.ini`
   - في WAMP: `C:\wamp64\bin\php\php{version}\php.ini`

2. افتح الملف وابحث عن:
   ```ini
   max_execution_time = 60
   ```

3. غيّر القيمة إلى:
   ```ini
   max_execution_time = 3600
   ```
   (3600 ثانية = 60 دقيقة)

4. احفظ الملف وأعد تشغيل Apache/Nginx

### الطريقة 2: تعديل .htaccess (إذا كان متاحاً)

أضف هذه السطور إلى ملف `.htaccess` في مجلد `public`:

```apache
php_value max_execution_time 3600
php_value memory_limit 512M
```

### الطريقة 3: تعديل في الكود (تم تطبيقه بالفعل)

تم إضافة `set_time_limit(3600)` في الكود تلقائياً، لكن قد لا يعمل في بعض الحالات.

## إعدادات إضافية موصى بها

في `php.ini`، تأكد من:

```ini
max_execution_time = 3600
memory_limit = 512M
post_max_size = 100M
upload_max_filesize = 100M
```

## ملاحظات مهمة

- بعد تعديل `php.ini`، يجب إعادة تشغيل Apache/Nginx
- يمكنك التحقق من الإعدادات الحالية عبر: `php -i | findstr max_execution_time`
- للتحقق من PHP-FPM: `php -i | findstr max_execution_time`

## التحقق من الإعدادات

أنشئ ملف `info.php` في مجلد `public`:

```php
<?php
phpinfo();
```

ثم افتح `http://localhost/info.php` وابحث عن `max_execution_time`.

**تحذير**: احذف ملف `info.php` بعد التحقق من الإعدادات لأسباب أمنية.
