<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
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
            $sppRows = Spp::where('siswa_id', $user->id)->get(['nominal', 'dibayar', 'status']);

            $sppStats = [
                'total' => $sppRows->count(),
                'lunas' => $sppRows->where('status', 'lunas')->count(),
                'belum' => $sppRows->where('status', '!=', 'lunas')->count(),
                'kekurangan' => $sppRows->sum(fn ($row) => $row->kekurangan),
            ];
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
