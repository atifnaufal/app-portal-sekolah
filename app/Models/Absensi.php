<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $fillable = ['user_id', 'kelas_id', 'tanggal', 'waktu', 'status'];
    protected function casts(): array { return ['tanggal' => 'date']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class); }
}
