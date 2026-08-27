# Implementation Plan: Comprehensive App-like Notifications

This plan aims to make the notification system more comprehensive and behave like a modern application by triggering notifications for all relevant events (Tasks, SPP, Announcements, Profile updates) and preparing for real-time/push capabilities.

## User Review Required

> [!NOTE]
> I will be adding a `NotificationHelper` to centralize notification logic. This will allow for consistent behavior across Database, WebSockets, and future Push Notification (FCM) integration.

## Proposed Changes

### Core Logic

#### [NEW] [NotificationHelper.php](file:///C:/laragon/www/app-portal-sekolah/app/Helpers/NotificationHelper.php)
- Create a helper class with static methods to send notifications.
- `send($userId, $title, $message, $url = null, $type = 'general')`
- `sendToClass($kelasId, $title, $message, $url = null, $type = 'general')`
- `sendToAll($title, $message, $url = null, $type = 'general')`

---

### Integration into Controllers

#### [MODIFY] [TugasController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/TugasController.php)
- Update `store()`: Notify all students in the class when a new task is created.
- Update `submit()` and `review()`: Use the new `NotificationHelper`.

#### [MODIFY] [PengumumanController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/PengumumanController.php)
- Update `store()`: Notify all users when a new announcement is created.

#### [MODIFY] [SppController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/SppController.php)
- Update `store()`: Notify the student when a new SPP bill is created.
- Update `remind()`: Use the new `NotificationHelper`.

#### [MODIFY] [ProfileController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/ProfileController.php)
- Update `update()`: Notify the user that their profile has been updated.

---

### UI / Mobile Integration

#### [MODIFY] [mobile-page.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/layouts/mobile-page.blade.php) (if exists) or equivalent
- Ensure the notification bell icon correctly reflects the number of unread notifications.

## Verification Plan

### Automated Tests
- N/A

### Manual Verification
1. Log in as Guru, create a Task, and verify all Students in that class receive a notification.
2. Log in as Admin, create an Announcement, and verify all users receive a notification.
3. Log in as Student, update Profile, and verify a "Profile updated" notification appears.
4. Verify SPP creation/reminders trigger notifications.
