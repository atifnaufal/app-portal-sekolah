<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockAdminOnMobile
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('user_role') !== 'admin') {
            return $next($request);
        }

        $userAgent = (string) $request->userAgent();
        $isMobile = (bool) preg_match('/(android|iphone|ipad|mobile)/i', $userAgent);
        $wantsAppShell = $request->boolean('app');

        if ($isMobile && ! $wantsAppShell) {
            return redirect()->route('dashboard')
                ->with('info', 'Menu admin lengkap hanya tersedia di versi Desktop/Web.');
        }

        return $next($request);
    }
}
