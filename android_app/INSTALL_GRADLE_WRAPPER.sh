#!/bin/bash

# Script to install Gradle Wrapper
# Usage: ./INSTALL_GRADLE_WRAPPER.sh

echo "📦 Installing Gradle Wrapper..."

cd "$(dirname "$0")"

# Create wrapper directory
mkdir -p gradle/wrapper

# Download gradle-wrapper.jar
echo "⬇️  Downloading gradle-wrapper.jar..."
curl -L -o gradle/wrapper/gradle-wrapper.jar \
  https://raw.githubusercontent.com/gradle/gradle/v8.5.0/gradle/wrapper/gradle-wrapper.jar

if [ $? -eq 0 ]; then
    echo "✅ gradle-wrapper.jar downloaded successfully"
else
    echo "❌ Failed to download gradle-wrapper.jar"
    echo "Trying alternative method..."
    
    # Alternative: Use wget if available
    if command -v wget &> /dev/null; then
        wget -O gradle/wrapper/gradle-wrapper.jar \
          https://raw.githubusercontent.com/gradle/gradle/v8.5.0/gradle/wrapper/gradle-wrapper.jar
    else
        echo "❌ Please download gradle-wrapper.jar manually:"
        echo "   https://raw.githubusercontent.com/gradle/gradle/v8.5.0/gradle/wrapper/gradle-wrapper.jar"
        echo "   Save it to: gradle/wrapper/gradle-wrapper.jar"
        exit 1
    fi
fi

# Make gradlew executable
chmod +x gradlew

echo "✅ Gradle Wrapper installed successfully!"
echo ""
echo "You can now run:"
echo "  ./gradlew --version"
echo "  ./gradlew installDebug"

