<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        abort_unless((bool) Setting::getValue('registration_enabled', false), 404);
        return view('auth.register', ['kelases' => Kelas::orderBy('tingkat')->orderBy('nama')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless((bool) Setting::getValue('registration_enabled', false), 404);
        $data = $request->validate([
            'role' => ['required', 'in:guru,siswa'], 'nik' => ['required', 'digits_between:8,30', 'unique:users,nik'],
            'name' => ['required', 'max:255'], 'no_hp' => ['required', 'max:25'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'kelas_id' => ['required', 'exists:kelas,id'], 'password' => ['required', 'min:8', 'confirmed'],
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['aktif'] = true;
        $user = User::create($data);

        event(new \Illuminate\Auth\Events\Registered($user));

        return redirect()->route('login')->with('success', 'Registrasi berhasil. Silakan cek email Anda untuk verifikasi sebelum login.');
    }
}
