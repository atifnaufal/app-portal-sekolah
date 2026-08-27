# Implementation Plan: Advanced UI & Session Restoration

This plan implements high-end UI refinements and solves the persistent logout issue using advanced session restoration techniques.

## User Review Required

> [!IMPORTANT]
> To solve the session logout issue on Railway, ensure that your `SESSION_DRIVER` in the Railway environment variables is NOT set to `file` or `database` (if using SQLite). I recommend setting it to `cookie` for the most stable experience on serverless platforms, OR ensuring you are using a managed MySQL/Postgres database.

## Proposed Changes

### 1. Dashboard & Notification Logic

#### [MODIFY] [dashboard.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/dashboard.blade.php)
- **UI Cleanup**: Remove the class/role badge below the user's name.
- **Notification Bell**: Redesign as a clean white circular button with a blue bell icon.
- **Green Indicator**: Change the notification dot to green (`bg-success`).
- **Voice Alert**: Add logic to play the "Ada notifikasi untukmu" voice alert exactly once when new data is detected.

### 2. Session Persistence Engine

#### [MODIFY] [RoleMiddleware.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Middleware/RoleMiddleware.php)
- **Deep Restoration**: Enhance the middleware to forcefully re-authenticate and re-populate session keys if the user has a valid "Remember Me" cookie, even if the primary session storage was wiped.

#### [MODIFY] [AuthController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/AuthController.php)
- **Cookie Security**: Ensure the `remember` duration is explicitly long and cookies are queued correctly.

## Verification Plan

### Manual Verification
1. Log in and verify the dashboard header is clean (no badge below name).
2. Trigger a notification and verify:
   - The indicator turns **Green**.
   - The device speaks **"Ada notifikasi untukmu"**.
3. Close the browser tab/app completely, wait 1 minute, and re-open.
4. Verify you are **STILL logged in** without seeing the login screen.
