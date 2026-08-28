<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\Nilai;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LmsSeeder extends Seeder
{
    public function run(): void
    {
        $thAjaran = '2026/2027';

        // ===== Jurusan =====
        $jurusanRpl = Jurusan::updateOrCreate(['kode' => 'RPL'], ['nama' => 'Rekayasa Perangkat Lunak']);
        $jurusanTkj = Jurusan::updateOrCreate(['kode' => 'TKJ'], ['nama' => 'Teknik Komputer & Jaringan']);
        $jurusanDkv = Jurusan::updateOrCreate(['kode' => 'DKV'], ['nama' => 'Desain Komunikasi Visual']);

        // ===== Kelas =====
        $kelas = [];
        foreach ([10 => 'X', 11 => 'XI', 12 => 'XII'] as $tingkat => $romawi) {
            foreach (['RPL', 'TKJ'] as $prodi) {
                $nama = "$romawi $prodi";
                $kelas[$nama] = Kelas::updateOrCreate(
                    ['nama' => $nama, 'tahun_ajaran' => $thAjaran],
                    ['tingkat' => $tingkat]
                );
            }
        }
        $kelasX_RPL = $kelas['X RPL'];
        $kelasX_TKJ = $kelas['X TKJ'];
        $kelasXI_RPL = $kelas['XI RPL'];

        // ===== Guru (pengampu mapel) =====
        $guruData = [
            ['Budi Santoso', 'budi@sekolah.com', '1234567890123', '081210001001'],
            ['Siti Rahayu', 'siti@sekolah.com', '1234567890124', '081210001002'],
            ['Andi Wijaya', 'andi@sekolah.com', '1234567890125', '081210001003'],
            ['Dewi Lestari', 'dewi@sekolah.com', '1234567890126', '081210001004'],
            ['Rudi Hartono', 'rudi@sekolah.com', '1234567890127', '081210001005'],
            ['Maya Anggraini', 'maya@sekolah.com', '1234567890128', '081210001006'],
        ];
        $guru = [];
        foreach ($guruData as $i => [$name, $email, $nik, $hp]) {
            $u = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'guru',
                    'nik' => $nik,
                    'no_hp' => $hp,
                    'aktif' => true,
                    'kelas_id' => $i < 2 ? $kelasX_RPL->id : ($i < 4 ? $kelasX_TKJ->id : $kelasXI_RPL->id),
                ]
            );
            $guru[$name] = $u;
        }

        // ===== Wali Kelas (guru kelas) =====
        $kelas['X RPL']->update(['pembina_id' => $guru['Budi Santoso']->id]);
        $kelas['X TKJ']->update(['pembina_id' => $guru['Rudi Hartono']->id]);
        $kelas['XI RPL']->update(['pembina_id' => $guru['Maya Anggraini']->id]);

        // ===== Siswa =====
        $namaSiswa = ['Agus Pratama', 'Bella Seputri', 'Citra Kirana', 'Dedi Kurniawan', 'Eka Putri',
                      'Fajar Nugroho', 'Gita Savitri', 'Hendra Gunawan', 'Intan Ayu', 'Joko Susilo',
                      'Kartika Sari', 'Lukman Hakim', 'Maya Dewi', 'Naufal Rizki', 'Putri Maharani',
                      'Rangga Aditya', 'Sri Wahyuni', 'Taufik Hidayat'];
        $siswa = [];
        foreach ($namaSiswa as $i => $name) {
            $email = 'siswa'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT).'@sekolah.com';
            $kls = $i < 8 ? $kelasX_RPL : ($i < 14 ? $kelasX_TKJ : $kelasXI_RPL);
            $u = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'siswa',
                    'nik' => '2233'.str_pad((string) ($i + 1), 9, '0', STR_PAD_LEFT),
                    'no_hp' => '0813'.str_pad((string) ($i + 1), 8, '0', STR_PAD_LEFT),
                    'aktif' => true,
                    'kelas_id' => $kls->id,
                ]
            );
            $siswa[$name] = $u;
        }

        // ===== Mata Pelajaran =====
        $mapelData = [
            ['Pemrograman Web', 'RPL-WEB', $kelasX_RPL, $guru['Budi Santoso'], 75],
            ['Basis Data', 'RPL-BD', $kelasX_RPL, $guru['Siti Rahayu'], 70],
            ['Matematika', 'UMUM-MTK', $kelasX_RPL, $guru['Andi Wijaya'], 75],
            ['Bahasa Indonesia', 'UMUM-BIN', $kelasX_TKJ, $guru['Dewi Lestari'], 75],
            ['Administrasi Server', 'TKJ-SRV', $kelasX_TKJ, $guru['Rudi Hartono'], 70],
            ['Desain Grafis', 'DKV-DG', $kelasXI_RPL, $guru['Maya Anggraini'], 75],
        ];
        $mapel = [];
        foreach ($mapelData as [$nama, $kode, $kls, $gr, $kkm]) {
            $mapel[$kode] = MataPelajaran::updateOrCreate(
                ['kode' => $kode],
                ['nama' => $nama, 'kelas_id' => $kls->id, 'guru_id' => $gr->id, 'kkm' => $kkm]
            );
        }

        // ===== Materi (6 contoh) =====
        $materiData = [
            [$mapel['RPL-WEB'], $guru['Budi Santoso'], 'Pengenalan HTML & CSS',
             'Materi dasar struktur halaman web menggunakan HTML5 dan styling dengan CSS3. Meliputi tag semantic, selektor, dan layout flexbox. Silakan pelajari dan praktikkan langsung di editor kalian masing-masing.', null, null],
            [$mapel['RPL-WEB'], $guru['Budi Santoso'], 'JavaScript Dasar',
             'Pengenalan variabel, tipe data, fungsi, dan manipulasi DOM menggunakan JavaScript. Video penjelasan tersedia untuk menambah pemahaman.', null, 'https://www.youtube.com/watch?v=W6NZfCO5SIk'],
            [$mapel['RPL-BD'], $guru['Siti Rahayu'], 'Entity Relationship Diagram (ERD)',
             'Konsep perancangan basis data dengan ERD: entitas, atribut, relasi, dan kardinalitas. Termasuk contoh studi kasus sistem perpustakaan sekolah.', null, null],
            [$mapel['RPL-BD'], $guru['Siti Rahayu'], 'Normalisasi Basis Data',
             'Materi normalisasi 1NF, 2NF, dan 3NF dengan contoh penerapan. Penting untuk membangun skema database yang efisien dan bebas redundansi.', null, null],
            [$mapel['UMUM-MTK'], $guru['Andi Wijaya'], 'Aljabar & Persamaan Linear',
             'Ringkasan materi aljabar, penyelesaian persamaan linear satu dan dua variabel, lengkap dengan latihan soal dan pembahasan.', null, null],
            [$mapel['TKJ-SRV'], $guru['Rudi Hartono'], 'Pengenalan Jaringan Komputer',
             'Dasar-dasar jaringan komputer: model OSI, TCP/IP, topologi jaringan, dan perangkat jaringan. Materi wajib sebelum praktik administrasi server.', null, 'https://www.youtube.com/watch?v=3Q9R0c9F2hQ'],
        ];
        foreach ($materiData as [$mp, $gr, $judul, $deskripsi, $file, $video]) {
            Materi::updateOrCreate(
                ['mata_pelajaran_id' => $mp->id, 'judul' => $judul],
                ['user_id' => $gr->id, 'deskripsi' => $deskripsi, 'file_materi' => $file, 'file_nama' => $file, 'video_url' => $video]
            );
        }

        // ===== Tugas (6 contoh: 4 file + 2 form) =====
        $tugasFile = [
            [$mapel['RPL-WEB'], $kelasX_RPL, $guru['Budi Santoso'], 'Membuat Halaman Profil Pribadi', 'Buatlah halaman web profil pribadi menggunakan HTML dan CSS. Tampilkan foto, biodata, dan hobi. Gunakan minimal 2 selektor CSS dan 1 flexbox.', now()->addDays(7)],
            [$mapel['RPL-BD'], $kelasX_RPL, $guru['Siti Rahayu'], 'ERD Sistem Perpustakaan', 'Rancang ERD untuk sistem perpustakaan sekolah. Identifikasi minimal 5 entitas dan hubungannya. Upload hasil dalam bentuk PDF atau gambar.', now()->addDays(5)],
            [$mapel['UMUM-MTK'], $kelasX_RPL, $guru['Andi Wijaya'], 'Latihan Soal Persamaan Linear', 'Kerjakan 10 soal persamaan linear yang terlampir. Tuliskan langkah penyelesaian secara lengkap di kertas lalu foto dan upload.', now()->addDays(3)],
            [$mapel['TKJ-SRV'], $kelasX_TKJ, $guru['Rudi Hartono'], 'Ringkasan Jaringan Komputer', 'Buat ringkasan 1 halaman tentang model OSI dan TCP/IP beserta perbandingannya. Upload dalam format PDF.', now()->addDays(10)],
        ];
        $tugas = [];
        foreach ($tugasFile as [$mp, $kls, $gr, $judul, $desk, $deadline]) {
            $tugas[] = Tugas::updateOrCreate(
                ['mata_pelajaran_id' => $mp->id, 'judul' => $judul],
                ['user_id' => $gr->id, 'kelas_id' => $kls->id, 'deskripsi' => $desk, 'batas_pengumpulan' => $deadline->toDateString(), 'tipe' => 'file', 'form_data' => null]
            );
        }

        $formWeb = [
            ['text' => 'Apa yang dimaksud dengan tag semantic pada HTML5?', 'type' => 'essay', 'options' => [], 'required' => true],
            ['text' => 'Manakah berikut ini yang merupakan selektor CSS yang benar?', 'type' => 'multiple', 'options' => ['.class', '#id', 'element', 'Semua benar'], 'required' => true],
            ['text' => 'Properti CSS apa saja yang kamu ketahui? (boleh lebih dari satu)', 'type' => 'checkbox', 'options' => ['color', 'margin', 'display', 'font-size'], 'required' => true],
            ['text' => 'Pilih framework CSS favoritmu', 'type' => 'dropdown', 'options' => ['Bootstrap', 'Tailwind', 'Bulma', 'Tidak pakai framework'], 'required' => false],
        ];
        $tugas[] = Tugas::updateOrCreate(
            ['mata_pelajaran_id' => $mapel['RPL-WEB']->id, 'judul' => 'Kuis Singkat: Dasar CSS'],
            ['user_id' => $guru['Budi Santoso']->id, 'kelas_id' => $kelasX_RPL->id, 'deskripsi' => 'Jawab pertanyaan singkat berikut tentang dasar-dasar CSS. Formulir akan otomatis diisi secara online.', 'batas_pengumpulan' => now()->addDays(2)->toDateString(), 'tipe' => 'form', 'form_data' => $formWeb]
        );

        $formBd = [
            ['text' => 'Jelaskan perbedaan antara 1NF, 2NF, dan 3NF', 'type' => 'essay', 'options' => [], 'required' => true],
            ['text' => 'Pada normalisasi, tabel dikatakan 2NF jika...', 'type' => 'multiple', 'options' => ['Sudah 1NF dan Tidak ada dependensi parsial', 'Semua atribut bergantung penuh pada primary key', 'Tidak ada dependensi transitif', 'A dan C benar'], 'required' => true],
        ];
        $tugas[] = Tugas::updateOrCreate(
            ['mata_pelajaran_id' => $mapel['RPL-BD']->id, 'judul' => 'Kuis Normalisasi Basis Data'],
            ['user_id' => $guru['Siti Rahayu']->id, 'kelas_id' => $kelasX_RPL->id, 'deskripsi' => 'Kerjakan kuis singkat tentang normalisasi basis data', 'batas_pengumpulan' => now()->addDays(4)->toDateString(), 'tipe' => 'form', 'form_data' => $formBd]
        );

        // ===== Nilai contoh (mapel RPL-WEB untuk siswa X RPL) =====
        $webMapel = $mapel['RPL-WEB'];
        foreach ($kelasX_RPL->users()->where('role', 'siswa')->get() as $s) {
            Nilai::updateOrCreate(
                ['siswa_id' => $s->id, 'mata_pelajaran_id' => $webMapel->id, 'semester' => 1, 'tahun_ajaran' => $thAjaran],
                ['kelas_id' => $kelasX_RPL->id, 'tugas' => rand(75, 95), 'uts' => rand(70, 92), 'uas' => rand(72, 96)]
            );
        }

        // ===== Jadwal Guru (contoh agenda mengajar) =====
        $jadwalData = [
            [$mapel['RPL-WEB']->id, $kelasX_RPL->id, $guru['Budi Santoso']->id, 'senin', '07:30', '09:10', 'Lab Komputer 1'],
            [$mapel['RPL-BD']->id, $kelasX_RPL->id, $guru['Siti Rahayu']->id, 'senin', '09:20', '11:00', 'Lab Komputer 2'],
            [$mapel['UMUM-MTK']->id, $kelasX_RPL->id, $guru['Andi Wijaya']->id, 'selasa', '07:30', '09:10', 'Ruang 3'],
            [$mapel['RPL-WEB']->id, $kelasX_RPL->id, $guru['Budi Santoso']->id, 'rabu', '10:00', '11:40', 'Lab Komputer 1'],
            [$mapel['RPL-BD']->id, $kelasX_RPL->id, $guru['Siti Rahayu']->id, 'kamis', '07:30', '09:10', 'Lab Komputer 2'],
            [$mapel['TKJ-SRV']->id, $kelasX_TKJ->id, $guru['Rudi Hartono']->id, 'jumat', '08:00', '09:40', 'Lab Jaringan'],
            [$mapel['UMUM-BIN']->id, $kelasX_TKJ->id, $guru['Dewi Lestari']->id, 'senin', '13:00', '14:40', 'Ruang 2'],
            [$mapel['DKV-DG']->id, $kelasXI_RPL->id, $guru['Maya Anggraini']->id, 'kamis', '13:00', '14:40', 'Studio Desain'],
        ];
        foreach ($jadwalData as [$mpId, $klsId, $grId, $hari, $mulai, $selesai, $ruang]) {
            Jadwal::updateOrCreate(
                ['mata_pelajaran_id' => $mpId, 'kelas_id' => $klsId, 'hari' => $hari, 'jam_mulai' => $mulai],
                ['guru_id' => $grId, 'jam_selesai' => $selesai, 'ruangan' => $ruang]
            );
        }

        $this->command?->info('Seeder LMS berhasil dijalankan.');
    }
}
