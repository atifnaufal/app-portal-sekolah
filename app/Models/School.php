<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = ['name','city','city_code','enroll_code','logo','slug','is_active','reg_guru_open','reg_siswa_open'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'reg_guru_open' => 'boolean', 'reg_siswa_open' => 'boolean'];
    }

    /** Kode Pendaftaran umum: {id}{city_code} — digenerate otomatis. */
    public static function makeEnrollCode(int $id, ?string $cityCode): string
    {
        $suffix = preg_replace('/\D/', '', (string) $cityCode) ?: '00000';

        return $id.$suffix;
    }

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function posts(): HasMany { return $this->hasMany(GlobalPost::class); }
    public function schoolFeatures(): HasMany { return $this->hasMany(SchoolFeature::class); }
}
