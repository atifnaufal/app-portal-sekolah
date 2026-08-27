<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Jika session kosong tapi user ter-autentikasi (via remember me), restorasi session
        if (!$request->session()->has('user_id') && Auth::check()) {
            $user = Auth::user();

            // Restorasi data penting ke session
            $request->session()->put([
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_kelas_id' => $user->kelas_id,
                'admin_name' => $user->name,
            ]);
        }

        $userId = $request->session()->get('user_id');
        $userRole = $request->session()->get('user_role');

        // 2. Jika masih tidak ada userId di session
        if (!$userId) {
            // Cek sekali lagi via Auth guard untuk memastikan (siapa tahu sesi baru saja ter-autentikasi)
            if (Auth::check()) {
                $user = Auth::user();
                $userId = $user->id;
                $userRole = $user->role;

                // Isi kembali session agar request berikutnya lancar
                $request->session()->put([
                    'user_id' => $user->id,
                    'user_role' => $user->role,
                    'user_kelas_id' => $user->kelas_id,
                    'admin_name' => $user->name,
                ]);
            } else {
                return redirect()
                    ->route('login')
                    ->with('error', 'Silakan login terlebih dahulu.');
            }
        }

        if (!in_array($userRole, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }

}
