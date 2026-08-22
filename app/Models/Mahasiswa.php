<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';

    protected $fillable = ['nim', 'nama', 'email', 'jenis_kelamin', 'tanggal_lahir', 'alamat', 'jurusan_id', 'kelas_id'];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date'];
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }
}
