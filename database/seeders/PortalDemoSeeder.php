<?php

namespace Database\Seeders;

use App\Models\GlobalPost;
use App\Models\Kelas;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data demo untuk testing ala developer: 3 sekolah (bawaan migrasi),
 * tiap sekolah: 1 kelas + 2 guru + 4 siswa + 1 admin + 2 postingan.
 * Total: 6 guru, 12 siswa, 3 admin. Idempotent via firstOrCreate.
 */
class PortalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $slugs = ['portal-pusat', 'sman1-jkt', 'smk-telkom'];
        $guruNames = ['Budi Santoso', 'Citra Dewi'];
        $siswaNames = ['Aldi Pratama', 'Bela Sari', 'Charlie Oktavia', 'Dewi Putri'];

        foreach ($slugs as $i => $slug) {
            $school = School::where('slug', $slug)->first();
            if (! $school) {
                $this->command->warn("Sekolah slug {$slug} tidak ada — lewati.");

                continue;
            }
            $tag = strtolower(str_replace([' ', '-'], '', $slug));

            $kelas = Kelas::firstOrCreate(
                ['nama' => 'X-'.($i + 1).' Demo', 'school_id' => $school->id],
                ['tingkat' => 10, 'tahun_ajaran' => '2026/2027']
            );

            // Admin sekolah.
            User::firstOrCreate(
                ['email' => "admin.{$tag}@sekolah.sch.id"],
                [
                    'name' => "Admin {$school->name}", 'password' => Hash::make('password123'),
                    'role' => 'admin', 'aktif' => true, 'school_id' => $school->id,
                    'nik' => 'ADM'.$school->id.'001', 'no_hp' => '0812'.$school->id.'000001',
                ]
            );

            // Guru.
            foreach ($guruNames as $g => $guruName) {
                User::firstOrCreate(
                    ['email' => "guru{$g}.{$tag}@sekolah.sch.id"],
                    [
                        'name' => "{$guruName} ({$school->city})", 'password' => Hash::make('password123'),
                        'role' => 'guru', 'aktif' => true, 'school_id' => $school->id, 'kelas_id' => $kelas->id,
                        'nik' => 'GR'.$school->id.'00'.$g, 'no_hp' => '0813'.$school->id.'00000'.$g,
                    ]
                );
            }

            // Siswa.
            $siswaIds = [];
            foreach ($siswaNames as $s => $siswaName) {
                $u = User::firstOrCreate(
                    ['email' => "siswa{$s}.{$tag}@sekolah.sch.id"],
                    [
                        'name' => "{$siswaName} ({$school->city})", 'password' => Hash::make('password123'),
                        'role' => 'siswa', 'aktif' => true, 'school_id' => $school->id, 'kelas_id' => $kelas->id,
                        'nik' => 'SW'.$school->id.'00'.$s, 'no_hp' => '0814'.$school->id.'00000'.$s,
                    ]
                );
                $siswaIds[] = $u->id;
            }

            // Postingan contoh (tanpa gambar agar ringan).
            foreach ([
                "Halo dari {$school->name}! Ini postingan contoh untuk testing Global Portal.",
                "Jadwal ujian tengah semester {$school->name} akan segera diumumkan. Pantau terus portal ini.",
            ] as $content) {
                GlobalPost::firstOrCreate(
                    ['user_id' => $siswaIds[0], 'content' => $content],
                    ['school_id' => $school->id]
                );
            }
        }

        $this->command->info('PortalDemoSeeder: 3 sekolah, 6 guru, 12 siswa, 3 admin, 6 postingan.');
    }
}
