<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_returns_404_when_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_registration_page_can_be_rendered_when_enabled(): void
    {
        Setting::setValue('registration_siswa_enabled', '1');

        $this->get('/register')->assertOk();
    }

    public function test_siswa_can_register_when_enabled(): void
    {
        Setting::setValue('registration_siswa_enabled', '1');
        $kelas = Kelas::create(['nama' => 'X IPA 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027']);

        $response = $this->post('/register', [
            'role' => 'siswa',
            'nik' => '12345678',
            'name' => 'Siswa Baru',
            'no_hp' => '081234567890',
            'email' => 'siswa@sekolah.com',
            'kelas_id' => $kelas->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $user = User::where('email', 'siswa@sekolah.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('siswa', $user->role);

        // Verifikasi email sudah dihapus: akun baru dibuat nonaktif sampai
        // admin menyetujuinya lewat halaman Manajemen Akun.
        $this->assertFalse($user->aktif);
    }

    public function test_registered_siswa_cannot_login_until_admin_approves(): void
    {
        Setting::setValue('registration_siswa_enabled', '1');
        $kelas = Kelas::create(['nama' => 'X IPA 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027']);

        $this->post('/register', [
            'role' => 'siswa',
            'nik' => '22334455',
            'name' => 'Siswa Menunggu',
            'no_hp' => '081234500000',
            'email' => 'menunggu@sekolah.com',
            'kelas_id' => $kelas->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->post('/login', [
            'email' => 'menunggu@sekolah.com',
            'password' => 'password123',
        ])->assertRedirect();

        $this->assertFalse(Auth::check());
        $this->assertNull(session('user_id'));
    }

    public function test_guru_cannot_register_when_only_siswa_registration_is_enabled(): void
    {
        Setting::setValue('registration_siswa_enabled', '1');
        $kelas = Kelas::create(['nama' => 'X IPA 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027']);

        $response = $this->post('/register', [
            'role' => 'guru',
            'nik' => '87654321',
            'name' => 'Guru Baru',
            'no_hp' => '089876543210',
            'email' => 'guru@sekolah.com',
            'kelas_id' => $kelas->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'guru@sekolah.com']);
    }

    public function test_registration_requires_valid_data(): void
    {
        Setting::setValue('registration_siswa_enabled', '1');

        $response = $this->post('/register', [
            'role' => 'siswa',
            'nik' => '123',
            'name' => '',
            'no_hp' => '',
            'email' => 'bukan-email',
            'kelas_id' => 999,
            'password' => 'pendek',
            'password_confirmation' => 'beda',
        ]);

        $response->assertSessionHasErrors(['nik', 'name', 'no_hp', 'email', 'kelas_id', 'password']);
    }
}
