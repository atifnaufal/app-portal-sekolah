<?php

namespace App\Helpers;

use App\Events\NotificationEvent;
use App\Models\ChatGroup;
use App\Models\Notifikasi;
use App\Models\User;

class NotificationHelper
{
    /**
     * Send a notification to a specific user.
     */
    public static function send($userId, $title, $message, $url = null, $type = 'general', $actorName = null, $actorPhoto = null)
    {
        Notifikasi::create([
            'user_id' => $userId,
            'judul' => $title,
            'pesan' => $message,
            'url' => $url,
            'dibaca_pada' => null,
            'type' => $type,
            'actor_name' => $actorName,
            'actor_photo' => $actorPhoto,
        ]);

        try {
            event(new NotificationEvent($userId, $title, $message, $type, $actorName, $actorPhoto));
        } catch (\Throwable $e) {
            // Broadcast watchdog error (mis. Reverb belum ready / kredensial salah saat
            // cold-start Railway) TIDAK boleh menggagalkan aksi yang sudah sukses.
            // Notifikasi tetap tersimpan di DB dan terbaca di menu notifikasi.
            \Illuminate\Support\Facades\Log::warning('Broadcast notifikasi gagal: '.$e->getMessage());
        }

        // Push native (FCM) — best-effort, gagal aman. Jalan bila Firebase
        // dikonfigurasi DAN user punya token perangkat terdaftar.
        try {
            \App\Services\FcmService::sendToUser((int) $userId, $title, $message, [
                'type' => (string) $type,
                'url' => (string) ($url ?? ''),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('FCM hook gagal: '.$e->getMessage());
        }
    }

    /**
     * Send chat notification to all group members except sender.
     */
    public static function sendToChat($chatGroupId, $senderUserId, $message)
    {
        $group = ChatGroup::find($chatGroupId);
        if (!$group) return;

        $sender = User::find($senderUserId);
        if (!$sender) return;

        $actorName = $sender->name ?? 'Seseorang';
        $actorPhoto = $sender->foto ?? null;
        $preview = mb_strlen($message) > 80 ? mb_substr($message, 0, 80) . '...' : $message;
        if (empty(trim($preview))) $preview = 'Mengirim lampiran';

        $title = $group->type === 'private' ? $actorName : $group->name;
        $url = '/chat/' . $chatGroupId;

        // Hanya kirim ke member yang sudah disetujui (approved), bukan yang masih pending.
        $memberIds = $group->approvedMembers()->pluck('users.id')->toArray();
        $recipients = array_filter($memberIds, fn($id) => $id != $senderUserId);

        foreach ($recipients as $userId) {
            self::send($userId, $title, $preview, $url, 'chat', $actorName, $actorPhoto);
        }
    }

    /**
     * Kirim notifikasi undangan bergabung ke grup (kepada anggota yang diundang).
     *
     * @param ChatGroup $group
     * @param User      $inviter
     * @param User|null $specificUser  Bila diberikan, hanya kirim ke user ini (undangan tunggal).
     */
    public static function sendToChatInvite(ChatGroup $group, User $inviter, ?User $specificUser = null)
    {
        $group->loadMissing(['members']);

        // Hanya member berstatus pending (belum accept) yang menerima undangan.
        $invitees = $group->members
            ->filter(fn ($m) => $m->pivot->status === 'pending');
        if ($specificUser) {
            $invitees = $invitees->filter(fn ($m) => $m->id === $specificUser->id);
        }

        $title = 'Undangan grup '.$group->name;
        $message = $inviter->name.' mengundang Anda bergabung ke grup "'.$group->name.'".';
        $url = '/chat?invite=' . $group->id;
        $actorName = $inviter->name ?? 'Seseorang';
        $actorPhoto = $inviter->foto ?? null;

        foreach ($invitees as $m) {
            self::send((int) $m->id, $title, $message, $url, 'chat_invite', $actorName, $actorPhoto);
        }
    }

    /**
     * Send a notification to all students in a specific class.
     */
    public static function sendToClass($kelasId, $title, $message, $url = null, $type = 'general')
    {
        $users = User::where('kelas_id', $kelasId)->where('role', 'siswa')->get();
        foreach ($users as $user) {
            self::send($user->id, $title, $message, $url, $type);
        }
    }

    /**
     * Send a notification to all members of an extracurricular.
     */
    public static function sendToEskul($eskulId, $title, $message, $url = null, $type = 'general')
    {
        $users = User::whereHas('eskuls', function ($q) use ($eskulId) {
            $q->where('eskul_id', $eskulId)->where('status', 'approved');
        })->get();

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
