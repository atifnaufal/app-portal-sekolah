<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\User;
use App\Events\ChatMessageEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $userId = session('user_id');
        $user = User::with(['kelas', 'eskuls'])->findOrFail($userId);

        // Ensure School Group exists
        $schoolGroup = ChatGroup::firstOrCreate(
            ['type' => 'school'],
            ['name' => 'Grup Sekolah', 'avatar' => null]
        );

        // Ensure user is in School Group
        if (!$schoolGroup->members()->where('user_id', $userId)->exists()) {
            $schoolGroup->members()->attach($userId);
        }

        // Ensure Class Group exists
        if ($user->kelas_id) {
            $classGroup = ChatGroup::firstOrCreate(
                ['type' => 'class', 'related_id' => $user->kelas_id],
                ['name' => 'Grup ' . $user->kelas->nama]
            );
            if (!$classGroup->members()->where('user_id', $userId)->exists()) {
                $classGroup->members()->attach($userId);
            }
        }

        // Ensure user is in their eskul chat groups (auto-join approved eskuls)
        $this->syncEskulChatGroups($user, $userId);

        // Get all groups user belongs to (after eskul sync so new groups appear)
        $groups = $user->chatGroups()->with('lastMessage')->get();

        // Split groups into Class (school + class) and Eskul sections
        $classGroups = $groups->filter(fn ($g) => in_array($g->type, ['school', 'class']));
        $eskulGroups = $groups->filter(fn ($g) => $g->type === 'eskul');

        // Default to first group if no group selected
        $activeGroupId = $request->query('group_id') ?: ($groups->first()?->id);
        $activeGroup = $activeGroupId ? ChatGroup::with('members')->findOrFail($activeGroupId) : null;

        $messages = $activeGroup
            ? ChatMessage::with('user')->where('chat_group_id', $activeGroup->id)->oldest()->get()
            : collect();

        return view('mobile.chat', [
            'user' => $user,
            'groups' => $groups,
            'classGroups' => $classGroups,
            'eskulGroups' => $eskulGroups,
            'activeGroup' => $activeGroup,
            'messages' => $messages
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
                ['name' => 'Group ' . $eskul->nama]
            );

            if (! $group->members()->where('user_id', $userId)->exists()) {
                $group->members()->attach($userId);
            }
        }
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $userId = session('user_id');
        $user = User::findOrFail($userId);

        $data = $request->validate([
            'pesan' => ['nullable', 'string', 'max:1000'],
            'file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'chat_group_id' => ['required', 'exists:chat_groups,id']
        ]);

        if (empty($data['pesan']) && !$request->hasFile('file')) {
            return response()->json(['error' => 'Pesan atau gambar harus diisi'], 422);
        }

        // Check if user is member of the group
        $group = ChatGroup::findOrFail($data['chat_group_id']);
        abort_unless($group->members()->where('user_id', $userId)->exists(), 403);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('chat_files', 'public');
        }

        $message = ChatMessage::create([
            'user_id' => $userId,
            'chat_group_id' => $group->id,
            'pesan' => $data['pesan'] ?? '',
            'file' => $filePath
        ]);

        $message->load('user');

        try {
            broadcast(new ChatMessageEvent($message));
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
                'file_url' => $message->file ? asset('storage/' . $message->file) : null,
                'nama' => $message->user?->name,
                'foto' => $message->user?->foto ? asset('storage/' . $message->user->foto) : null,
                'waktu' => $message->created_at?->format('H:i'),
            ]);
        }

        return back();
    }

    public function poll(Request $request): JsonResponse
    {
        $userId = session('user_id');
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

        $data = $messages->map(fn($msg) => [
            'id' => $msg->id,
            'user_id' => $msg->user_id,
            'nama' => $msg->user->name,
            'pesan' => $msg->pesan,
            'file_url' => $msg->file ? asset('storage/'.$msg->file) : null,
            'waktu' => $msg->created_at->format('H:i'),
        ]);

        return response()->json($data);
    }
}
