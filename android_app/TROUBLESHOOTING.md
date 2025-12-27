# استكشاف الأخطاء وحلها

## المشكلة: التطبيق يعمل لكن لا يوجد عرض

### 1. تحقق من Laravel Server

```bash
# تأكد من أن Laravel server يعمل
curl http://localhost:8000/api/v1/screens/SCREEN001

# أو من المحاكي
adb shell "curl http://10.0.2.2:8000/api/v1/screens/SCREEN001"
```

### 2. تحقق من Screen Code

```bash
# شغل التطبيق مع screen code محدد
adb shell am start -n com.clinic.screen/.ui.main.MainActivity --es SCREEN_CODE "SCREEN001"

# تحقق من Logcat
adb logcat | grep -i "MainActivity\|MainViewModel\|MediaPlayer"
```

### 3. تحقق من API Response

في Logcat ابحث عن:
- `MainActivity: Screen Code: ...`
- `MainViewModel: Error loading screen data`
- `MainActivity: Updating screen content: X items`

### 4. تحقق من الصور/الفيديوهات

- تأكد من أن URLs صحيحة
- تأكد من أن الصور موجودة في `storage/app/public`
- تأكد من أن `php artisan storage:link` تم تنفيذه

### 5. تحقق من Network

```bash
# تحقق من الاتصال بالإنترنت في المحاكي
adb shell ping -c 3 10.0.2.2

# تحقق من DNS
adb shell getprop | grep dns
```

### 6. تحقق من Permissions

في `AndroidManifest.xml` يجب أن يكون:
```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
```

### 7. تحقق من MediaPlayerFragment

- تأكد من أن Fragment تم إضافته إلى Activity
- تحقق من Logcat: `MediaPlayerFragment: Displaying media: ...`

## خطوات التشخيص:

1. **افتح Logcat**:
   ```bash
   adb logcat | grep -i "clinic\|MainActivity\|MainViewModel\|MediaPlayer\|Pusher"
   ```

2. **تحقق من Screen Code**:
   ```bash
   adb shell am start -n com.clinic.screen/.ui.main.MainActivity --es SCREEN_CODE "SCREEN001"
   ```

3. **تحقق من API**:
   ```bash
   curl http://localhost:8000/api/v1/screens/SCREEN001 | jq
   ```

4. **تحقق من البيانات**:
   - تأكد من أن Screen موجود في قاعدة البيانات
   - تأكد من أن Screen لديه Playlist أو Media Item
   - تأكد من أن Media Items موجودة

## حلول سريعة:

### إذا كان Screen Code غير صحيح:
```bash
adb shell am start -n com.clinic.screen/.ui.main.MainActivity --es SCREEN_CODE "YOUR_SCREEN_CODE"
```

### إذا كان API URL غير صحيح:
غير في `ApiConfig.kt`:
- للمحاكي: `http://10.0.2.2:8000/api/v1/`
- للجهاز الحقيقي: `http://YOUR_IP:8000/api/v1/`

### إذا كان Laravel Server لا يعمل:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

