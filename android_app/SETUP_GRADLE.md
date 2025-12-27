# إعداد Gradle Wrapper

## المشكلة
إذا ظهرت رسالة `zsh: no such file or directory: ./gradlew`، هذا يعني أن Gradle Wrapper غير موجود.

## الحل

### الطريقة 1: تحميل Gradle Wrapper يدوياً

قم بتحميل `gradle-wrapper.jar`:

```bash
cd android_app
mkdir -p gradle/wrapper
curl -L -o gradle/wrapper/gradle-wrapper.jar \
  https://raw.githubusercontent.com/gradle/gradle/v8.5.0/gradle/wrapper/gradle-wrapper.jar
chmod +x gradlew
```

### الطريقة 2: استخدام Gradle المثبت محلياً

إذا كان Gradle مثبت على جهازك:

```bash
cd android_app
gradle wrapper --gradle-version 8.5
```

### الطريقة 3: استخدام Android Studio

1. افتح المشروع في Android Studio
2. Android Studio سيقوم بإنشاء Gradle Wrapper تلقائياً

## التحقق من الإعداد

بعد إعداد Gradle Wrapper، تحقق:

```bash
cd android_app
./gradlew --version
```

يجب أن ترى إصدار Gradle.

## بعد الإعداد

الآن يمكنك استخدام:

```bash
./gradlew installDebug
./gradlew build
./gradlew clean
```

