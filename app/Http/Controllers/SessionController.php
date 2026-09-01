<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Endpoint ringan untuk "heartbeat" sesi.
     *
     * Dipanggil berkala dari frontend (mobile & desktop) supaya:
     *  - last_activity sesi terus di-slide (tetap login seperti aplikasi native),
     *  - sesi yang sudah mati/expired terdeteksi langsung (JSON) sehingga
     *    aplikasi bisa mengarahkan ke halaman login secara realtime (tanpa
     *    menunggu refresh halaman).
     * Sekaligus mengembalikan jumlah notifikasi belum dibaca untuk badge live.
     */
    public function status(Request $request): JsonResponse
    {
        $userId = $request->user()?->id ?: $request->session()->get('user_id');
        $role = $request->user()?->role ?: $request->session()->get('user_role');

        if (!$userId || !$role) {
            return response()->json([
                'authenticated' => false,
                'redirect' => route('login'),
            ]);
        }

        $unread = Notifikasi::where('user_id', $userId)->whereNull('dibaca_pada')->count();
        $user = User::find($userId);
        $name = $user ? explode(' ', $user->name)[0] : '';

        if ($request->hasSession()) {
            $request->session()->put('user_last_activity', now()->timestamp);
        }

        if ($user) {
            $user->updateQuietly(['last_activity_at' => now()]);
            $user->refresh();
        }

        return response()->json([
            'authenticated' => true,
            'user_id' => (int) $userId,
            'role' => $role,
            'name' => $name,
            'unread' => (int) $unread,
            'is_online' => $user ? $user->isOnline() : false,
            'last_seen' => $user ? $user->last_seen : '-',
            'status_label' => $user ? $user->status_label : 'offline',
            'status_badge' => $user ? $user->status_badge : 'offline',
            'now' => now()->toIso8601String(),
        ]);
    }
}
