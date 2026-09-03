<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /** Cek Kode Pendaftaran umum → detail sekolah (untuk alur kode-dulu). */
    public function check(Request $request)
    {
        $code = trim((string) $request->query('code', ''));
        abort_if($code === '', 400, 'Kode kosong.');

        $school = \App\Models\School::where('enroll_code', $code)->where('is_active', true)->first(
            ['id', 'name', 'city', 'slug', 'enroll_code', 'reg_guru_open', 'reg_siswa_open']
        );
        abort_if(! $school, 404, 'Kode tidak ditemukan / sekolah nonaktif.');
        abort_unless($school->reg_guru_open || $school->reg_siswa_open, 423, 'Pendaftaran sekolah ini sedang ditutup.');

        return response()->json([
            'id' => $school->id,
            'name' => $school->name,
            'city' => $school->city,
            'slug' => $school->slug,
            'enroll_code' => $school->enroll_code,
            'reg_guru_open' => (bool) $school->reg_guru_open,
            'reg_siswa_open' => (bool) $school->reg_siswa_open,
        ]);
    }

    public function create()
    {
        // Pendaftaran dibuka PER SEKOLAH (kolom schools.reg_*_open, dikelola
        // Admin Pusat & admin sekolah). Halaman ada jika min. satu sekolah buka.
        // Fallback global bila migrasi belum jalan (anti-500 saat deploy).
        if (! \Illuminate\Support\Facades\Schema::hasColumn('schools', 'reg_guru_open')) {
            $guruEnabled = (bool) \App\Models\Setting::getValue('registration_guru_enabled', false);
            $siswaEnabled = (bool) \App\Models\Setting::getValue('registration_siswa_enabled', false);

            if (! $guruEnabled && ! $siswaEnabled) {
                return redirect()->route('feature.locked', ['msg' => 'Pendaftaran akun sedang ditutup. Hubungi admin sekolah atau Admin Pusat.']);
            }

            return view('auth.register', [
                'kelases' => Kelas::orderBy('tingkat')->orderBy('nama')->get(),
                'schools' => \App\Models\School::where('is_active', true)->orderBy('name')->get(),
                'guruEnabled' => $guruEnabled,
                'siswaEnabled' => $siswaEnabled,
                'perSchoolReg' => false,
            ]);
        }

        $schools = \App\Models\School::where('is_active', true)
            ->where(fn ($q) => $q->where('reg_guru_open', true)->orWhere('reg_siswa_open', true))
            ->orderBy('name')
            ->get(['id', 'name', 'city', 'reg_guru_open', 'reg_siswa_open']);

        if ($schools->isEmpty()) {
            return redirect()->route('feature.locked', ['msg' => 'Pendaftaran akun sedang ditutup untuk semua sekolah. Hubungi admin sekolah atau Admin Pusat.']);
        }

        return view('auth.register', [
            'kelases' => Kelas::orderBy('tingkat')->orderBy('nama')->get(),
            'schools' => $schools,
            'guruEnabled' => $schools->contains(fn ($s) => $s->reg_guru_open),
            'siswaEnabled' => $schools->contains(fn ($s) => $s->reg_siswa_open),
            'perSchoolReg' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:guru,siswa'],
            'nik' => ['required', 'digits_between:8,30', 'unique:users,nik'],
            'name' => ['required', 'max:255'],
            'no_hp' => ['required', 'max:25'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'school_id' => ['required', 'exists:schools,id'],
            'enroll_code' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);
        $school = \App\Models\School::find($data['school_id']);
        if (! $school || ! $school->is_active) {
            return back()->withErrors(['school_id' => 'Sekolah tidak aktif / ID tidak diberi akses. Hubungi Admin Pusat.'])->withInput();
        }
        if (! empty($data['enroll_code']) && $data['enroll_code'] !== $school->enroll_code) {
            return back()->withErrors(['enroll_code' => 'Kode Pendaftaran tidak cocok dengan sekolah terpilih.'])->withInput();
        }

        if (! \App\Helpers\FeatureHelper::registrationOpen($school->id, $data['role'])) {
            $label = $data['role'] === 'guru' ? 'Guru' : 'Siswa';

            return back()->withErrors(['role' => "Pendaftaran {$label} untuk {$school->name} sedang DITUTUP oleh admin."])->withInput();
        }

        $data['password'] = Hash::make($data['password']);

        // Akun baru TIDAK langsung aktif. Admin harus menyetujuinya lewat
        // halaman Manajemen Akun. Kolom `aktif` menggantikan verifikasi email.
        $data['aktif'] = false;
        $user = User::create($data);

        // Wajibkan membaca Syarat & Ketentuan sebelum ke login.
        return redirect()->route('register.syarat')->with('registered_name', $user->name);
    }

    /** Halaman Syarat & Ketentuan pasca-pendaftaran (wajib dibaca + disetujui). */
    public function syarat()
    {
        return view('auth.syarat', ['name' => session('registered_name')]);
    }
}
