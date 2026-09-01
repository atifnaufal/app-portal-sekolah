<?php

namespace App\Http\Controllers;

use App\Helpers\UserHistoryHelper;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
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
                        ? 'Akun Anda belum disetujui admin, atau sedang dinonaktifkan. Hubungi admin IT sekolah.'
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

        // Persistent login untuk SEMUA role (default ON). Dengan remember-token
        // yang berumur panjang, sesi tetap hidup layaknya aplikasi native sampai
        // user benar-benar logout — bahkan saat tab/popup ditutup.
        $remember = $request->boolean('remember', true);
        Auth::login($user, $remember);

        // Generate Sanctum token for mobile polling
        $token = $user->createToken('mobile-app')->plainTextToken;
        $request->session()->put('api_token', $token);

        // Pastikan session langsung disimpan
        $request->session()->save();

        UserHistoryHelper::logLogin($user->id, $request);
        $user->updateQuietly(['status' => 'aktif', 'last_activity_at' => now()]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            UserHistoryHelper::logLogout($user->id, $request);
            $user->updateQuietly(['status' => 'terdaftar']);
            $user->tokens()->delete();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email reset password: '.$e->getMessage());

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Gagal mengirim email reset. Silakan coba beberapa saat lagi atau hubungi admin IT sekolah.');
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Link reset sudah dikirim ke email Anda.')
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email tersebut tidak terdaftar di sistem kami.']);
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => null,
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        return redirect()->route('login')->with('success', 'Password berhasil diganti. Silakan masuk.');
    }

    public function showForgotEmail(): View
    {
        return view('auth.forgot-email');
    }

    public function findEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'nik' => ['required', 'string'],
            'no_hp' => ['required', 'string'],
        ]);

        $user = User::where('nik', $request->nik)
            ->where('no_hp', $request->no_hp)
            ->first();

        if (! $user) {
            return back()->with('error', 'Data tidak ditemukan. Pastikan NIK dan No HP benar.');
        }

        return back()->with('found_email', $user->email);
    }
}
