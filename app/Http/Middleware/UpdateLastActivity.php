<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only for authenticated users, skip polling endpoints to avoid double-write
        if (in_array($request->path(), ['session/status', 'notifikasi/poll'])) {
            return $response;
        }

        $userId = $request->session()->get('user_id') ?: Auth::id();
        if ($userId) {
            // Throttle: update at most once per 30s per user to avoid DB spam
            $cacheKey = 'last_activity_'.$userId;
            if (! cache()->has($cacheKey)) {
                try {
                    \App\Models\User::where('id', $userId)->update(['last_activity_at' => now()]);
                } catch (\Throwable $e) {
                    // ignore
                }
                cache()->put($cacheKey, true, 30);
            }
        }

        return $response;
    }
}
