#!/bin/bash

# Quick Run Script for Android App
# Usage: ./QUICK_RUN.sh

set -e

echo "🚀 Starting Android App..."

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Set Android SDK path (common locations)
if [ -z "$ANDROID_HOME" ]; then
    if [ -d "$HOME/Library/Android/sdk" ]; then
        export ANDROID_HOME="$HOME/Library/Android/sdk"
    elif [ -d "$HOME/Android/Sdk" ]; then
        export ANDROID_HOME="$HOME/Android/Sdk"
    fi
fi

# Add Android tools to PATH
if [ -n "$ANDROID_HOME" ]; then
    export PATH="$ANDROID_HOME/emulator:$ANDROID_HOME/platform-tools:$ANDROID_HOME/tools:$PATH"
fi

# Check if emulator command exists
if ! command -v emulator &> /dev/null; then
    echo -e "${RED}❌ emulator command not found.${NC}"
    echo "Please set ANDROID_HOME or add Android SDK to PATH:"
    echo "export ANDROID_HOME=\$HOME/Library/Android/sdk"
    echo "export PATH=\$ANDROID_HOME/emulator:\$PATH"
    exit 1
fi

# Check if adb command exists
if ! command -v adb &> /dev/null; then
    echo -e "${RED}❌ adb command not found.${NC}"
    echo "Please add Android SDK platform-tools to PATH"
    exit 1
fi

# Check if emulator is running
echo -e "${YELLOW}Checking for running emulator...${NC}"
if ! adb devices | grep -q "emulator"; then
    echo -e "${YELLOW}No emulator found. Starting emulator...${NC}"
    # Try to start first available emulator
    FIRST_AVD=$(emulator -list-avds | head -n 1)
    if [ -z "$FIRST_AVD" ]; then
        echo -e "${RED}❌ No AVD found. Please create an emulator first.${NC}"
        echo "Run: emulator -list-avds to see available emulators"
        exit 1
    fi
    echo "Starting emulator: $FIRST_AVD"
    emulator -avd "$FIRST_AVD" > /dev/null 2>&1 &
    echo "Waiting for emulator to boot (this may take a minute)..."
    adb wait-for-device
    sleep 10
    echo -e "${GREEN}Emulator is ready!${NC}"
else
    echo -e "${GREEN}Emulator is already running${NC}"
fi

# Check devices
echo -e "${YELLOW}Connected devices:${NC}"
adb devices

# Build and install
echo -e "${YELLOW}Building and installing app...${NC}"
./gradlew installDebug

# Launch app
echo -e "${GREEN}Launching app...${NC}"
adb shell am start -n com.clinic.screen/.ui.main.MainActivity

echo -e "${GREEN}✅ Done! App should be running on emulator.${NC}"

