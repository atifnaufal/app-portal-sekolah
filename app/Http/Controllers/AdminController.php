<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Notifikasi;
use App\Models\Spp;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $today = today();
        return view('admin.dashboard', ['totalGuru' => User::where('role', 'guru')->count(), 'totalSiswa' => User::where('role', 'siswa')->count(), 'totalKelas' => Kelas::count(), 'sppKurang' => Spp::where('status', 'belum_lunas')->count(), 'sppTotal' => Spp::count(), 'sppTerbayar' => Spp::sum('dibayar'), 'sppTagihan' => Spp::sum('nominal'), 'sppByMonth' => Spp::selectRaw('tahun, bulan, SUM(nominal) as tagihan, SUM(dibayar) as terbayar')->groupBy('tahun', 'bulan')->orderByDesc('tahun')->orderByDesc('bulan')->take(6)->get(), 'kelasSummaries' => Kelas::with(['users' => fn ($query) => $query->whereIn('role', ['guru', 'siswa'])->orderBy('role')->orderBy('name')])->withCount(['users as guru_count' => fn ($query) => $query->where('role', 'guru'), 'users as siswa_count' => fn ($query) => $query->where('role', 'siswa')])->orderBy('tingkat')->orderBy('nama')->get(), 'recentUsers' => User::whereIn('role', ['guru', 'siswa'])->latest()->take(8)->get(), 'registrationEnabled' => (bool) Setting::getValue('registration_enabled', false)]);
    }

    public function users(Request $request): View
    {
        $users = User::with('kelas')->whereIn('role', ['guru', 'siswa'])->when($request->search, fn ($query, $search) => $query->where(fn ($q) => $q->where('name', 'like', "%$search%")->orWhere('nik', 'like', "%$search%")->orWhere('email', 'like', "%$search%")))->latest()->get();
        return view('admin.users', ['users' => $users, 'kelases' => Kelas::orderBy('tingkat')->orderBy('nama')->get(), 'registrationEnabled' => (bool) Setting::getValue('registration_enabled', false)]);
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['guru', 'siswa'], true), 404);
        $data = $request->validate(['name' => ['required', 'max:255'], 'nik' => ['required', 'max:30', 'unique:users,nik,'.$user->id], 'no_hp' => ['required', 'max:25'], 'email' => ['required', 'email', 'unique:users,email,'.$user->id], 'kelas_id' => ['required', 'exists:kelas,id'], 'role' => ['required', 'in:guru,siswa']]);
        $user->update($data);
        return back()->with('success', 'Data akun berhasil diperbarui.');
    }

    public function toggleUser(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['guru', 'siswa'], true), 404);
        $user->update(['aktif' => ! $user->aktif]);
        return back()->with('success', 'Status akun berhasil diubah.');
    }

    public function toggleRegistration(): RedirectResponse
    {
        $enabled = ! (bool) Setting::getValue('registration_enabled', false);
        Setting::setValue('registration_enabled', $enabled ? '1' : '0');
        return back()->with('success', $enabled ? 'Registrasi siswa/guru diaktifkan.' : 'Registrasi siswa/guru dinonaktifkan.');
    }
}
