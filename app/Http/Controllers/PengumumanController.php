<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Eskul;
use App\Models\EskulMember;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use App\Helpers\NotificationHelper;

class PengumumanController extends Controller
{
    /**
     * Menghitung siapa penerima pengumuman publik (umum/kelas/eskul) untuk otorisasi.
     */
    public function index(Request $request): View
    {
        $userId = $request->session()->get('user_id');
        $user = User::with(['kelas', 'eskuls'])->findOrFail($userId);

        $role = $request->session()->get('user_role');
        $kelasId = $user->kelas_id;
        $myEskulIds = $user->eskuls()->pluck('eskuls.id')->toArray();

        // Pengumuman publik berdasarkan scope (umum / kelas / eskul) milik user.
        $pengumumans = Pengumuman::with(['kelas', 'eskul', 'user'])
            ->where('publik', true)
            ->where(function($query) use ($kelasId, $myEskulIds) {
                $query->whereNull('kelas_id')->whereNull('eskul_id');

                if ($kelasId) {
                    $query->orWhere('kelas_id', $kelasId);
                }

                if (!empty($myEskulIds)) {
                    $query->orWhereIn('eskul_id', $myEskulIds);
                }
            })
            ->latest()
            ->get();

        // Pengumuman privat yang ditujukan khusus user ini.
        $privat = Pengumuman::with(['user'])
            ->with(['users' => fn($q) => $q->where('users.id', $userId)])
            ->where('publik', false)
            ->whereHas('users', fn($q) => $q->where('users.id', $userId))
            ->latest()
            ->get();

        // Gabungkan: privat tampil paling atas (lebih mendesak), sisanya kronologis.
        $semua = $privat->merge($pengumumans)->unique('id');

        // Tandai status dibaca untuk pengumuman privat, lalu tandai dibaca.
        foreach ($privat as $p) {
            $pivot = $p->users->first()?->pivot;
            $p->user_read_at = $pivot?->read_at;
        }

        if ($role !== 'admin') {
            $isWaliKelas = Kelas::where('pembina_id', $userId)->exists();
            $pembinaEskuls = Eskul::whereHas('members', fn($q) => $q->where('user_id', $userId)->where('is_admin', true))->get();
            $canCreate = ($role === 'guru' && $isWaliKelas) || $pembinaEskuls->isNotEmpty();
            $canManage = $role === 'guru' && ($isWaliKelas || $pembinaEskuls->isNotEmpty());

            return view('mobile.pengumuman', [
                'pengumumans' => $semua,
                'privat' => $privat,
                'user' => $user,
                'canCreate' => $canCreate,
                'canManage' => $canManage,
                'isWaliKelas' => $isWaliKelas ? Kelas::where('pembina_id', $userId)->first() : null,
                'pembinaEskuls' => $pembinaEskuls,
            ]);
        }

        return view('pengumuman.index', ['pengumumans' => $pengumumans, 'semua' => $semua]);
    }

    public function create(Request $request): View
    {
        $role = session('user_role');
        $userId = session('user_id');

        // Admin IT, Wali Kelas, atau Admin Eskul
        $isWaliKelas = Kelas::where('pembina_id', $userId)->first();
        $adminEskuls = Eskul::whereHas('members', function($q) use ($userId) {
            $q->where('user_id', $userId)->where('is_admin', true);
        })->get();

        abort_unless($role === 'admin' || $isWaliKelas || $adminEskuls->isNotEmpty(), 403);

        // Daftar siswa untuk pengumuman privat (penerima per-student).
        $siswaList = collect();
        if ($isWaliKelas) {
            $siswaList = User::where('kelas_id', $isWaliKelas->id)->where('role', 'siswa')->orderBy('name')->get(['id', 'name']);
        }

        return view('pengumuman.form', [
            'pengumuman' => new Pengumuman,
            'isWaliKelas' => $isWaliKelas,
            'adminEskuls' => $adminEskuls,
            'siswaList' => $siswaList,
            'selectedSiswa' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $role = session('user_role');
        $userId = session('user_id');

        $data = $request->validate([
            'judul' => ['required', 'max:255'],
            'isi' => ['required'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'tanggal_acara' => ['nullable', 'date'],
            'is_landing' => ['nullable', 'boolean'],
            'target' => ['required', 'string'], // 'general', 'class', 'eskul:{id}', 'private'
            'siswa_ids' => ['nullable', 'array'],
            'siswa_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $data['user_id'] = $userId;
        $data['publik'] = true;
        $data['is_landing'] = $request->boolean('is_landing');
        $privateSiswaIds = [];

        // Logic target
        if ($data['target'] === 'general') {
            abort_unless($role === 'admin', 403);
            $data['kelas_id'] = null;
            $data['eskul_id'] = null;
        } elseif ($data['target'] === 'class') {
            $kelas = Kelas::where('pembina_id', $userId)->first();
            abort_unless($role === 'admin' || $kelas, 403);
            $data['kelas_id'] = $kelas ? $kelas->id : $request->kelas_id; // Admin IT bisa pilih kelas
            $data['eskul_id'] = null;
        } elseif (str_starts_with($data['target'], 'eskul:')) {
            $eskulId = explode(':', $data['target'])[1];
            $isAdminEskul = EskulMember::where('user_id', $userId)->where('eskul_id', $eskulId)->where('is_admin', true)->exists();
            abort_unless($role === 'admin' || $isAdminEskul, 403);
            $data['kelas_id'] = null;
            $data['eskul_id'] = $eskulId;
        } elseif ($data['target'] === 'private') {
            // Pengumuman privat per-siswa: hanya guru (wali kelas) boleh.
            $isWaliKelas = Kelas::where('pembina_id', $userId)->first();
            abort_unless($role === 'admin' || $isWaliKelas, 403);

            $privateSiswaIds = $request->input('siswa_ids', []);
            abort_unless(count($privateSiswaIds) > 0, 422, 'Pilih minimal satu siswa penerima.');

            // Wali kelas hanya boleh menargetkan siswa di kelasnya.
            if ($isWaliKelas) {
                $validIds = User::where('kelas_id', $isWaliKelas->id)
                    ->where('role', 'siswa')
                    ->pluck('id')
                    ->all();
                $privateSiswaIds = array_values(array_intersect($privateSiswaIds, $validIds));
                abort_unless(count($privateSiswaIds) > 0, 403);
            }

            $data['kelas_id'] = null;
            $data['eskul_id'] = null;
            $data['publik'] = false; // privat
        }

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('pengumuman', 'public');
            $data['gambar_nama'] = $request->file('gambar')->getClientOriginalName();
        }

        $pengumuman = Pengumuman::create($data);

        // Kirim notifikasi sesuai target
        $targetName = "Semua";
        if ($pengumuman->kelas_id) {
            $targetName = "Kelas " . $pengumuman->kelas->nama;
            NotificationHelper::sendToClass($pengumuman->kelas_id, "Pengumuman ($targetName)", $pengumuman->judul, route('pengumuman.index'), 'announcement');
        } elseif ($pengumuman->eskul_id) {
            $targetName = "Eskul " . $pengumuman->eskul->nama;
            NotificationHelper::sendToEskul($pengumuman->eskul_id, "Pengumuman ($targetName)", $pengumuman->judul, route('pengumuman.index'), 'announcement');
        } elseif (!empty($privateSiswaIds)) {
            // Privat: lampirkan penerima + kirim notifikasi individu.
            $pengumuman->users()->sync($privateSiswaIds);
            foreach ($privateSiswaIds as $sid) {
                NotificationHelper::send($sid, "Pengumuman Pribadi", $pengumuman->judul, route('pengumuman.index'), 'announcement');
            }
        } else {
            // Umum
            NotificationHelper::sendToAll("Pengumuman ($targetName)", $pengumuman->judul, route('pengumuman.index'), 'announcement', null, $userId);
        }

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    // Edit/Update/Destroy logic remains similar but with scope checks
    public function edit(Pengumuman $pengumuman): View
    {
        $userId = session('user_id');
        $role = session('user_role');

        abort_unless($role === 'admin' || $pengumuman->user_id === $userId, 403);

        $isWaliKelas = Kelas::where('pembina_id', $userId)->first();
        $adminEskuls = Eskul::whereHas('members', function($q) use ($userId) {
            $q->where('user_id', $userId)->where('is_admin', true);
        })->get();

        // Siswa penerima untuk pengumuman privat.
        $siswaList = collect();
        if ($isWaliKelas) {
            $siswaList = User::where('kelas_id', $isWaliKelas->id)->where('role', 'siswa')->orderBy('name')->get(['id', 'name']);
        }
        $selectedSiswa = $pengumuman->isPrivate() ? $pengumuman->users()->pluck('users.id')->all() : [];

        return view('pengumuman.form', compact('pengumuman', 'isWaliKelas', 'adminEskuls', 'siswaList', 'selectedSiswa'));
    }

    public function update(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $userId = session('user_id');
        $role = session('user_role');
        abort_unless($role === 'admin' || $pengumuman->user_id === $userId, 403);

        $data = $request->validate([
            'judul' => ['required', 'max:255'],
            'isi' => ['required'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'tanggal_acara' => ['nullable', 'date'],
            'is_landing' => ['nullable', 'boolean'],
            'target' => ['nullable', 'string'],
            'siswa_ids' => ['nullable', 'array'],
            'siswa_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $data['is_landing'] = $request->boolean('is_landing');

        // Perbarui jangkauan bila target diubah.
        if ($request->target) {
            $data['publik'] = true;
            if ($request->target === 'general') {
                abort_unless($role === 'admin', 403);
                $data['kelas_id'] = null;
                $data['eskul_id'] = null;
                $pengumuman->users()->sync([]);
            } elseif ($request->target === 'class') {
                $kelas = Kelas::where('pembina_id', $userId)->first();
                abort_unless($role === 'admin' || $kelas, 403);
                $data['kelas_id'] = $kelas ? $kelas->id : $request->kelas_id;
                $data['eskul_id'] = null;
                $pengumuman->users()->sync([]);
            } elseif (str_starts_with($request->target, 'eskul:')) {
                $eskulId = explode(':', $request->target)[1];
                $isAdminEskul = EskulMember::where('user_id', $userId)->where('eskul_id', $eskulId)->where('is_admin', true)->exists();
                abort_unless($role === 'admin' || $isAdminEskul, 403);
                $data['kelas_id'] = null;
                $data['eskul_id'] = $eskulId;
                $pengumuman->users()->sync([]);
            } elseif ($request->target === 'private') {
                $isWaliKelas = Kelas::where('pembina_id', $userId)->first();
                abort_unless($role === 'admin' || $isWaliKelas, 403);
                $ids = $request->input('siswa_ids', []);
                abort_unless(count($ids) > 0, 422, 'Pilih minimal satu siswa penerima.');
                if ($isWaliKelas) {
                    $validIds = User::where('kelas_id', $isWaliKelas->id)->where('role', 'siswa')->pluck('id')->all();
                    $ids = array_values(array_intersect($ids, $validIds));
                    abort_unless(count($ids) > 0, 403);
                }
                $data['kelas_id'] = null;
                $data['eskul_id'] = null;
                $data['publik'] = false;
                $pengumuman->users()->sync($ids);
            }
        } elseif ($pengumuman->isPrivate()) {
            $isWaliKelas = Kelas::where('pembina_id', $userId)->first();
            if ($isWaliKelas) {
                $ids = $request->input('siswa_ids', []);
                $validIds = User::where('kelas_id', $isWaliKelas->id)->where('role', 'siswa')->pluck('id')->all();
                $pengumuman->users()->sync(array_values(array_intersect($ids, $validIds)));
            }
        }

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('pengumuman', 'public');
            $data['gambar_nama'] = $request->file('gambar')->getClientOriginalName();
        }
        $pengumuman->update($data);
        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        // Hanya Admin yang boleh menghapus pengumuman (guru/pembina eskul tidak).
        abort_unless(session('user_role') === 'admin', 403);

        $pengumuman->delete();
        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
