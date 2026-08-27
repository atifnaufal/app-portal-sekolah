# Implementation Plan: App-Like UI/UX Overhaul

This plan transforms the mobile web experience into a professional, fluid application with persistent floating navigation, integrated transitions, and a clean minimalist aesthetic.

## Proposed Changes

### 1. Layout & Navigation (Unified Framework)

#### [MODIFY] [mobile-app.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/layouts/mobile-app.blade.php)
- **Floating Menu**: Redesign `.bottom-nav` to be a floating bar (rounded corners, detached from bottom, backdrop-blur).
- **Splash Screen**: Update `#page-loader` to be transparent (removing the white background) and center the school logo.
- **Header Removal**: Remove all top-level headers and "APP MAHASISWA" text.
- **Fluid Transitions**: Wrap the content area in an `animate.css` container (e.g., `animate__fadeIn`) to make page loads feel like app transitions.

#### [DELETE] [mobile-page.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/layouts/mobile-page.blade.php)
- This layout will be deprecated in favor of a single unified `mobile-app` layout to ensure the floating menu is always visible.

### 2. View Migration (Standardizing UI)

#### [MODIFY] Multiple Files in `resources/views/mobile/`
- All pages currently using `layouts.mobile-page` will be migrated to `layouts.mobile-app`.
- Impacted files: `absensi.blade.php`, `chat.blade.php`, `notifications.blade.php`, `profile.blade.php`, `spp.blade.php`, `tugas.blade.php`, and more.
- Sub-pages will have a slim, integrated "Back" button added to the content area since the global header is being removed.

## Verification Plan

### Manual Verification
1. Log in and verify the new **Floating Bottom Nav**.
2. Navigate between tabs (Dashboard, Absensi, etc.) and verify the menu stays visible and pages slide/fade in smoothly.
3. Verify that the **Splash Screen** no longer has a white background flash.
4. Open a sub-page (e.g., Notification details or Profile edit) and verify the floating menu is still there and a back button is available.
5. Check for any "Internal Server Errors" or layout breaks on all screens.
