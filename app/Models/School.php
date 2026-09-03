<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = ['name','city','logo','slug','is_active','reg_guru_open','reg_siswa_open'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'reg_guru_open' => 'boolean', 'reg_siswa_open' => 'boolean'];
    }

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function posts(): HasMany { return $this->hasMany(GlobalPost::class); }
    public function schoolFeatures(): HasMany { return $this->hasMany(SchoolFeature::class); }
}
