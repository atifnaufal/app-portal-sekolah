<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatGroup extends Model
{
    protected $fillable = ['name', 'type', 'related_id', 'avatar'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_group_members');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class)->latest();
    }
}
