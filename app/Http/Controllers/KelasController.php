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
        $me = \App\Helpers\UserContextHelper::user();
        $isSuper = $me && $me->isSuperAdmin();
        $schoolFilter = $isSuper
            ? (request('school_id') ? (int) request('school_id') : null)
            : ($me?->school_id);

        $kelases = Kelas::with('school:id,name')
            ->withCount([
                'users as siswa_count' => fn ($q) => $q->where('role', 'siswa'),
                'users as guru_count' => fn ($q) => $q->where('role', 'guru'),
            ])
            ->when($schoolFilter, fn ($q, $sid) => $q->where('school_id', $sid))
            ->latest()->get();

        $countQ = fn ($q) => $schoolFilter ? $q->where('school_id', $schoolFilter) : $q;
        $totalSiswa = $countQ(User::where('role', 'siswa'))->count();
        $totalGuru = $countQ(User::where('role', 'guru'))->count();
        $totalKelas = $kelases->count();

        return view('kelas.index', compact('kelases', 'totalSiswa', 'totalGuru', 'totalKelas')
            + ['isSuperAdmin' => $isSuper, 'schoolFilter' => $schoolFilter,
               'schools' => $isSuper ? \App\Models\School::orderBy('name')->get(['id', 'name']) : collect()]);
    }

    public function create(): View
    {
        $me = \App\Helpers\UserContextHelper::user();
        $isSuper = $me && $me->isSuperAdmin();

        return view('kelas.form', ['kelas' => new Kelas, 'isSuperAdmin' => $isSuper,
            'schools' => $isSuper ? \App\Models\School::orderBy('name')->get(['id', 'name']) : collect()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $me = \App\Helpers\UserContextHelper::user();
        $isSuper = $me && $me->isSuperAdmin();
        $data = $request->validate([
            'nama' => ['required', 'max:255'],
            'tingkat' => ['required', 'integer', 'between:1,13'],
            'tahun_ajaran' => ['required', 'regex:/^\\d{4}\\/\\d{4}$/'],
            'school_id' => [$isSuper ? 'required' : 'nullable', 'exists:schools,id'],
        ]);
        if (! $isSuper) {
            $data['school_id'] = $me?->school_id;
        }

        Kelas::create($data);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas): View
    {
        $me = \App\Helpers\UserContextHelper::user();
        $isSuper = $me && $me->isSuperAdmin();

        return view('kelas.form', compact('kelas') + ['isSuperAdmin' => $isSuper,
            'schools' => $isSuper ? \App\Models\School::orderBy('name')->get(['id', 'name']) : collect()]);
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $me = \App\Helpers\UserContextHelper::user();
        $isSuper = $me && $me->isSuperAdmin();
        abort_if(! $isSuper && $me?->school_id && $kelas->school_id && (int) $kelas->school_id !== (int) $me->school_id, 403);

        $data = $request->validate([
            'nama' => ['required', 'max:255'],
            'tingkat' => ['required', 'integer', 'between:1,13'],
            'tahun_ajaran' => ['required', 'regex:/^\\d{4}\\/\\d{4}$/'],
            'school_id' => ['nullable', 'exists:schools,id'],
        ]);
        if (! $isSuper) {
            unset($data['school_id']); // admin sekolah tak bisa pindah kelas ke sekolah lain
        }
        $kelas->update($data);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        $me = \App\Helpers\UserContextHelper::user();
        $isSuper = $me && $me->isSuperAdmin();
        abort_if(! $isSuper && $me?->school_id && $kelas->school_id && (int) $kelas->school_id !== (int) $me->school_id, 403);
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
