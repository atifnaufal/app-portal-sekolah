<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';

    protected $fillable = ['user_id', 'chat_group_id', 'kelas_id', 'pesan', 'file'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chatGroup(): BelongsTo
    {
        return $this->belongsTo(ChatGroup::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }
}
