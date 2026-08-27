<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel private per-user untuk notifikasi real-time.
Broadcast::channel('portal-notifications.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel private per-kelas untuk chat real-time (hanya Guru & Siswa kelas tsb).
Broadcast::channel('portal-chat.{kelasId}', function ($user, $kelasId) {
    if (! in_array($user->role, ['guru', 'siswa'], true)) {
        return false;
    }

    if ($user->role === 'siswa') {
        return (int) $user->kelas_id === (int) $kelasId;
    }

    return true;
});
