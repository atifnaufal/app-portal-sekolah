<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatGroup extends Model
{
    protected $fillable = ['name', 'type', 'related_id', 'avatar', 'created_by'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_group_members')
            ->withPivot(['status', 'role', 'invited_by']);
    }

    public function approvedMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_group_members')
            ->withPivot(['status', 'role', 'invited_by'])
            ->wherePivot('status', 'approved');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class)->latest();
    }

    /** Apakah user tergabung (approved) di grup ini. */
    public function isApprovedMember(int $userId): bool
    {
        return $this->approvedMembers()->where('users.id', $userId)->exists();
    }

    /** Apakah user adalah admin/pemilik grup. */
    public function isAdmin(int $userId): bool
    {
        if ((int) $this->created_by === $userId) {
            return true;
        }

        return $this->members()->where('users.id', $userId)->wherePivot('role', 'admin')->exists();
    }

    /** Status keanggotaan user (pending/approved) atau null bila bukan member. */
    public function memberStatus(int $userId): ?string
    {
        $row = $this->members()->where('users.id', $userId)->first();

        return $row?->pivot->status ?? null;
    }
}
