<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EskulMember extends Model
{
    protected $fillable = ['user_id', 'eskul_id', 'is_admin'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eskul(): BelongsTo
    {
        return $this->belongsTo(Eskul::class);
    }
}
