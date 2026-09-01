<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserHistory extends Model
{
    protected $fillable = [
        'user_id',
        'activity_type',
        'description',
        'metadata',
        'lat',
        'long',
        'ip_address',
        'user_agent',
        'device_info',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'lat' => 'decimal:8',
            'long' => 'decimal:8',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
