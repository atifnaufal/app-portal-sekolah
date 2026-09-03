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

// Channel private per-GRUP chat untuk pesan realtime. Hanya member approved
// yang boleh subscribe (pending/undangan-belum-approve tidak boleh masuk).
Broadcast::channel('portal-chat-group.{groupId}', function ($user, $groupId) {
    $group = \App\Models\ChatGroup::find($groupId);
    if (! $group) {
        return false;
    }

    return $group->isApprovedMember((int) $user->id);
});
