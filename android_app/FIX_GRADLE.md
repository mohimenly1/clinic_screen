# إصلاح مشكلة Gradle Wrapper

## المشكلة
`./gradlew` غير موجود أو `gradle-wrapper.jar` مفقود.

## الحل السريع

### الخطوة 1: تحميل gradle-wrapper.jar

قم بتشغيل هذا الأمر في Terminal:

```bash
cd "/Users/sulimangzllal/Development/clinic screen/clink_screen/android_app"
mkdir -p gradle/wrapper
curl -L -o gradle/wrapper/gradle-wrapper.jar \
  https://raw.githubusercontent.com/gradle/gradle/v8.5.0/gradle/wrapper/gradle-wrapper.jar
```

**أو** استخدم الـ script المرفق:

```bash
cd android_app
./INSTALL_GRADLE_WRAPPER.sh
```

### الخطوة 2: جعل gradlew قابل للتنفيذ

```bash
chmod +x gradlew
```

### الخطوة 3: التحقق

```bash
./gradlew --version
```

يجب أن ترى إصدار Gradle.

---

## إذا فشل التحميل

### الطريقة البديلة 1: استخدام wget

```bash
cd android_app
mkdir -p gradle/wrapper
wget -O gradle/wrapper/gradle-wrapper.jar \
  https://raw.githubusercontent.com/gradle/gradle/v8.5.0/gradle/wrapper/gradle-wrapper.jar
chmod +x gradlew
```

### الطريقة البديلة 2: تحميل يدوي

1. افتح المتصفح واذهب إلى:
   ```
   https://raw.githubusercontent.com/gradle/gradle/v8.5.0/gradle/wrapper/gradle-wrapper.jar
   ```

2. احفظ الملف في:
   ```
   android_app/gradle/wrapper/gradle-wrapper.jar
   ```

3. ثم:
   ```bash
   cd android_app
   chmod +x gradlew
   ```

### الطريقة البديلة 3: استخدام Android Studio

1. افتح المشروع في Android Studio
2. Android Studio سيقوم بتحميل Gradle Wrapper تلقائياً

---

## بعد الإعداد

الآن يمكنك استخدام:

```bash
cd android_app
./gradlew installDebug
./gradlew build
./gradlew clean
```

