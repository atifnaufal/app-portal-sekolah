<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Eskul extends Model
{
    protected $fillable = ['nama', 'slug', 'deskripsi', 'pembina_id', 'logo', 'aktif'];

    public function pembina(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembina_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'eskul_members')
                    ->withPivot(['is_admin', 'status'])
                    ->withTimestamps();
    }

    public function pengumuman(): HasMany
    {
        return $this->hasMany(Pengumuman::class);
    }

    public function approvedMembers(): BelongsToMany
    {
        return $this->members()->wherePivot('status', 'approved');
    }
}
