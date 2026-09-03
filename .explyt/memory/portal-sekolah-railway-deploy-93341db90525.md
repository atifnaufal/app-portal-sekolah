---
name: "portal-sekolah-railway-deploy"
description: UserContextHelper di EskulController admin CRUD utk API mobile; Reverb/Email/FCM gap konfigurasi Railway
type: project
lastUpdated: 2026-09-03T19:45
lastRecall: 2026-09-03T19:33
---

# app-portal-sekolah — deployment & 500 Railway

Laravel 13 / PHP 8.3 app sekolah, deploys di Railway, repo `atifnaufal/app-portal-sekolah`. Remote: origin main.

## Error 500 Railway (semua halaman) — root cause & fix
App LOKAL bekerja sempurna (SQLite & MySQL + production cache = 200). 500 semua halaman di Railway berasal dari konfigurasi deploy, bukan kode inti. Fix yang di-push (commit f8c25ca):
- Restart web server harus `bash start-web.sh` = `php artisan serve` di $PORT. JANGAN pakai `php-fpm -D ... nginx` — binary php-fpm/nginx tidak terpasang andal di image Railway ("php-fpm: command not found") → server gagal start → 500. nixpacks.toml [start] sekarang = start-web.sh (sama dengan Procfile). nginx.conf tidak terpakai lagi.
- `UpdateLastActivity` middleware (global di web group) memanggil cache() di luar try/catch → jika tabel cache/DB belum siap saat cold start, SEMUA request web 500. Fix: bungkus semua dalam try/catch.
- `NotificationHelper::send()` memanggil event() (broadcast Reverb) tanpa try/catch → creds/Reverb gagal = 500 padahal aksi sukses. Fix: bungkus dalam try/catch + log warning.
- `db:seed --force` dihapus dari start-web.sh (nixpacks deploy phase juga sudah menghapus) → cegah data seed ganda tiap cold start. Admin dibuat via migration (add_admin_user / ensure_admin_pusat).

## Auth: web session vs Sanctum API (mobile Capacitor)
Controller TIDAK boleh resolusi user hanya dari `session('user_id')` lalu `findOrFail`— 500 saat dipanggil via API Sanctum (Bearer token, tanpa sesi web). Gunakan helper baru `app/Helpers/UserContextHelper.php` (UserContextHelper::id/user/role/abortUnauthorized): prioritas $request->user() → session('user_id') → Auth::id(). Diterapkan di ~18 controller.

## ensure-env.sh — DATABASE_URL
Railway supplies DB via DATABASE_URL. Parser memakai `parse_url` PHP (bukan regex bash) karena password boleh berisi @, :, /. Meng-inject DB_USERNAME/PASSWORD/HOST/PORT/DATABASE ke .env. APP_KEY: diprioritaskan dari env Railway, else generate stabil.

## Test
Suite: 3 test pre-existing gagal (RegistrationTest ×2 + ExampleTest root redirect) — gagal juga di clean HEAD, TIDAK terkait fix ini (terkait APP_URL .env & setting registrasi). Ketika push via PowerShell `git push` menulis ke stderr → tampil sebagai "error" padahal sukses; cek baris "main -> main".

## EskulController admin CRUD — session-based auth (fix 2026-09-03)
6 method admin `EskulController` (adminIndex/store/update/destroy/toggle/setAdmin) memakai `abort_unless(session('user_role')==='admin')`. Route-nya ada juga di grup `api.role:admin,guru` (Bearer tanpa sesi) → mobile admin selalu dapat 403. Fix: ganti ke `abort_unless(UserContextHelper::role($request)==='admin')`; tambah param `Request $request` di adminIndex/destroy/toggle. Perlu `use App\Helpers\UserContextHelper;` (sudah ada). Juga bersihkan Log::info debug 'Export PDF/Excel Debug:' + variabel mati `$sessionUserId` di TugasController::exportPdf/exportExcel.
## Gap konfigurasi runtime (bukan kode) — perlu diisi di Railway
- Reverb: `REVERB_SERVER_PORT` di .env = 8080 bentrok dgn `php artisan serve` (web). Butuh service reverb terpisah port 6001 + VITE_REVERB_HOST=domain service reverb.
- Email Brevo: MAIL_USERNAME/PASSWORD masih placeholder → reset password & email tugas belum jalan sampai kredensial + sender terverifikasi diisi.
- FCM/Firebase: FIREBASE_ENABLED=false (fallback storage public aman); push notification mobile belum aktif.
