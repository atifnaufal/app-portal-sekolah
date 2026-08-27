<?php

namespace Tests\Feature;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_notification_center(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/notifikasi')->assertOk();
    }

    public function test_non_admin_cannot_view_notification_center(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($guru)->get('/notifikasi')->assertForbidden();
        $this->actingAs($siswa)->get('/notifikasi')->assertForbidden();
    }

    public function test_teacher_sees_own_notifications_and_they_are_marked_as_read(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $unread = Notifikasi::create([
            'user_id' => $guru->id,
            'judul' => 'Tugas baru tersedia',
            'pesan' => 'Ada tugas baru untuk kelas Anda.',
            'url' => '/tugas',
            'dibaca_pada' => null,
        ]);

        $siswa = User::factory()->create(['role' => 'siswa']);
        Notifikasi::create([
            'user_id' => $siswa->id,
            'judul' => 'Pengingat SPP',
            'pesan' => 'Notifikasi milik siswa lain.',
            'url' => null,
            'dibaca_pada' => null,
        ]);

        $response = $this->actingAs($guru)->get('/notifikasi-saya');

        $response->assertOk()
            ->assertSee('Tugas baru tersedia')
            ->assertDontSee('Pengingat SPP');
        $this->assertNotNull($unread->fresh()->dibaca_pada);
    }

    public function test_admin_cannot_access_own_notifications_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/notifikasi-saya')->assertForbidden();
    }

    public function test_teacher_can_view_task_notifications(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        Notifikasi::create([
            'user_id' => $guru->id,
            'judul' => 'Jawaban tugas diterima',
            'pesan' => 'Seorang siswa mengumpulkan tugas.',
            'url' => null,
            'dibaca_pada' => null,
        ]);

        $this->actingAs($guru)->get('/tugas-notifikasi')
            ->assertOk()
            ->assertSee('Jawaban tugas diterima');
    }

    public function test_siswa_cannot_access_task_notifications(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($siswa)->get('/tugas-notifikasi')->assertForbidden();
    }
}
