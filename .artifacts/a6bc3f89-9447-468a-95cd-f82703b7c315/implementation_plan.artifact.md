# Persistent Login Sessions (Guru & Siswa)

This plan implements a truly persistent login experience for Teachers (Guru) and Students (Siswa), while maintaining standard session security for Administrators.

## User Review Required

> [!IMPORTANT]
> The "Remember Me" functionality will be automatically enabled for Teachers and Students to prevent forced logouts when the app is closed. For Administrators, the session will remain standard (requires re-login after session expiry/app close) as requested.

## Proposed Changes

### Authentication & Middleware Layer

#### [MODIFY] [AuthController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/AuthController.php)
- Update `login` method to automatically set the `remember` flag to `true` for Guru and Siswa roles.
- Set `remember` to `false` for the Admin role to ensure standard session behavior.

#### [MODIFY] [RoleMiddleware.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Middleware/RoleMiddleware.php)
- Refine the session restoration logic to be more aggressive and reliable.
- Ensure that if a user is authenticated via a "Remember Me" cookie, their session keys (`user_id`, `role`, etc.) are immediately repopulated.

## Verification Plan

### Manual Verification
1. Log in as a **Student** or **Teacher**.
2. Close the application (swipe away from recent apps/task switcher).
3. Re-open the application.
4. Verify that you are **NOT** redirected to the login page and can see the dashboard immediately.
5. Log in as an **Admin**.
6. Close the application and re-open.
7. Verify that for Admin, it follows standard behavior (should require login if the session cookie expired).
