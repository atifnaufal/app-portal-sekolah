<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use App\Events\ChatMessageEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = User::with('kelas')->findOrFail($request->session()->get('user_id'));
        abort_unless(in_array($user->role, ['guru', 'siswa'], true), 403);
        abort_unless($user->kelas_id, 403, 'Akun belum memiliki kelas.');
        return view('mobile.chat', ['user' => $user, 'messages' => ChatMessage::with('user')->where('kelas_id', $user->kelas_id)->oldest()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->session()->get('user_id'));
        abort_unless(in_array($user->role, ['guru', 'siswa'], true), 403);
        abort_unless($user->kelas_id, 403);
        $data = $request->validate(['pesan' => ['required', 'string', 'max:1000']]);
        $message = ChatMessage::create(['user_id' => $user->id, 'kelas_id' => $user->kelas_id, 'pesan' => $data['pesan']]);

        broadcast(new ChatMessageEvent($message->load('user')));

        return back();
    }
}
