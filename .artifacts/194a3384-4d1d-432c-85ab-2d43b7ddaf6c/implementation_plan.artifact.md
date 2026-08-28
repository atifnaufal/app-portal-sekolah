# Implementation Plan - Fix Eskul Chat Images & Simplify Library UI

This plan addresses the issue where images cannot be sent in the Eskul chat and simplifies the Library mobile UI which was reported as "overdone and overlapping".

## Proposed Changes

### 1. Chat Module (Eskul & Groups)

#### [NEW] [2026_08_30_000001_add_file_to_chat_messages_table.php](file:///C:/laragon/www/app-portal-sekolah/database/migrations/2026_08_30_000001_add_file_to_chat_messages_table.php)
- Add `file` string column (nullable) to `chat_messages` table.

#### [MODIFY] [ChatMessage.php](file:///C:/laragon/www/app-portal-sekolah/app/Models/ChatMessage.php)
- Update `$fillable` to include `chat_group_id` and `file`.
- Add `chatGroup` relationship.

#### [MODIFY] [ChatController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/ChatController.php)
- Update `store` method to handle file uploads (images).
- Validate optional `file` input.
- Return `file_url` in JSON response.

#### [MODIFY] [ChatMessageEvent.php](file:///C:/laragon/www/app-portal-sekolah/app/Events/ChatMessageEvent.php)
- Include `file_url` in `broadcastWith`.

#### [MODIFY] [chat.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/chat.blade.php)
- Add hidden file input and trigger it via the "+" button.
- Update UI to display images in chat bubbles.
- Update `appendMessage` JS function to handle images.

### 2. Library Module UI

#### [MODIFY] [index.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/perpustakaan/index.blade.php)
- Reduce hero height and simplify background.
- Fix overlapping categories by removing negative margins or adjusting layout.
- Clean up card styling to be less "busy".

#### [MODIFY] [show.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/perpustakaan/show.blade.php)
- Adjust meta cards layout to prevent awkward overlapping on small screens.
- Simplify gradients and shadow effects.

## Verification Plan

### Automated Tests
- N/A (Manual UI and Functionality check preferred for these changes).

### Manual Verification
1.  **Chat Image:**
    - Open a group chat (Eskul).
    - Click the "+" button, select an image.
    - Send and verify it appears in the chat bubble for both sender and receiver (real-time).
2.  **Library UI:**
    - Open library catalog on mobile.
    - Check for overlapping elements (categories vs hero).
    - Open book detail and check for clarity.
