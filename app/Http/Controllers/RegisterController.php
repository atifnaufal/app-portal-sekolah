<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        $guruEnabled = (bool) Setting::getValue('registration_guru_enabled', false);
        $siswaEnabled = (bool) Setting::getValue('registration_siswa_enabled', false);

        abort_unless($guruEnabled || $siswaEnabled, 404);

        return view('auth.register', [
            'kelases' => Kelas::orderBy('tingkat')->orderBy('nama')->get(),
            'schools' => \App\Models\School::where('is_active',true)->orderBy('name')->get(),
            'guruEnabled' => $guruEnabled,
            'siswaEnabled' => $siswaEnabled,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $guruEnabled = (bool) Setting::getValue('registration_guru_enabled', false);
        $siswaEnabled = (bool) Setting::getValue('registration_siswa_enabled', false);

        abort_unless($guruEnabled || $siswaEnabled, 404);

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
        if(!$school || !$school->is_active){ return back()->withErrors(['school_id'=>'Sekolah tidak aktif / ID tidak diberi akses. Hubungi Admin Pusat.'])->withInput(); }

        if ($data['role'] === 'guru' && ! $guruEnabled) {
            return back()->withErrors(['role' => 'Pendaftaran Guru sedang dinonaktifkan.'])->withInput();
        }

        if ($data['role'] === 'siswa' && ! $siswaEnabled) {
            return back()->withErrors(['role' => 'Pendaftaran Siswa sedang dinonaktifkan.'])->withInput();
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
