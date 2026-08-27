<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('portal-chat.'.$this->message->kelas_id),
        ];
    }

    public function broadcastAs()
    {
        return 'new-message';
    }

    public function broadcastWith(): array
    {
        $sender = $this->message->user;

        return [
            'id' => $this->message->id,
            'user_id' => $this->message->user_id,
            'kelas_id' => $this->message->kelas_id,
            'pesan' => $this->message->pesan,
            'nama' => $sender?->name,
            'foto' => $sender?->foto ? asset('storage/'.$sender->foto) : null,
            'waktu' => $this->message->created_at?->format('H:i'),
        ];
    }
}
