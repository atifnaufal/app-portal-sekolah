<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriBuku;
use App\Models\Buku;
use Illuminate\Support\Str;

class PerpustakaanSeeder extends Seeder
{
    public function run()
    {
        // Bersihkan data lama untuk testing
        Buku::truncate();
        KategoriBuku::truncate();

        $categories = [
            ['nama' => 'Sastra & Novel', 'slug' => 'sastra-novel'],
            ['nama' => 'Pengembangan Diri', 'slug' => 'pengembangan-diri'],
            ['nama' => 'Pelajaran & Edukasi', 'slug' => 'pelajaran-edukasi'],
            ['nama' => 'Sejarah & Budaya', 'slug' => 'sejarah-budaya'],
        ];

        foreach ($categories as $cat) {
            KategoriBuku::create($cat);
        }

        $sastra = KategoriBuku::where('slug', 'sastra-novel')->first()->id;
        $selfHelp = KategoriBuku::where('slug', 'pengembangan-diri')->first()->id;
        $edu = KategoriBuku::where('slug', 'pelajaran-edukasi')->first()->id;
        $history = KategoriBuku::where('slug', 'sejarah-budaya')->first()->id;

        $books = [
            [
                'kategori_buku_id' => $sastra,
                'judul' => 'Laskar Pelangi',
                'penulis' => 'Andrea Hirata',
                'penerbit' => 'Bentang Pustaka',
                'tahun_terbit' => 2005,
                'stok' => 5,
                'deskripsi' => 'Mengisahkan perjuangan 10 anak di Desa Gantung, Belitung, yang bersekolah di sebuah sekolah Muhammadiyah yang kondisinya memprihatinkan. Buku ini menggambarkan kekuatan mimpi, persahabatan, dan dedikasi guru dalam keterbatasan ekonomi.',
                'file_pdf' => 'perpustakaan/dummy.pdf'
            ],
            [
                'kategori_buku_id' => $history,
                'judul' => 'Bumi Manusia',
                'penulis' => 'Pramoedya Ananta Toer',
                'penerbit' => 'Hasta Mitra',
                'tahun_terbit' => 1980,
                'stok' => 3,
                'deskripsi' => 'Berlatar masa kolonial Belanda, buku ini mengikuti kisah Minke, seorang pemuda pribumi yang cerdas, dan perjuangannya melawan ketidakadilan sistem kasta serta kolonialisme.',
                'file_pdf' => 'perpustakaan/dummy.pdf'
            ],
            [
                'kategori_buku_id' => $edu,
                'judul' => 'Negeri 5 Menara',
                'penulis' => 'Ahmad Fuadi',
                'penerbit' => 'Gramedia Pustaka Utama',
                'tahun_terbit' => 2009,
                'stok' => 7,
                'deskripsi' => 'Kisah Alif yang terpaksa merantau ke pondok pesantren di Ponorogo. Belajar tentang mantra "Man Jadda Wajada" (siapa yang bersungguh-sungguh pasti berhasil) untuk meraih mimpi mereka.',
                'file_pdf' => 'perpustakaan/dummy.pdf'
            ],
            [
                'kategori_buku_id' => $sastra,
                'judul' => '5 cm',
                'penulis' => 'Donny Dhirgantoro',
                'penerbit' => 'Grasindo',
                'tahun_terbit' => 2005,
                'stok' => 4,
                'deskripsi' => 'Lima sahabat karib memutuskan untuk tidak berkomunikasi selama tiga bulan. Pertemuan kembali mereka dirayakan dengan pendakian ke puncak tertinggi di Jawa, Gunung Semeru.',
                'file_pdf' => 'perpustakaan/dummy.pdf'
            ],
            [
                'kategori_buku_id' => $sastra,
                'judul' => 'Perahu Kertas',
                'penulis' => 'Dee Lestari',
                'penerbit' => 'Bentang Pustaka',
                'tahun_terbit' => 2009,
                'stok' => 6,
                'deskripsi' => 'Menceritakan perjalanan hidup Kugy, seorang gadis eksentrik, dan Keenan, seorang pelukis muda berbakat. Keduanya terjebak antara mengejar idealisme mimpi atau tuntutan realita.',
                'file_pdf' => 'perpustakaan/dummy.pdf'
            ],
            [
                'kategori_buku_id' => $selfHelp,
                'judul' => 'Filosofi Teras',
                'penulis' => 'Henry Manampiring',
                'penerbit' => 'Buku Kompas',
                'tahun_terbit' => 2018,
                'stok' => 10,
                'deskripsi' => 'Memperkenalkan ajaran Stoisisme (filsafat Yunani-Romawi kuno) dengan bahasa yang ringan. Membantu pembaca mengatasi emosi negatif dan membangun mental yang tangguh.',
                'file_pdf' => 'perpustakaan/dummy.pdf'
            ],
            [
                'kategori_buku_id' => $sastra,
                'judul' => 'Pulang',
                'penulis' => 'Tere Liye',
                'penerbit' => 'Republika',
                'tahun_terbit' => 2015,
                'stok' => 8,
                'deskripsi' => 'Mengikuti tokoh bernama Bujang yang tumbuh di dunia ekonomi bayangan. Ini adalah kisah tentang keberanian, pengkhianatan, dan perjalanan pulang untuk berdamai dengan masa lalu.',
                'file_pdf' => 'perpustakaan/dummy.pdf'
            ],
            [
                'kategori_buku_id' => $sastra,
                'judul' => 'Hafalan Shalat Delisa',
                'penulis' => 'Tere Liye',
                'penerbit' => 'Republika',
                'tahun_terbit' => 2005,
                'stok' => 5,
                'deskripsi' => 'Berlatar tragedi Tsunami Aceh 2004, Delisa kehilangan kaki dan keluarganya saat ia sedang berusaha keras menghafal bacaan shalatnya. Kisah tentang ketabahan dan keikhlasan.',
                'file_pdf' => 'perpustakaan/dummy.pdf'
            ],
            [
                'kategori_buku_id' => $sastra,
                'judul' => 'Dilan: Dia adalah Dilanku Tahun 1990',
                'penulis' => 'Pidi Baiq',
                'penerbit' => 'Pastel Books',
                'tahun_terbit' => 2014,
                'stok' => 12,
                'deskripsi' => 'Berlatar Bandung tahun 1990, Milea menceritakan kembali masa SMA-nya saat didekati oleh Dilan, anggota geng motor yang memiliki cara puitis dan humoris.',
                'file_pdf' => 'perpustakaan/dummy.pdf'
            ],
            [
                'kategori_buku_id' => $selfHelp,
                'judul' => 'Atomic Habits',
                'penulis' => 'James Clear',
                'penerbit' => 'Gramedia Pustaka Utama',
                'tahun_terbit' => 2018,
                'stok' => 15,
                'deskripsi' => 'Panduan komprehensif untuk membangun kebiasaan baik dan menghilangkan kebiasaan buruk dengan perubahan kecil yang konsisten (1% setiap hari).',
                'file_pdf' => 'perpustakaan/dummy.pdf'
            ]
        ];

        foreach ($books as $b) {
            $b['slug'] = Str::slug($b['judul']);
            Buku::create($b);
        }
    }
}
