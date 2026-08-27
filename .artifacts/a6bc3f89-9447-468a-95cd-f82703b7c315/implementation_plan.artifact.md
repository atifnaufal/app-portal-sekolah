# Implementation Plan: Role-Based Registration & Verification Bypass

This plan implements separate registration controls for Guru and Siswa, allowing Admin to enable/disable them independently. Additionally, it ensures that Guru accounts bypass email verification, just like Admin accounts.

## Proposed Changes

### 1. Middleware: Verification Bypass

#### [MODIFY] [VerifiedExceptAdmin.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Middleware/VerifiedExceptAdmin.php)
- Update logic to bypass verification for both `admin` AND `guru` roles.

### 2. Admin: Granular Registration Control

#### [MODIFY] [AdminController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/AdminController.php)
- Update `settings` to include `registration_guru_enabled` and `registration_siswa_enabled`.
- Update `updateSettings` to handle these new granular toggles.
- Update `dashboard` and `users` view data to reflect new settings.

#### [MODIFY] [admin-settings.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/admin-settings.blade.php)
- Split the "Registrasi Mandiri" toggle into two separate switches: "Registrasi Guru" and "Registrasi Siswa".

### 3. Registration: Granular Logic

#### [MODIFY] [RegisterController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/RegisterController.php)
- Update `create` to check for specific role enablement.
- Update `store` to validate and enforce separate enablement flags.

#### [MODIFY] [register.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/auth/register.blade.php)
- Dynamic role selection: only show roles that are currently enabled by the Admin.
- If only one is enabled, hide the select box and force that role.
- If none are enabled, show a professional "Registration Closed" message.

## Verification Plan

### Manual Verification
1. **Admin Control**: Go to Admin Settings. Disable "Registrasi Guru" and keep "Siswa" enabled.
2. **Register**: Go to the Registration page. Verify only "Siswa" is available in the role selection.
3. **Guru Login**: Create/Log in as a Guru. Verify they are NOT prompted for email verification.
4. **Siswa Login**: Log in as a Siswa. Verify they ARE prompted for email verification.
5. **Security**: Try to register as a Guru via POST request even when disabled. Verify it returns a 404/Error.
