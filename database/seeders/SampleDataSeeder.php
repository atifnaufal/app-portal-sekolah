<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user if not exists
        $admin = User::firstOrCreate([
            'name' => 'Admin Smp',
            'email' => 'admin@sekolah.sch.id',
            'role' => 'admin',
        ], ['password' => bcrypt('password123')]);
        $admin->forceFill(['email_verified_at' => now()])->save();

        // Create sample classes
        $kelasX = Kelas::firstOrCreate(['nama' => 'X IPA', 'tingkat' => 'X', 'tahun_ajaran' => '2025/2026', 'pembina_id' => $admin->id]);
        $kelasXI = Kelas::firstOrCreate(['nama' => 'XI IPA', 'tingkat' => 'XI', 'tahun_ajaran' => '2025/2026', 'pembina_id' => $admin->id]);
        $kelasXII = Kelas::firstOrCreate(['nama' => 'XII IPA', 'tingkat' => 'XII', 'tahun_ajaran' => '2025/2026', 'pembina_id' => $admin->id]);

        // Create sample subject teachers (gurus)
        $guruB = User::firstOrCreate([
            'name' => 'Budi Santoso, S.Pd',
            'email' => 'budi@sekolah.sch.id',
            'role' => 'guru',
        ]);
        $guruC = User::firstOrCreate([
            'name' => 'Citra Dewi, S.Pd',
            'email' => 'citra@sekolah.sch.id',
            'role' => 'guru',
        ]);
        $guruD = User::firstOrCreate([
            'name' => 'Doni Wijaya, S.Pd',
            'email' => 'doni@sekolah.sch.id',
            'role' => 'guru',
        ]);

        // Assign kelas to gurus
        $kelasX->update(['pembina_id' => $guruB->id]);
        $kelasXI->update(['pembina_id' => $guruC->id]);
        $kelasXII->update(['pembina_id' => $guruD->id]);

        // Create sample students (siswa)
        $studentNames = [
            ['nama' => 'Aldi Pratama', 'kelas_id' => $kelasX->id],
            ['nama' => 'Bela Sari', 'kelas_id' => $kelasX->id],
            ['nama' => 'Charlie Oktavia', 'kelas_id' => $kelasX->id],
            ['nama' => 'Dewi Putri', 'kelas_id' => $kelasXI->id],
            ['nama' => 'Evan Saputra', 'kelas_id' => $kelasXI->id],
            ['nama' => 'Fitri Risma', 'kelas_id' => $kelasXI->id],
            ['nama' => 'Galih Pratama', 'kelas_id' => $kelasXII->id],
            ['nama' => 'Hana Syafira', 'kelas_id' => $kelasXII->id],
        ];

        $students = [];
        foreach ($studentNames as $sn) {
            $s = User::firstOrCreate([
                'name' => $sn['nama'],
                'email' => strtolower(str_replace([' ', '.', ','], '', $sn['nama'])) . '@sekolah.sch.id',
                'role' => 'siswa',
                'kelas_id' => $sn['kelas_id'],
            ]);
            $students[$sn['nama']] = $s;
        }

        // Create sample subjects per class
        // X IPA: Bahasa Indonesia, Matematika, IPA
        // XI IPA: Matematika, Fisika, Kimia
        // XII IPA: Matematika, Kimia, Bahasa Inggris

        $subjectsX = [
            'Bahasa Indonesia' => ['guru_id' => $guruB->id, 'mapel_code' => 'INDO'],
            'Matematika' => ['guru_id' => $guruC->id, 'mapel_code' => 'MATH'],
            'Fisika' => ['guru_id' => $guruD->id, 'mapel_code' => 'FIS'],
        ];

        $subjectsXI = [
            'Matematika' => ['guru_id' => $guruC->id, 'mapel_code' => 'MATH'],
            'Fisika' => ['guru_id' => $guruD->id, 'mapel_code' => 'FIS'],
            'Kimia' => ['guru_id' => $guruB->id, 'mapel_code' => 'KIM'],
        ];

        $subjectsXII = [
            'Matematika' => ['guru_id' => $guruC->id, 'mapel_code' => 'MATH'],
            'Kimia' => ['guru_id' => $guruB->id, 'mapel_code' => 'KIM'],
            'Bahasa Inggris' => ['guru_id' => $guruD->id, 'mapel_code' => 'ING'],
        ];

        // Create MataPelajaran and Nilai for each class
        $now = now();
        $tahunAjaran = $now->format('Y').'/'.($now->format('Y') + 1);

        // Helper to create nilai records
        $createNilai = function ($siswa, $mp, $tugas, $uts, $uas, $semester) use ($tahunAjaran, $students) {
            $nilaiKey = $siswa->id . '_' . $mapel->id;
            $existing = Nilai::where('siswa_id', $siswa->id)
                ->where('mata_pelajaran_id', $mapel->id)
                ->where('semester', $semester)
                ->first();

            if (!$existing) {
                Nilai::create([
                    'siswa_id' => $siswa->id,
                    'mata_pelajaran_id' => $mapel->id,
                    'kelas_id' => $siswa->kelas_id,
                    'tahun_ajaran' => $tahunAjaran,
                    'semester' => $semester,
                    'tugas' => $tugas,
                    'uts' => $uts,
                    'uas' => $uas,
                ]);
            }
        };

        // Semester 1 data for X IPA
        $mapelsX1 = [
            'Bahasa Indonesia' => MataPelajaran::firstOrCreate(['nama' => 'Bahasa Indonesia', 'kelas_id' => $kelasX->id, 'guru_id' => $guruB->id]),
            'Matematika' => MataPelajaran::firstOrCreate(['nama' => 'Matematika', 'kelas_id' => $kelasX->id, 'guru_id' => $guruC->id]),
            'Fisika' => MataPelajaran::firstOrCreate(['nama' => 'Fisika', 'kelas_id' => $kelasX->id, 'guru_id' => $guruD->id]),
        ];

        $xipaStudents = [$students['Aldi Pratama'], $students['Bela Sari'], $students['Charlie Oktavia']];
        foreach ($xipaStudents as $idx => $siswa) {
            $sem = 1;
            $rand = function($min, $max) { return rand($min, $max); };
            foreach ($mapelsX1 as $mp => $mpObj) {
                $tugas = $rand(0, 100);
                $uts = $rand(0, 100);
                $uas = $rand(0, 100);
                $createNilai($siswa, $mpObj, $tugas, $uts, $uas, $sem);
            }
        }

        // Semester 2 data for X IPA
        $mapelsX2 = [
            'Bahasa Indonesia' => MataPelajaran::firstOrCreate(['nama' => 'Bahasa Indonesia', 'kelas_id' => $kelasX->id, 'guru_id' => $guruB->id, 'semester' => 2]),
            'Matematika' => MataPelajaran::firstOrCreate(['nama' => 'Matematika', 'kelas_id' => $kelasX->id, 'guru_id' => $guruC->id, 'semester' => 2]),
            'Fisika' => MataPelajaran::firstOrCreate(['nama' => 'Fisika', 'kelas_id' => $kelasX->id, 'guru_id' => $guruD->id, 'semester' => 2]),
        ];

        $xipaStudents2 = [$students['Aldi Pratama'], $students['Bela Sari'], $students['Charlie Oktavia']];
        foreach ($xipaStudents2 as $siswa) {
            $sem = 2;
            $rand = function($min, $max) { return rand($min, $max); };
            foreach ($mapelsX2 as $mp => $mpObj) {
                $tugas = $rand(0, 100);
                $uts = $rand(0, $max(100, $rand(0, 100)));
                $uas = $rand(0, 100);
                $createNilai($siswa, $mpObj, $tugas, $uts, $uas, $sem);
            }
        }

        // Similar for XI and XII grades...
        // ( abbreviated for brevity - full data seeding would continue similarly )

        // Create sample Tugas (assignments) for tutor gurus
        $today = now();
        $createTugas = function ($judul, $mapel, $guru, $kelas, $batas) use ($today) {
            $t = \App\Models\Tugas::firstOrCreate([
                'judul' => $judul,
                'user_id' => $guru->id,
                'kelas_id' => $kelas->id,
                'mata_pelajaran_id' => $mapel->id,
                'batas_pengumpulan' => $batas,
                'tipe' => 'file',
            ]);
            // Create some sample submissions
            foreach ($kelas->users->where('role', 'siswa') as $siswa) {
                $statuses = ['terkirim', 'dinilai', 'tidak_mengumpulkan'];
                $status = $statuses[array_rand($statuses)];
                $nilai = $status === 'dinilai' ? rand(50, 100) : null;
                $feedback = $status === 'dinilai' ? 'Baik kerjanya' : null;
                $dikumpulkan = $status === 'tidak_mengumpulkan' ? null : $today->copy()->subDay(rand(1, 30));

                \App\Models\PengumpulanTugas::firstOrCreate([
                    'tugas_id' => $t->id,
                    'siswa_id' => $siswa->id,
                    'status' => $status,
                    'nilai' => $nilai,
                    'feedback_guru' => $feedback,
                    'dikumpulkan_pada' => $dikumpulan,
                ]);
            }
            return $t;
        };

        // Create tugas for each grade
        $tugasX = $createTugas(
            'Tugas Fisika Pertemuan 5',
            $subjectsX['Fisika'],
            $guruD,
            $kelasX,
            $today->copy()->addWeek(2)
        );

        $tugasXI = $createTugas(
            'Tugas Kimia Nasional',
            $subjectsXI['Kimia'],
            $guruB,
            $kelasXI,
            $today->copy()->addWeek(1)
        );

        $tugasXII = $createTugas(
            'Tugas Matematika Kompetisi',
            $subjectsXII['Matematika'],
            $guruC,
            $kelasXII,
            $today->copy()->addWeek(3)
        );

        // Create a siswa submission for testing tugas export
        $submission = \App\Models\PengumpulanTugas::firstOrCreate([
            'tugas_id' => $tugasX->id,
            'siswa_id' => $students['Aldi Pratama']->id,
            'status' => 'dinilai',
            'nilai' => 85,
            'feedback_guru' => 'Kerja bagus, teruskan!',
            'dikumpulkan_pada' => $today->copy()->subDay(5),
        ]);

        // Create another submission with revisi
        \App\Models\PengumpulanTugas::firstOrCreate([
            'tugas_id' => $tugasX->id,
            'siswa_id' => $students['Bela Sari']->id,
            'status' => 'perlu_revisi',
            'nilai' => null,
            'feedback_guru' => 'Perlu perbaikan soal 3',
            'dikumpulkan_pada' => $today->copy()->subDay(10),
            'revisi_aktif' => true,
        ]);

        // Create "not submitted" student
        \App\Models\PengumpulanTugas::firstOrCreate([
            'tugas_id' => $tugasX->id,
            'siswa_id' => $students['Charlie Oktavia']->id,
            'status' => 'tidak_mengumpulkan',
            'nilai' => null,
            'feedback_guru' => null,
            'dikumpulkan_pada' => null,
            'revisi_aktif' => false,
        ]);

        // Force refresh relationships
        $kelasX->load(['users' => fn($q) => $q->where('role', 'siswa')]);
        $kelasXI->load(['users' => fn($q) => $q->where('role', 'siswa')]);
        $kelasXII->load(['users' => fn($q) => $q->where('role', 'siswa')]);

        // Force create mata pelajaran relationships
        MataPelajaran::whereIn('nama', ['Bahasa Indonesia', 'Matematika', 'Fisika', 'Kimia', 'Bahasa Inggris'])
            ->each(function ($mp) {
                $mp->load('kelas', 'guru');
            });

        echo "Sample data seeded successfully!\n";
    }
}