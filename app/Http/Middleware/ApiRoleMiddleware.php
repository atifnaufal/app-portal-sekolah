<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiRoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if (Auth::guard('web')->check()) {
                $user = Auth::guard('web')->user();
            }
        }

        if (! $user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Anda harus login terlebih dahulu.',
            ], 401);
        }

        $userRole = $user->role ?? $request->session()->get('user_role');

        if (! $userRole || ! in_array($userRole, $roles, true)) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Kamu tidak memiliki akses ke halaman ini.',
            ], 403);
        }

        return $next($request);
    }
}