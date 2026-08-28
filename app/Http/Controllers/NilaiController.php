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
        $userId = session('user_id') ?: Auth::id();
        $user = \App\Models\User::with('kelas')->findOrFail($userId);
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

                // Get all students in the class
                $allStudents = \App\Models\User::where('role', 'siswa')
                    ->where('kelas_id', $selectedSubject->kelas_id)
                    ->orderBy('name')
                    ->get();

                // Get their grades for this subject
                $nilais = Nilai::where('mata_pelajaran_id', $selectedSubject->id)
                    ->get()
                    ->groupBy('siswa_id');

                $students = $allStudents->map(function($student) use ($nilais) {
                    $student->nilai_records = $nilais->get($student->id, collect());
                    return $student;
                });
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

    public function upsert(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:users,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'semester' => 'required|integer',
            'tugas' => 'nullable|numeric|min:0|max:100',
            'uts' => 'nullable|numeric|min:0|max:100',
            'uas' => 'nullable|numeric|min:0|max:100',
        ]);

        $userId = session('user_id') ?: Auth::id();
        $subject = MataPelajaran::findOrFail($data['mata_pelajaran_id']);
        abort_unless($subject->guru_id == $userId, 403);

        Nilai::updateOrCreate(
            [
                'siswa_id' => $data['siswa_id'],
                'mata_pelajaran_id' => $data['mata_pelajaran_id'],
                'semester' => $data['semester']
            ],
            [
                'tugas' => $data['tugas'] ?? 0,
                'uts' => $data['uts'] ?? 0,
                'uas' => $data['uas'] ?? 0,
            ]
        );

        return back()->with('success', 'Nilai berhasil diperbarui.');
    }
}
