<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailSimulatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulator_is_not_available_outside_local_environment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->unverified()->create();

        $this->actingAs($admin)->get('/dev/email-simulator')->assertNotFound();
        $this->actingAs($admin)->post('/dev/email-simulator/'.$user->id)->assertNotFound();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_guest_is_redirected_to_login_in_local_environment(): void
    {
        $this->app->instance('env', 'local');

        $this->get('/dev/email-simulator')->assertRedirect(route('login'));
    }

    public function test_non_admin_is_forbidden_in_local_environment(): void
    {
        $this->app->instance('env', 'local');
        $siswa = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($siswa)->get('/dev/email-simulator')->assertForbidden();
    }

    public function test_admin_can_view_pending_users_in_local_environment(): void
    {
        $this->app->instance('env', 'local');
        $admin = User::factory()->create(['role' => 'admin']);
        $pending = User::factory()->unverified()->create(['role' => 'siswa']);

        $this->actingAs($admin)->get('/dev/email-simulator')
            ->assertOk()
            ->assertSee($pending->email);
    }

    public function test_admin_can_instantly_verify_user_in_local_environment(): void
    {
        $this->app->instance('env', 'local');
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->unverified()->create(['role' => 'siswa']);

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->actingAs($admin)
            ->post('/dev/email-simulator/'.$user->id);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
