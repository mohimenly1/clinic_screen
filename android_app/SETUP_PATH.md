# إعداد PATH لـ Android SDK

## المشكلة
`emulator: command not found` - يعني أن Android SDK tools ليست في PATH

## الحل السريع (مؤقت للجلسة الحالية):

```bash
export ANDROID_HOME=$HOME/Library/Android/sdk
export PATH=$ANDROID_HOME/emulator:$ANDROID_HOME/platform-tools:$ANDROID_HOME/tools:$PATH
```

## الحل الدائم (إضافة إلى ~/.zshrc):

```bash
# أضف هذا إلى ~/.zshrc
echo 'export ANDROID_HOME=$HOME/Library/Android/sdk' >> ~/.zshrc
echo 'export PATH=$ANDROID_HOME/emulator:$ANDROID_HOME/platform-tools:$ANDROID_HOME/tools:$PATH' >> ~/.zshrc

# ثم أعد تحميل shell
source ~/.zshrc
```

## التحقق من الإعداد:

```bash
# تحقق من ANDROID_HOME
echo $ANDROID_HOME

# تحقق من emulator
which emulator

# تحقق من adb
which adb

# قائمة المحاكيات
emulator -list-avds
```

## إذا كان Android SDK في مكان آخر:

```bash
# ابحث عن Android SDK
find ~ -name "emulator" -type f 2>/dev/null | head -1

# ثم استخدم المسار الذي تجده
export ANDROID_HOME=/path/to/android/sdk
```

## بعد الإعداد:

```bash
cd android_app
./QUICK_RUN.sh
```

