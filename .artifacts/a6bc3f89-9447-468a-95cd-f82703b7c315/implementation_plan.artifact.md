# Implementation Plan: Advanced Account Recovery

This plan implements account recovery features including password reset via email, email recovery via phone number, and clear instructions for NIK-based manual recovery.

## Proposed Changes

### 1. Authentication Controller

#### [MODIFY] [AuthController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/AuthController.php)
- Add `forgotPassword` view method.
- Add `forgotEmail` view method.
- Add `findEmail` logic (search by NIK and Phone Number).
- (Optional) Use Laravel's built-in password reset or a simplified custom version if email setup is pending.

### 2. Routes

#### [MODIFY] [web.php](file:///C:/laragon/www/app-portal-sekolah/routes/web.php)
- Add `/forgot-password` (GET/POST).
- Add `/forgot-email` (GET/POST).

### 3. Views

#### [NEW] [forgot-password.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/auth/forgot-password.blade.php)
- High-end mobile UI for email-based reset.

#### [NEW] [forgot-email.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/auth/forgot-email.blade.php)
- High-end mobile UI for recovery via Phone Number & NIK.

#### [MODIFY] [login.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/auth/login.blade.php)
- Add links to recovery pages.
- Add the mandatory NIK instruction: "Lupa semuanya? Berikan NIK ke Bagian Admin IT Sekolah untuk reset atau aktivasi."

## Verification Plan

### Manual Verification
1. Open login page, verify recovery links and NIK instructions are visible.
2. Test "Lupa Email": Input NIK and Phone, verify it displays the registered email.
3. Test "Lupa Password": Input email, verify it triggers the reset flow.
4. Verify the UI remains stable and high-end across all recovery steps.
