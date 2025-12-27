# إصلاح مشكلة JDK

## المشكلة
```
Error while executing process /Library/Java/JavaVirtualMachines/jdk-21.jdk/Contents/Home/bin/jlink
```

هذه المشكلة تحدث لأن Android Gradle Plugin 8.2.0 يحتاج JDK 17، لكن النظام يستخدم JDK 21.

## الحل

### الطريقة 1: تثبيت JDK 17 (موصى به)

#### باستخدام Homebrew:
```bash
brew install openjdk@17
```

#### ثم تعيين JAVA_HOME:
```bash
# في ~/.zshrc أو ~/.bash_profile
export JAVA_HOME=$(/usr/libexec/java_home -v 17)
```

#### أو مؤقتاً في الجلسة الحالية:
```bash
export JAVA_HOME=$(/usr/libexec/java_home -v 17)
cd android_app
./gradlew installDebug
```

### الطريقة 2: تنزيل JDK 17 يدوياً

1. اذهب إلى: https://adoptium.net/temurin/releases/?version=17
2. حمّل JDK 17 لـ macOS
3. ثبت الملف `.dmg`
4. ثم:
```bash
export JAVA_HOME=/Library/Java/JavaVirtualMachines/temurin-17.jdk/Contents/Home
cd android_app
./gradlew installDebug
```

### الطريقة 3: استخدام Android Studio

إذا كان لديك Android Studio مثبت:
- Android Studio يأتي مع JDK 17 مدمج
- افتح المشروع في Android Studio
- سيستخدم JDK 17 تلقائياً

## التحقق من الإصدار

```bash
java -version
# يجب أن يظهر: openjdk version "17.x.x"
```

## ملاحظة

بعد تثبيت JDK 17، قد تحتاج إلى:
```bash
cd android_app
./gradlew clean
./gradlew installDebug
```

