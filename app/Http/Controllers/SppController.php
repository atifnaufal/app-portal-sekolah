<?php

namespace App\Http\Controllers;

use App\Models\Spp;
use App\Models\User;
use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SppController extends Controller
{
    public function index(Request $request): View
    {
        $user = User::with('kelas')->findOrFail($request->session()->get('user_id'));
        $query = Spp::with('siswa')->when($user->role !== 'admin', fn ($q) => $q->whereHas('siswa', fn ($student) => $student->where('kelas_id', $user->kelas_id)));
        if ($user->role === 'siswa') $query->where('siswa_id', $user->id);
        return view($user->role === 'admin' ? 'admin.spp' : 'mobile.spp', ['spps' => $query->latest('tahun')->latest('bulan')->get(), 'user' => $user]);
    }

    public function create(): View
    {
        abort_unless(in_array(session('user_role'), ['admin', 'guru'], true), 403);
        return view(session('user_role') === 'admin' ? 'admin.spp-form' : 'mobile.spp-form', ['siswas' => User::where('role', 'siswa')->when(session('user_role') === 'guru', fn ($q) => $q->where('kelas_id', session('user_kelas_id')))->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->session()->get('user_role'), ['admin', 'guru'], true), 403);
        $data = $request->validate(['siswa_id' => ['required', 'exists:users,id'], 'bulan' => ['required', 'integer', 'between:1,12'], 'tahun' => ['required', 'integer', 'min:2020'], 'nominal' => ['required', 'numeric', 'min:0'], 'dibayar' => ['nullable', 'numeric', 'min:0'], 'jatuh_tempo' => ['nullable', 'date']]);
        $siswa = User::where('role', 'siswa')->when(session('user_role') === 'guru', fn ($q) => $q->where('kelas_id', session('user_kelas_id')))->findOrFail($data['siswa_id']);
        $data['status'] = (float) ($data['dibayar'] ?? 0) >= (float) $data['nominal'] ? 'lunas' : 'belum_lunas';
        $spp = Spp::updateOrCreate(['siswa_id' => $siswa->id, 'bulan' => $data['bulan'], 'tahun' => $data['tahun']], $data);
        return redirect()->route('spp.index')->with('success', 'Data SPP berhasil disimpan.');
    }

    public function remind(Request $request, Spp $spp): RedirectResponse
    {
        abort_unless($request->session()->get('user_role') === 'guru', 403);
        $spp->load('siswa');
        abort_unless($spp->siswa->kelas_id === $request->session()->get('user_kelas_id'), 403);
        Notifikasi::create(['user_id' => $spp->siswa_id, 'judul' => 'Pengingat pembayaran SPP', 'pesan' => 'SPP bulan '.$spp->bulan.'/'.$spp->tahun.' masih memiliki kekurangan Rp '.number_format($spp->kekurangan, 0, ',', '.'), 'url' => route('spp.index')]);
        return back()->with('success', 'Pengingat SPP dikirim ke siswa.');
    }

    public function edit(Spp $spp): View
    {
        abort_unless(session('user_role') === 'admin', 403);
        return view('admin.spp-form', ['spp' => $spp, 'siswas' => User::where('role', 'siswa')->with('kelas')->orderBy('name')->get()]);
    }

    public function update(Request $request, Spp $spp): RedirectResponse
    {
        abort_unless(session('user_role') === 'admin', 403);
        $data = $request->validate(['siswa_id' => ['required', 'exists:users,id'], 'bulan' => ['required', 'integer', 'between:1,12'], 'tahun' => ['required', 'integer', 'min:2020'], 'nominal' => ['required', 'numeric', 'min:0'], 'dibayar' => ['nullable', 'numeric', 'min:0'], 'jatuh_tempo' => ['nullable', 'date']]);
        $data['status'] = (float) ($data['dibayar'] ?? 0) >= (float) $data['nominal'] ? 'lunas' : 'belum_lunas';
        $spp->update($data);
        return redirect()->route('spp.index')->with('success', 'Data SPP berhasil diperbarui.');
    }

    public function destroy(Request $request, Spp $spp): RedirectResponse
    {
        abort_unless($request->session()->get('user_role') === 'admin', 403);
        $spp->delete();
        return back()->with('success', 'Tagihan SPP berhasil dihapus.');
    }
}
