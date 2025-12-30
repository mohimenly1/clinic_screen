#!/bin/bash

# Script to create a keystore for signing the Android app

KEYSTORE_NAME="clinic-screen-release.jks"
KEYSTORE_PATH="app/${KEYSTORE_NAME}"
KEY_ALIAS="clinic-screen-key"
STORE_PASSWORD="123@@123"
KEY_PASSWORD="123@@123"

echo "Creating keystore for Android app signing..."
echo "Keystore file: ${KEYSTORE_PATH}"
echo "Key alias: ${KEY_ALIAS}"

# Check if keystore already exists
if [ -f "${KEYSTORE_PATH}" ]; then
    echo "Warning: Keystore file already exists at ${KEYSTORE_PATH}"
    read -p "Do you want to overwrite it? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Aborted. Keystore creation cancelled."
        exit 1
    fi
    rm "${KEYSTORE_PATH}"
fi

# Create the keystore
keytool -genkeypair \
    -v \
    -storetype PKCS12 \
    -keystore "${KEYSTORE_PATH}" \
    -alias "${KEY_ALIAS}" \
    -keyalg RSA \
    -keysize 2048 \
    -validity 10000 \
    -storepass "${STORE_PASSWORD}" \
    -keypass "${KEY_PASSWORD}" \
    -dname "CN=Clinic Screen, OU=Development, O=Clinic Screen, L=City, ST=State, C=SA"

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Keystore created successfully!"
    echo "  Location: ${KEYSTORE_PATH}"
    echo ""
    echo "Note: Keep this keystore file safe. You'll need it to sign future updates."
    echo "      The keystore.properties file is already configured with the correct values."
else
    echo ""
    echo "✗ Failed to create keystore. Please check the error messages above."
    exit 1
fi


