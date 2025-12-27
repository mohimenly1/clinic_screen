# إعداد VSCode لتطوير Android

## إضافة Extensions الموصى بها

### 1. Kotlin Language
- **Extension ID**: `fwcd.kotlin`
- **Name**: Kotlin Language

### 2. Gradle Tasks
- **Extension ID**: `richardwillis.vscode-gradle`
- **Name**: Gradle for Java

### 3. Android (اختياري)
- **Extension ID**: `vscjava.vscode-gradle`
- **Name**: Gradle for Java (يتضمن دعم Android)

## إعداد Tasks في VSCode

أنشئ ملف `.vscode/tasks.json`:

```json
{
    "version": "2.0.0",
    "tasks": [
        {
            "label": "Build Debug APK",
            "type": "shell",
            "command": "./gradlew",
            "args": ["assembleDebug"],
            "options": {
                "cwd": "${workspaceFolder}/android_app"
            },
            "group": {
                "kind": "build",
                "isDefault": true
            },
            "problemMatcher": []
        },
        {
            "label": "Install Debug",
            "type": "shell",
            "command": "./gradlew",
            "args": ["installDebug"],
            "options": {
                "cwd": "${workspaceFolder}/android_app"
            },
            "problemMatcher": []
        },
        {
            "label": "Run App",
            "type": "shell",
            "command": "adb",
            "args": [
                "shell",
                "am",
                "start",
                "-n",
                "com.clinic.screen/.ui.main.MainActivity",
                "--es",
                "SCREEN_CODE",
                "SCREEN001"
            ],
            "problemMatcher": []
        },
        {
            "label": "Show Logs",
            "type": "shell",
            "command": "adb",
            "args": ["logcat", "-s", "MainActivity", "MainViewModel", "PusherService"],
            "isBackground": true,
            "problemMatcher": {
                "pattern": {
                    "regexp": "^([^:]*):(\\d+):(\\d+):\\s+(warning|error):\\s+(.*)$",
                    "file": 1,
                    "line": 2,
                    "column": 3,
                    "severity": 4,
                    "message": 5
                },
                "background": {
                    "activeOnStart": true,
                    "beginsPattern": ".*",
                    "endsPattern": ".*"
                }
            }
        },
        {
            "label": "Clean Build",
            "type": "shell",
            "command": "./gradlew",
            "args": ["clean"],
            "options": {
                "cwd": "${workspaceFolder}/android_app"
            },
            "problemMatcher": []
        }
    ]
}
```

## إعداد Launch Configuration

أنشئ ملف `.vscode/launch.json`:

```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "type": "kotlin",
            "request": "launch",
            "name": "Build & Run Android App",
            "projectRoot": "${workspaceFolder}/android_app",
            "mainClass": "com.clinic.screen.ui.main.MainActivity",
            "preLaunchTask": "Install Debug"
        }
    ]
}
```

## استخدام Tasks

### من Command Palette:
1. اضغط `Cmd+Shift+P` (Mac) أو `Ctrl+Shift+P` (Windows/Linux)
2. اكتب `Tasks: Run Task`
3. اختر المهمة المطلوبة

### من Terminal:
استخدم الـ scripts المرفقة:
```bash
cd android_app
./build-and-run.sh SCREEN001
```

## Keyboard Shortcuts (اختياري)

أضف في `.vscode/keybindings.json`:

```json
[
    {
        "key": "cmd+shift+b",
        "command": "workbench.action.tasks.build"
    },
    {
        "key": "cmd+shift+i",
        "command": "workbench.action.tasks.runTask",
        "args": "Install Debug"
    }
]
```

## نصائح VSCode

1. **Multi-root Workspace**: يمكنك فتح كل من Laravel و Android في نفس الـ workspace
2. **Terminal Integration**: استخدم Split Terminal لتشغيل Laravel و Android في نفس الوقت
3. **File Watchers**: يمكنك إعداد File Watchers لتشغيل Gradle تلقائياً عند تغيير الملفات

