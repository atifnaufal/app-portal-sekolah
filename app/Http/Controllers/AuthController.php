<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

public function login(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    $user = User::where('email', $credentials['email'])->first();

    if (
        ! $user ||
        ! $user->aktif ||
        ! Hash::check($credentials['password'], $user->password)
    ) {
        return back()
            ->withInput($request->only('email'))
            ->with('error',
                $user && ! $user->aktif
                    ? 'Akun sedang dinonaktifkan oleh admin.'
                    : 'Email atau password salah.'
            );
    }

    // Regenerasi session setelah login berhasil
    $request->session()->regenerate();

    // Simpan data login ke session
    $request->session()->put([
        'user_id' => $user->id,
        'user_role' => $user->role,
        'user_kelas_id' => $user->kelas_id,
        'admin_name' => $user->name,
    ]);

    // Gunakan Auth::login dengan remember=true untuk sesi persisten
    Auth::login($user, true);

    // Pastikan session langsung disimpan
    $request->session()->save();

    return redirect()->intended(route('dashboard'));
}
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
