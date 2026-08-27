<?php

namespace App\Helpers;

use App\Models\Notifikasi;
use App\Models\User;
use App\Events\NotificationEvent;

class NotificationHelper
{
    /**
     * Send a notification to a specific user.
     */
    public static function send($userId, $title, $message, $url = null, $type = 'general')
    {
        Notifikasi::create([
            'user_id' => $userId,
            'judul' => $title,
            'pesan' => $message,
            'url' => $url,
            'dibaca_pada' => null
        ]);

        // Trigger real-time event
        event(new NotificationEvent($title, $message, $type));
    }

    /**
     * Send a notification to all students in a specific class.
     */
    public static function sendToClass($kelasId, $title, $message, $url = null, $type = 'general')
    {
        $users = User::where('kelas_id', $kelasId)->get();
        foreach ($users as $user) {
            self::send($user->id, $title, $message, $url, $type);
        }
    }

    /**
     * Send a notification to all users or specific roles.
     */
    public static function sendToAll($title, $message, $url = null, $type = 'general', $role = null, $excludeUserId = null)
    {
        $query = User::query();
        if ($role) {
            $query->where('role', $role);
        }
        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        $users = $query->get();
        foreach ($users as $user) {
            self::send($user->id, $title, $message, $url, $type);
        }
    }
}
