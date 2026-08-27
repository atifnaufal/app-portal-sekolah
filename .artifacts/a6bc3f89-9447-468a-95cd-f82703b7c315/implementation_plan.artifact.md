# Bug Fix: Attendance Null Error & Enhanced Face Verification (Vermuk)

This plan fixes the critical `null` object error on the mobile attendance page and upgrades the face verification system to be more robust and professional.

## User Review Required

> [!IMPORTANT]
> I am upgrading the face detection engine to a more stable version of the TensorFlow.js models. I am also fixing the Blade template to handle cases where no attendance record exists for the day (fixing the `null` error).

## Proposed Changes

### UI / Mobile Layer

#### [MODIFY] [absensi.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/absensi.blade.php)
- Fix the `Attempt to read property "waktu_masuk" on null` error by adding null-safe checks.
- Upgrade the face detection library and logic to a more reliable implementation.
- Improve the camera UI with clearer instructions and visual feedback.

### Backend Layer

#### [MODIFY] [AbsensiController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/AbsensiController.php)
- Ensure the `myAttendance` variable is always handled safely even if it's the first time the user opens the page.

## Verification Plan

### Automated Tests
- N/A

### Manual Verification
1. Log in as a Student who has NOT yet performed attendance.
2. Verify the page loads without the "Internal Server Error".
3. Click "Buka Kamera Vermuk" and verify the face detection is fast and accurate.
4. Complete attendance and verify the record is saved correctly.
