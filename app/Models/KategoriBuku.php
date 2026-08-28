<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBuku extends Model
{
    protected $fillable = ['nama', 'slug'];

    public function bukus(): HasMany
    {
        return $this->hasMany(Buku::class);
    }
}
