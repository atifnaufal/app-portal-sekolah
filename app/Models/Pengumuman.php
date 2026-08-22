<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';
    protected $fillable = ['user_id', 'kelas_id', 'judul', 'isi', 'gambar', 'gambar_nama', 'publik', 'is_landing', 'tanggal_acara'];
    protected function casts(): array { return ['publik' => 'boolean', 'is_landing' => 'boolean', 'tanggal_acara' => 'date']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class); }
}
