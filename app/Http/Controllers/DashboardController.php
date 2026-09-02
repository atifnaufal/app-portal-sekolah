<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\MataPelajaran;
use App\Models\Notifikasi;
use App\Models\Pengumuman;
use App\Models\Spp;
use App\Models\Tugas;
use App\Helpers\UserContextHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        if (UserContextHelper::role($request) === 'admin') {
            return app(AdminController::class)->dashboard();
        }

        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }

        $mapels = $user->role === 'guru'
            ? MataPelajaran::where('guru_id', $user->id)->with('kelas')->get()
            : MataPelajaran::where('kelas_id', $user->kelas_id)->with('guru')->get();

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

        $classmates = collect();
        if ($user->kelas_id) {
            $classmates = User::where('kelas_id', $user->kelas_id)
                ->where('role', 'siswa')
                ->orderBy('name')
                ->get();
        }

        return view('mobile.dashboard', [
            'user' => $user,
            'mapels' => $mapels,
            'notifications' => Notifikasi::where('user_id', $user->id)->latest()->take(5)->get(),
            'unreadNotificationsCount' => Notifikasi::where('user_id', $user->id)->whereNull('dibaca_pada')->count(),
            'publicPengumumans' => Pengumuman::where('publik', true)->latest()->take(3)->get(),
            'tugas' => $tugasTerbaru,
            'tugasAktif' => $tugasAktif,
            'sppStats' => $sppStats,
            'absensiHariIni' => $absensiHariIni,
            'absensiBulan' => $absensiBulan,
            'totalSiswaKelas' => User::where('role', 'siswa')->where('kelas_id', $user->kelas_id)->count(),
            'classmates' => $classmates,
        ]);
    }
}
