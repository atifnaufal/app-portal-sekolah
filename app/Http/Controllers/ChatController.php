<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageEvent;
use App\Helpers\UserContextHelper;
use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use App\Models\ChatMessage;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }
        $userId = $user->id;

        // Ensure School Group exists
        $schoolGroup = ChatGroup::firstOrCreate(
            ['type' => 'school'],
            ['name' => 'Grup Sekolah', 'avatar' => null]
        );

        // Ensure user is in School Group
        if (! $schoolGroup->members()->where('user_id', $userId)->exists()) {
            $schoolGroup->members()->attach($userId);
        }

        // Ensure Class Group exists
        if ($user->kelas_id) {
            $classGroup = ChatGroup::firstOrCreate(
                ['type' => 'class', 'related_id' => $user->kelas_id],
                ['name' => 'Grup '.$user->kelas->nama]
            );
            if (! $classGroup->members()->where('user_id', $userId)->exists()) {
                $classGroup->members()->attach($userId);
            }
        }

        // Ensure user is in their eskul chat groups (auto-join approved eskuls)
        $this->syncEskulChatGroups($user, $userId);

        // Get all groups user belongs to (after eskul sync so new groups appear)
        $groups = $user->chatGroups()->with(['lastMessage', 'members'])->get();

        // Split groups into sections
        $classGroups = $groups->filter(fn ($g) => in_array($g->type, ['school', 'class']));
        $eskulGroups = $groups->filter(fn ($g) => $g->type === 'eskul');
        $privateGroups = $groups->filter(fn ($g) => $g->type === 'private');
        $customGroups = $groups->filter(fn ($g) => $g->type === 'custom');

        // Hitung unread per group dari tabel notifikasi
        $unreadMap = [];
        $groupIds = $groups->pluck('id')->toArray();
        if (!empty($groupIds)) {
            $unreadRows = Notifikasi::where('user_id', $userId)
                ->whereNull('dibaca_pada')
                ->where(function ($q) use ($groupIds) {
                    foreach ($groupIds as $gid) {
                        $q->orWhere('url', 'like', '%/chat/' . $gid . '%');
                    }
                })
                ->selectRaw('url, count(*) as total')
                ->groupBy('url')
                ->get();
            foreach ($unreadRows as $row) {
                if (preg_match('#/chat/(\d+)#', $row->url, $m)) {
                    $unreadMap[(int) $m[1]] = (int) $row->total;
                }
            }
        }

        // Ambil daftar undangan grup pending (belum disetujui) untuk user ini.
        $pendingInvites = ChatGroup::whereHas('members', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('chat_group_members.status', 'pending');
        })->with(['owner', 'approvedMembers'])->get();

        return view('mobile.chat', [
            'user' => $user,
            'classGroups' => $classGroups,
            'customGroups' => $customGroups,
            'eskulGroups' => $eskulGroups,
            'privateGroups' => $privateGroups,
            'unreadMap' => $unreadMap,
            'pendingInvites' => $pendingInvites,
        ]);
    }

    /**
     * Start or resume a private chat with another user.
     */
    public function startPrivate(Request $request, User $recipient): RedirectResponse
    {
        $userId = UserContextHelper::id($request);
        if ($userId == $recipient->id) {
            return back()->with('error', 'Tidak bisa chat dengan diri sendiri');
        }

        // Look for existing private group between these two users
        $group = ChatGroup::where('type', 'private')
            ->whereHas('members', fn($q) => $q->where('user_id', $userId))
            ->whereHas('members', fn($q) => $q->where('user_id', $recipient->id))
            ->first();

        if (!$group) {
            $group = ChatGroup::create([
                'name' => 'Chat Pribadi',
                'type' => 'private'
            ]);
            $group->members()->attach([$userId, $recipient->id]);
        }

        return redirect()->route('chat.show', $group->id);
    }

    /**
     * Halaman percakapan untuk SATU grup tertentu.
     */
    public function show(Request $request, ChatGroup $group): View
    {
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }
        $userId = $user->id;

        // Hanya member yang sudah disetujui (approved) yang boleh membuka percakapan.
        abort_unless($group->isApprovedMember($userId), 403);

        $group->load(['members', 'lastMessage', 'owner']);

        // IF Private, set name to the other member
        if ($group->type === 'private') {
            $other = $group->members->first(fn($m) => $m->id != $userId);
            $group->name = $other ? $other->name : 'Chat Pribadi';
            $group->avatar = $other ? $other->foto : null;
            // Also add a custom property for the avatar logic in the view
            $group->other_user = $other;
        }

        // Info keanggotaan untuk UI premium.
        $group->is_admin = $group->isAdmin($userId);
        $group->is_owner = (int) $group->created_by === $userId;
        $group->approved_count = $group->approvedMembers()->count();
        $candidates = [];
        if ($group->type === 'custom') {
            $groupId = $group->id;
            $memberIds = $group->members()->pluck('users.id')->toArray();
            $candidates = User::where('school_id', $user->school_id)
                ->where('id', '!=', $userId)
                ->where('role', '!=', 'admin')
                ->whereNotIn('id', $memberIds)
                ->orderBy('name')
                ->get(['id', 'name', 'foto']);
        }

        $messages = ChatMessage::with('user')
            ->where('chat_group_id', $group->id)
            ->oldest()
            ->get();

        return view('mobile.chat-thread', [
            'user' => $user,
            'group' => $group,
            'messages' => $messages,
            'candidates' => $candidates,
        ]);
    }

    /**
     * Auto-attach the user to every approved eskul's chat group,
     * creating the group first if it does not exist yet.
     */
    private function syncEskulChatGroups(User $user, int $userId): void
    {
        $user->load('eskuls');

        foreach ($user->eskuls as $eskul) {
            if ((string) $eskul->pivot->status !== 'approved') {
                continue;
            }

            $group = ChatGroup::firstOrCreate(
                ['type' => 'eskul', 'related_id' => $eskul->id],
                ['name' => 'Group '.$eskul->nama]
            );

            if (! $group->members()->where('user_id', $userId)->exists()) {
                $group->members()->attach($userId);
            }
        }
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }
        $userId = $user->id;

        $data = $request->validate([
            'pesan' => ['nullable', 'string', 'max:1000'],
            'file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'chat_group_id' => ['required', 'exists:chat_groups,id'],
        ]);

        if (empty($data['pesan']) && ! $request->hasFile('file')) {
            return response()->json(['error' => 'Pesan atau gambar harus diisi'], 422);
        }

        // Check if user is an APPROVED member of the group
        $group = ChatGroup::findOrFail($data['chat_group_id']);
        abort_unless($group->members()->where('users.id', $userId)->wherePivot('status', 'approved')->exists(), 403);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = \App\Services\FirebaseStorageService::put('chat_files', $request->file('file'));
        }

        $message = ChatMessage::create([
            'user_id' => $userId,
            'chat_group_id' => $group->id,
            'pesan' => $data['pesan'] ?? '',
            'file' => $filePath,
        ]);

        $message->load('user');

        try {
            broadcast(new ChatMessageEvent($message));
        } catch (\Throwable $e) {
            report($e);
        }

        // Kirim notifikasi ke semua member kecuali pengirim
        try {
            \App\Helpers\NotificationHelper::sendToChat(
                $group->id,
                $userId,
                $data['pesan'] ?? ''
            );
        } catch (\Throwable $e) {
            report($e);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'id' => $message->id,
                'user_id' => $message->user_id,
                'chat_group_id' => $message->chat_group_id,
                'pesan' => $message->pesan,
                'file_url' => \App\Services\FirebaseStorageService::url($message->file),
                'nama' => $message->user?->name,
                'foto' => $message->user?->foto ? \App\Services\FirebaseStorageService::url($message->user->foto) : null,
                'waktu' => $message->created_at?->format('H:i'),
            ]);
        }

        return back();
    }

    public function poll(Request $request): JsonResponse
    {
        $userId = UserContextHelper::id($request);
        $groupId = $request->query('group_id');
        $lastId = $request->query('last_id', 0);

        abort_unless($groupId, 400);

        // Check if user is member
        $group = ChatGroup::findOrFail($groupId);
        abort_unless($group->members()->where('user_id', $userId)->exists(), 403);

        $messages = ChatMessage::with('user')
            ->where('chat_group_id', $groupId)
            ->where('id', '>', $lastId)
            ->oldest()
            ->get();

        $data = $messages->map(fn ($msg) => [
            'id' => $msg->id,
            'user_id' => $msg->user_id,
            'nama' => $msg->user?->name ?? 'Unknown',
            'pesan' => $msg->isDeleted() ? '' : $msg->pesan,
            'file_url' => $msg->isDeleted() ? null : \App\Services\FirebaseStorageService::url($msg->file),
            'waktu' => $msg->created_at->format('H:i'),
            'edited' => $msg->isEdited(),
            'deleted' => $msg->isDeleted(),
        ]);

        return response()->json($data);
    }

    // ================== FITUR CHAT PREMIUM (WhatsApp-like) ==================

    /**
     * Form buat grup custom (mobile).
     */
    public function create(Request $request): View
    {
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }

        // Usulan anggota: sesama sekolah, kecuali admin.
        $candidates = User::where('school_id', $user->school_id)
            ->where('id', '!=', $user->id)
            ->where('role', '!=', 'admin')
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'foto', 'kelas_id']);

        return view('mobile.chat-create', [
            'user' => $user,
            'candidates' => $candidates,
        ]);
    }

    /**
     * Simpan grup custom baru. Pembuat otomatis jadi admin + approved.
     * Anggota yang dipilih dimasukkan sebagai pending (undangan menunggu accept).
     */
    public function storeGroup(Request $request): RedirectResponse|JsonResponse
    {
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }
        $userId = $user->id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'member_ids' => ['nullable', 'array', 'max:50'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $group = ChatGroup::create([
            'name' => trim($data['name']),
            'type' => 'custom',
            'created_by' => $userId,
        ]);

        // Pembuat = admin, langsung approved.
        ChatGroupMember::create([
            'chat_group_id' => $group->id,
            'user_id' => $userId,
            'status' => 'approved',
            'role' => 'admin',
        ]);

        // Anggota yang diundang = pending (harus accept dulu).
        $memberIds = array_slice(array_map('intval', $data['member_ids'] ?? []), 0, 50);
        foreach ($memberIds as $mid) {
            if ($mid === $userId) {
                continue;
            }
            ChatGroupMember::firstOrCreate(
                ['chat_group_id' => $group->id, 'user_id' => $mid],
                ['status' => 'pending', 'role' => 'member', 'invited_by' => $userId]
            );
        }

        // Notifikasi undangan ke anggota yang diundang.
        try {
            \App\Helpers\NotificationHelper::sendToChatInvite($group, $user);
        } catch (\Throwable $e) {
            report($e);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['ok' => true, 'id' => $group->id]);
        }

        return redirect()->route('chat.show', $group->id)
            ->with('success', 'Grup berhasil dibuat. Anggota perlu menyetujui undangan.');
    }

    /**
     * Undang user ke grup (oleh admin/owner).
     */
    public function invite(Request $request, ChatGroup $group): RedirectResponse|JsonResponse
    {
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }
        abort_unless($group->isAdmin($user->id), 403);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if ((int) $data['user_id'] === $user->id) {
            return $this->chatRespond($request, ['error' => 'Tidak bisa mengundang diri sendiri'], 422);
        }

        ChatGroupMember::firstOrCreate(
            ['chat_group_id' => $group->id, 'user_id' => $data['user_id']],
            ['status' => 'pending', 'role' => 'member', 'invited_by' => $user->id]
        );

        $invited = User::find($data['user_id']);
        try {
            \App\Helpers\NotificationHelper::sendToChatInvite($group, $user, $invited);
        } catch (\Throwable $e) {
            report($e);
        }

        return $this->chatRespond($request, ['ok' => true, 'group_id' => $group->id]);
    }

    /**
     * User menyetujui undangan dan masuk ke grup.
     */
    public function acceptInvite(Request $request, ChatGroup $group): RedirectResponse|JsonResponse
    {
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }

        $row = ChatGroupMember::where('chat_group_id', $group->id)
            ->where('user_id', $user->id)
            ->first();

        abort_unless($row, 403, 'Anda tidak diundang ke grup ini.');
        abort_unless($row->status === 'pending', 422, 'Undangan sudah diproses.');

        $row->update(['status' => 'approved']);

        return $this->chatRespond($request, ['ok' => true, 'group_id' => $group->id]);
    }

    /**
     * User menolak undangan.
     */
    public function rejectInvite(Request $request, ChatGroup $group): RedirectResponse|JsonResponse
    {
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }

        ChatGroupMember::where('chat_group_id', $group->id)
            ->where('user_id', $user->id)
            ->delete();

        return $this->chatRespond($request, ['ok' => true]);
    }

    /**
     * User keluar dari grup. Jika owner keluar, suksesor admin diambil dari approved member.
     */
    public function leave(Request $request, ChatGroup $group): RedirectResponse|JsonResponse
    {
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }
        $userId = $user->id;

        abort_unless($group->isApprovedMember($userId), 403);

        // Jika owner keluar dari grup custom: transfer kepemilikan ke admin lain,
        // atau hapus grup bila tidak ada anggota tersisa.
        if ($group->type === 'custom' && (int) $group->created_by === $userId) {
            $otherAdmin = ChatGroupMember::where('chat_group_id', $group->id)
                ->where('user_id', '!=', $userId)
                ->where('status', 'approved')
                ->where('role', 'admin')
                ->first();
            $otherMember = ChatGroupMember::where('chat_group_id', $group->id)
                ->where('user_id', '!=', $userId)
                ->where('status', 'approved')
                ->first();
            $successor = $otherAdmin ?? $otherMember;

            if ($successor) {
                ChatGroupMember::where('chat_group_id', $group->id)->where('user_id', $userId)->delete();
                $group->update(['created_by' => $successor->user_id]);
                ChatGroupMember::where('chat_group_id', $group->id)
                    ->where('user_id', $successor->user_id)
                    ->update(['role' => 'admin']);
            } else {
                // Tidak ada anggota tersisa — hapus grup beserta membernya.
                ChatGroupMember::where('chat_group_id', $group->id)->delete();
                $group->delete();

                return $this->chatRespond($request, ['ok' => true, 'deleted_group' => true]);
            }
        } else {
            ChatGroupMember::where('chat_group_id', $group->id)->where('user_id', $userId)->delete();
        }

        return $this->chatRespond($request, ['ok' => true]);
    }

    /**
     * Edit isi pesan sendiri.
     */
    public function updateMessage(Request $request, ChatMessage $message): RedirectResponse|JsonResponse
    {
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }

        abort_unless((int) $message->user_id === $user->id, 403, 'Hanya pengirim yang bisa mengedit.');
        abort_if($message->isDeleted(), 422, 'Pesan sudah dihapus, tidak bisa diedit.');

        $data = $request->validate([
            'pesan' => ['required', 'string', 'max:1000'],
        ]);

        $message->update([
            'pesan' => $data['pesan'],
            'edited' => true,
            'edited_at' => now(),
        ]);

        try {
            broadcast(new ChatMessageEvent($message, 'updated'));
        } catch (\Throwable $e) {
            report($e);
        }

        return $this->chatRespond($request, [
            'ok' => true,
            'id' => $message->id,
            'pesan' => $message->pesan,
            'edited' => true,
            'waktu' => $message->created_at?->format('H:i'),
        ]);
    }

    /**
     * Hapus pesan secara permanen (untuk semua). UI menampilkan placeholder "Pesan dihapus".
     */
    public function destroyMessage(Request $request, ChatMessage $message): RedirectResponse|JsonResponse
    {
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }
        $userId = $user->id;

        // Hanya pengirim atau admin grup yang boleh menghapus.
        $group = $message->chatGroup;
        $canDelete = (int) $message->user_id === $userId || ($group && $group->isAdmin($userId));
        abort_unless($canDelete, 403);

        $message->update([
            'pesan' => '',
            'file' => null,
            'deleted_at' => now(),
            'deleted_by' => $userId,
        ]);

        try {
            broadcast(new ChatMessageEvent($message, 'deleted'));
        } catch (\Throwable $e) {
            report($e);
        }

        return $this->chatRespond($request, ['ok' => true, 'id' => $message->id, 'deleted' => true]);
    }

    /**
     * Merapikan respons JSON vs redirect (web vs mobile API).
     */
    private function chatRespond(Request $request, array $payload, int $status = 200)
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json($payload, $status);
        }

        if (($payload['ok'] ?? false) && isset($payload['group_id'])) {
            return redirect()->route('chat.show', $payload['group_id'])->with('success', 'Berhasil.');
        }
        if (($payload['error'] ?? false) && $status >= 400) {
            return back()->with('error', $payload['error']);
        }

        return back()->with('success', 'Berhasil.');
    }
}
