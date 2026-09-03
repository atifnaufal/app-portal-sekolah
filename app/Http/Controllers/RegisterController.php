<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
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
            'password' => ['required', 'min:8', 'confirmed'],
        ]);
        $school = \App\Models\School::find($data['school_id']);
        if (! $school || ! $school->is_active) {
            return back()->withErrors(['school_id' => 'Sekolah tidak aktif / ID tidak diberi akses. Hubungi Admin Pusat.'])->withInput();
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

        return redirect()->route('login')->with('success',
            'Pendaftaran berhasil. Akun Anda menunggu persetujuan admin sebelum bisa digunakan.');
    }
}
