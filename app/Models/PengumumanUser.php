<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengumumanUser extends Model
{
    protected $table = 'pengumuman_user';

    protected $fillable = ['pengumuman_id', 'user_id', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function pengumuman(): BelongsTo
    {
        return $this->belongsTo(Pengumuman::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
