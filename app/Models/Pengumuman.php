<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $fillable = ['user_id', 'kelas_id', 'eskul_id', 'judul', 'isi', 'gambar', 'gambar_nama', 'publik', 'is_landing', 'tanggal_acara'];

    protected function casts(): array
    {
        return ['publik' => 'boolean', 'is_landing' => 'boolean', 'tanggal_acara' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function eskul(): BelongsTo
    {
        return $this->belongsTo(Eskul::class);
    }

    /**
     * Penerima pengumuman privat (per-student). read_at untuk status dibaca.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pengumuman_user')
            ->withPivot(['read_at'])
            ->withTimestamps();
    }

    public function isPrivate(): bool
    {
        return $this->kelas_id === null && $this->eskul_id === null && ! $this->publik && $this->users()->exists();
    }
}
