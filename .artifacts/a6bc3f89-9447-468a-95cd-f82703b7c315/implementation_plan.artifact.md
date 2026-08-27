# Implementation Plan: Dashboard & UI Polish

This plan addresses the final dashboard refinements: fixing inappropriate text labels and upgrading the visual appeal of the main activity widgets.

## Proposed Changes

### 1. Dashboard Enhancements

#### [MODIFY] [dashboard.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/dashboard.blade.php)
- **Header Text**: Change "SISWA SPACE" to "PORTAL AKADEMIK" for a more professional tone.
- **Widget Design**: Redesign "Tugas Aktif" and "SPP" cards into high-end "Widgets" with:
    - White backgrounds and subtle shadows.
    - Linear gradient icon backgrounds (Blue for Tugas, Gold for SPP).
    - Modern Bootstrap Icons.
    - Improved typography and spacing.
- **Visual Depth**: Increase hero border-radius and add backdrop-blur effects to UI elements.
- **Announcement Polish**: Improve the "Pengumuman" card layout with better image ratios and cleaner badges.

## Verification Plan

### Manual Verification
1. Open the dashboard and verify the header says **PORTAL AKADEMIK**.
2. Verify the **Tugas** and **SPP** widgets are visually distinct and aesthetically pleasing.
3. Check the **Pengumuman** cards for proper image scaling and text readability.
4. Verify the dashboard margins and spacing are consistent with the new floating navigation.
