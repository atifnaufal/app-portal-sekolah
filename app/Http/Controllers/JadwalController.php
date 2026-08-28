<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    public function index()
    {
        $userId = session('user_id') ?: Auth::id();
        $user = \App\Models\User::with('kelas')->findOrFail($userId);
        $isGuru = $user->role === 'guru';

        $query = Jadwal::with(['mataPelajaran', 'kelas', 'guru']);

        if ($isGuru) {
            $query->where('guru_id', $user->id);
        } else {
            $query->where('kelas_id', $user->kelas_id);
        }

        $jadwals = $query->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('hari');

        return view('mobile.jadwal', compact('user', 'isGuru', 'jadwals'));
    }
}
