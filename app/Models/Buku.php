<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buku extends Model
{
    protected $fillable = [
        'kategori_buku_id', 'judul', 'slug', 'penulis', 'penerbit',
        'tahun_terbit', 'deskripsi', 'cover', 'file_pdf', 'stok'
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBuku::class, 'kategori_buku_id');
    }

    public function peminjamans(): HasMany
    {
        return $this->hasMany(PeminjamanBuku::class);
    }
}
