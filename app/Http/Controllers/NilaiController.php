<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NilaiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGuru = $user->role === 'guru';

        if ($isGuru) {
            $mataPelajarans = MataPelajaran::where('guru_id', $user->id)
                ->with('kelas')
                ->get();

            $selectedSubject = null;
            $students = collect();

            if ($request->has('subject_id')) {
                $selectedSubject = MataPelajaran::with('kelas')->findOrFail($request->subject_id);
                if ($selectedSubject->guru_id !== $user->id) {
                    abort(403);
                }

                $students = Nilai::where('mata_pelajaran_id', $selectedSubject->id)
                    ->with('siswa')
                    ->get()
                    ->groupBy('siswa_id');
            }

            return view('mobile.nilai', compact('user', 'isGuru', 'mataPelajarans', 'selectedSubject', 'students'));
        }

        // For Siswa
        $nilais = Nilai::where('siswa_id', $user->id)
            ->with('mataPelajaran')
            ->get()
            ->groupBy('mata_pelajaran_id');

        return view('mobile.nilai', compact('user', 'isGuru', 'nilais'));
    }
}
