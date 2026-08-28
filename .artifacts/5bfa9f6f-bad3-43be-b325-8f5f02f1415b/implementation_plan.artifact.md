# Implementasi Fitur Perpustakaan Digital

Menambahkan modul Perpustakaan Digital ke dalam aplikasi Portal Sekolah untuk memudahkan siswa dan guru mengakses koleksi buku digital (PDF).

## User Review Required

> [!IMPORTANT]
> Fitur ini akan menambahkan 3 tabel baru ke database: `kategori_bukus`, `bukus`, dan `peminjaman_bukus`.

## Proposed Changes

### Database & Models

#### [NEW] [Migration: create_kategori_bukus_table](file:///C:/laragon/www/app-portal-sekolah/database/migrations/2026_08_28_000001_create_kategori_bukus_table.php)
#### [NEW] [Migration: create_bukus_table](file:///C:/laragon/www/app-portal-sekolah/database/migrations/2026_08_28_000002_create_bukus_table.php)
#### [NEW] [Migration: create_peminjaman_bukus_table](file:///C:/laragon/www/app-portal-sekolah/database/migrations/2026_08_28_000003_create_peminjaman_bukus_table.php)

#### [NEW] [KategoriBuku.php](file:///C:/laragon/www/app-portal-sekolah/app/Models/KategoriBuku.php)
#### [NEW] [Buku.php](file:///C:/laragon/www/app-portal-sekolah/app/Models/Buku.php)
#### [NEW] [PeminjamanBuku.php](file:///C:/laragon/www/app-portal-sekolah/app/Models/PeminjamanBuku.php)

---

### Backend Logic

#### [NEW] [PerpustakaanController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/PerpustakaanController.php)
Menangani tampilan katalog, detail buku, dan pembaca PDF untuk sisi mobile/siswa.

#### [NEW] [AdminPerpustakaanController.php](file:///C:/laragon/www/app-portal-sekolah/app/Http/Controllers/AdminPerpustakaanController.php)
Menangani CRUD buku dan kategori untuk sisi Admin.

---

### UI / Views (Mobile)

#### [NEW] [index.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/perpustakaan/index.blade.php)
Halaman katalog buku dengan pencarian dan kategori.

#### [NEW] [show.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/perpustakaan/show.blade.php)
Halaman detail buku dan tombol baca.

#### [NEW] [read.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/perpustakaan/read.blade.php)
Halaman pembaca PDF (menggunakan PDF.js atau iframe browser).

---

### Routing & Navigation

#### [MODIFY] [web.php](file:///C:/laragon/www/app-portal-sekolah/routes/web.php)
Menambahkan route untuk perpustakaan dan admin perpustakaan.

#### [MODIFY] [dashboard.blade.php](file:///C:/laragon/www/app-portal-sekolah/resources/views/mobile/dashboard.blade.php)
Menambahkan menu "Perpustakaan" pada dashboard mobile.

## Verification Plan

### Automated Tests
- Menjalankan `php artisan migrate` untuk memastikan skema database benar.

### Manual Verification
- Membuka halaman Perpustakaan di mobile dashboard.
- Mencoba mencari buku.
- Mencoba membuka detail buku dan membaca file PDF.
- (Admin) Menambah buku baru melalui panel admin.
