<?php

namespace App\Http\Controllers;

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
        $user = User::with('kelas')->findOrFail($request->session()->get('user_id'));
        abort_unless(in_array($user->role, ['guru', 'siswa'], true), 403);
        abort_unless($user->kelas_id, 403, 'Akun belum memiliki kelas.');
        return view('mobile.chat', ['user' => $user, 'messages' => ChatMessage::with('user')->where('kelas_id', $user->kelas_id)->oldest()->get()]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = User::findOrFail($request->session()->get('user_id'));
        abort_unless(in_array($user->role, ['guru', 'siswa'], true), 403);
        abort_unless($user->kelas_id, 403);
        $data = $request->validate(['pesan' => ['required', 'string', 'max:1000']]);
        $message = ChatMessage::create(['user_id' => $user->id, 'kelas_id' => $user->kelas_id, 'pesan' => $data['pesan']]);
        $message->load('user');

        // Kirim pesan ke channel realtime. Dibungkus try/catch agar kegagalan
        // Reverb TIDAK pernah membuat respons pengirim error (pesan tetap tersimpan).
        try {
            broadcast(new ChatMessageEvent($message));
        } catch (\Throwable $e) {
            report($e);
        }

        // Kirim JSON untuk AJAX/fetch (tanpa redirect penuh halaman) -> respons cepat.
        // Pengirim sudah melihat pesannya secara instan lewat optimistic UI di frontend.
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'id' => $message->id,
                'user_id' => $message->user_id,
                'kelas_id' => $message->kelas_id,
                'pesan' => $message->pesan,
                'nama' => $message->user?->name,
                'foto' => $message->user?->foto ? asset('storage/' . $message->user->foto) : null,
                'waktu' => $message->created_at?->format('H:i'),
            ]);
        }

        return back();
    }
}
