<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;

class QueuedVerifyEmail extends VerifyEmailNotification implements ShouldQueue
{
    use Queueable;
}
