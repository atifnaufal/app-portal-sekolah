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
        // Admin Pusat (super admin) bisa lihat per-sekolah, admin sekolah otomatis terfilter sekolahnya
        $me = \App\Models\User::find(session('user_id') ?? auth()->id());
        $isSuper = $me && $me->isSuperAdmin();
        $filterSchoolId = $isSuper ? request('school_id') : ($me->school_id ?? null);
        $filterSchoolId = $filterSchoolId ? (int)$filterSchoolId : null;
        // Cache dimatikan sementara untuk hindari serialisasi model bug — gunakan index DB untuk ringan

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

        $uQ = fn($q)=> $filterSchoolId ? $q->where('school_id',$filterSchoolId) : $q;
        $data = [
            'totalGuru' => $uQ(User::where('role', 'guru'))->count(),
            'totalSiswa' => $uQ(User::where('role', 'siswa'))->count(),
            'totalKelas' => Kelas::count(),
            'sppKurang' => Spp::where('status', 'belum_lunas')->count(),
            'pendingCount' => $uQ(User::where('aktif', false))->count(),
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

        // Global Portal premium stats (filter per sekolah jika super admin pilih)
        $gpQ = fn($q)=> $filterSchoolId ? $q->where('school_id',$filterSchoolId) : $q;
        $data['totalGlobalPosts'] = $gpQ(GlobalPost::query())->count();
        $data['globalPostsHariIni'] = $gpQ(GlobalPost::whereDate('created_at', today()))->count();
        $data['totalGlobalLikes'] = \DB::table('global_likes')->count();
        $data['totalGlobalComments'] = \DB::table('global_comments')->count();
        $data['totalSchools'] = School::count();
        $data['topSchool'] = School::withCount('posts')->orderByDesc('posts_count')->first();
        $data['recentGlobalPosts'] = $gpQ(GlobalPost::with(['user','school'])->latest())->take(4)->get();
        $meForView = \App\Models\User::find(session('user_id') ?? auth()->id());
        $data['user'] = $meForView;
        $data['filterSchoolId'] = $filterSchoolId;
        $data['isSuperAdmin'] = $meForView && $meForView->isSuperAdmin();
        $data['allSchools'] = School::withCount(['users','posts'])->orderBy('name')->get();
        $data['filterSchool'] = $filterSchoolId ? School::withCount(['users','posts'])->find($filterSchoolId) : null;

        return view($isMobile ? 'mobile.admin-dashboard' : 'admin.dashboard', $data);
    }

    private function isMobileRequest(): bool
    {
        return (bool) preg_match('/(android|iphone|ipad|mobile)/i', (string) request()->userAgent());
    }

    public function users(Request $request): View
    {
        $search = $request->search;
        $me = User::find(session('user_id') ?? auth()->id());
        $isSuper = $me && $me->isSuperAdmin();
        // Admin sekolah hanya lihat user sekolahnya sendiri
        $schoolFilter = (!$isSuper && $me && $me->school_id) ? $me->school_id : (request('school_id') ? (int)request('school_id') : null);

        $usersQuery = User::with(['kelas', 'mataPelajarans.kelas', 'school'])
            ->whereIn('role', ['guru', 'siswa'])
            ->when($schoolFilter, fn($q,$sid)=> $q->where('school_id',$sid))
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

    // === Sekolah — premium admin only (publik bisa daftar jika is_active) ===
    public function schoolsIndex(): View
    {
        $me = User::find(session('user_id') ?? auth()->id());
        abort_unless(($me && $me->isSuperAdmin()) || session('user_role')==='admin',403);
        $schools = School::withCount(['users','posts'])->orderBy('name')->get();
        return view('admin.schools', compact('schools'));
    }
    public function schoolsStore(Request $request): RedirectResponse
    {
        $me = User::find(session('user_id') ?? auth()->id());
        abort_unless(($me && $me->isSuperAdmin()) || session('user_role')==='admin',403);
        $data=$request->validate(['name'=>['required','max:100'],'city'=>['nullable','max:50'],'slug'=>['required','max:50','unique:schools,slug'],'is_active'=>['nullable','boolean']]);
        $data['is_active']=$request->boolean('is_active',true);
        School::create($data);
        return back()->with('success','Sekolah ditambahkan');
    }
    public function schoolsUpdate(Request $request, School $school): RedirectResponse
    {
        $me = User::find(session('user_id') ?? auth()->id());
        abort_unless(($me && $me->isSuperAdmin()) || session('user_role')==='admin',403);
        $data=$request->validate(['name'=>['required','max:100'],'city'=>['nullable','max:50'],'slug'=>['required','max:50','unique:schools,slug,'.$school->id],'is_active'=>['nullable','boolean']]);
        $data['is_active']=$request->boolean('is_active');
        $school->update($data);
        return back()->with('success','Sekolah diperbarui');
    }
    public function schoolsDestroy(School $school): RedirectResponse
    {
        $me = User::find(session('user_id') ?? auth()->id());
        abort_unless($me && $me->isSuperAdmin(),403);
        if($school->users()->exists()) return back()->with('error','Tidak bisa hapus sekolah yang masih punya user');
        $school->delete();
        return back()->with('success','Sekolah dihapus');
    }
    public function schoolsToggle(School $school): RedirectResponse
    {
        $me = User::find(session('user_id') ?? auth()->id());
        abort_unless(($me && $me->isSuperAdmin()) || session('user_role')==='admin',403);
        $school->update(['is_active'=>!$school->is_active]);
        return back()->with('success',$school->is_active?'Sekolah diaktifkan — pendaftaran dibuka':'Sekolah dinonaktifkan — pendaftaran ditutup');
    }
    // Admin pusat bisa buat akun admin sekolah
    public function createSchoolAdmin(Request $request): RedirectResponse
    {
        $me = User::find(session('user_id') ?? auth()->id());
        abort_unless($me && $me->isSuperAdmin(),403);
        $data=$request->validate(['name'=>['required','max:100'],'email'=>['required','email','unique:users,email'],'password'=>['required','min:8','confirmed'],'school_id'=>['required','exists:schools,id']]);
        \App\Models\User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>\Illuminate\Support\Facades\Hash::make($data['password']),'role'=>'admin','school_id'=>$data['school_id'],'aktif'=>true,'nik'=>'ADM'.rand(1000,9999),'no_hp'=>'08'.rand(1000000000,9999999999)]);
        return back()->with('success','Admin sekolah dibuat');
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
