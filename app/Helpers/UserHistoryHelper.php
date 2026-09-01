<?php

namespace App\Helpers;

use App\Models\UserHistory;
use Illuminate\Http\Request;

class UserHistoryHelper
{
    public static function log(
        int $userId,
        string $activityType,
        string $description,
        array $metadata = null,
        ?float $lat = null,
        ?float $long = null,
        ?Request $request = null,
    ): UserHistory {
        return UserHistory::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'description' => $description,
            'metadata' => $metadata,
            'lat' => $lat,
            'long' => $long,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'device_info' => self::parseDevice($request),
        ]);
    }

    public static function logLogin(int $userId, ?Request $request = null): UserHistory
    {
        return self::log($userId, 'login', 'User berhasil login', null, null, null, $request);
    }

    public static function logLogout(int $userId, ?Request $request = null): UserHistory
    {
        return self::log($userId, 'logout', 'User logout dari aplikasi', null, null, null, $request);
    }

    public static function logAbsensi(
        int $userId,
        string $tipe,
        string $status,
        ?float $lat = null,
        ?float $long = null,
        ?Request $request = null,
    ): UserHistory {
        return self::log(
            $userId,
            'absensi',
            "Absensi {$tipe}: {$status}",
            ['tipe' => $tipe, 'status' => $status],
            $lat,
            $long,
            $request,
        );
    }

    public static function logProfileUpdate(int $userId, array $changes, ?Request $request = null): UserHistory
    {
        return self::log(
            $userId,
            'profile_update',
            'Profil berhasil diperbarui',
            ['changes' => $changes],
            null,
            null,
            $request,
        );
    }

    public static function logPageView(int $userId, string $page, ?Request $request = null): UserHistory
    {
        return self::log($userId, 'page_view', 'Melihat halaman: '.$page, ['page' => $page], null, null, $request);
    }

    public static function logSessionStart(int $userId, ?float $lat = null, ?float $long = null, ?Request $request = null): UserHistory
    {
        return self::log($userId, 'session_start', 'Sesi aplikasi dimulai', null, $lat, $long, $request);
    }

    public static function parseDevice(?Request $request): ?string
    {
        if (! $request) {
            return null;
        }
        $ua = $request->userAgent();
        if (! $ua) {
            return null;
        }

        $os = 'Unknown OS';
        if (str_contains($ua, 'Android')) {
            preg_match('/Android ([\d.]+)/', $ua, $m);
            $os = 'Android '.($m[1] ?? '');
        } elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            $os = 'iOS';
        } elseif (str_contains($ua, 'Windows')) {
            $os = 'Windows';
        }

        $browser = 'Unknown Browser';
        if (str_contains($ua, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'Safari')) {
            $browser = 'Safari';
        } elseif (str_contains($ua, 'CapacitorWebView') || str_contains($ua, 'Capacitor')) {
            $browser = 'Capacitor WebView';
        }

        return $os.' / '.$browser;
    }
}
