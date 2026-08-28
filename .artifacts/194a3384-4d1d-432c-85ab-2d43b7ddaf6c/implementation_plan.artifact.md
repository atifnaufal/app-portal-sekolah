# Mobile UI Refinement and Feature Implementation Plan

Refine the "overdone" mobile UI for Library and Assignments to be more professional and compact. Implement the foundation for new modules: Tasks (Tugas), Grades (Nilai), and Schedules (Jadwal).

## User Review Required

> [!IMPORTANT]
> The "Jadwal" and "Nilai" modules will initially be launched with "Coming Soon" indicators on the dashboard while the backend controllers are being finalized.

## Proposed Changes

### Library (Perpustakaan) Module
Refine the mobile interface to reduce clutter and improve readability.

#### [MODIFY] [perpustakaan/index.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/perpustakaan/index.blade.php)
- Compact hero section with reduced padding.
- Simplified background gradients.
- Compact search bar.

#### [MODIFY] [perpustakaan/show.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/perpustakaan/show.blade.php)
- Fixed hero depth and book cover scaling.
- Optimized metadata card layout.

---

### Assignments (Tugas) Module
Modernize the UI to match the "Premium Dark" theme of the school portal.

#### [MODIFY] [tugas.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/tugas.blade.php)
- Updated hero section with dark gradients and high-contrast typography.
- Refined status chips and stat counters.

#### [MODIFY] [tugas-detail.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/tugas-detail.blade.php)
- Premium hero header for task details.
- Compact metadata display.

---

### New Modules: Foundation (Database)
Implement models and migrations for Schedules and Grades.

#### [NEW] app/Models/MataPelajaran.php
#### [NEW] app/Models/Nilai.php
#### [NEW] app/Models/Jadwal.php
#### [NEW] database/migrations/2026_08_31_000001_create_mata_pelajaran_table.php
#### [NEW] database/migrations/2026_08_31_000002_create_nilais_table.php
#### [NEW] database/migrations/2026_08_31_000003_create_jadwals_table.php

---

### Dashboard Integration
Wire up the new modules to the mobile dashboard.

#### [MODIFY] [dashboard.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/dashboard.blade.php)
- Added "Jadwal" and "Nilai" icons to the menu grid.
- Implemented temporary placeholders for new features.

## Verification Plan

### Automated Tests
- Run `php artisan migrate` to verify schema integrity.
- Manual check of UI responsiveness on mobile viewport.

### Manual Verification
- Verify Library hero height across different screen sizes.
- Ensure Assignment status chips are correctly colored based on status.
- Confirm dashboard icons link correctly (or show "Coming Soon").
