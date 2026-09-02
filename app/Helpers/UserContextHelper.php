<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserContextHelper
{
    /**
     * Resolve the authenticated user's id for both web (session) and Sanctum API requests.
     *
     * Precedence:
     *  1. $request->user() (Sanctum token / API guard)
     *  2. session('user_id')
     *  3. Auth::id() / Auth::guard('web')->id()
     *
     * @param Request|null $request If omitted, the current request (request()) is used.
     * @return int|null
     */
    public static function id(?Request $request = null): ?int
    {
        $request = $request ?? request();

        $apiUser = $request->user();
        if ($apiUser) {
            return $apiUser->id;
        }

        $sessionUserId = $request->session()->get('user_id');
        if ($sessionUserId) {
            return (int) $sessionUserId;
        }

        $id = Auth::id();
        if ($id) {
            return (int) $id;
        }

        $guardId = Auth::guard('web')->id();
        if ($guardId) {
            return (int) $guardId;
        }

        return null;
    }

    /**
     * Resolve the authenticated User model (with 'kelas' relation loaded) or null.
     */
    public static function user(?Request $request = null): ?User
    {
        $userId = self::id($request);

        if (! $userId) {
            return null;
        }

        return User::with('kelas')->find($userId);
    }

    /**
     * Resolve the current authenticated user's role, from the API user / web session.
     */
    public static function role(?Request $request = null): ?string
    {
        $request = $request ?? request();

        $apiUser = $request->user();
        if ($apiUser) {
            return $apiUser->role;
        }

        $role = $request->session()->get('user_role');
        if ($role) {
            return $role;
        }

        return optional(Auth::user())->role;
    }

    /**
     * Abort with 401 (JSON for API/expectsJson requests, redirect/abort otherwise) when
     * the current user cannot be resolved.
     */
    public static function abortUnauthorized(?Request $request = null)
    {
        $request = $request ?? request();

        if ($request->expectsJson() || $request->is('api/*')) {
            abort(response()->json(['message' => 'Unauthorized'], 401));
        }

        abort(401);
    }
}
