<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Pengumuman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class PengumumanController extends Controller
{
    public function index(Request $request): View
    {
        $kelasId = in_array($request->session()->get('user_role'), ['guru', 'siswa'], true) ? $request->session()->get('user_kelas_id') : null;
        $pengumumans = Pengumuman::with(['kelas', 'user'])
            ->where('publik', true)
            ->when($kelasId, fn ($query) => $query->where(function ($nestedQuery) use ($kelasId) {
                $nestedQuery->whereNull('kelas_id')->orWhere('kelas_id', $kelasId);
            }))
            ->latest()
            ->get();
        if ($request->session()->get('user_role') !== 'admin') return view('mobile.pengumuman', ['pengumumans' => $pengumumans, 'user' => User::with('kelas')->findOrFail($request->session()->get('user_id'))]);
        return view('pengumuman.index', compact('pengumumans'));
    }

    public function create(): View { abort_unless(session('user_role') === 'admin', 403); return view('pengumuman.form', ['pengumuman' => new Pengumuman]); }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->session()->get('user_role') === 'admin', 403);
        $data = $request->validate(['judul' => ['required', 'max:255'], 'isi' => ['required'], 'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'tanggal_acara' => ['nullable', 'date'], 'is_landing' => ['nullable', 'boolean']]);
        $data['user_id'] = $request->session()->get('user_id');
        $data['publik'] = true;
        $data['is_landing'] = $request->boolean('is_landing');
        if ($request->hasFile('gambar')) { $data['gambar'] = $request->file('gambar')->store('pengumuman', 'public'); $data['gambar_nama'] = $request->file('gambar')->getClientOriginalName(); }
        Pengumuman::create($data);
        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function edit(Pengumuman $pengumuman): View { abort_unless(session('user_role') === 'admin', 403); return view('pengumuman.form', compact('pengumuman')); }

    public function update(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        abort_unless($request->session()->get('user_role') === 'admin', 403);
        $data = $request->validate(['judul' => ['required', 'max:255'], 'isi' => ['required'], 'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'tanggal_acara' => ['nullable', 'date'], 'is_landing' => ['nullable', 'boolean']]);
        $data['is_landing'] = $request->boolean('is_landing');
        if ($request->hasFile('gambar')) { $data['gambar'] = $request->file('gambar')->store('pengumuman', 'public'); $data['gambar_nama'] = $request->file('gambar')->getClientOriginalName(); }
        $pengumuman->update($data);
        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        abort_unless($request->session()->get('user_role') === 'admin', 403);
        $pengumuman->delete();
        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
