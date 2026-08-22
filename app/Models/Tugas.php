<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tugas extends Model
{
    protected $table = 'tugas';
    protected $fillable = ['user_id', 'kelas_id', 'judul', 'deskripsi', 'lampiran', 'lampiran_nama', 'batas_pengumpulan'];
    protected function casts(): array { return ['batas_pengumpulan' => 'date']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class); }
    public function pengumpulan(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(PengumpulanTugas::class); }
}
