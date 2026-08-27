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
        $needsRestore = !$request->session()->has('user_id')
            || !$request->session()->has('user_role');

        if ($needsRestore && Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            $request->session()->put([
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_kelas_id' => $user->kelas_id,
                'admin_name' => $user->name,
            ]);

            $request->session()->save();
        }

        $userId = $request->session()->get('user_id');
        $userRole = $request->session()->get('user_role');

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
