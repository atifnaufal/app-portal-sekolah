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
        $userId = $request->session()->get('user_id');
        $userRole = $request->session()->get('user_role');

        // Sesi login "hilang" (cookie/waktu habis) atau tidak lengkap.
        // Coba pulihkan otomatis dari Auth guard (remember-token) supaya user
        // TETAP login seperti aplikasi native sampai dia logout sendiri.
        if (!$userId || !$userRole) {
            if (Auth::guard('web')->check()) {
                $user = Auth::guard('web')->user();

                $request->session()->put([
                    'user_id' => $user->id,
                    'user_role' => $user->role,
                    'user_kelas_id' => $user->kelas_id,
                    'admin_name' => $user->name,
                ]);

                $request->session()->save();

                $userId = $user->id;
                $userRole = $user->role;
            }
        }

        // Bahkan saat sesi ada, pastikan data user tersinkron bila guard sudah
        // terautentikasi (mengantisipasi sesi parsial pasca-restore).
        if (Auth::guard('web')->check() && $request->session()->get('user_role') !== Auth::guard('web')->user()->role) {
            $u = Auth::guard('web')->user();
            $request->session()->put([
                'user_id' => $u->id,
                'user_role' => $u->role,
                'user_kelas_id' => $u->kelas_id,
                'admin_name' => $u->name,
            ]);
            $request->session()->save();
        }

        if (!$userId) {
            return redirect()
                ->route('login')
                ->with('error', 'Sesi berakhir. Silakan login kembali.');
        }

        if (!$userRole || !in_array($userRole, $roles, true)) {
            return redirect()
                ->route('login')
                ->with('error', 'Sesi tidak lengkap. Silakan login kembali.');
        }

        return $next($request);
    }
}
