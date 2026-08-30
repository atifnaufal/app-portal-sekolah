<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $title;
    public $message;
    public $type;
    public $actorName;
    public $actorPhoto;
    public $userId;

    public function __construct($userId, $title, $message, $type = 'general', $actorName = null, $actorPhoto = null)
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->actorName = $actorName;
        $this->actorPhoto = $actorPhoto;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('portal-notifications.'.$this->userId),
        ];
    }

    public function broadcastAs()
    {
        return 'new-notification';
    }

    public function broadcastWith(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'actor_name' => $this->actorName,
            'actor_photo' => $this->actorPhoto,
        ];
    }
}
