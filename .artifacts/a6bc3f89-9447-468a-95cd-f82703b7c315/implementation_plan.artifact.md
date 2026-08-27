# Persistent Login Session Implementation

The goal is to ensure that students and teachers stay logged in even after closing the application. The session should only end when the user explicitly logs out from their profile settings.

## Proposed Changes

### 1. Authentication Controller

#### [MODIFY] [AuthController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/AuthController.php)
- Update the `login` method to use `Auth::login($user, true)`. The `true` parameter enables the "Remember Me" functionality, which uses a persistent cookie.
- Update the `logout` method to call `Auth::logout()` alongside session invalidation to ensure the "Remember Me" token is also cleared.

### 2. Role Middleware

#### [MODIFY] [RoleMiddleware.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Middleware/RoleMiddleware.php)
- Add logic to automatically re-populate custom session keys (`user_id`, `user_role`, etc.) if the user is authenticated via the "Remember Me" cookie but the session has expired. This ensures compatibility with the existing code that relies on these session keys.

### 3. Environment Configuration

#### [MODIFY] [.env](file:///C:/laragon/www/app-portal-sekolah/.env)
- Increase `SESSION_LIFETIME` to a much larger value (e.g., 30 days) to provide a better user experience even without the "Remember Me" token.

## Verification Plan

### Manual Verification
- Log in as a student or teacher.
- Close the application (simulate app kill).
- Re-open the application and verify it goes directly to the dashboard without requiring a new login.
- Go to the profile settings and click logout.
- Verify that the user is redirected to the login screen and subsequent app openings require login.
