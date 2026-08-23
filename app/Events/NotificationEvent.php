<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $title;
    public $message;
    public $type; // 'announcement' or 'task'

    public function __construct($title, $message, $type = 'announcement')
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('portal-notifications'),
        ];
    }

    public function broadcastAs()
    {
        return 'new-notification';
    }
}
