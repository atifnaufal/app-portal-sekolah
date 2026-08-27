# Implementation Plan: Full-Screen Overhaul & Notification Fix

This plan addresses the final UI refinements: full-screen focus modes for Chat and Tasks, and fixing notification icon rendering issues.

## Proposed Changes

### 1. Layout & Framework

#### [MODIFY] [mobile-app.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/layouts/mobile-app.blade.php)
- Add support for hiding the bottom navigation menu via a `$hideNav` variable.
- Integrate Bootstrap Icons globally.

### 2. Chat & Tasks Overhaul

#### [MODIFY] [chat.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/chat.blade.php)
- Rename to "Grup Chat".
- Hide bottom navigation.
- Add sticky top header with "Kembali" button.
- Implement an interactive Emoji and GIF picker UI.

#### [MODIFY] [tugas.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/tugas.blade.php)
- Hide bottom navigation for full-screen focus.
- Add sticky top header with "Kembali" button.

### 3. Notification Cleanup

#### [MODIFY] [notifications.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/notifications.blade.php)
- Fix broken entity rendering (remove `&#9993;` and `&#8364;`).
- Replace with professional Bootstrap Icons.
- Simplify the layout to focus on text as requested.

## Verification Plan

### Manual Verification
1. Open **Grup Chat** and verify the bottom menu is gone and the top header is visible.
2. Test the **Emoji Picker** and verify emojis are added to the text input.
3. Open **Tugas** and verify it is full screen.
4. Open **Notifikasi** and verify all icons render correctly without broken text codes.
