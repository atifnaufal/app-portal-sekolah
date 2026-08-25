# Implementation Plan - App Customization and Production Build

This plan covers the customization of the Android app (Logo, Splash Screen, URL) and the final production APK build.

## Proposed Changes

### Android Customization
#### [MODIFY] [capacitor.config.json](file:///C:/laragon/www/app-portal-sekolah/capacitor.config.json)
- Add Splash Screen configuration for production look.
- Ensure production URL is set.

#### [MODIFY] [android/app/src/main/assets/capacitor.config.json](file:///C:/laragon/www/app-portal-sekolah/android/app/src/main/assets/capacitor.config.json)
- Sync changes with the main config.

#### [MODIFY] Android Assets (Icons and Splash)
- Replace all `ic_launcher*.png` and `splash.png` files in `android/app/src/main/res/` with the downloaded logo from pngtree.

### Build and Deployment
- Run `./gradlew assembleDebug` (or `assembleRelease` if signing is configured, but we'll stick to debug for now as requested).
- Copy the final APK to `public/downloads/app-portal-sekolah.apk`.

## Verification Plan

### Automated Tests
- Check if `./gradlew assembleDebug` completes successfully.
- Verify the existence of the APK in the public downloads folder.

### Manual Verification
- User to download and install the APK to verify the new logo and splash screen.
- User to verify that the app points to the production URL.
