<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MapelController extends Controller
{
    public function show(Request $request, MataPelajaran $mapel): View
    {
        $userId = session('user_id') ?: Auth::id();
        $user = User::findOrFail($userId);

        // Security check
        if ($user->role === 'guru' && $mapel->guru_id !== $user->id) abort(403);
        if ($user->role === 'siswa' && $mapel->kelas_id !== $user->kelas_id) abort(403);

        $materi = Materi::where('mata_pelajaran_id', $mapel->id)->latest()->get();
        $tugas = Tugas::where('mata_pelajaran_id', $mapel->id)->latest()->get();

        return view('mobile.mapel-detail', compact('user', 'mapel', 'materi', 'tugas'));
    }
}
