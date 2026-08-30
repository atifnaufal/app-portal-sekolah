<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminPerpustakaanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EskulController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PerpustakaanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SppController;
use App\Http\Controllers\TugasController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/download/apk', function () {
    $apk = public_path('downloads/app-portal-sekolah1.apk');

    if (is_file($apk)) {
        return response()->download($apk, 'app-portal-sekolah1.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }

    $url = env('APK_DOWNLOAD_URL');
    abort_unless($url, 404);

    return redirect($url);
})->name('download.apk');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');
Route::get('/forgot-email', [AuthController::class, 'showForgotEmail'])->name('email.request');
Route::post('/forgot-email', [AuthController::class, 'findEmail'])->middleware('throttle:6,1')->name('email.find');

Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Gerbang akses kini kolom `aktif` yang diset admin, diperiksa saat login.
// Verifikasi email sudah dihapus sehingga tidak ada middleware 'verified'.
Route::middleware('role:admin,guru,siswa')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
    Route::get('/spp', [SppController::class, 'index'])->name('spp.index');
    Route::get('/perpustakaan', [PerpustakaanController::class, 'index'])->name('perpustakaan.index');
    Route::get('/perpustakaan/{buku:slug}', [PerpustakaanController::class, 'show'])->name('perpustakaan.show');
    Route::get('/perpustakaan/{buku:slug}/read', [PerpustakaanController::class, 'read'])->name('perpustakaan.read');

    // Eskul
    Route::get('/eskul', [EskulController::class, 'index'])->name('eskul.index');
    Route::post('/eskul/{eskul}/join', [EskulController::class, 'join'])->name('eskul.join');
    Route::get('/eskul/{eskul}/members', [EskulController::class, 'members'])->name('eskul.members');
    Route::post('/eskul/members/{member}/approve', [EskulController::class, 'approveMember'])->name('eskul.members.approve');
    Route::post('/eskul/members/{member}/reject', [EskulController::class, 'rejectMember'])->name('eskul.members.reject');

    // Nilai & Jadwal
    Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai.index');
    Route::post('/nilai/upsert', [NilaiController::class, 'upsert'])->name('nilai.upsert');
    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');

    // LMS - Mata Pelajaran
    Route::get('/mapel/{mapel}', [MapelController::class, 'show'])->name('mapel.show');

    // LMS - Materi (semua role dapat melihat detail materi)
    Route::get('/mapel/{mapel}/materi/{materi}', [MateriController::class, 'show'])->name('materi.show');
});

// Unduhan rekap (nilai & absensi) hanya untuk staf (admin/guru) — prevent siswa 403.
Route::middleware('role:admin,guru')->group(function () {
    Route::get('/nilai/recap/{kelas}', [NilaiController::class, 'recapPdf'])->name('nilai.recap');
    Route::get('/nilai/recap/{kelas}/excel', [NilaiController::class, 'recapExcel'])->name('nilai.recap.excel');
    Route::get('/nilai/recap-mapel/{mapel}', [NilaiController::class, 'recapMapelPdf'])->name('nilai.recap.mapel');
    Route::get('/nilai/recap-mapel/{mapel}/excel', [NilaiController::class, 'recapMapelExcel'])->name('nilai.recap.mapel.excel');
    Route::get('/nilai/recap-periode', [NilaiController::class, 'recapPeriodePdf'])->name('nilai.recap.periode');
    Route::get('/nilai/recap-periode/excel', [NilaiController::class, 'recapPeriodeExcel'])->name('nilai.recap.periode.excel');
    Route::get('/absensi/recap', [AbsensiController::class, 'recapPdf'])->name('absensi.recap');
    Route::get('/absensi/recap/excel', [AbsensiController::class, 'recapExcel'])->name('absensi.recap.excel');
});

Route::middleware('role:guru,siswa')->group(function () {
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/absensi', [AbsensiController::class, 'store'])->name('absensi.store');
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profil/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profil/foto', [ProfileController::class, 'uploadFoto'])->name('profile.foto.upload');
    Route::patch('/profil/foto-posisi', [ProfileController::class, 'updateFotoPosisi'])->name('profile.foto.posisi');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');
    Route::get('/chat/poll', [ChatController::class, 'poll'])->name('chat.poll');
    Route::get('/chat/private/{recipient}', [ChatController::class, 'startPrivate'])->name('chat.startPrivate');
    Route::get('/chat/{group}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('/notifikasi-saya', [NotifikasiController::class, 'mine'])->name('notifications.index');
    Route::get('/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::get('/tugas', [TugasController::class, 'index'])->name('tugas.index');
    Route::get('/tugas/{tugas}', [TugasController::class, 'show'])->whereNumber('tugas')->name('tugas.show');

    // Help & FAQ (Premium Features)
    Route::get('/help/faq', [HelpController::class, 'faq'])->name('help.faq');
    Route::get('/about', [HelpController::class, 'about'])->name('about.show');
    Route::get('/legal/privacy', function() {
        return view('mobile.legal', [
            'title' => 'Kebijakan Privasi',
            'content' => 'Kami berkomitmen untuk melindungi data pribadi Anda. Data yang dikumpulkan hanya digunakan untuk keperluan akademik dan administrasi sekolah. Kami tidak membagikan data Anda kepada pihak ketiga tanpa izin Anda.'
        ]);
    })->name('legal.privacy');
    Route::get('/legal/terms', function() {
        return view('mobile.legal', [
            'title' => 'Syarat & Ketentuan',
            'content' => 'Dengan menggunakan aplikasi ini, Anda setuju untuk mematuhi semua peraturan sekolah yang berlaku. Segala bentuk penyalahgunaan akun akan ditindak tegas sesuai hukum yang berlaku di Indonesia.'
        ]);
    })->name('legal.terms');
    Route::get('/security/settings', [HelpController::class, 'security'])->name('security.settings');
    Route::get('/settings/notifications', [HelpController::class, 'notificationSettings'])->name('settings.notifications');
});

// Endpoint JSON realtime (heartbeat sesi & polling notifikasi). Sengaja DI LUAR
// middleware role: harus mengembalikan JSON (bukan redirect 302) saat sesi mati
// supaya client aplikasi bisa mendeteksi expiry & diarahkan ke login realtime.
// Aman: controller memeriksa `session('user_id')` & mengembalikan authenticated:false.
Route::get('/notifikasi/poll', [NotifikasiController::class, 'poll'])->name('notifications.poll');
Route::get('/session/status', [SessionController::class, 'status'])->name('session.status');

// Admin, Wali Kelas, dan Pembina Eskul dapat membuat/kelola pengumuman (termasuk privat per-siswa).
Route::middleware('role:admin,guru')->group(function () {
    Route::resource('pengumuman', PengumumanController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
});

// Menu admin berat khusus desktop.
Route::middleware(['role:admin', 'admin.desktop'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::put('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.user.update');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.user.destroy');
    Route::patch('/admin/users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('admin.user.toggle');
    Route::patch('/admin/registration/toggle', [AdminController::class, 'toggleRegistration'])->name('admin.registration.toggle');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');

    Route::get('/admin/perpustakaan', [AdminPerpustakaanController::class, 'index'])->name('admin.perpustakaan.index');
    Route::get('/admin/perpustakaan/create', [AdminPerpustakaanController::class, 'create'])->name('admin.perpustakaan.create');
    Route::post('/admin/perpustakaan', [AdminPerpustakaanController::class, 'store'])->name('admin.perpustakaan.store');
    Route::get('/admin/perpustakaan/{buku}/edit', [AdminPerpustakaanController::class, 'edit'])->name('admin.perpustakaan.edit');
    Route::put('/admin/perpustakaan/{buku}', [AdminPerpustakaanController::class, 'update'])->name('admin.perpustakaan.update');
    Route::delete('/admin/perpustakaan/{buku}', [AdminPerpustakaanController::class, 'destroy'])->name('admin.perpustakaan.destroy');
    Route::get('/admin/perpustakaan/kategori', [AdminPerpustakaanController::class, 'kategoriIndex'])->name('admin.perpustakaan.kategori.index');
    Route::post('/admin/perpustakaan/kategori', [AdminPerpustakaanController::class, 'kategoriStore'])->name('admin.perpustakaan.kategori.store');
    Route::put('/admin/perpustakaan/kategori/{kategori}', [AdminPerpustakaanController::class, 'kategoriUpdate'])->name('admin.perpustakaan.kategori.update');
    Route::delete('/admin/perpustakaan/kategori/{kategori}', [AdminPerpustakaanController::class, 'kategoriDestroy'])->name('admin.perpustakaan.kategori.destroy');

    // Admin Eskul
    Route::get('/admin/eskul', [EskulController::class, 'adminIndex'])->name('admin.eskul.index');
    Route::post('/admin/eskul', [EskulController::class, 'store'])->name('admin.eskul.store');
    Route::put('/admin/eskul/{eskul}', [EskulController::class, 'update'])->name('admin.eskul.update');
    Route::delete('/admin/eskul/{eskul}', [EskulController::class, 'destroy'])->name('admin.eskul.destroy');
    Route::patch('/admin/eskul/{eskul}/toggle', [EskulController::class, 'toggle'])->name('admin.eskul.toggle');
    Route::post('/admin/eskul/{eskul}/set-admin', [EskulController::class, 'setAdmin'])->name('admin.eskul.set-admin');

    Route::resource('jurusan', JurusanController::class)->except('show');
    Route::resource('kelas', KelasController::class)->except('show');
    Route::resource('mahasiswa', MahasiswaController::class)->except(['index', 'show']);
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');

    // Admin Jadwal (LMS)
    Route::get('/admin/jadwal', [JadwalController::class, 'adminIndex'])->name('admin.jadwal.index');
    Route::post('/admin/jadwal', [JadwalController::class, 'store'])->name('admin.jadwal.store');
    Route::put('/admin/jadwal/{jadwal}', [JadwalController::class, 'update'])->name('admin.jadwal.update');
    Route::delete('/admin/jadwal/{jadwal}', [JadwalController::class, 'destroy'])->name('admin.jadwal.destroy');
});

Route::middleware('role:guru')->group(function () {
    Route::get('/tugas/create', [TugasController::class, 'create'])->name('tugas.create');
    Route::post('/tugas', [TugasController::class, 'store'])->name('tugas.store');
    Route::get('/tugas/{tugas}/edit', [TugasController::class, 'edit'])->name('tugas.edit');
    Route::put('/tugas/{tugas}', [TugasController::class, 'update'])->name('tugas.update');
    Route::delete('/tugas/{tugas}', [TugasController::class, 'destroy'])->name('tugas.destroy');
    Route::get('/tugas/{tugas}/export/pdf', [TugasController::class, 'exportPdf'])->name('tugas.export.pdf');
    Route::get('/tugas/{tugas}/export/excel', [TugasController::class, 'exportExcel'])->name('tugas.export.excel');
    Route::post('/pengumpulan/{pengumpulan}/review', [TugasController::class, 'review'])->name('tugas.review');
    Route::get('/tugas-notifikasi', [NotifikasiController::class, 'tugas'])->name('tugas.notifikasi');
    Route::post('/spp/{spp}/remind', [SppController::class, 'remind'])->name('spp.remind');

    // LMS - Materi CRUD (hanya guru)
    Route::get('/mapel/{mapel}/materi/create', [MateriController::class, 'create'])->name('materi.create');
    Route::post('/mapel/{mapel}/materi', [MateriController::class, 'store'])->name('materi.store');
    Route::get('/mapel/{mapel}/materi/{materi}/edit', [MateriController::class, 'edit'])->name('materi.edit');
    Route::put('/mapel/{mapel}/materi/{materi}', [MateriController::class, 'update'])->name('materi.update');
    Route::delete('/mapel/{mapel}/materi/{materi}', [MateriController::class, 'destroy'])->name('materi.destroy');
});

Route::middleware('role:admin,guru')->group(function () {
    Route::get('/spp/create', [SppController::class, 'create'])->name('spp.create');
    Route::post('/spp', [SppController::class, 'store'])->name('spp.store');
});

Route::middleware('role:admin')->group(function () {
    Route::get('/spp/{spp}/edit', [SppController::class, 'edit'])->name('spp.edit');
    Route::put('/spp/{spp}', [SppController::class, 'update'])->name('spp.update');
    Route::delete('/spp/{spp}', [SppController::class, 'destroy'])->name('spp.destroy');
});

Route::middleware('role:siswa')->group(function () {
    Route::post('/tugas/{tugas}/submit', [TugasController::class, 'submit'])->name('tugas.submit');
});
