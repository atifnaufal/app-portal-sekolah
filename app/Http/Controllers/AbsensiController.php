<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->session()->get('user_role');
        $userId = $request->session()->get('user_id');
        $kelasId = $request->session()->get('user_kelas_id');
        $today = now()->toDateString();
        $query = Absensi::with(['user', 'kelas'])->whereDate('tanggal', $today)->latest('waktu');
        if ($role === 'guru') $query->where('kelas_id', $kelasId)->whereHas('user', fn ($q) => $q->where('role', 'siswa'));
        if ($role === 'siswa') $query->where('user_id', $userId);
        return view($role === 'admin' ? 'absensi.admin' : 'mobile.absensi', [
            'absensis' => $query->get(),
            'todayAttendance' => Absensi::where('user_id', $userId)->whereDate('tanggal', $today)->first(),
            'user' => User::with('kelas')->findOrFail($userId),
            'isAdmin' => $role === 'admin',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->session()->get('user_id'));
        if ($user->role === 'admin') return back()->with('error', 'Admin tidak perlu melakukan absensi.');
        if (Absensi::where('user_id', $user->id)->whereDate('tanggal', now())->exists()) return back()->with('error', 'Anda sudah absen hari ini.');
        Absensi::create(['user_id' => $user->id, 'kelas_id' => $user->kelas_id, 'tanggal' => now()->toDateString(), 'waktu' => now()->format('H:i:s'), 'status' => 'hadir']);
        return back()->with('success', 'Absensi kedatangan berhasil dicatat.');
    }

    public function notifications(): View
    {
        abort_unless(session('user_role') === 'admin', 403);
        $today = now()->toDateString();
        return view('notifikasi.index', [
            'latest' => Absensi::with(['user', 'kelas'])->latest()->take(20)->get(),
            'todayCount' => Absensi::whereDate('tanggal', $today)->count(),
            'teacherCount' => Absensi::whereDate('tanggal', $today)->whereHas('user', fn ($q) => $q->where('role', 'guru'))->count(),
            'studentCount' => Absensi::whereDate('tanggal', $today)->whereHas('user', fn ($q) => $q->where('role', 'siswa'))->count(),
        ]);
    }
}
