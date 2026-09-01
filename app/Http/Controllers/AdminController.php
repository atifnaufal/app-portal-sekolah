<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\GlobalPost;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\Nilai;
use App\Models\PengumpulanTugas;
use App\Models\School;
use App\Models\Setting;
use App\Models\Spp;
use App\Models\Tugas;
use App\Models\User;
use App\Models\UserHistory;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $isMobile = $this->isMobileRequest();

        $data = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_v3'.($isMobile?'_m':'_d'), 120, function () use ($isMobile) {

        // Diurutkan menurun lalu dibalik agar benar-benar 6 bulan TERAKHIR.
        $sppData = Spp::selectRaw('tahun, bulan, SUM(nominal) as tagihan, SUM(dibayar) as terbayar')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->take(6)
            ->get()
            ->reverse()
            ->values();

        $totalMapel = MataPelajaran::count();
        $totalTugas = Tugas::count();
        $totalMateri = Materi::count();
        $tugasBelumDinilai = PengumpulanTugas::whereNull('nilai')->where('revisi_aktif', false)->count();

        // ===== Analytics tambahan =====
        $totalNilai = Nilai::count();
        $rataNilai = round((float) Nilai::selectRaw('(COALESCE(tugas,0)+COALESCE(uts,0)+COALESCE(uas,0))/3 as r')->get()->avg('r'), 2);

        $totalAbsensi = Absensi::count();
        $absensiHariIni = Absensi::whereDate('tanggal', today())->count();
        $hadirHariIni = Absensi::whereDate('tanggal', today())->where('status', 'hadir')->count();
        $terlambatHari = Absensi::whereDate('tanggal', today())->where('status', 'terlambat')->count();
        $izinHariIni = Absensi::whereDate('tanggal', today())->where('status', 'izin')->count();

        $totalPengumpulan = PengumpulanTugas::count();
        $totalTugasForm = Tugas::where('tipe', 'form')->count();
        $totalPengumpulanDinilai = PengumpulanTugas::whereNotNull('nilai')->count();

        // Distribusi nilai (rata-rata per siswa) untuk grafik predikat.
        $gradeDist = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
        Nilai::selectRaw('siswa_id, AVG((COALESCE(tugas,0)+COALESCE(uts,0)+COALESCE(uas,0))/3) as r')
            ->groupBy('siswa_id')
            ->get()
            ->each(function ($n) use (&$gradeDist) {
                $avg = (float) $n->r;
                $key = match (true) {
                    $avg >= 90 => 'A', $avg >= 80 => 'B', $avg >= 70 => 'C',
                    $avg >= 60 => 'D', default => 'E',
                };
                $gradeDist[$key]++;
            });

        // Distribusi status absensi (keseluruhan).
        $distAbsensi = [
            'hadir' => Absensi::where('status', 'hadir')->count(),
            'terlambat' => Absensi::where('status', 'terlambat')->count(),
            'izin' => Absensi::where('status', 'izin')->count(),
            'sakit' => Absensi::where('status', 'sakit')->count(),
            'alpha' => Absensi::where('status', 'alpha')->count(),
        ];

        // Tren pendaftaran 6 bulan terakhir (DB-agnostic, diproses di PHP).
        $regTrend = User::whereIn('role', ['guru', 'siswa'])
            ->get(['created_at'])
            ->groupBy(fn ($u) => optional($u->created_at)->format('Y-m'))
            ->map->count()
            ->sortKeys()
            ->take(-6);
        $regLabels = $regTrend->keys()->map(fn ($k) => Carbon::createFromFormat('Y-m', $k)->translatedFormat('M y'))->values();
        $regCounts = $regTrend->values();

        $data = [
            'totalGuru' => User::where('role', 'guru')->count(),
            'totalSiswa' => User::where('role', 'siswa')->count(),
            'totalKelas' => Kelas::count(),
            'sppKurang' => Spp::where('status', 'belum_lunas')->count(),
            'pendingCount' => User::where('aktif', false)->count(),
            'sppTerbayar' => Spp::sum('dibayar'),
            'sppTagihan' => Spp::sum('nominal'),
            'chartLabels' => $sppData->map(fn ($item) => "$item->bulan/$item->tahun"),
            'chartTagihan' => $sppData->map(fn ($item) => $item->tagihan),
            'chartTerbayar' => $sppData->map(fn ($item) => $item->terbayar),
            'registrationGuruEnabled' => (bool) Setting::getValue('registration_guru_enabled', false),
            'registrationSiswaEnabled' => (bool) Setting::getValue('registration_siswa_enabled', false),
            // Statistik LMS
            'totalMapel' => $totalMapel,
            'totalTugas' => $totalTugas,
            'totalMateri' => $totalMateri,
            'tugasBelumDinilai' => $tugasBelumDinilai,
            // Analytics nilai
            'totalNilai' => $totalNilai,
            'rataNilai' => $rataNilai,
            'gradeDist' => $gradeDist,
            // Analytics absensi
            'totalAbsensi' => $totalAbsensi,
            'absensiHariIni' => $absensiHariIni,
            'hadirHariIni' => $hadirHariIni,
            'terlambatHari' => $terlambatHari,
            'izinHariIni' => $izinHariIni,
            'distAbsensi' => $distAbsensi,
            // Analytics pengumpulan tugas
            'totalPengumpulan' => $totalPengumpulan,
            'totalTugasForm' => $totalTugasForm,
            'totalPengumpulanDinilai' => $totalPengumpulanDinilai,
            // Tren pendaftaran
            'regLabels' => $regLabels,
            'regCounts' => $regCounts,
        ];

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

        // Data grafik distribusi siswa per kelas.
        $data['kelasNames'] = collect($data['kelasSummaries'])->pluck('nama');
        $data['kelasSiswa'] = collect($data['kelasSummaries'])->pluck('siswa_count');
        $data['kelasGuru'] = collect($data['kelasSummaries'])->pluck('guru_count');

        // Global Portal premium stats
        $data['totalGlobalPosts'] = GlobalPost::count();
        $data['globalPostsHariIni'] = GlobalPost::whereDate('created_at', today())->count();
        $data['totalGlobalLikes'] = \DB::table('global_likes')->count();
        $data['totalGlobalComments'] = \DB::table('global_comments')->count();
        $data['totalSchools'] = School::count();
        $data['topSchool'] = School::withCount('posts')->orderByDesc('posts_count')->first();
        $data['recentGlobalPosts'] = GlobalPost::with(['user','school'])->latest()->take(4)->get();

        return $data;
        });

        return view($isMobile ? 'mobile.admin-dashboard' : 'admin.dashboard', $data);
    }

    private function isMobileRequest(): bool
    {
        return (bool) preg_match('/(android|iphone|ipad|mobile)/i', (string) request()->userAgent());
    }

    public function users(Request $request): View
    {
        $search = $request->search;
        $usersQuery = User::with(['kelas', 'mataPelajarans.kelas'])
            ->whereIn('role', ['guru', 'siswa'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('nik', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            });

        $users = $usersQuery->latest()->get();

        $data = [
            'users' => $users,
            'kelases' => Kelas::orderBy('tingkat')->orderBy('nama')->get(),
            'schools' => \App\Models\School::orderBy('name')->get(),
            'totalGuru' => User::where('role', 'guru')->count(),
            'totalSiswa' => User::where('role', 'siswa')->count(),
            'pendingUsers' => User::where('aktif', false)->whereIn('role', ['guru', 'siswa'])->count(),
        ];

        return view($this->isMobileRequest() ? 'mobile.admin-users' : 'admin.users', $data);
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['guru', 'siswa'], true), 404);
        $data = $request->validate(['name' => ['required', 'max:255'], 'nik' => ['required', 'max:30', 'unique:users,nik,'.$user->id], 'no_hp' => ['required', 'max:25'], 'email' => ['required', 'email', 'unique:users,email,'.$user->id], 'kelas_id' => ['required', 'exists:kelas,id'], 'school_id' => ['nullable','exists:schools,id'], 'role' => ['required', 'in:guru,siswa']]);
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

        try {
            $user->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Akun tidak dapat dihapus karena masih terhubung ke data lain (nilai, tugas, materi, pengumpulan, dst). Pindahkan atau hapus data terkait terlebih dahulu.');
        }

        return back()->with('success', 'Akun berhasil dihapus permanen beserta seluruh data tautannya.');
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

    public function userHistory(Request $request): View
    {
        $search = trim((string) $request->search);
        $typeFilter = $request->type;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        // Ringan: whereHas langsung + select minimal, hindari pluck besar
        $query = UserHistory::with(['user:id,name,email,role,foto'])
            ->whereHas('user', fn($q) => $q->whereIn('role', ['guru','siswa']))
            ->latest('user_histories.created_at');

        if ($search !== '') {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($typeFilter) $query->ofType($typeFilter);
        if ($dateFrom) $query->whereDate('user_histories.created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('user_histories.created_at', '<=', $dateTo);

        $histories = $query->paginate(20)->withQueryString();

        // Ringan + cache 60 detik untuk stats & chart — DB-agnostic
        $cacheKey = 'admin_history_stats_'.md5(json_encode([$search,$typeFilter,$dateFrom,$dateTo]));
        [$activityTypes, $stats, $dailyActivity] = cache()->remember($cacheKey, 60, function () {
            $types = UserHistory::distinct()->pluck('activity_type')->filter()->values();
            $driver = \Illuminate\Support\Facades\DB::getDriverName();
            $dateExpr = $driver === 'sqlite' ? "strftime('%Y-%m-%d', created_at)" : "DATE(created_at)";
            try {
                $daily = UserHistory::where('created_at', '>=', now()->subDays(30))
                    ->selectRaw("$dateExpr as date, COUNT(*) as count")
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
            } catch (\Throwable $e) {
                $daily = collect();
            }
            $stats = [
                'total' => UserHistory::count(),
                'today' => UserHistory::whereDate('created_at', today())->count(),
                'activeUsers' => UserHistory::whereDate('created_at', today())->distinct('user_id')->count('user_id'),
                'logins' => UserHistory::ofType('login')->whereDate('created_at', today())->count(),
            ];
            return [$types, $stats, $daily];
        });

        return view($this->isMobileRequest() ? 'mobile.admin-history' : 'admin.history', [
            'histories' => $histories,
            'activityTypes' => $activityTypes,
            'stats' => $stats,
            'dailyActivity' => $dailyActivity,
        ]);
    }
}
