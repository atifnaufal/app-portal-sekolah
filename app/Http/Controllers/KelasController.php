<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View { return view('kelas.index', ['kelases' => Kelas::withCount('mahasiswa')->latest()->get()]); }
    public function create(): View { return view('kelas.form', ['kelas' => new Kelas]); }
    public function store(Request $request): RedirectResponse { Kelas::create($request->validate(['nama' => ['required', 'max:255'], 'tingkat' => ['required', 'integer', 'between:1,6'], 'tahun_ajaran' => ['required', 'regex:/^\\d{4}\\/\\d{4}$/']])); return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.'); }
    public function edit(Kelas $kelas): View { return view('kelas.form', compact('kelas')); }
    public function update(Request $request, Kelas $kelas): RedirectResponse { $kelas->update($request->validate(['nama' => ['required', 'max:255'], 'tingkat' => ['required', 'integer', 'between:1,6'], 'tahun_ajaran' => ['required', 'regex:/^\\d{4}\\/\\d{4}$/']])); return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.'); }
    public function destroy(Kelas $kelas): RedirectResponse { if ($kelas->mahasiswa()->exists()) return back()->with('error', 'Kelas masih memiliki mahasiswa.'); $kelas->delete(); return back()->with('success', 'Kelas berhasil dihapus.'); }
}
