<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Kontrak registrasi per-sekolah (kolom schools.reg_*_open):
 * - tidak ada sekolah buka → redirect ke halaman Terkunci
 * - sekolah buka → daftar dengan school_id, akun nonaktif menunggu approval
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function openSchool(bool $guru = false, bool $siswa = true): array
    {
        $school = School::create([
            'name' => 'SMA Tes', 'city' => 'Tes', 'slug' => 'sma-tes-reg',
            'is_active' => true, 'reg_guru_open' => $guru, 'reg_siswa_open' => $siswa,
        ]);
        $kelas = Kelas::create([
            'nama' => 'X IPA 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027',
            'school_id' => $school->id,
        ]);

        return [$school, $kelas];
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'role' => 'siswa',
            'nik' => '12345678',
            'name' => 'Siswa Baru',
            'no_hp' => '081234567890',
            'email' => 'siswa@sekolah.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $over);
    }

    public function test_registration_page_redirects_to_locked_when_all_closed(): void
    {
        $this->get('/register')->assertRedirect(route('feature.locked', ['msg' => 'Pendaftaran akun sedang ditutup untuk semua sekolah. Hubungi admin sekolah atau Admin Pusat.']));
    }

    public function test_registration_page_can_be_rendered_when_school_open(): void
    {
        [$school, $kelas] = $this->openSchool();

        $this->get('/register')->assertOk()->assertSee('[ID: '.$school->id.']');
    }

    public function test_siswa_can_register_when_enabled(): void
    {
        [$school, $kelas] = $this->openSchool();

        $response = $this->post('/register', $this->payload([
            'kelas_id' => $kelas->id, 'school_id' => $school->id,
        ]));

        $response->assertRedirect(route('register.syarat'));
        $user = User::where('email', 'siswa@sekolah.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('siswa', $user->role);
        $this->assertSame($school->id, (int) $user->school_id);

        // Verifikasi email sudah dihapus: akun baru dibuat nonaktif sampai
        // admin menyetujuinya lewat halaman Manajemen Akun.
        $this->assertFalse($user->aktif);

        // Halaman Syarat & Ketentuan tampil pasca-daftar.
        $this->get(route('register.syarat'))->assertOk()->assertSee('Ketentuan Penting');
    }

    public function test_registered_siswa_cannot_login_until_admin_approves(): void
    {
        [$school, $kelas] = $this->openSchool();

        $this->post('/register', $this->payload([
            'nik' => '22334455', 'name' => 'Siswa Menunggu', 'email' => 'menunggu@sekolah.com',
            'kelas_id' => $kelas->id, 'school_id' => $school->id,
        ]));

        $this->post('/login', [
            'email' => 'menunggu@sekolah.com',
            'password' => 'password123',
        ])->assertRedirect();

        $this->assertFalse(Auth::check());
        $this->assertNull(session('user_id'));
    }

    public function test_guru_cannot_register_when_school_opens_siswa_only(): void
    {
        [$school, $kelas] = $this->openSchool(guru: false, siswa: true);

        $response = $this->post('/register', $this->payload([
            'role' => 'guru', 'nik' => '87654321', 'name' => 'Guru Baru',
            'email' => 'guru@sekolah.com', 'kelas_id' => $kelas->id, 'school_id' => $school->id,
        ]));

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'guru@sekolah.com']);
    }

    public function test_registration_requires_valid_data(): void
    {
        [$school, $kelas] = $this->openSchool();

        $response = $this->post('/register', [
            'role' => 'siswa',
            'nik' => '123',
            'name' => '',
            'no_hp' => '',
            'email' => 'bukan-email',
            'kelas_id' => 999,
            'school_id' => $school->id,
            'password' => 'pendek',
            'password_confirmation' => 'beda',
        ]);

        $response->assertSessionHasErrors(['nik', 'name', 'no_hp', 'email', 'kelas_id', 'password']);
    }
}
