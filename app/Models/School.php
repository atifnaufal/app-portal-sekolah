<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = ['name','city','logo','slug','is_active'];

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function posts(): HasMany { return $this->hasMany(GlobalPost::class); }
}
