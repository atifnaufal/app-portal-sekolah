# Walkthrough - Full Screen UI & Advanced Chat

I have upgraded the application to include full-screen focus modes for Chat and Tasks, and fixed notification rendering issues.

## Changes Made

### 1. Advanced "Grup Chat" (Full Screen)
- **WhatsApp Style Header**: Added a sticky top header with a "Kembali" button, class name, and active status.
- **Hidden Navigation**: The floating bottom menu is now hidden on the chat page to provide a full-screen immersive experience.
- **Interactive Emoji Picker**: Integrated a custom emoji panel with a wide selection of school-friendly emojis.
- **GIF Integration (Mock)**: Added a GIF tab with simulated results that can be sent in messages.
- **Refined Bubbles**: Updated chat bubbles with distinct colors for "Mine" and "Other" participants.

### 2. Immersive "Ruang Tugas" (Full Screen)
- **Full Screen Focus**: Like chat, the Tasks page now hides the bottom navigation to minimize distractions.
- **Header Integration**: Added a clean top header with a quick back button to the dashboard.
- **Task Card Polish**: Improved the deadline visibility and status badges (Selesai/Belum Dikumpul).

### 3. Notification Rendering Fix
- **Entity Repair**: Fixed the issue where icon codes like `&#9993;` were showing as raw text.
- **Icon Upgrade**: Replaced all HTML entities with modern **Bootstrap Icons** (`bi-chat-left-text`, `bi-wallet2`).
- **Clean UI**: Removed distracting elements and focused on text readability as requested.

### 4. Framework Enhancements
- **Dynamic Layout**: The `mobile-app` layout now supports a `hideNav` flag for granular control over navigation visibility.
- **Icon Library**: Globally integrated Bootstrap Icons (v1.11.1) for consistent high-quality visuals.

## Verification Results
- **Chat Persistence**: The floating menu correctly disappears on the chat page and reappears on the dashboard.
- **Emoji/GIF Interaction**: The panels open/close smoothly and append content to the input field correctly.
- **Notification Clarity**: Verified that icons now render perfectly as intended.
