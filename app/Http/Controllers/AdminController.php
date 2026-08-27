<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Spp;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $isMobile = $this->isMobileRequest();

        // Diurutkan menurun lalu dibalik agar benar-benar 6 bulan TERAKHIR.
        $sppData = Spp::selectRaw('tahun, bulan, SUM(nominal) as tagihan, SUM(dibayar) as terbayar')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->take(6)
            ->get()
            ->reverse()
            ->values();

        $data = [
            'totalGuru' => User::where('role', 'guru')->count(),
            'totalSiswa' => User::where('role', 'siswa')->count(),
            'totalKelas' => Kelas::count(),
            'sppKurang' => Spp::where('status', 'belum_lunas')->count(),
            'sppTerbayar' => Spp::sum('dibayar'),
            'sppTagihan' => Spp::sum('nominal'),
            'chartLabels' => $sppData->map(fn ($item) => "$item->bulan/$item->tahun"),
            'chartTagihan' => $sppData->map(fn ($item) => $item->tagihan),
            'chartTerbayar' => $sppData->map(fn ($item) => $item->terbayar),
            'registrationGuruEnabled' => (bool) Setting::getValue('registration_guru_enabled', false),
            'registrationSiswaEnabled' => (bool) Setting::getValue('registration_siswa_enabled', false),
        ];

        // Hanya view desktop yang memakai dua query berat ini.
        if (! $isMobile) {
            // View hanya membaca agregat guru_count / siswa_count, jadi tidak perlu
            // ikut memuat koleksi users lengkap untuk setiap kelas.
            $data['kelasSummaries'] = Kelas::withCount([
                    'users as guru_count' => fn ($query) => $query->where('role', 'guru'),
                    'users as siswa_count' => fn ($query) => $query->where('role', 'siswa'),
                ])
                ->orderBy('tingkat')
                ->orderBy('nama')
                ->get();

            $data['recentUsers'] = User::whereIn('role', ['guru', 'siswa'])->latest()->take(8)->get();
        }

        return view($isMobile ? 'mobile.admin-dashboard' : 'admin.dashboard', $data);
    }

    private function isMobileRequest(): bool
    {
        return (bool) preg_match('/(android|iphone|ipad|mobile)/i', (string) request()->userAgent());
    }

    public function users(Request $request): View
    {
        $users = User::with('kelas')->whereIn('role', ['guru', 'siswa'])->when($request->search, fn ($query, $search) => $query->where(fn ($q) => $q->where('name', 'like', "%$search%")->orWhere('nik', 'like', "%$search%")->orWhere('email', 'like', "%$search%")))->latest()->get();

        $data = ['users' => $users, 'kelases' => Kelas::orderBy('tingkat')->orderBy('nama')->get()];

        return view($this->isMobileRequest() ? 'mobile.admin-users' : 'admin.users', $data);
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

    public function toggleRegistration(Request $request): RedirectResponse
    {
        $role = $request->input('role', 'siswa'); // Default ke siswa jika tidak ada input
        $key = "registration_{$role}_enabled";

        $enabled = ! (bool) Setting::getValue($key, false);
        Setting::setValue($key, $enabled ? '1' : '0');

        $roleName = ucfirst($role);
        return back()->with('success', $enabled ? "Registrasi {$roleName} diaktifkan." : "Registrasi {$roleName} dinonaktifkan.");
    }

    public function settings(): View
    {
        $data = [
            'registrationGuruEnabled' => (bool) Setting::getValue('registration_guru_enabled', false),
            'registrationSiswaEnabled' => (bool) Setting::getValue('registration_siswa_enabled', false),
            'attendanceActive' => (bool) Setting::getValue('attendance_active', false),
            'startTime' => Setting::getValue('attendance_start_time', '07:00'),
            'endTime' => Setting::getValue('attendance_end_time', '15:00'),
            'lateTime' => Setting::getValue('attendance_late_time', '07:30'),
        ];

        return view($this->isMobileRequest() ? 'mobile.admin-settings' : 'admin.settings', $data);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'attendance_active' => 'required|boolean',
            'attendance_start_time' => 'required',
            'attendance_end_time' => 'required',
            'attendance_late_time' => 'required',
            'registration_guru_enabled' => 'required|boolean',
            'registration_siswa_enabled' => 'required|boolean',
        ]);

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
