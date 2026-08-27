<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\ProfileController;
use App\Models\Notifikasi;
use App\Http\Controllers\SppController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AdminController;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

Route::redirect('/', '/dashboard');

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/dashboard')->with('success', 'Email berhasil diverifikasi.');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Link verifikasi baru telah dikirim!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['role:admin,guru,siswa', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
    Route::get('/spp', [SppController::class, 'index'])->name('spp.index');
});

Route::middleware('role:guru,siswa')->group(function () {
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/absensi', [AbsensiController::class, 'store'])->name('absensi.store');
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profil/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/chat', [ChatController::class, 'index'])->middleware('verified')->name('chat.index');
    Route::post('/chat', [ChatController::class, 'store'])->middleware('verified')->name('chat.store');
});

Route::middleware('role:admin')->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::put('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.user.update');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.user.destroy');
    Route::patch('/admin/users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('admin.user.toggle');
    Route::patch('/admin/registration/toggle', [AdminController::class, 'toggleRegistration'])->name('admin.registration.toggle');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    Route::resource('jurusan', JurusanController::class)->except('show');
    Route::resource('kelas', KelasController::class)->except('show');
    Route::resource('pengumuman', PengumumanController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/notifikasi', [AbsensiController::class, 'notifications'])->name('notifikasi.index');
});

Route::middleware(['role:guru', 'verified'])->group(function () {
    Route::resource('tugas', TugasController::class)->except(['index', 'show']);
    Route::get('/tugas/{tugas}/export', [TugasController::class, 'exportGrades'])->name('tugas.export');
    Route::post('/pengumpulan/{pengumpulan}/review', [TugasController::class, 'review'])->name('tugas.review');
    Route::get('/tugas-notifikasi', function () { return view('mobile.tugas-notifikasi', ['notifikasis' => Notifikasi::where('user_id', session('user_id'))->latest()->get()]); })->name('tugas.notifikasi');
    Route::post('/spp/{spp}/remind', [SppController::class, 'remind'])->name('spp.remind');
});

Route::middleware(['role:admin,guru', 'verified'])->group(function () {
    Route::get('/spp/create', [SppController::class, 'create'])->name('spp.create');
    Route::post('/spp', [SppController::class, 'store'])->name('spp.store');
});

Route::middleware(['role:admin', 'verified'])->group(function () {
    Route::get('/spp/{spp}/edit', [SppController::class, 'edit'])->name('spp.edit');
    Route::put('/spp/{spp}', [SppController::class, 'update'])->name('spp.update');
    Route::delete('/spp/{spp}', [SppController::class, 'destroy'])->name('spp.destroy');
});

Route::middleware(['role:guru,siswa', 'verified'])->group(function () {
    Route::get('/notifikasi-saya', function () {
        $userId = session('user_id');
        $notifications = Notifikasi::where('user_id', $userId)->latest()->get();
        Notifikasi::where('user_id', $userId)->whereNull('dibaca_pada')->update(['dibaca_pada' => now()]);
        return view('mobile.notifications', ['notifications' => $notifications]);
    })->name('notifications.index');
});

Route::middleware(['role:siswa', 'verified'])->group(function () {
    Route::post('/tugas/{tugas}/submit', [TugasController::class, 'submit'])->name('tugas.submit');
});

Route::middleware(['role:guru,siswa', 'verified'])->group(function () {
    Route::get('/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::get('/tugas', [TugasController::class, 'index'])->name('tugas.index');
    Route::get('/tugas/{tugas}', [TugasController::class, 'show'])->whereNumber('tugas')->name('tugas.show');
});
