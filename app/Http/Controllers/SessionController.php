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
        $userId = $request->session()->get('user_id');
        $role = $request->session()->get('user_role');

        if (! $userId || ! $role) {
            return response()->json([
                'authenticated' => false,
                'redirect' => route('login'),
            ]);
        }

        $unread = Notifikasi::where('user_id', $userId)->whereNull('dibaca_pada')->count();
        $user = User::find($userId);
        $name = $user ? explode(' ', $user->name)[0] : '';

        // Perbarui last_activity (session handler database melakukannya otomatis
        // saat request ini berjalan); panggilan ini eksplisit agar selalu segar.
        $request->session()->put('user_last_activity', now()->timestamp);

        return response()->json([
            'authenticated' => true,
            'user_id' => (int) $userId,
            'role' => $role,
            'name' => $name,
            'unread' => (int) $unread,
            'now' => now()->toIso8601String(),
        ]);
    }
}
