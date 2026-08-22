<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    public function index(Request $request): View { $mahasiswas = Mahasiswa::with(['jurusan', 'kelas'])->when($request->search, fn ($query, $search) => $query->where(fn ($q) => $q->where('nim', 'like', "%$search%")->orWhere('nama', 'like', "%$search%")))->latest()->paginate(10)->withQueryString(); return view('mahasiswa.index', compact('mahasiswas')); }
    public function create(): View { return view('mahasiswa.form', ['mahasiswa' => new Mahasiswa, 'jurusans' => Jurusan::orderBy('nama')->get(), 'kelases' => Kelas::orderBy('tingkat')->orderBy('nama')->get()]); }
    public function store(Request $request): RedirectResponse { Mahasiswa::create($this->validated($request)); return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil ditambahkan.'); }
    public function edit(Mahasiswa $mahasiswa): View { return view('mahasiswa.form', ['mahasiswa' => $mahasiswa, 'jurusans' => Jurusan::orderBy('nama')->get(), 'kelases' => Kelas::orderBy('tingkat')->orderBy('nama')->get()]); }
    public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse { $mahasiswa->update($this->validated($request, $mahasiswa)); return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui.'); }
    public function destroy(Mahasiswa $mahasiswa): RedirectResponse { $mahasiswa->delete(); return back()->with('success', 'Data mahasiswa berhasil dihapus.'); }
    private function validated(Request $request, ?Mahasiswa $mahasiswa = null): array { return $request->validate(['nim' => ['required', 'max:30', 'unique:mahasiswa,nim,'.($mahasiswa?->id ?? 'NULL')], 'nama' => ['required', 'max:255'], 'email' => ['nullable', 'email', 'max:255'], 'jenis_kelamin' => ['required', 'in:L,P'], 'tanggal_lahir' => ['nullable', 'date'], 'alamat' => ['nullable', 'string'], 'jurusan_id' => ['required', 'exists:jurusan,id'], 'kelas_id' => ['required', 'exists:kelas,id']]); }
}
