<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_email_uses_indonesian_branded_template(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create(['role' => 'siswa']);

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo(
            $user,
            VerifyEmailNotification::class,
            function ($notification, $channels, $notifiable) {
                $mail = $notification->toMail($notifiable);

                $this->assertSame('Verifikasi Email - ' . config('app.name'), $mail->subject);
                $this->assertSame('emails.verify-email', $mail->view);
                $this->assertArrayHasKey('url', $mail->viewData);
                $this->assertStringContainsString('/email/verify/', $mail->viewData['url']);

                return true;
            }
        );
    }

    public function test_user_can_verify_email_with_valid_signed_url(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'siswa']);

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect('/dashboard');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verification_fails_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'siswa']);

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->getKey(),
            'hash' => sha1('email-salah@sekolah.com'),
        ]);

        $this->actingAs($user)->get($url)->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_resend_link_shows_success_message_with_recipient(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create(['role' => 'siswa']);

        $this->actingAs($user)
            ->from('/email/verify')
            ->post('/email/verification-notification')
            ->assertRedirect('/email/verify')
            ->assertSessionHas('message', 'Link verifikasi baru telah dikirim ke ' . $user->email . '!');

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_resend_link_is_skipped_when_email_already_verified(): void
    {
        Notification::fake();
        $user = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($user)
            ->post('/email/verification-notification')
            ->assertRedirect('/dashboard');

        Notification::assertNotSentTo($user, VerifyEmailNotification::class);
    }

    public function test_resend_link_shows_error_when_smtp_fails(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'siswa']);

        $this->mock(MailFactory::class, function ($mock) {
            $mock->shouldReceive('mailer')->andReturnSelf();
            $mock->shouldReceive('send')->andThrow(new \RuntimeException('SMTP server down'));
        });

        $this->actingAs($user)
            ->from('/email/verify')
            ->post('/email/verification-notification')
            ->assertRedirect('/email/verify')
            ->assertSessionHas('error', 'Gagal mengirim email verifikasi. Silakan coba beberapa saat lagi atau hubungi admin.');
    }

    public function test_registration_still_creates_account_when_email_fails_to_send(): void
    {
        Setting::setValue('registration_siswa_enabled', '1');
        $kelas = Kelas::create(['nama' => 'X IPA 1', 'tingkat' => 'X', 'tahun_ajaran' => '2026/2027']);

        $this->mock(MailFactory::class, function ($mock) {
            $mock->shouldReceive('mailer')->andReturnSelf();
            $mock->shouldReceive('send')->andThrow(new \RuntimeException('SMTP server down'));
        });

        $response = $this->post('/register', [
            'role' => 'siswa',
            'nik' => '11223344',
            'name' => 'Siswa Baru',
            'no_hp' => '081234567890',
            'email' => 'siswa.gagal@sekolah.com',
            'kelas_id' => $kelas->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Akun tetap terbuat meskipun email verifikasi gagal dikirim.
        $this->assertDatabaseHas('users', ['email' => 'siswa.gagal@sekolah.com']);
        $response->assertSessionHas('error');
    }
}
