<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $action;

    public function __construct(ChatMessage $message, string $action = 'created')
    {
        $this->message = $message;
        $this->action = $action;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('portal-chat-group.'.$this->message->chat_group_id),
        ];
    }

    public function broadcastAs()
    {
        return 'new-message';
    }

    public function broadcastWith(): array
    {
        $sender = $this->message->user;
        $isDeleted = $this->message->isDeleted();

        return [
            'action' => $this->action,
            'id' => $this->message->id,
            'user_id' => $this->message->user_id,
            'chat_group_id' => $this->message->chat_group_id,
            'pesan' => $isDeleted ? '' : $this->message->pesan,
            'file_url' => $isDeleted ? null : \App\Services\FirebaseStorageService::url($this->message->file),
            'nama' => $sender?->name,
            'foto' => \App\Services\FirebaseStorageService::url($sender?->foto),
            'waktu' => $this->message->created_at?->format('H:i'),
            'edited' => $this->message->isEdited(),
            'deleted' => $isDeleted,
            'deleted_by' => $this->message->deleted_by,
        ];
    }
}
