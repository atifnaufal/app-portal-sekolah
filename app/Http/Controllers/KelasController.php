<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        $kelases = Kelas::withCount([
            'users as siswa_count' => fn ($q) => $q->where('role', 'siswa'),
            'users as guru_count' => fn ($q) => $q->where('role', 'guru'),
        ])->latest()->get();

        $totalSiswa = User::where('role', 'siswa')->count();
        $totalGuru = User::where('role', 'guru')->count();
        $totalKelas = $kelases->count();

        return view('kelas.index', compact('kelases', 'totalSiswa', 'totalGuru', 'totalKelas'));
    }

    public function create(): View
    {
        return view('kelas.form', ['kelas' => new Kelas]);
    }

    public function store(Request $request): RedirectResponse
    {
        Kelas::create($request->validate(['nama' => ['required', 'max:255'], 'tingkat' => ['required', 'integer', 'between:1,13'], 'tahun_ajaran' => ['required', 'regex:/^\\d{4}\\/\\d{4}$/']]));

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas): View
    {
        return view('kelas.form', compact('kelas'));
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $kelas->update($request->validate(['nama' => ['required', 'max:255'], 'tingkat' => ['required', 'integer', 'between:1,13'], 'tahun_ajaran' => ['required', 'regex:/^\\d{4}\\/\\d{4}$/']]));

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        if (\App\Models\User::where('kelas_id', $kelas->id)->where('role', 'siswa')->exists()) {
            return back()->with('error', 'Kelas masih terhubung ke data siswa. Pindahkan/hapus siswa dahulu.');
        }

        try {
            $kelas->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Kelas tidak dapat dihapus karena masih terhubung ke data lain (siswa, mapel, nilai, dst). Pindahkan atau hapus data terkait terlebih dahulu.');
        }

        return back()->with('success', 'Kelas berhasil dihapus. Seluruh data terkait (mapel, jadwal, nilai, tugas) ikut terhapus otomatis.');
    }
}
