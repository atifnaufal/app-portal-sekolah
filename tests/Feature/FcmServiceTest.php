<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FcmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FcmServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_degrades_gracefully_without_config(): void
    {
        $user = User::create([
            'name' => 'U', 'email' => 'u@fcm.id', 'password' => Hash::make('secret123'),
            'role' => 'siswa', 'aktif' => true, 'nik' => 'NIKFCM1', 'no_hp' => '081',
        ]);
        \App\Models\DeviceToken::create(['user_id' => $user->id, 'token' => 'fake-token-123', 'platform' => 'android']);

        // Bila FCM belum dikonfigurasi → mode off. Bila sudah → coba kirim.
        $res = FcmService::sendToUser($user->id, 'Judul', 'Pesan');

        $this->assertContains($res['mode'], ['off', 'fcm', 'error']);
        $this->assertIsInt($res['sent']);
        $this->assertArrayHasKey('error', $res);
    }

    public function test_send_without_tokens_is_off(): void
    {
        $user = User::create([
            'name' => 'U2', 'email' => 'u2@fcm.id', 'password' => Hash::make('secret123'),
            'role' => 'siswa', 'aktif' => true, 'nik' => 'NIKFCM2', 'no_hp' => '081',
        ]);

        $res = FcmService::sendToUser($user->id, 'Judul', 'Pesan');

        $this->assertSame('off', $res['mode']);
    }

    public function test_device_token_api_register_and_remove(): void
    {
        $user = User::create([
            'name' => 'U3', 'email' => 'u3@fcm.id', 'password' => Hash::make('secret123'),
            'role' => 'siswa', 'aktif' => true, 'nik' => 'NIKFCM3', 'no_hp' => '081',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/device-token', ['token' => 'dev-token-xyz', 'platform' => 'android'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('device_tokens', ['user_id' => $user->id, 'token' => 'dev-token-xyz']);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/device-token', ['token' => 'dev-token-xyz'])
            ->assertOk();

        $this->assertDatabaseMissing('device_tokens', ['token' => 'dev-token-xyz']);
    }

    public function test_notification_send_still_works_with_fcm_hook(): void
    {
        $user = User::create([
            'name' => 'U4', 'email' => 'u4@fcm.id', 'password' => Hash::make('secret123'),
            'role' => 'siswa', 'aktif' => true, 'nik' => 'NIKFCM4', 'no_hp' => '081',
        ]);

        \App\Helpers\NotificationHelper::send($user->id, 'Halo', 'Isi pesan');

        $this->assertDatabaseHas('notifikasi', ['user_id' => $user->id, 'judul' => 'Halo']);
    }
}
