<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'role' => 'siswa',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $response->assertSessionHas('user_role', 'siswa');
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHas('error', 'Email atau password salah.');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
            'aktif' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHas('error', 'Akun sedang dinonaktifkan oleh admin.');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_dashboard_redirects_guest_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_unverified_siswa_is_redirected_to_verification_notice(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'siswa']);

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('verification.notice'));
    }

    public function test_admin_can_access_dashboard_without_email_verification(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'admin']);

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    public function test_siswa_cannot_access_admin_user_management(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($user)->get('/admin/users')->assertForbidden();
    }
}
