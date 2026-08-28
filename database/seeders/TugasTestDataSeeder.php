<?php

namespace Database\Seeders;

use App\Models\MataPelajaran;
use App\Models\Tugas;
use Illuminate\Database\Seeder;

/**
 * Data uji tugas: memastikan setiap mata pelajaran memiliki MINIMAL 3 tugas.
 * Idempoten — tidak membuat duplikat (firstOrCreate pada mata_pelajaran_id + judul).
 */
class TugasTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // kode mapel => daftar tugas [judul, deskripsi, tipe]
        $data = [
            'RPL-WEB' => [
                ['Membuat Halaman Profil Pribadi', 'Buatlah halaman web profil pribadi menggunakan HTML dan CSS. Tampilkan foto, biodata, dan hobi. Gunakan minimal 2 selektor CSS dan 1 flexbox.', 'file'],
                ['Landing Page Produk Sederhana', 'Rancang halaman landing page satu produk menggunakan Bootstrap. Sertakan navbar, hero, dan footer.', 'file'],
                ['Kuis Singkat: Dasar CSS', 'Jawab pertanyaan singkat berikut tentang dasar-dasar CSS. Formulir akan otomatis diisi secara online.', 'form'],
            ],
            'RPL-BD' => [
                ['ERD Sistem Perpustakaan', 'Rancang ERD untuk sistem perpustakaan sekolah. Identifikasi minimal 5 entitas dan hubungannya. Upload hasil dalam bentuk PDF atau gambar.', 'file'],
                ['Skema Database Penjualan', 'Buat skema database sederhana untuk sistem penjualan, lengkap dengan tabel produk, pelanggan, dan transaksi beserta relasinya.', 'file'],
                ['Kuis Normalisasi Basis Data', 'Kerjakan kuis singkat tentang normalisasi basis data.', 'form'],
            ],
            'UMUM-MTK' => [
                ['Latihan Soal Persamaan Linear', 'Kerjakan 10 soal persamaan linear yang terlampir. Tuliskan langkah penyelesaian secara lengkap di kertas lalu foto dan upload.', 'file'],
                ['Soal Cerita Fungsi Kuadrat', 'Selesaikan 5 soal cerita penerapan fungsi kuadrat. Sertakan grafik parabola untuk tiap soal.', 'file'],
                ['Kuis Logika Matematika', 'Kerjakan kuis singkat tentang logika matematika: pernyataan, negasi, konjungsi, dan disjungsi.', 'form'],
            ],
            'UMUM-BIN' => [
                ['Teks Eksposisi: Teknologi', 'Buat teks eksposisi bertema teknologi sekitar 3 paragraf. Perhatikan struktur tesis, argumen, dan penegasan ulang.', 'file'],
                ['Resensi Buku Nonsastra', 'Tulis resensi terhadap satu buku nonsastra yang pernah kamu baca. Sertakan identitas buku dan penilaian.', 'file'],
                ['Kuis Struktur Teks', 'Jawab pertanyaan tentang struktur dan kaidah kebahasaan berbagai jenis teks.', 'form'],
            ],
            'TKJ-SRV' => [
                ['Ringkasan Jaringan Komputer', 'Buat ringkasan 1 halaman tentang model OSI dan TCP/IP beserta perbandingannya. Upload dalam format PDF.', 'file'],
                ['Konfigurasi IP Address', 'Lakukan konfigurasi IP address pada perangkat dan jelaskan langkah-langkahnya beserta tangkapan layar.', 'file'],
                ['Kuis Perangkat Jaringan', 'Kerjakan kuis singkat tentang perangkat keras jaringan dan fungsinya.', 'form'],
            ],
            'DKV-DG' => [
                ['Desain Poster Promosi', 'Buat desain poster promosi kegiatan sekolah menggunakan aplikasi desain grafis. Perhatikan komposisi dan tipografi.', 'file'],
                ['Moodboard Brand', 'Buat moodboard untuk brand fiktif yang menggambarkan warna, tipografi, dan gaya visual yang dipilih.', 'file'],
                ['Kuis Prinsip Desain', 'Jawab pertanyaan tentang prinsip-prinsip desain: keseimbangan, kontras, kesatuan, dan hierarki.', 'form'],
            ],
        ];

        foreach ($data as $kode => $tugasList) {
            $mapel = MataPelajaran::where('kode', $kode)->first();
            if (! $mapel) {
                continue;
            }

            foreach ($tugasList as [$judul, $deskripsi, $tipe]) {
                $formData = null;
                if ($tipe === 'form') {
                    $formData = [
                        ['text' => $deskripsi, 'type' => 'essay', 'options' => [], 'required' => true],
                    ];
                }

                Tugas::firstOrCreate(
                    ['mata_pelajaran_id' => $mapel->id, 'judul' => $judul],
                    [
                        'user_id' => $mapel->guru_id,
                        'kelas_id' => $mapel->kelas_id,
                        'deskripsi' => $deskripsi,
                        'batas_pengumpulan' => now()->addDays(rand(3, 14))->toDateString(),
                        'tipe' => $tipe,
                        'form_data' => $formData,
                    ]
                );
            }
        }

        $this->command?->info('Data uji tugas (min. 3 per mapel) berhasil dipastikan.');
    }
}
