<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Helpers\UserContextHelper;
use App\Models\Spp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SppController extends Controller
{
    private function resolveUserId(Request $request): ?int
    {
        return UserContextHelper::id($request);
    }

    public function index(Request $request): View
    {
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }

        $query = Spp::with('siswa.kelas');

        if ($user->role === 'siswa') {
            $query->where('siswa_id', $user->id);
        } elseif ($user->role === 'guru') {
            $query->whereHas('siswa', fn ($q) => $q->where('kelas_id', $user->kelas_id));
        }

        $spps = $query->latest('tahun')->latest('bulan')->get();

        $stats = [
            'total' => $spps->count(),
            'lunas' => $spps->where('status', 'lunas')->count(),
            'belum' => $spps->where('status', '!=', 'lunas')->count(),
            'total_nominal' => $spps->sum('nominal'),
            'total_terbayar' => $spps->sum('dibayar'),
            'total_kekurangan' => $spps->sum('kekurangan'),
        ];

        if ($user->role === 'admin') {
            return view('admin.spp', [
                'spps' => $spps,
                'user' => $user,
                'stats' => $stats,
            ]);
        }

        return view('mobile.spp', [
            'spps' => $spps,
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): View
    {
        $role = UserContextHelper::role($request);
        abort_unless(in_array($role, ['admin', 'guru'], true), 403);

        $kelasId = UserContextHelper::user($request)?->kelas_id;

        $siswas = User::where('role', 'siswa')
            ->with('kelas')
            ->when($role === 'guru', fn ($q) => $q->where('kelas_id', $kelasId))
            ->orderBy('name')
            ->get();

        if ($role === 'admin') {
            return view('admin.spp-form', ['siswas' => $siswas]);
        }

        return view('mobile.spp-form', ['siswas' => $siswas]);
    }

    public function store(Request $request): RedirectResponse
    {
        $role = UserContextHelper::role($request);
        abort_unless(in_array($role, ['admin', 'guru'], true), 403);

        $data = $request->validate([
            'siswa_id' => ['required', 'exists:users,id'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'min:2020'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'dibayar' => ['nullable', 'numeric', 'min:0'],
            'jatuh_tempo' => ['nullable', 'date'],
        ]);

        $kelasId = UserContextHelper::user($request)?->kelas_id;

        $siswa = User::where('role', 'siswa')
            ->when($role === 'guru', fn ($q) => $q->where('kelas_id', $kelasId))
            ->findOrFail($data['siswa_id']);

        $data['status'] = (float) ($data['dibayar'] ?? 0) >= (float) $data['nominal'] ? 'lunas' : 'belum_lunas';

        Spp::updateOrCreate(
            ['siswa_id' => $siswa->id, 'bulan' => $data['bulan'], 'tahun' => $data['tahun']],
            $data
        );

        NotificationHelper::send(
            $siswa->id,
            'Tagihan SPP Baru',
            'Tagihan SPP bulan '.$this->namaBulan($data['bulan']).' '.$data['tahun'].' telah diterbitkan.',
            route('spp.index'),
            'billing'
        );

        return redirect()->route('spp.index')->with('success', 'Data SPP berhasil disimpan.');
    }

    public function remind(Request $request, Spp $spp): RedirectResponse
    {
        $role = UserContextHelper::role($request);
        abort_unless($role === 'guru', 403);

        $kelasId = UserContextHelper::user($request)?->kelas_id;

        $spp->load('siswa');
        abort_unless((int) $spp->siswa->kelas_id === (int) $kelasId, 403);

        $pesan = 'SPP '.$this->namaBulan($spp->bulan).' '.$spp->tahun.' masih memiliki kekurangan Rp '.number_format($spp->kekurangan, 0, ',', '.');
        NotificationHelper::send($spp->siswa_id, 'Pengingat Pembayaran SPP', $pesan, route('spp.index'), 'billing');

        return back()->with('success', 'Pengingat SPP dikirim ke siswa.');
    }

    public function edit(Request $request, Spp $spp): View
    {
        $role = UserContextHelper::role($request);
        abort_unless($role === 'admin', 403);

        $siswas = User::where('role', 'siswa')->with('kelas')->orderBy('name')->get();

        if ($role === 'admin') {
            return view('admin.spp-form', ['spp' => $spp, 'siswas' => $siswas]);
        }

        return view('mobile.spp-form', ['spp' => $spp, 'siswas' => $siswas]);
    }

    public function update(Request $request, Spp $spp): RedirectResponse
    {
        $role = UserContextHelper::role($request);
        abort_unless($role === 'admin', 403);

        $data = $request->validate([
            'siswa_id' => ['required', 'exists:users,id'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'min:2020'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'dibayar' => ['nullable', 'numeric', 'min:0'],
            'jatuh_tempo' => ['nullable', 'date'],
        ]);

        $data['status'] = (float) ($data['dibayar'] ?? 0) >= (float) $data['nominal'] ? 'lunas' : 'belum_lunas';
        $spp->update($data);

        return redirect()->route('spp.index')->with('success', 'Data SPP berhasil diperbarui.');
    }

    public function destroy(Request $request, Spp $spp): RedirectResponse
    {
        $role = UserContextHelper::role($request);
        abort_unless($role === 'admin', 403);

        $spp->delete();

        return back()->with('success', 'Tagihan SPP berhasil dihapus.');
    }

    private function namaBulan(int $bulan): string
    {
        return ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$bulan] ?? '';
    }
}
