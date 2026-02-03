# إعداد Reverb على IP و Port مخصص

## الطريقة 1: تعديل ملف `.env` (الطريقة الموصى بها)

### إعدادات Server (الخادم):
```env
# IP الذي سيربط عليه Reverb Server (0.0.0.0 = جميع العناوين)
REVERB_SERVER_HOST=0.0.0.0

# Port الذي سيعمل عليه Reverb Server
REVERB_SERVER_PORT=8080
```

### إعدادات Application (للاتصال من Frontend):
```env
# IP الذي سيستخدمه Frontend للاتصال بـ Reverb
REVERB_HOST=127.0.0.1

# Port الذي سيستخدمه Frontend للاتصال بـ Reverb
REVERB_PORT=8080

# Scheme (http أو https)
REVERB_SCHEME=http
```

### إعدادات Frontend (Vite):
```env
# IP و Port للـ Frontend
VITE_REVERB_HOST=127.0.0.1
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

## الطريقة 2: استخدام خيارات سطر الأوامر

يمكنك تشغيل Reverb مع IP و Port محددين مباشرة:

```bash
php artisan reverb:start --host=192.168.1.100 --port=9000
```

### الخيارات المتاحة:
- `--host`: IP address (مثال: `192.168.1.100` أو `0.0.0.0`)
- `--port`: Port number (مثال: `9000`)
- `--hostname`: Hostname للوصول من الخارج
- `--path`: Path prefix للـ routes
- `--debug`: تفعيل وضع التصحيح

## أمثلة عملية:

### مثال 1: تشغيل على IP محلي معين
```env
# .env
REVERB_SERVER_HOST=192.168.1.100
REVERB_SERVER_PORT=9000
REVERB_HOST=192.168.1.100
REVERB_PORT=9000
VITE_REVERB_HOST=192.168.1.100
VITE_REVERB_PORT=9000
```

ثم:
```bash
php artisan reverb:start
```

### مثال 2: تشغيل على جميع العناوين (للوصول من الشبكة)
```env
# .env
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
REVERB_HOST=192.168.1.100  # IP الخاص بك في الشبكة
REVERB_PORT=8080
VITE_REVERB_HOST=192.168.1.100
VITE_REVERB_PORT=8080
```

### مثال 3: استخدام سطر الأوامر مباشرة
```bash
# تشغيل على IP و Port محددين
php artisan reverb:start --host=0.0.0.0 --port=9000

# مع hostname
php artisan reverb:start --host=0.0.0.0 --port=9000 --hostname=reverb.example.com

# مع debug
php artisan reverb:start --host=0.0.0.0 --port=9000 --debug
```

## تحديث package.json للسهولة:

يمكنك إضافة scripts مخصصة في `package.json`:

```json
{
  "scripts": {
    "reverb": "php artisan reverb:start",
    "reverb:local": "php artisan reverb:start --host=127.0.0.1 --port=8080",
    "reverb:network": "php artisan reverb:start --host=0.0.0.0 --port=9000",
    "reverb:custom": "php artisan reverb:start --host=192.168.1.100 --port=9000"
  }
}
```

ثم استخدم:
```bash
npm run reverb:local
npm run reverb:network
npm run reverb:custom
```

## ملاحظات مهمة:

### 1. الفرق بين REVERB_SERVER_HOST و REVERB_HOST:
- **REVERB_SERVER_HOST**: IP الذي سيربط عليه الخادم (عادة `0.0.0.0` للسماح بالاتصالات من جميع العناوين)
- **REVERB_HOST**: IP الذي سيستخدمه Frontend للاتصال (عادة IP الخاص بك في الشبكة أو `127.0.0.1` للمحلي)

### 2. الأمان:
- إذا كنت تستخدم `0.0.0.0`، تأكد من وجود جدار ناري
- في الإنتاج، استخدم HTTPS (`REVERB_SCHEME=https`)

### 3. CORS:
- تأكد من أن `allowed_origins` في `config/reverb.php` يحتوي على العناوين المسموحة

### 4. التحقق من المنفذ:
```bash
# Windows
netstat -ano | findstr :8080

# macOS/Linux
lsof -i :8080
```

## مثال كامل:

### السيناريو: تشغيل Reverb على IP `192.168.1.100` و Port `9000`

#### 1. تعديل `.env`:
```env
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=9000
REVERB_HOST=192.168.1.100
REVERB_PORT=9000
REVERB_SCHEME=http
VITE_REVERB_HOST=192.168.1.100
VITE_REVERB_PORT=9000
VITE_REVERB_SCHEME=http
```

#### 2. مسح الكاش:
```bash
php artisan config:clear
```

#### 3. تشغيل Reverb:
```bash
php artisan reverb:start
```

#### 4. التحقق:
افتح المتصفح على `http://192.168.1.100:8000` وافتح Console (F12)، يجب أن ترى اتصال WebSocket على `ws://192.168.1.100:9000`
