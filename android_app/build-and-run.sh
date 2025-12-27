#!/bin/bash

# Script to build and run the Android app
# Usage: ./build-and-run.sh [screen_code]

SCREEN_CODE=${1:-"SCREEN001"}

echo "🔨 Building app..."
cd "$(dirname "$0")"
./gradlew assembleDebug

if [ $? -ne 0 ]; then
    echo "❌ Build failed!"
    exit 1
fi

echo "📱 Installing app..."
./gradlew installDebug

if [ $? -ne 0 ]; then
    echo "❌ Installation failed!"
    exit 1
fi

echo "🚀 Launching app with screen code: $SCREEN_CODE"
adb shell am start -n com.clinic.screen/.ui.main.MainActivity --es SCREEN_CODE "$SCREEN_CODE"

echo "📊 Showing logs..."
adb logcat -c  # Clear logs
adb logcat -s MainActivity MainViewModel PusherService RetrofitClient

