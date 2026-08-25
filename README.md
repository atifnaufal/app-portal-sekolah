# app-portal-sekolah
applikasi berbasis sekolah

## Android Build Fixes

The following issues were resolved to ensure a successful Android build:

1.  **SDK Location**: Created `android/local.properties` with the correct `sdk.dir` pointing to `C:\Users\AbidSukses\AppData\Local\Android\Sdk`.
2.  **Plugin Compilation Error**: Fixed a compilation error in `capacitor-native-biometric` by adding the missing `androidx.activity` dependency in its `build.gradle`.
3.  **Gradle Warnings**: Cleaned up `android/gradle.properties` to remove deprecated flags and suppressed unsupported project option warnings.
4.  **Environment Variable Conflict**: Identified a conflict where both `ANDROID_PREFS_ROOT` and `ANDROID_USER_HOME` were set. 

### Important Note on Environment Variables

If you encounter an error related to `ANDROID_PREFS_ROOT` and `ANDROID_USER_HOME` both being set, you should:
- Unset `ANDROID_PREFS_ROOT` in your environment.
- Or ensures `ANDROID_USER_HOME` is the only one set to `C:\Users\AbidSukses\.android`.

In PowerShell, you can run:
```powershell
$env:ANDROID_PREFS_ROOT=$null
./gradlew assembleDebug
```

## Build Instructions

To build the project, navigate to the `android/` directory and run:
```bash
./gradlew assembleDebug
```
