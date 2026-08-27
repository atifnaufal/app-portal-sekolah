<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Notifikasi;
use App\Models\Pengumuman;
use App\Models\Spp;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        if (session('user_role') === 'admin') return app(AdminController::class)->dashboard();

        $userId = session('user_id');
        if (!$userId && Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        }

        $user = User::with('kelas')->findOrFail($userId);

        $tugasQuery = $user->role === 'guru'
            ? Tugas::where('user_id', $user->id)
            : Tugas::where('kelas_id', $user->kelas_id);

        $tugasAktif = (clone $tugasQuery)->where(function ($q) {
            $q->whereNull('batas_pengumpulan')->orWhere('batas_pengumpulan', '>=', today());
        })->count();

        $tugasTerbaru = (clone $tugasQuery)->latest()->take(4)->get();

        $sppStats = null;
        if ($user->role === 'siswa') {
            $sppTotal = Spp::where('siswa_id', $user->id)->count();
            $sppLunas = Spp::where('siswa_id', $user->id)->where('status', 'lunas')->count();
            $sppKekurangan = Spp::where('siswa_id', $user->id)->sum(\DB::raw('GREATEST(nominal - dibayar, 0)'));
            $sppStats = ['total' => $sppTotal, 'lunas' => $sppLunas, 'belum' => $sppTotal - $sppLunas, 'kekurangan' => $sppKekurangan];
        }

        $absensiHariIni = Absensi::where('user_id', $user->id)->whereDate('tanggal', today())->first();
        $absensiBulan = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', now()->month)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('mobile.dashboard', [
            'user' => $user,
            'notifications' => Notifikasi::where('user_id', $user->id)->latest()->take(5)->get(),
            'unreadNotificationsCount' => Notifikasi::where('user_id', $user->id)->whereNull('dibaca_pada')->count(),
            'publicPengumumans' => Pengumuman::where('publik', true)->latest()->take(3)->get(),
            'tugas' => $tugasTerbaru,
            'tugasAktif' => $tugasAktif,
            'sppStats' => $sppStats,
            'absensiHariIni' => $absensiHariIni,
            'absensiBulan' => $absensiBulan,
            'totalSiswaKelas' => User::where('role', 'siswa')->where('kelas_id', $user->kelas_id)->count(),
        ]);
    }
}
