<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spp extends Model
{
    protected $table = 'spp';

    protected $fillable = ['siswa_id', 'bulan', 'tahun', 'nominal', 'dibayar', 'status', 'jatuh_tempo'];

    protected function casts(): array
    {
        return ['nominal' => 'decimal:2', 'dibayar' => 'decimal:2', 'jatuh_tempo' => 'date'];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function getKekuranganAttribute(): float
    {
        return max(0, (float) $this->nominal - (float) $this->dibayar);
    }
}
