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

        // Data for SPP Chart (Last 6 Months)
        $sppData = Spp::selectRaw('tahun, bulan, SUM(nominal) as tagihan, SUM(dibayar) as terbayar')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->take(6)
            ->get();

        $chartLabels = $sppData->map(fn($item) => "$item->bulan/$item->tahun");
        $chartTagihan = $sppData->map(fn($item) => $item->tagihan);
        $chartTerbayar = $sppData->map(fn($item) => $item->terbayar);

        $data = [
            'totalGuru' => User::where('role', 'guru')->count(),
            'totalSiswa' => User::where('role', 'siswa')->count(),
            'totalKelas' => Kelas::count(),
            'sppKurang' => Spp::where('status', 'belum_lunas')->count(),
            'sppTotal' => Spp::count(),
            'sppTerbayar' => Spp::sum('dibayar'),
            'sppTagihan' => Spp::sum('nominal'),
            'sppByMonth' => $sppData->reverse(),
            'chartLabels' => $chartLabels,
            'chartTagihan' => $chartTagihan,
            'chartTerbayar' => $chartTerbayar,
            'kelasSummaries' => Kelas::with(['users' => fn ($query) => $query->whereIn('role', ['guru', 'siswa'])->orderBy('role')->orderBy('name')])->withCount(['users as guru_count' => fn ($query) => $query->where('role', 'guru'), 'users as siswa_count' => fn ($query) => $query->where('role', 'siswa')])->orderBy('tingkat')->orderBy('nama')->get(),
            'recentUsers' => User::whereIn('role', ['guru', 'siswa'])->latest()->take(8)->get(),
            'registrationEnabled' => (bool) Setting::getValue('registration_enabled', false)
        ];

        // Deteksi apakah akses dari mobile/aplikasi
        $userAgent = request()->header('User-Agent');
        $isMobile = preg_match('/(android|iphone|ipad|mobile)/i', $userAgent);

        if ($isMobile) {
            return view('mobile.admin-dashboard', $data);
        }

        return view('admin.dashboard', $data);
    }

    public function users(Request $request): View
    {
        $users = User::with('kelas')->whereIn('role', ['guru', 'siswa'])->when($request->search, fn ($query, $search) => $query->where(fn ($q) => $q->where('name', 'like', "%$search%")->orWhere('nik', 'like', "%$search%")->orWhere('email', 'like', "%$search%")))->latest()->get();

        $data = ['users' => $users, 'kelases' => Kelas::orderBy('tingkat')->orderBy('nama')->get(), 'registrationEnabled' => (bool) Setting::getValue('registration_enabled', false)];

        if (preg_match('/(android|iphone|ipad|mobile)/i', request()->header('User-Agent'))) {
            return view('mobile.admin-users', $data);
        }

        return view('admin.users', $data);
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

    public function destroyUser(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['guru', 'siswa'], true), 404);
        $user->delete();
        return back()->with('success', 'Akun berhasil dihapus permanen.');
    }

    public function toggleRegistration(): RedirectResponse
    {
        $enabled = ! (bool) Setting::getValue('registration_enabled', false);
        Setting::setValue('registration_enabled', $enabled ? '1' : '0');
        return back()->with('success', $enabled ? 'Registrasi siswa/guru diaktifkan.' : 'Registrasi siswa/guru dinonaktifkan.');
    }

    public function settings(): View
    {
        $data = [
            'registrationEnabled' => (bool) Setting::getValue('registration_enabled', false),
            'attendanceActive' => (bool) Setting::getValue('attendance_active', false),
            'startTime' => Setting::getValue('attendance_start_time', '07:00'),
            'endTime' => Setting::getValue('attendance_end_time', '15:00'),
            'lateTime' => Setting::getValue('attendance_late_time', '07:30'),
        ];

        if (preg_match('/(android|iphone|ipad|mobile)/i', request()->header('User-Agent'))) {
            return view('mobile.admin-settings', $data);
        }

        return view('admin.settings', $data);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'attendance_active' => 'required|boolean',
            'attendance_start_time' => 'required',
            'attendance_end_time' => 'required',
            'attendance_late_time' => 'required',
            'registration_enabled' => 'required|boolean',
        ]);

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
