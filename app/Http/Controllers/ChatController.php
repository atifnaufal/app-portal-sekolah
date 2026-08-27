<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    private function resolveUser(Request $request): User
    {
        $userId = $request->session()->get('user_id');
        if (!$userId && Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        }
        return User::with('kelas')->findOrFail($userId);
    }

    public function index(Request $request): View
    {
        $user = $this->resolveUser($request);
        abort_unless(in_array($user->role, ['guru', 'siswa'], true), 403);
        abort_unless($user->kelas_id, 403, 'Akun belum memiliki kelas.');

        $messages = ChatMessage::with('user')
            ->where('kelas_id', $user->kelas_id)
            ->oldest()
            ->get();

        $members = User::where('role', '!=', 'admin')
            ->where('kelas_id', $user->kelas_id)
            ->count();

        return view('mobile.chat', [
            'user' => $user,
            'messages' => $messages,
            'memberCount' => $members,
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->resolveUser($request);
        abort_unless(in_array($user->role, ['guru', 'siswa'], true), 403);
        abort_unless($user->kelas_id, 403);

        $data = $request->validate([
            'pesan' => ['required', 'string', 'max:1000'],
        ]);

        $message = ChatMessage::create([
            'user_id' => $user->id,
            'kelas_id' => $user->kelas_id,
            'pesan' => $data['pesan'],
        ]);

        $message->load('user');

        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'ok' => true,
                'message' => [
                    'id' => $message->id,
                    'pesan' => $message->pesan,
                    'created_at' => $message->created_at->format('H:i'),
                    'date_label' => $message->created_at->isToday() ? 'Hari ini' : $message->created_at->format('d M Y'),
                    'user' => [
                        'id' => $message->user->id,
                        'name' => $message->user->name,
                        'foto' => $message->user->foto ? asset('storage/' . $message->user->foto) : null,
                        'role' => $message->user->role,
                        'initial' => strtoupper(substr($message->user->name, 0, 1)),
                    ],
                ],
            ]);
        }

        return back();
    }

    public function poll(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        abort_unless($user->kelas_id, 403);

        $afterId = $request->input('after', 0);

        $messages = ChatMessage::with('user')
            ->where('kelas_id', $user->kelas_id)
            ->where('id', '>', $afterId)
            ->oldest()
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'pesan' => $m->pesan,
                    'created_at' => $m->created_at->format('H:i'),
                    'date_label' => $m->created_at->isToday() ? 'Hari ini' : $m->created_at->format('d M Y'),
                    'user_id' => $m->user_id,
                    'user' => [
                        'id' => $m->user->id,
                        'name' => $m->user->name,
                        'foto' => $m->user->foto ? asset('storage/' . $m->user->foto) : null,
                        'role' => $m->user->role,
                        'initial' => strtoupper(substr($m->user->name, 0, 1)),
                    ],
                ];
            });

        return response()->json(['messages' => $messages]);
    }
}
