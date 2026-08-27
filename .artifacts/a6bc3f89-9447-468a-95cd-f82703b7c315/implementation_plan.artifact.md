# Implementation Plan: Stabilized Profile & Header Cleanup

This plan focuses on fixing the profile image alignment issues, cleaning up the header UI, and ensuring the voice notification system is flawless.

## Proposed Changes

### 1. Header & Avatar Stabilization

#### [MODIFY] [dashboard.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/dashboard.blade.php)
- **Avatar Stabilization**: Remove dynamic `object-position` (X/Y) which is causing the image to shift. Use `object-position: center` for a stable, automatic center-crop.
- **Header Cleanup**: Completely remove the `mt-2` div and any badge logic below the user's name to eliminate the "white box" issue.
- **Bell Button**: Keep the white circular button style with a blue icon and green indicator.
- **Voice Logic**: Refine the local storage check to ensure the voice alert only plays once per unique notification count.

### 2. Session Integrity

#### [MODIFY] [RoleMiddleware.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Middleware/RoleMiddleware.php)
- Ensure the re-authentication logic is robust enough to handle fast app-switching/tab-closure scenarios.

## Verification Plan

### Manual Verification
1. Open the dashboard and verify the profile picture is **perfectly centered** (stable).
2. Confirm there is **absolutely nothing** (no white pills/badges) below the user's name.
3. Trigger a notification and listen for the voice alert (verify it only happens once).
4. Verify the indicator is **Green**.
5. Close the app/tab, re-open, and verify you are still logged in.
