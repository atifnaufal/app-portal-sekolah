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

        // Get all groups user belongs to
        $groups = $user->chatGroups()->with('lastMessage')->get();

        // Default to first group if no group selected
        $activeGroupId = $request->query('group_id') ?: ($groups->first()?->id);
        $activeGroup = $activeGroupId ? ChatGroup::with('members')->findOrFail($activeGroupId) : null;

        $messages = $activeGroup
            ? ChatMessage::with('user')->where('chat_group_id', $activeGroup->id)->oldest()->get()
            : collect();

        return view('mobile.chat', [
            'user' => $user,
            'groups' => $groups,
            'activeGroup' => $activeGroup,
            'messages' => $messages
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $userId = session('user_id');
        $user = User::findOrFail($userId);

        $data = $request->validate([
            'pesan' => ['required', 'string', 'max:1000'],
            'chat_group_id' => ['required', 'exists:chat_groups,id']
        ]);

        // Check if user is member of the group
        $group = ChatGroup::findOrFail($data['chat_group_id']);
        abort_unless($group->members()->where('user_id', $userId)->exists(), 403);

        $message = ChatMessage::create([
            'user_id' => $userId,
            'chat_group_id' => $group->id,
            'pesan' => $data['pesan']
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
                'nama' => $message->user?->name,
                'foto' => $message->user?->foto ? asset('storage/' . $message->user->foto) : null,
                'waktu' => $message->created_at?->format('H:i'),
            ]);
        }

        return back();
    }
}
