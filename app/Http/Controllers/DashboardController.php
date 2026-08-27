<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Pengumuman;
use App\Models\Tugas;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Notifikasi;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        if (session('user_role') === 'admin') return app(AdminController::class)->dashboard();
        if (session('user_role') !== 'admin') {
            $user = User::with('kelas')->findOrFail(session('user_id'));
            return view('mobile.dashboard', [
                'user' => $user,
                'notifications' => Notifikasi::where('user_id', $user->id)->latest()->take(5)->get(),
                'unreadNotificationsCount' => Notifikasi::where('user_id', $user->id)->whereNull('dibaca_pada')->count(),
                'publicPengumumans' => Pengumuman::where('publik', true)->latest()->take(3)->get(),
                'pengumumans' => Pengumuman::with('user')->where(function ($query) use ($user) {
                    $query->whereNull('kelas_id')->orWhere('kelas_id', $user->kelas_id);
                })->latest()->take(3)->get(),
                'tugas' => Tugas::where('kelas_id', $user->kelas_id)->latest()->take(3)->get(),
            ]);
        }

        return view('dashboard', [
            'totalMahasiswa' => Mahasiswa::count(),
            'totalJurusan' => Jurusan::count(),
            'totalKelas' => Kelas::count(),
            'mahasiswaTerbaru' => Mahasiswa::with(['jurusan', 'kelas'])->latest()->take(5)->get(),
            'absensiHariIni' => Absensi::with('user')->whereDate('tanggal', today())->latest('waktu_masuk')->take(5)->get(),
            'totalHadirHariIni' => Absensi::whereDate('tanggal', today())->count(),
            'guruHadirHariIni' => Absensi::whereDate('tanggal', today())->whereHas('user', fn ($query) => $query->where('role', 'guru'))->count(),
            'siswaHadirHariIni' => Absensi::whereDate('tanggal', today())->whereHas('user', fn ($query) => $query->where('role', 'siswa'))->count(),
        ]);
    }
}
