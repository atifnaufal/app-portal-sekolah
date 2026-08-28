<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Kelola data peserta didik.
 *
 * CATATAN: Data siswa disimpan pada tabel `users` (role = 'siswa'), bukan tabel
 * `mahasiswa` yang lama (legacy & kosong). Controller ini dibaca dari users agar
 * daftar mahasiswa tampil dan selaras dengan modul lain (nilai, jadwal, LMS, dll).
 */
class MahasiswaController extends Controller
{
    public function index(Request $request): View
    {
        $mahasiswas = User::with('kelas')
            ->where('role', 'siswa')
            ->when($request->search, fn ($query, $search) => $query->where(
                fn ($q) => $q->where('name', 'like', "%$search%")
                    ->orWhere('nik', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
            ))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('mahasiswa.index', compact('mahasiswas'));
    }

    public function create(): View
    {
        return view('mahasiswa.form', [
            'mahasiswa' => new User,
            'kelases' => Kelas::orderBy('tingkat')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::create($this->validated($request) + [
            'password' => Hash::make($request->input('password', 'password')),
            'role' => 'siswa',
            'aktif' => true,
        ]);

        return redirect()->route('mahasiswa.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(User $mahasiswa): View
    {
        return view('mahasiswa.form', [
            'mahasiswa' => $mahasiswa,
            'kelases' => Kelas::orderBy('tingkat')->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, User $mahasiswa): RedirectResponse
    {
        $mahasiswa->update($this->validated($request, $mahasiswa));

        return redirect()->route('mahasiswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(User $mahasiswa): RedirectResponse
    {
        try {
            $mahasiswa->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Siswa tidak dapat dihapus karena masih terhubung ke data lain (nilai, tugas, pengumpulan, dst). Pindahkan atau hapus data terkait dahulu.');
        }

        return back()->with('success', 'Data siswa berhasil dihapus beserta seluruh data tautannya.');
    }

    private function validated(Request $request, ?User $mahasiswa = null): array
    {
        $nikRule = Rule::unique('users', 'nik');
        $emailRule = Rule::unique('users', 'email');

        if ($mahasiswa) {
            $nikRule = $nikRule->ignore($mahasiswa->id);
            $emailRule = $emailRule->ignore($mahasiswa->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:30', $nikRule],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'no_hp' => ['nullable', 'string', 'max:25'],
            'kelas_id' => ['required', 'exists:kelas,id'],
        ]);
    }
}
