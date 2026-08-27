# Implementation Plan: Modern & Interactive Login UI

This plan focuses on upgrading the login page to provide a professional "school portal" experience with improved UX and visual aesthetics.

## Proposed Changes

### UI / UX Enhancements

#### [MODIFY] [login.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/auth/login.blade.php)
- **Visuals**: Replace the current header with a clean Undraw illustration.
- **Password Toggle**: Add an "Eye" icon to toggle password visibility.
- **Guidance**: Add helper text explaining password security/requirements.
- **Splash Screen**: Refine the loading animation to be smoother and more immersive.
- **Mobile Optimization**: Ensure the design is pixel-perfect on small screens (Android App view).

## Verification Plan

### Manual Verification
1. Open the login page on a mobile device/emulator.
2. Click the eye icon in the password field and verify it toggles correctly.
3. Submit the form and verify the "Memuat Portal" splash screen appears with a smooth animation.
4. Verify the new Undraw illustration displays correctly.
