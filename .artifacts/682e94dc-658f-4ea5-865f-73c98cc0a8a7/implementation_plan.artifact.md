# Stabilisasi Proyek dan Integrasi Android (Capacitor)

Proyek ini adalah aplikasi berbasis Laravel yang akan dikonversi agar dapat berjalan di Android menggunakan **Capacitor**. Selain itu, terminal akan dikonfigurasi agar memiliki akses penuh ke tools yang diperlukan.

## User Review Required
> [!IMPORTANT]
> - Saya telah mengubah **PowerShell Execution Policy** menjadi `RemoteSigned` untuk mengizinkan eksekusi script di terminal.
> - Tools (PHP, Composer, Node.js) akan diakses langsung dari folder instalasi Laragon karena belum terdaftar di System PATH.

## Proposed Changes

### [Terminal & Environment]
- Mengatur Environment Path untuk sesi ini agar mengenali `php`, `composer`, `npm`, dan `adb`.

### [Laravel Stability]
- Menginstal dependensi PHP (`composer install`).
- Menginstal dependensi JavaScript (`npm install`).
- Menyiapkan database SQLite (`database/database.sqlite`).
- Menjalankan migrasi database.

### [Android Integration (Capacitor)]
- Menginstal `@capacitor/core`, `@capacitor/cli`, dan `@capacitor/android`.
- Inisialisasi Capacitor dengan nama aplikasi "App Portal Sekolah".
- Membangun aset web (`npm run build`).
- Menambahkan platform Android (`npx cap add android`).

## Verification Plan

### Automated Steps
- Menjalankan `php artisan migrate` untuk memastikan database siap.
- Menjalankan `npx cap sync` untuk sinkronisasi kode ke folder Android.

### Manual Verification
- Membuka folder `android` di Android Studio untuk build APK.
- Mencoba menjalankan di Emulator menggunakan `npx cap run android`.
