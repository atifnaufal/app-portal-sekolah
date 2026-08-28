<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        return view('kelas.index', ['kelases' => Kelas::withCount('mahasiswa')->latest()->get()]);
    }

    public function create(): View
    {
        return view('kelas.form', ['kelas' => new Kelas]);
    }

    public function store(Request $request): RedirectResponse
    {
        Kelas::create($request->validate(['nama' => ['required', 'max:255'], 'tingkat' => ['required', 'integer', 'between:1,6'], 'tahun_ajaran' => ['required', 'regex:/^\\d{4}\\/\\d{4}$/']]));

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas): View
    {
        return view('kelas.form', compact('kelas'));
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $kelas->update($request->validate(['nama' => ['required', 'max:255'], 'tingkat' => ['required', 'integer', 'between:1,6'], 'tahun_ajaran' => ['required', 'regex:/^\\d{4}\\/\\d{4}$/']]));

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        if (Mahasiswa::where('kelas_id', $kelas->id)->exists()) {
            return back()->with('error', 'Kelas masih terhubung ke data mahasiswa. Pindahkan/hapus mahasiswa dahulu.');
        }

        try {
            $kelas->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Kelas tidak dapat dihapus karena masih terhubung ke data lain (siswa, mapel, nilai, dst). Pindahkan atau hapus data terkait terlebih dahulu.');
        }

        return back()->with('success', 'Kelas berhasil dihapus. Seluruh data terkait (mapel, jadwal, nilai, tugas) ikut terhapus otomatis.');
    }
}
