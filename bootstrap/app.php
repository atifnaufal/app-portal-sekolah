<?php

use App\Http\Middleware\ApiRoleMiddleware;
use App\Http\Middleware\BlockAdminOnMobile;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\UpdateLastActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // MENGGUNAKAN ROLE MIDDLEWARE CUSTOM:
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'api.role' => ApiRoleMiddleware::class,
            'admin.desktop' => BlockAdminOnMobile::class,
            'last.activity' => UpdateLastActivity::class,
        ]);
        $middleware->appendToGroup('web', UpdateLastActivity::class);

    })

    ->withExceptions(function (Exceptions $exceptions): void {
        // API: always return JSON
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Map HTTP exceptions to custom error pages
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Not found'], 404);
            }
            return response()->view('errors.404', [], 404);
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            return response()->view('errors.403', [], 403);
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Too many requests'], 429);
            }
            return response()->view('errors.429', [], 429);
        });

        // 419 Page Expired (CSRF)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->is('api/*') || $request->expectsJson()) {
                    return response()->json(['message' => 'Page expired'], 419);
                }
                return response()->view('errors.419', [], 419);
            }
            if ($e->getStatusCode() === 503) {
                if ($request->is('api/*') || $request->expectsJson()) {
                    return response()->json(['message' => 'Service unavailable'], 503);
                }
                return response()->view('errors.503', [], 503);
            }
        });

        // Catch-all: uncaught exceptions → 500 (but don't swallow known HttpExceptions)
        $exceptions->renderable(function (\Throwable $e, Request $request) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) return null;
            if ($e instanceof \Illuminate\Auth\AuthenticationException) return null;
            if ($e instanceof \Illuminate\Validation\ValidationException) return null;
            \Illuminate\Support\Facades\Log::error('Uncaught exception: ' . $e->getMessage(), [
                'file' => $e->getFile(), 'line' => $e->getLine(),
            ]);
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Terjadi kesalahan sistem'], 500);
            }
            return response()->view('errors.500', [], 500);
        });

        // Disable detailed error reporting to users
        $exceptions->dontReport([
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Validation\ValidationException::class,
        ]);
    })->create();
