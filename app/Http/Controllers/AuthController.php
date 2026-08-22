<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]);
        $user = User::where('email', $credentials['email'])->first();
        if (! $user || ! $user->aktif || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withInput()->with('error', $user && ! $user->aktif ? 'Akun sedang dinonaktifkan oleh admin.' : 'Email atau password salah.');
        }
        $request->session()->regenerate();
        $request->session()->put(['user_id' => $user->id, 'user_role' => $user->role, 'user_kelas_id' => $user->kelas_id, 'admin_name' => $user->name]);
        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
