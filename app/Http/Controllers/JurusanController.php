<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JurusanController extends Controller
{
    public function index(): View { return view('jurusan.index', ['jurusans' => Jurusan::withCount('mahasiswa')->latest()->get()]); }
    public function create(): View { return view('jurusan.form', ['jurusan' => new Jurusan]); }
    public function store(Request $request): RedirectResponse { Jurusan::create($request->validate(['kode' => ['required', 'max:20', 'unique:jurusan,kode'], 'nama' => ['required', 'max:255']])); return redirect()->route('jurusan.index')->with('success', 'Jurusan berhasil ditambahkan.'); }
    public function edit(Jurusan $jurusan): View { return view('jurusan.form', compact('jurusan')); }
    public function update(Request $request, Jurusan $jurusan): RedirectResponse { $jurusan->update($request->validate(['kode' => ['required', 'max:20', 'unique:jurusan,kode,'.$jurusan->id], 'nama' => ['required', 'max:255']])); return redirect()->route('jurusan.index')->with('success', 'Jurusan berhasil diperbarui.'); }
    public function destroy(Jurusan $jurusan): RedirectResponse
    {
        if (\App\Models\Mahasiswa::where('jurusan_id', $jurusan->id)->exists()) {
            return back()->with('error', 'Jurusan masih terhubung ke data mahasiswa. Pindahkan/hapus mahasiswa dahulu.');
        }

        try {
            $jurusan->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Jurusan tidak dapat dihapus karena masih terhubung ke data lain. Pindahkan atau hapus data terkait terlebih dahulu.');
        }

        return back()->with('success', 'Jurusan berhasil dihapus.');
    }
}
