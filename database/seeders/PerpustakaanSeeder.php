<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriBuku;
use App\Models\Buku;

class PerpustakaanSeeder extends Seeder
{
    public function run()
    {
        $kategori = KategoriBuku::create([
            'nama' => 'Pelajaran',
            'slug' => 'pelajaran'
        ]);

        KategoriBuku::create([
            'nama' => 'Novel',
            'slug' => 'novel'
        ]);

        Buku::create([
            'kategori_buku_id' => $kategori->id,
            'judul' => 'Matematika Dasar',
            'slug' => 'matematika-dasar',
            'penulis' => 'Dr. Budi Utomo',
            'penerbit' => 'Erlangga',
            'tahun_terbit' => 2023,
            'stok' => 10,
            'deskripsi' => 'Buku pegangan matematika dasar untuk tingkat menengah.',
            'file_pdf' => 'perpustakaan/dummy.pdf'
        ]);
    }
}
