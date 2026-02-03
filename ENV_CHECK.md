# فحص إعدادات البيئة - Reverb Broadcasting

## ✅ الإعدادات الصحيحة في `.env`

### Backend (Laravel):
```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=local-app
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
```

### Frontend (Vite):
```env
VITE_BROADCAST_DRIVER=reverb
VITE_REVERB_APP_KEY=local-key
VITE_REVERB_HOST=127.0.0.1
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

## 📋 التحقق من الإعدادات

### 1. التحقق من Backend:
```bash
php artisan config:show broadcasting.default
# يجب أن يعرض: reverb
```

### 2. التحقق من Reverb Config:
```bash
php artisan config:show reverb.apps.apps.0.key
# يجب أن يعرض: local-key
```

### 3. التحقق من Frontend:
بعد تشغيل `npm run dev`، افتح المتصفح وافتح Console (F12)، يجب أن ترى:
- اتصال WebSocket على `ws://127.0.0.1:8080`
- لا توجد أخطاء CORS

## 🔧 خطوات التشغيل

### 1. تشغيل Reverb:
```bash
php artisan reverb:start
```

### 2. تشغيل Laravel:
```bash
php artisan serve
```

### 3. تشغيل Vite:
```bash
npm run dev
```

## ⚠️ ملاحظات مهمة

1. **المنفذ**: تأكد من أن Port 8080 غير مستخدم:
   ```bash
   # Windows
   netstat -ano | findstr :8080
   
   # macOS/Linux
   lsof -i :8080
   ```

2. **متغيرات Vite**: يجب أن تبدأ جميع متغيرات البيئة للـ frontend بـ `VITE_`

3. **إعادة بناء Assets**: بعد تغيير متغيرات `.env`، يجب إعادة بناء assets:
   ```bash
   npm run build
   ```

4. **مسح الكاش**: بعد تغيير الإعدادات:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

## 🐛 استكشاف الأخطاء

### المشكلة: WebSocket لا يتصل
- تأكد من أن Reverb يعمل: `php artisan reverb:start`
- تأكد من أن `VITE_REVERB_PORT=8080` في `.env`
- تأكد من أن `REVERB_PORT=8080` في `.env`

### المشكلة: CORS Error
- تأكد من أن `allowed_origins` في `config/reverb.php` يحتوي على `*`
- تأكد من أن `REVERB_HOST=127.0.0.1` في `.env`

### المشكلة: متغيرات Vite غير موجودة
- تأكد من أن جميع المتغيرات تبدأ بـ `VITE_`
- أعد بناء assets: `npm run build`
- أعد تشغيل Vite dev server: `npm run dev`
