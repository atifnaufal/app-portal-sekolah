<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
{
    $userId = $request->session()->get('user_id');
    $userRole = $request->session()->get('user_role');

    if (! $userId) {
        return redirect()
            ->route('login')
            ->with('error', 'Silakan login terlebih dahulu.');
    }

    if (! in_array($userRole, $roles, true)) {
        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }

    return $next($request);
}
    
}
