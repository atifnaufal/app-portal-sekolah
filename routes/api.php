<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ===== DIAGNOSTIK DEPLOY (sementara) =====
// Membantu melihat kenapa halaman 500 saat deploy Railway. TANPA middleware 'web'
// sehingga tidak bergantung pada tabel sessions (yang justru bisa jadi penyebab 500).
// Hanya aktif kala APP_DEBUG=true. HAPUS endpoint ini setelah masalah teratasi.
Route::get('/__diag', function () {
    if ((bool) config('app.debug') !== true) {
        return response()->json(['enabled' => false, 'message' => 'Set APP_DEBUG=true di Railway lalu muat ulang.'], 404);
    }

    $c = config('database.default');
    $hidden = function ($v) {
        if ($v === null || $v === '') {
            return '(kosong/kehampa)';
        }
        return (string) $v;
    };

    $cfg = config("database.connections.$c", []);
    $data = [
        'app_env'         => config('app.env'),
        'app_debug'       => var_export(config('app.debug'), true),
        'db_connection'   => $c,
        'db_host'         => $hidden($cfg['host'] ?? ($c === 'sqlite' ? '(sqlite)' : '')),
        'db_port'         => $cfg['port'] ?? '',
        'db_database'     => $hidden($cfg['database'] ?? ''),
        'has_mysql_host_literal' => ($cfg['host'] ?? null) === '{{MYSQLHOST}}',
        'has_databurl'    => env('DATABASE_URL') ? 'yes' : 'no',
    ];

    // Koneksi DB
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $data['db_connect'] = 'OK';
    } catch (\Throwable $e) {
        $data['db_connect'] = 'FAIL: '.$e->getMessage();
    }

    // Cek keberadaan tabel inti
    foreach (['users', 'sessions', 'cache', 'jobs', 'migrations'] as $t) {
        try {
            $data['table_'.$t] = \Illuminate\Support\Facades\Schema::hasTable($t) ? 'ok' : 'MISSING';
        } catch (\Throwable $e) {
            $data['table_'.$t] = 'ERROR: '.$e->getMessage();
        }
    }

    return response()->json($data, 200);
})->name('api.__diag');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [\App\Http\Controllers\Api\AuthApiController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Api\AuthApiController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [\App\Http\Controllers\Api\AuthApiController::class, 'me'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'api.role:admin,guru,siswa'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);
    Route::get('/pengumuman', [\App\Http\Controllers\PengumumanController::class, 'index']);
    Route::get('/spp', [\App\Http\Controllers\SppController::class, 'index']);
    Route::get('/perpustakaan', [\App\Http\Controllers\PerpustakaanController::class, 'index']);
    Route::get('/perpustakaan/{buku:slug}', [\App\Http\Controllers\PerpustakaanController::class, 'show']);
    Route::get('/perpustakaan/{buku:slug}/read', [\App\Http\Controllers\PerpustakaanController::class, 'read']);

    Route::get('/eskul', [\App\Http\Controllers\EskulController::class, 'index']);
    Route::post('/eskul/{eskul}/join', [\App\Http\Controllers\EskulController::class, 'join']);
    Route::get('/eskul/{eskul}/members', [\App\Http\Controllers\EskulController::class, 'members']);
    Route::post('/eskul/members/{member}/approve', [\App\Http\Controllers\EskulController::class, 'approveMember']);
    Route::post('/eskul/members/{member}/reject', [\App\Http\Controllers\EskulController::class, 'rejectMember']);

    Route::get('/nilai', [\App\Http\Controllers\NilaiController::class, 'index']);
    Route::post('/nilai/upsert', [\App\Http\Controllers\NilaiController::class, 'upsert']);
    Route::get('/jadwal', [\App\Http\Controllers\JadwalController::class, 'index']);

    Route::get('/mapel/{mapel}', [\App\Http\Controllers\MapelController::class, 'show']);
    Route::get('/mapel/{mapel}/materi/{materi}', [\App\Http\Controllers\MateriController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'api.role:admin,guru'])->group(function () {
    Route::get('/nilai/recap/{kelas}', [\App\Http\Controllers\NilaiController::class, 'recapPdf']);
    Route::get('/nilai/recap/{kelas}/excel', [\App\Http\Controllers\NilaiController::class, 'recapExcel']);
    Route::get('/nilai/recap-mapel/{mapel}', [\App\Http\Controllers\NilaiController::class, 'recapMapelPdf']);
    Route::get('/nilai/recap-mapel/{mapel}/excel', [\App\Http\Controllers\NilaiController::class, 'recapMapelExcel']);
    Route::get('/nilai/recap-periode', [\App\Http\Controllers\NilaiController::class, 'recapPeriodePdf']);
    Route::get('/nilai/recap-periode/excel', [\App\Http\Controllers\NilaiController::class, 'recapPeriodeExcel']);
    Route::get('/absensi/recap', [\App\Http\Controllers\AbsensiController::class, 'recapPdf']);
    Route::get('/absensi/recap/excel', [\App\Http\Controllers\AbsensiController::class, 'recapExcel']);
    Route::get('/absensi', [\App\Http\Controllers\AbsensiController::class, 'index']);
    Route::post('/absensi', [\App\Http\Controllers\AbsensiController::class, 'store']);
    Route::get('/profil', [\App\Http\Controllers\ProfileController::class, 'show']);
    Route::get('/profil/edit', [\App\Http\Controllers\ProfileController::class, 'edit']);
    Route::put('/profil', [\App\Http\Controllers\ProfileController::class, 'update']);
    Route::post('/profil/foto', [\App\Http\Controllers\ProfileController::class, 'uploadFoto']);
    Route::patch('/profil/foto-posisi', [\App\Http\Controllers\ProfileController::class, 'updateFotoPosisi']);
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index']);
    Route::post('/chat', [\App\Http\Controllers\ChatController::class, 'store']);
    Route::get('/chat/create', [\App\Http\Controllers\ChatController::class, 'create']);
    Route::post('/chat/create', [\App\Http\Controllers\ChatController::class, 'storeGroup']);
    Route::post('/chat/{group}/invite', [\App\Http\Controllers\ChatController::class, 'invite']);
    Route::post('/chat/{group}/accept', [\App\Http\Controllers\ChatController::class, 'acceptInvite']);
    Route::post('/chat/{group}/reject', [\App\Http\Controllers\ChatController::class, 'rejectInvite']);
    Route::post('/chat/{group}/leave', [\App\Http\Controllers\ChatController::class, 'leave']);
    Route::put('/pesan/{message}', [\App\Http\Controllers\ChatController::class, 'updateMessage']);
    Route::delete('/pesan/{message}', [\App\Http\Controllers\ChatController::class, 'destroyMessage']);
    Route::get('/chat/poll', [\App\Http\Controllers\ChatController::class, 'poll']);
    Route::get('/chat/private/{recipient}', [\App\Http\Controllers\ChatController::class, 'startPrivate']);
    Route::get('/chat/{group}', [\App\Http\Controllers\ChatController::class, 'show']);
    Route::get('/notifikasi-saya', [\App\Http\Controllers\NotifikasiController::class, 'mine']);
    Route::get('/mahasiswa', [\App\Http\Controllers\MahasiswaController::class, 'index']);
    Route::get('/tugas', [\App\Http\Controllers\TugasController::class, 'index']);
    Route::get('/tugas/{tugas}', [\App\Http\Controllers\TugasController::class, 'show']);
    Route::get('/notifikasi', [\App\Http\Controllers\NotifikasiController::class, 'index']);
    Route::get('/admin/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard']);
    Route::get('/admin/users', [\App\Http\Controllers\AdminController::class, 'users']);
    Route::put('/admin/users/{user}', [\App\Http\Controllers\AdminController::class, 'updateUser']);
    Route::delete('/admin/users/{user}', [\App\Http\Controllers\AdminController::class, 'destroyUser']);
    Route::patch('/admin/users/{user}/toggle', [\App\Http\Controllers\AdminController::class, 'toggleUser']);
    Route::patch('/admin/registration/toggle', [\App\Http\Controllers\AdminController::class, 'toggleRegistration']);
    Route::get('/admin/settings', [\App\Http\Controllers\AdminController::class, 'settings']);
    Route::post('/admin/settings', [\App\Http\Controllers\AdminController::class, 'updateSettings']);
    Route::get('/admin/perpustakaan', [\App\Http\Controllers\AdminPerpustakaanController::class, 'index']);
    Route::get('/admin/perpustakaan/create', [\App\Http\Controllers\AdminPerpustakaanController::class, 'create']);
    Route::post('/admin/perpustakaan', [\App\Http\Controllers\AdminPerpustakaanController::class, 'store']);
    Route::get('/admin/perpustakaan/{buku}/edit', [\App\Http\Controllers\AdminPerpustakaanController::class, 'edit']);
    Route::put('/admin/perpustakaan/{buku}', [\App\Http\Controllers\AdminPerpustakaanController::class, 'update']);
    Route::delete('/admin/perpustakaan/{buku}', [\App\Http\Controllers\AdminPerpustakaanController::class, 'destroy']);
    Route::get('/admin/perpustakaan/kategori', [\App\Http\Controllers\AdminPerpustakaanController::class, 'kategoriIndex']);
    Route::post('/admin/perpustakaan/kategori', [\App\Http\Controllers\AdminPerpustakaanController::class, 'kategoriStore']);
    Route::put('/admin/perpustakaan/kategori/{kategori}', [\App\Http\Controllers\AdminPerpustakaanController::class, 'kategoriUpdate']);
    Route::delete('/admin/perpustakaan/kategori/{kategori}', [\App\Http\Controllers\AdminPerpustakaanController::class, 'kategoriDestroy']);
    Route::get('/admin/eskul', [\App\Http\Controllers\EskulController::class, 'adminIndex']);
    Route::post('/admin/eskul', [\App\Http\Controllers\EskulController::class, 'store']);
    Route::put('/admin/eskul/{eskul}', [\App\Http\Controllers\EskulController::class, 'update']);
    Route::delete('/admin/eskul/{eskul}', [\App\Http\Controllers\EskulController::class, 'destroy']);
    Route::patch('/admin/eskul/{eskul}/toggle', [\App\Http\Controllers\EskulController::class, 'toggle']);
    Route::post('/admin/eskul/{eskul}/set-admin', [\App\Http\Controllers\EskulController::class, 'setAdmin']);
    Route::get('/jurusan', [\App\Http\Controllers\JurusanController::class, 'index'])->name('api.jurusan.index');
    Route::post('/jurusan', [\App\Http\Controllers\JurusanController::class, 'store'])->name('api.jurusan.store');
    Route::put('/jurusan/{jurusan}', [\App\Http\Controllers\JurusanController::class, 'update'])->name('api.jurusan.update');
    Route::delete('/jurusan/{jurusan}', [\App\Http\Controllers\JurusanController::class, 'destroy'])->name('api.jurusan.destroy');
    Route::get('/kelas', [\App\Http\Controllers\KelasController::class, 'index'])->name('api.kelas.index');
    Route::post('/kelas', [\App\Http\Controllers\KelasController::class, 'store'])->name('api.kelas.store');
    Route::put('/kelas/{kelas}', [\App\Http\Controllers\KelasController::class, 'update'])->name('api.kelas.update');
    Route::delete('/kelas/{kelas}', [\App\Http\Controllers\KelasController::class, 'destroy'])->name('api.kelas.destroy');
    Route::get('/mahasiswa', [\App\Http\Controllers\MahasiswaController::class, 'index'])->name('api.mahasiswa.index');
    Route::post('/mahasiswa', [\App\Http\Controllers\MahasiswaController::class, 'store'])->name('api.mahasiswa.store');
    Route::put('/mahasiswa/{mahasiswa}', [\App\Http\Controllers\MahasiswaController::class, 'update'])->name('api.mahasiswa.update');
    Route::delete('/mahasiswa/{mahasiswa}', [\App\Http\Controllers\MahasiswaController::class, 'destroy'])->name('api.mahasiswa.destroy');
    Route::get('/pengumuman/create', [\App\Http\Controllers\PengumumanController::class, 'create'])->name('api.pengumuman.create');
    Route::post('/pengumuman', [\App\Http\Controllers\PengumumanController::class, 'store'])->name('api.pengumuman.store');
    Route::get('/pengumuman/{pengumuman}/edit', [\App\Http\Controllers\PengumumanController::class, 'edit'])->name('api.pengumuman.edit');
    Route::put('/pengumuman/{pengumuman}', [\App\Http\Controllers\PengumumanController::class, 'update'])->name('api.pengumuman.update');
    Route::delete('/pengumuman/{pengumuman}', [\App\Http\Controllers\PengumumanController::class, 'destroy'])->name('api.pengumuman.destroy');
    Route::get('/admin/jadwal', [\App\Http\Controllers\JadwalController::class, 'adminIndex']);
    Route::post('/admin/jadwal', [\App\Http\Controllers\JadwalController::class, 'store']);
    Route::put('/admin/jadwal/{jadwal}', [\App\Http\Controllers\JadwalController::class, 'update']);
    Route::delete('/admin/jadwal/{jadwal}', [\App\Http\Controllers\JadwalController::class, 'destroy']);
    Route::get('/spp/create', [\App\Http\Controllers\SppController::class, 'create']);
    Route::post('/spp', [\App\Http\Controllers\SppController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'api.role:guru'])->group(function () {
    Route::get('/tugas/create', [\App\Http\Controllers\TugasController::class, 'create']);
    Route::post('/tugas', [\App\Http\Controllers\TugasController::class, 'store']);
    Route::get('/tugas/{tugas}/edit', [\App\Http\Controllers\TugasController::class, 'edit']);
    Route::put('/tugas/{tugas}', [\App\Http\Controllers\TugasController::class, 'update']);
    Route::delete('/tugas/{tugas}', [\App\Http\Controllers\TugasController::class, 'destroy']);
    Route::get('/tugas/{tugas}/export/pdf', [\App\Http\Controllers\TugasController::class, 'exportPdf']);
    Route::get('/tugas/{tugas}/export/excel', [\App\Http\Controllers\TugasController::class, 'exportExcel']);
    Route::post('/pengumpulan/{pengumpulan}/review', [\App\Http\Controllers\TugasController::class, 'review']);
    Route::get('/tugas-notifikasi', [\App\Http\Controllers\NotifikasiController::class, 'tugas']);
    Route::post('/spp/{spp}/remind', [\App\Http\Controllers\SppController::class, 'remind']);
    Route::get('/mapel/{mapel}/materi/create', [\App\Http\Controllers\MateriController::class, 'create']);
    Route::post('/mapel/{mapel}/materi', [\App\Http\Controllers\MateriController::class, 'store']);
    Route::get('/mapel/{mapel}/materi/{materi}/edit', [\App\Http\Controllers\MateriController::class, 'edit']);
    Route::put('/mapel/{mapel}/materi/{materi}', [\App\Http\Controllers\MateriController::class, 'update']);
    Route::delete('/mapel/{mapel}/materi/{materi}', [\App\Http\Controllers\MateriController::class, 'destroy']);
    Route::get('/spp/{spp}/edit', [\App\Http\Controllers\SppController::class, 'edit']);
    Route::put('/spp/{spp}', [\App\Http\Controllers\SppController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'api.role:siswa'])->group(function () {
    Route::post('/tugas/{tugas}/submit', [\App\Http\Controllers\TugasController::class, 'submit']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/notifikasi/poll', [\App\Http\Controllers\NotifikasiController::class, 'poll']);
    Route::get('/session/status', [\App\Http\Controllers\SessionController::class, 'status']);
    // Daftarkan token FCM perangkat (aplikasi native) untuk push notification.
    Route::post('/device-token', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'in:android,ios,web'],
        ]);
        \App\Models\DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            ['user_id' => $request->user()->id, 'platform' => $data['platform'] ?? 'android']
        );

        return response()->json(['ok' => true]);
    });
    Route::delete('/device-token', function (\Illuminate\Http\Request $request) {
        $data = $request->validate(['token' => ['required', 'string', 'max:255']]);
        \App\Models\DeviceToken::where('token', $data['token'])
            ->where('user_id', $request->user()->id)->delete();

        return response()->json(['ok' => true]);
    });
});

Route::get('/status-aplikasi', function () {
    // Cek apakah sedang dalam mode maintenance
    // Untuk development: return status maintenance
    // Untuk production: return status normal
    $isMaintenance = false; // Set false saat rilis

    if ($isMaintenance) {
        return response()->json([
            'status' => 'maintenance',
            'message' => 'Aplikasi Sedang Dalam Pengembangan',
            'estimated_release' => '30 September 2026',
            'mode' => 'development'
        ], 503);
    }

    return response()->json([
        'status' => 'active',
        'message' => 'Aplikasi Berjalan Normal',
        'mode' => 'production'
    ]);
});
