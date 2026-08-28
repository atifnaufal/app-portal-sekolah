<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecapDownloadVerifyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedData();
    }

    private function seedData(): void
    {
        // Buat struktur minimum: kelas, user (admin + siswa), mapel, nilai
        $kelas = \App\Models\Kelas::create([
            'nama' => 'X RPL', 'tingkat' => 10, 'tahun_ajaran' => '2026/2027', 'pembina_id' => null,
        ]);
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $siswa = \App\Models\User::factory()->create(['role' => 'siswa', 'kelas_id' => $kelas->id]);
        $guru = \App\Models\User::factory()->create(['role' => 'guru', 'kelas_id' => $kelas->id]);
        $mapel = \App\Models\MataPelajaran::create([
            'nama' => 'Pemrograman Web', 'kelas_id' => $kelas->id, 'guru_id' => $guru->id, 'kode' => 'RPL-WEB',
        ]);
        \App\Models\Nilai::create([
            'siswa_id' => $siswa->id,
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'tugas' => 80, 'uts' => 85, 'uas' => 90,
            'semester' => 1, 'tahun_ajaran' => '2026/2027',
        ]);

        $this->kelas = $kelas;
        $this->admin = $admin;
        $this->mapel = $mapel;
    }

    public function test_admin_can_download_recap_excel(): void
    {
        session(['user_id' => $this->admin->id, 'user_role' => 'admin']);
        $res = $this->get(route('nilai.recap.excel', $this->kelas) . '?semester=1');
        $res->assertStatus(200);
        $res->assertHeader('Content-Type', 'application/vnd.ms-excel');
        $this->assertStringContainsString('REKAPITULASI NILAI', $res->getContent());
    }

    public function test_admin_can_download_recap_mapel_excel(): void
    {
        session(['user_id' => $this->admin->id, 'user_role' => 'admin']);
        $res = $this->get(route('nilai.recap.mapel.excel', $this->mapel) . '?semester=1');
        $res->assertStatus(200);
        $this->assertStringContainsString('REKAPITULASI NILAI MATA PELAJARAN', $res->getContent());
    }

    public function test_admin_can_download_recap_periode_excel(): void
    {
        session(['user_id' => $this->admin->id, 'user_role' => 'admin']);
        $res = $this->get(route('nilai.recap.periode.excel') . '?periode=tahunan&tahun_ajaran=2026/2027');
        $res->assertStatus(200);
        $this->assertStringContainsString('REKAPITULASI NILAI SISWA', $res->getContent());
    }

    public function test_recap_excel_shows_student_scores(): void
    {
        session(['user_id' => $this->admin->id, 'user_role' => 'admin']);
        $res = $this->get(route('nilai.recap.excel', $this->kelas) . '?semester=1');
        $body = $res->getContent();
        $this->assertStringContainsString('Nama Siswa', $body);
        $this->assertStringContainsString('85', $body); // rata-rata (80+85+90)/3
    }
}
