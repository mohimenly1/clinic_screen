# تقرير مراجعة المشروع - نظام إدارة شاشات العيادة (Clinic Screen Management System)

## نظرة عامة على المشروع

هذا مشروع نظام إدارة شاشات عرض للعيادات الطبية، مبني باستخدام:
- **Backend**: Laravel 12 مع PHP 8.2+
- **Frontend**: Vue 3 مع Inertia.js
- **Real-time Communication**: Pusher (WebSockets)
- **Styling**: Tailwind CSS
- **Database**: SQLite (يمكن تغييرها)

---

## بنية قاعدة البيانات

### الجداول الرئيسية:

1. **screens** (الشاشات)
   - `id`, `name`, `screen_code` (فريد), `orientation` (landscape/portrait)
   - `resolution`, `is_active`, `background_audio_id`
   - `timestamps`

2. **media_items** (الوسائط)
   - `id`, `file_path`, `file_type` (image/video/audio)
   - `duration` (للمدة بالثواني للصور)
   - `timestamps`

3. **playlists** (قوائم التشغيل)
   - `id`, `name`
   - `timestamps`

4. **media_item_playlist** (جدول الربط)
   - `media_item_id`, `playlist_id`, `order` (لترتيب العرض)
   - Primary key: (`media_item_id`, `playlist_id`)

5. **screen_assignments** (تخصيص المحتوى للشاشات)
   - `id`, `screen_id` (فريد)
   - `assignable_type` (Polymorphic: Playlist أو MediaItem)
   - `assignable_id`
   - `timestamps`

6. **departments** (الأقسام)
   - `id`, `name` (فريد)
   - `timestamps`

7. **doctors** (الأطباء)
   - `id`, `name`, `photo_path`, `department_id`
   - `timestamps`

8. **schedules** (جداول مواعيد الأطباء)
   - `id`, `doctor_id`
   - `day_of_week` (Saturday-Sunday-...-Friday)
   - `start_time`, `end_time`
   - `clinic_number`, `floor`
   - `timestamps`

9. **users** (المستخدمون)
   - الحقول الافتراضية من Laravel Breeze
   - `is_admin` (boolean) - لتحديد صلاحيات المدير

---

## الوظائف الرئيسية الموجودة

### 1. إدارة الشاشات (Screens Management)

**الملفات:**
- Controller: `app/Http/Controllers/Admin/ScreenController.php`
- Pages: `resources/js/Pages/Admin/Screens/`

**الوظائف:**
- ✅ إنشاء شاشات جديدة مع `screen_code` فريد
- ✅ تحديد اتجاه الشاشة (عرضي/طولي)
- ✅ تحديد دقة الشاشة (اختياري)
- ✅ تخصيص محتوى للشاشة:
  - إما قائمة تشغيل (Playlist)
  - أو ملف وسائط واحد (MediaItem)
- ✅ تحديد صوت خلفية (Background Audio) من ملفات الصوت
- ✅ تحديث المحتوى مع بث فوري عبر WebSocket
- ✅ عرض قائمة الشاشات
- ✅ حذف الشاشات

**Route:** `/admin/screens`

---

### 2. إدارة الوسائط (Media Items Management)

**الملفات:**
- Controller: `app/Http/Controllers/Admin/MediaItemController.php`
- Pages: `resources/js/Pages/Admin/MediaItems/`

**الوظائف:**
- ✅ رفع ملفات متعددة (صور/فيديو/صوت)
- ✅ أنواع الملفات المدعومة: JPG, JPEG, PNG, MP4, MOV, AVI, MP3, WAV
- ✅ تحديد نوع الملف تلقائياً (image/video/audio)
- ✅ تخزين الملفات في `storage/app/public/media`
- ✅ عرض قائمة الوسائط مع معاينة
- ✅ حذف الوسائط (مع حذف الملف من التخزين)

**Route:** `/admin/media-items`

**ملاحظة:** حالياً `duration` للصور يتم تعيينه افتراضياً على 10 ثوانٍ، ولا يوجد واجهة لتعديله.

---

### 3. إدارة قوائم التشغيل (Playlists Management)

**الملفات:**
- Controller: `app/Http/Controllers/Admin/PlaylistController.php`
- Pages: `resources/js/Pages/Admin/Playlists/`

**الوظائف:**
- ✅ إنشاء قوائم تشغيل جديدة
- ✅ إضافة/إزالة ملفات وسائط من القائمة
- ✅ ترتيب الملفات في القائمة (حالياً عبر `order` في pivot table)
- ✅ تحديث القائمة مع بث فوري للشاشات المرتبطة
- ✅ عرض قائمة قوائم التشغيل
- ✅ حذف قوائم التشغيل

**Route:** `/admin/playlists`

**ملاحظة:** واجهة تعديل ترتيب الملفات في القائمة (drag & drop) غير موجودة حالياً، والترتيب يعتمد على الـ order في قاعدة البيانات.

---

### 4. إدارة الأقسام (Departments Management)

**الملفات:**
- Controller: `app/Http/Controllers/Admin/DepartmentController.php`
- Pages: `resources/js/Pages/Admin/Departments/`

**الوظائف:**
- ✅ إنشاء أقسام جديدة (مع التحقق من الاسم الفريد)
- ✅ عرض قائمة الأقسام
- ✅ تعديل اسم القسم
- ✅ حذف الأقسام

**Route:** `/admin/departments`

---

### 5. إدارة الأطباء (Doctors Management)

**الملفات:**
- Controller: `app/Http/Controllers/Admin/DoctorController.php`
- Pages: `resources/js/Pages/Admin/Doctors/`

**الوظائف:**
- ✅ إنشاء أطباء جدد مع:
  - الاسم
  - القسم (Department)
  - صورة شخصية (اختياري)
- ✅ عرض قائمة الأطباء مع الصور
- ✅ تعديل بيانات الطبيب
- ✅ **إدارة جداول المواعيد:**
  - إضافة مواعيد (Schedule) لكل طبيب
  - تحديد: اليوم، وقت البدء والانتهاء، رقم العيادة، الطابق
  - حذف المواعيد
- ✅ حذف الأطباء (مع حذف الصور من التخزين)

**Routes:**
- `/admin/doctors` (CRUD)
- `/admin/doctors/{doctor}/schedules` (POST - إضافة موعد)
- `/admin/schedules/{schedule}` (DELETE - حذف موعد)

---

### 6. البث العام (Global Broadcast)

**الملفات:**
- Controller: `app/Http/Controllers/Admin/BroadcastController.php`
- Event: `app/Events/BroadcastMedia.php`
- Event: `app/Events/StopBroadcast.php`
- Page: `resources/js/Pages/Admin/Broadcast/Index.vue`

**الوظائف:**
- ✅ بث ملف وسائط واحد (صورة/فيديو) لجميع الشاشات فوراً
- ✅ إيقاف البث العام
- ✅ عرض حالة البث الحالي
- ✅ اختيار من قائمة الوسائط المرئية (صور/فيديو)

**Route:** `/admin/broadcast`

**ملاحظة:** البث العام له أولوية على محتوى الشاشة العادي، وعند إيقافه يعود المحتوى الأصلي.

---

### 7. شاشة العرض العامة (Display Screen)

**الملفات:**
- Controller: `app/Http/Controllers/DisplayController.php`
- Page: `resources/js/Pages/Display/Show.vue`
- Layout: `resources/views/display.blade.php`

**الوظائف:**
- ✅ عرض المحتوى المخصص للشاشة (Playlist أو MediaItem)
- ✅ مشغل تلقائي للمحتوى مع الانتقال بين العناصر
- ✅ دعم الصور (مع مدة عرض قابلة للتعديل)
- ✅ دعم الفيديو (مع تشغيل تلقائي)
- ✅ صوت خلفية قابل للتشغيل/الإيقاف
- ✅ **نظام الاستعلامات (Inquiry System):**
  - عرض الأقسام
  - عرض الأطباء في القسم
  - عرض تفاصيل الطبيب مع جدول المواعيد
  - ترجمة أيام الأسبوع للعربية
  - إغلاق تلقائي بعد 15 ثانية من عدم التفاعل
- ✅ **إشعارات المواعيد (Appointment Notifications):**
  - استقبال إشعارات فورية عند اقتراب موعد طبيب
  - عرض معلومات الطبيب والعيادة والطابق

**Route:** `/display/{code}` (عام، لا يحتاج مصادقة)

**Real-time Events المستخدمة:**
- `displays` channel: للبث العام (`BroadcastMedia`, `StopBroadcast`, `AppointmentApproaching`)
- `displays.{screen_code}` channel: لتحديث محتوى شاشة محددة (`ScreenContentUpdated`)

---

### 8. نظام الإشعارات الفورية (Real-time Notifications)

**الأحداث (Events):**

1. **ScreenContentUpdated**
   - يتم إرساله عند تحديث محتوى شاشة معينة
   - Channel: `displays.{screen_code}`
   - Event name: `ScreenContentUpdated`

2. **BroadcastMedia**
   - يتم إرساله عند بدء البث العام
   - Channel: `displays` (عام)
   - Event name: `BroadcastMedia`

3. **StopBroadcast**
   - يتم إرساله عند إيقاف البث العام
   - Channel: `displays` (عام)
   - Event name: `StopBroadcast`

4. **AppointmentApproaching**
   - يتم إرساله عند اقتراب موعد طبيب (من خلال Command)
   - Channel: `displays` (عام)
   - Event name: `AppointmentApproaching`
   - البيانات: معلومات الطبيب، الجدول، الصورة

**Command:**
- `app/Console/Commands/CheckAppointments.php`
- Command: `php artisan app:check-appointments`
- الوظيفة: فحص المواعيد المقبلة خلال الدقيقة القادمة وإرسال إشعارات

**ملاحظة:** يجب ضبط cron job أو task scheduler لتشغيل هذا الأمر بشكل دوري.

---

### 9. نظام المصادقة والصلاحيات

**الملفات:**
- Middleware: `app/Http/Middleware/CheckIsAdmin.php`
- Registration: Laravel Breeze

**الوظائف:**
- ✅ تسجيل الدخول/التسجيل (Laravel Breeze)
- ✅ التحقق من البريد الإلكتروني
- ✅ إعادة تعيين كلمة المرور
- ✅ صلاحيات المدير (`is_admin` flag)
- ✅ حماية مسارات `/admin/*` بصلاحيات المدير

**Middleware:** `admin` (مختصر لـ `CheckIsAdmin`)

---

## البنية التقنية

### Backend Structure:

```
app/
├── Console/Commands/
│   └── CheckAppointments.php          # فحص المواعيد المقبلة
├── Events/
│   ├── AppointmentApproaching.php     # إشعار اقتراب موعد
│   ├── BroadcastMedia.php             # حدث البث العام
│   ├── ScreenContentUpdated.php       # حدث تحديث محتوى الشاشة
│   └── StopBroadcast.php              # حدث إيقاف البث
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── BroadcastController.php
│   │   │   ├── DepartmentController.php
│   │   │   ├── DoctorController.php
│   │   │   ├── MediaItemController.php
│   │   │   ├── PlaylistController.php
│   │   │   └── ScreenController.php
│   │   ├── DisplayController.php      # عرض الشاشة العامة
│   │   └── ProfileController.php
│   └── Middleware/
│       └── CheckIsAdmin.php
└── Models/
    ├── Department.php
    ├── Doctor.php
    ├── MediaItem.php
    ├── Playlist.php
    ├── Schedule.php
    ├── Screen.php
    └── ScreenAssignment.php
```

### Frontend Structure:

```
resources/js/
├── Components/                        # مكونات Vue قابلة لإعادة الاستخدام
├── Layouts/
│   ├── AuthenticatedLayout.vue        # تخطيط لوحة التحكم
│   └── GuestLayout.vue                # تخطيط للزوار
├── Pages/
│   ├── Admin/
│   │   ├── Broadcast/Index.vue
│   │   ├── Departments/               # Create, Edit, Index
│   │   ├── Doctors/                   # Create, Edit, Index
│   │   ├── MediaItems/                # Create, Index
│   │   ├── Playlists/                 # Create, Edit, Index
│   │   └── Screens/                   # Create, Edit, Index
│   ├── Auth/                          # صفحات المصادقة (Breeze)
│   ├── Display/Show.vue               # شاشة العرض العامة
│   ├── Dashboard.vue
│   └── Profile/                       # Edit
└── app.js                             # نقطة دخول Vue + Inertia
```

---

## الإعدادات والتكوين

### Broadcasting Configuration:
- **Driver:** Pusher
- **Config:** `config/broadcasting.php`
- **Credentials:** موجودة مباشرة في الملف (يُنصح بنقلها لـ .env)

### Storage:
- **Disk:** `public`
- **Paths:**
  - Media: `storage/app/public/media`
  - Doctors Photos: `storage/app/public/doctors`

### Real-time Setup:
- **Laravel Echo:** مُثبت
- **Pusher JS:** مُثبت
- **Echo Configuration:** يجب التحقق من `resources/js/bootstrap.js`

---

## المهام والوظائف المكتملة ✅

1. ✅ إدارة الشاشات (CRUD)
2. ✅ إدارة الوسائط (رفع/عرض/حذف)
3. ✅ إدارة قوائم التشغيل (CRUD)
4. ✅ إدارة الأقسام (CRUD)
5. ✅ إدارة الأطباء (CRUD + جداول المواعيد)
6. ✅ البث العام للوسائط
7. ✅ شاشة العرض العامة مع مشغل تلقائي
8. ✅ نظام الاستعلامات (الأقسام/الأطباء/المواعيد)
9. ✅ إشعارات المواعيد الفورية
10. ✅ تحديث المحتوى الفوري عبر WebSocket
11. ✅ صوت خلفية للشاشات
12. ✅ نظام المصادقة والصلاحيات
13. ✅ واجهة إدارية كاملة

---

## المهام المفقودة أو التي تحتاج تحسين ⚠️

### 1. تحسينات في إدارة الوسائط:
- ⚠️ لا توجد واجهة لتعديل مدة عرض الصور (`duration`)
- ⚠️ مدة الصور ثابتة على 10 ثوانٍ
- ⚠️ لا يوجد معاينة قبل الرفع
- ⚠️ لا يوجد حد أقصى لحجم الملف في الواجهة

### 2. تحسينات في قوائم التشغيل:
- ⚠️ لا توجد واجهة drag & drop لترتيب الملفات
- ⚠️ الترتيب يعتمد على `order` في قاعدة البيانات لكن لا توجد واجهة لتعديله

### 3. تحسينات في إدارة المواعيد:
- ⚠️ لا يوجد تحقق من تعارض المواعيد (طبيب في مكانين في نفس الوقت)
- ⚠️ لا يوجد دعم للمواعيد المتعددة في نفس اليوم
- ⚠️ لا يوجد تحديد نطاق زمني للجدول (تاريخ بداية/نهاية)

### 4. نظام المواعيد والإشعارات:
- ⚠️ Command `CheckAppointments` يجب ضبطه على cron job
- ⚠️ لا يوجد واجهة لمعاينة المواعيد المقبلة
- ⚠️ الإشعارات تعتمد على `start_time` فقط (خلال دقيقة واحدة)

### 5. الأمان والصلاحيات:
- ⚠️ مفاتيح Pusher موجودة مباشرة في `config/broadcasting.php` (يُنصح بنقلها لـ .env)
- ⚠️ لا يوجد تسجيل للأنشطة (Activity Log)
- ⚠️ لا يوجد حماية CSRF خاصة للمسارات العامة

### 6. الأداء والتحسينات:
- ⚠️ لا يوجد نظام تخزين مؤقت (Cache) للوسائط
- ⚠️ الصور غير محسّنة (لا توجد resize/optimization)
- ⚠️ لا يوجد CDN للوسائط

### 7. تجربة المستخدم:
- ⚠️ لا توجد رسائل تأكيد قبل الحذف
- ⚠️ لا توجد pagination للقوائم الطويلة
- ⚠️ لا يوجد بحث/فلترة في القوائم

### 8. الوثائق والاختبارات:
- ⚠️ لا توجد اختبارات تلقائية (Tests)
- ⚠️ لا توجد وثائق API
- ⚠️ README.md لا يحتوي على معلومات المشروع

### 9. إضافات محتملة:
- ⚠️ نظام الإحصائيات (عدد المشاهدات، الملفات الأكثر استخداماً)
- ⚠️ جداول بث مجدولة (Scheduled Broadcasts)
- ⚠️ قوالب للشاشات (Templates)
- ⚠️ نظام النسخ الاحتياطي (Backup)

---

## التبعيات (Dependencies)

### Backend (PHP):
- laravel/framework: ^12.0
- inertiajs/inertia-laravel: ^2.0
- pusher/pusher-php-server: ^7.2
- laravel/sanctum: ^4.0
- tightenco/ziggy: ^2.0

### Frontend (JavaScript):
- vue: ^3.4.0
- @inertiajs/vue3: ^2.0.0
- laravel-echo: ^2.2.4
- pusher-js: ^8.4.0
- tailwindcss: ^3.2.1

---

## ملاحظات مهمة

1. **Pusher Configuration:** يجب التأكد من أن مفاتيح Pusher صحيحة وأن القناة `displays` موجودة.
2. **Storage Link:** يجب التأكد من تشغيل `php artisan storage:link` لربط التخزين العام.
3. **Queue:** الأحداث تستخدم `ShouldBroadcastNow` لذا لا تحتاج queue worker، لكن يُفضل استخدام Queue للتحسين.
4. **Cron Job:** يجب إضافة `php artisan app:check-appointments` للـ cron للتشغيل كل دقيقة.
5. **Database:** المشروع يستخدم SQLite افتراضياً، يمكن تغييره لـ MySQL/PostgreSQL في `.env`.

---

## الخلاصة

المشروع نظام متكامل لإدارة شاشات العرض في العيادات مع وظائف أساسية مكتملة. النظام يدعم:
- ✅ إدارة محتوى الشاشات
- ✅ البث الفوري
- ✅ إشعارات المواعيد
- ✅ نظام الاستعلامات

لكن يحتاج إلى تحسينات في:
- ⚠️ تجربة المستخدم (UX)
- ⚠️ إدارة الترتيب في قوائم التشغيل
- ⚠️ تحسين الأداء والأمان
- ⚠️ إضافة الاختبارات والوثائق

