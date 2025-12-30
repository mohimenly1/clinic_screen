#!/bin/bash

# Script to build release APK
# Usage: ./build-release.sh

set -e

echo "🚀 بدء بناء APK للتطبيق..."

# Navigate to android_app directory
cd "$(dirname "$0")"

# Clean previous builds
echo "🧹 تنظيف البناء السابق..."
./gradlew clean

# Build release APK
echo "🔨 بناء APK Release..."
./gradlew assembleRelease

# Check if APK was created
APK_PATH="app/build/outputs/apk/release/app-release.apk"
if [ -f "$APK_PATH" ]; then
    APK_SIZE=$(du -h "$APK_PATH" | cut -f1)
    echo ""
    echo "✅ تم بناء APK بنجاح!"
    echo "📦 الملف: $APK_PATH"
    echo "📊 الحجم: $APK_SIZE"
    echo ""
    echo "📱 لتثبيت APK على الجهاز:"
    echo "   adb install $APK_PATH"
    echo ""
else
    echo "❌ فشل بناء APK. تحقق من الأخطاء أعلاه."
    exit 1
fi



