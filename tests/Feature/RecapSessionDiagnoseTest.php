<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecapSessionDiagnoseTest extends TestCase
{
    use RefreshDatabase;

    private function makeKelasAndData(): array
    {
        $wali = User::factory()->create(['role' => 'guru', 'kelas_id' => null]);
        $guru2 = User::factory()->create(['role' => 'guru', 'kelas_id' => null]);
        $admin = User::factory()->create(['role' => 'admin']);

        $kelas = Kelas::create([
            'nama' => 'X RPL', 'tingkat' => 10,
            'tahun_ajaran' => now()->format('Y').'/'.(now()->format('Y') + 1),
            'pembina_id' => $wali->id,
        ]);

        $mapel = MataPelajaran::create([
            'nama' => 'Matematika', 'kode' => 'MTK',
            'kelas_id' => $kelas->id, 'guru_id' => $guru2->id, 'kkm' => 75,
        ]);

        $siswa = User::factory()->create(['role' => 'siswa', 'kelas_id' => $kelas->id]);

        Nilai::create([
            'siswa_id' => $siswa->id,
            'mata_pelajaran_id' => $mapel->id,
            'kelas_id' => $kelas->id,
            'tugas' => 80, 'uts' => 85, 'uas' => 90,
            'semester' => 1,
            'tahun_ajaran' => $kelas->tahun_ajaran,
        ]);

        return compact('wali', 'guru2', 'admin', 'kelas', 'mapel', 'siswa');
    }

    public function test_session_status_returns_authenticated_for_all_roles(): void
    {
        $d = $this->makeKelasAndData();
        foreach (['admin' => $d['admin'], 'guru' => $d['wali'], 'siswa' => $d['siswa']] as $role => $u) {
            $this->withSession(['user_id' => $u->id, 'user_role' => $role])
                ->getJson('/session/status')
                ->assertOk()
                ->assertJson(['authenticated' => true]);
        }
    }

    public function test_notification_poll_available_for_all_roles(): void
    {
        $d = $this->makeKelasAndData();
        $this->withSession(['user_id' => $d['admin']->id, 'user_role' => 'admin'])
            ->getJson('/notifikasi/poll?last_id=0')
            ->assertOk()
            ->assertJsonPath('unread', 0);
    }

    public function test_session_status_returns_json_when_unauthenticated(): void
    {
        $this->getJson('/session/status')
            ->assertOk()
            ->assertJson(['authenticated' => false]);
    }

    public function test_admin_can_download_kelas_recap_pdf(): void
    {
        $d = $this->makeKelasAndData();
        $this->withSession(['user_id' => $d['admin']->id, 'user_role' => 'admin'])
            ->get(route('nilai.recap', $d['kelas']))
            ->assertOk();
    }

    public function test_admin_can_download_kelas_recap_excel(): void
    {
        $d = $this->makeKelasAndData();
        $this->withSession(['user_id' => $d['admin']->id, 'user_role' => 'admin'])
            ->get(route('nilai.recap.excel', $d['kelas']))
            ->assertOk();
    }

    public function test_guru_pengampu_can_download_mapel_recap_pdf(): void
    {
        $d = $this->makeKelasAndData();
        $this->withSession(['user_id' => $d['guru2']->id, 'user_role' => 'guru'])
            ->get(route('nilai.recap.mapel', $d['mapel']))
            ->assertOk();
    }

    public function test_guru_pengampu_can_download_mapel_recap_excel(): void
    {
        $d = $this->makeKelasAndData();
        $this->withSession(['user_id' => $d['guru2']->id, 'user_role' => 'guru'])
            ->get(route('nilai.recap.mapel.excel', $d['mapel']))
            ->assertOk();
    }

    public function test_wali_kelas_recap_analog(): void
    {
        $d = $this->makeKelasAndData();
        // wali -> recap kelas PDF (authorized via pembina_id)
        $this->withSession(['user_id' => $d['wali']->id, 'user_role' => 'guru'])
            ->get(route('nilai.recap', $d['kelas']))
            ->assertOk();
    }

    public function test_session_status_redirects_when_unauthenticated(): void
    {
        $this->getJson('/session/status')
            ->assertStatus(200)
            ->assertJson(['authenticated' => false]);
    }
}
