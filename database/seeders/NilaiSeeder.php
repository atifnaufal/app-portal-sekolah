<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Nilai rekap siswa (semester 1) untuk setiap mata pelajaran.
 *
 * Idempoten: updateOrCreate pada (siswa_id, mata_pelajaran_id, semester).
 * Nilai deterministik agar terlihat realistis dan stabil di setiap eksekusi
 * (aman di-deploy ulang ke production via db:seed --force).
 */
class NilaiSeeder extends Seeder
{
    protected string $tahunAjaran = '2026/2027';
    protected int $semester = 1;

    public function run(): void
    {
        $nilaiBaru = 0;

        foreach (MataPelajaran::orderBy('id')->get() as $mapel) {
            $kelas = $mapel->kelas;
            if (! $kelas) {
                continue;
            }

            $siswas = User::where('role', 'siswa')
                ->where('kelas_id', $kelas->id)
                ->orderBy('name')
                ->get();

            foreach ($siswas as $siswa) {
                $tugas  = $this->score($siswa->id, $mapel->id, 0);
                $uts    = $this->score($siswa->id, $mapel->id, 1);
                $uas    = $this->score($siswa->id, $mapel->id, 2);

                Nilai::updateOrCreate(
                    [
                        'siswa_id'          => $siswa->id,
                        'mata_pelajaran_id' => $mapel->id,
                        'semester'          => $this->semester,
                        'tahun_ajaran'      => $this->tahunAjaran,
                    ],
                    [
                        'kelas_id' => $kelas->id,
                        'tugas'    => $tugas,
                        'uts'      => $uts,
                        'uas'      => $uas,
                    ]
                );
                $nilaiBaru++;
            }
        }

        $this->command?->info("Seeder nilai rekap berhasil (semester {$this->semester}, {$this->tahunAjaran}). Total record: {$nilaiBaru}.");
    }

    /**
     * Skor deterministik per (siswa, mapel, slot) agar stabil antar eksekusi.
     * slot: 0=tugas, 1=uts, 2=uas.
     */
    protected function score(int $siswaId, int $mapelId, int $slot): int
    {
        $seed = ($siswaId * 53) + ($mapelId * 17) + ($slot * 11);
        $base = 72 + ($seed % 23);                      // 72..94
        $delta = match ($slot) { 0 => -2, 1 => 0, 2 => 3 };
        $value = $base + $delta;

        return min(99, max(60, $value));
    }
}
