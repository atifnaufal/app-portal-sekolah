<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class VerifiedExceptAdmin
{
    public function handle(Request $request, Closure $next, $redirectToRoute = null): Response
    {
        $user = $request->user();

        // 1. Jika User adalah Admin atau Guru, bypass verifikasi email
        if ($user && in_array($user->role, ['admin', 'guru'], true)) {
            return $next($request);
        }

        // 2. Jika bukan admin/guru (yakni siswa), lakukan pengecekan standar verifikasi
        if (!$user ||
            ($user instanceof MustVerifyEmail &&
            !$user->hasVerifiedEmail())) {
            return $request->expectsJson()
                ? abort(403, 'Your email address is not verified.')
                : Redirect::guest(URL::route($redirectToRoute ?: 'verification.notice'));
        }

        return $next($request);
    }
}
