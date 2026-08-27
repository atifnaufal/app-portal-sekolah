<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalOnly
{
    /**
     * Tolak akses ke rute khusus pengembangan di luar lingkungan lokal.
     * Mengembalikan 404 agar keberadaan endpoint tidak terungkap di produksi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->environment('local'), 404);

        return $next($request);
    }
}
