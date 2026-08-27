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

        if (! $user && $request->session()->has('user_id')) {
            $user = \App\Models\User::find($request->session()->get('user_id'));
        }

        if ($user && in_array($user->role, ['admin', 'guru'], true)) {
            return $next($request);
        }

        if (! $user ||
            ($user instanceof MustVerifyEmail &&
            ! $user->hasVerifiedEmail())) {
            return $request->expectsJson()
                ? abort(403, 'Your email address is not verified.')
                : Redirect::guest(URL::route($redirectToRoute ?: 'verification.notice'));
        }

        return $next($request);
    }
}
