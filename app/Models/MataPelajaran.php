<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';
    protected $fillable = ['nama', 'kode', 'kelas_id', 'guru_id', 'kkm'];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function nilais(): HasMany
    {
        return $this->hasMany(Nilai::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class, 'mata_pelajaran_id');
    }

    public function materi(): HasMany
    {
        return $this->hasMany(Materi::class, 'mata_pelajaran_id');
    }
}
