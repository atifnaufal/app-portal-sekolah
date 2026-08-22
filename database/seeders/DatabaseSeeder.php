<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $jurusan = Jurusan::firstOrCreate(['kode' => 'RPL'], ['nama' => 'Rekayasa Perangkat Lunak']);
        $kelas = Kelas::firstOrCreate(['nama' => 'XII RPL 1', 'tahun_ajaran' => '2025/2026'], ['tingkat' => 3]);

        User::updateOrCreate(['email' => 'admin@sekolah.test'], ['name' => 'Administrator', 'password' => Hash::make('admin123'), 'role' => 'admin']);
        User::updateOrCreate(['email' => 'guru@sekolah.test'], ['name' => 'Guru Demo', 'password' => Hash::make('guru123'), 'role' => 'guru', 'kelas_id' => $kelas->id]);
        User::updateOrCreate(['email' => 'siswa@sekolah.test'], ['name' => 'Siswa Demo', 'password' => Hash::make('siswa123'), 'role' => 'siswa', 'kelas_id' => $kelas->id]);
    }
}
