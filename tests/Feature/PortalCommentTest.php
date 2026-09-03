<?php

namespace Tests\Feature;

use App\Models\GlobalPost;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_flow(): void
    {
        $school = School::create(['name' => 'S1', 'city' => 'C', 'slug' => 's1-cm', 'is_active' => true]);
        $guru = User::create([
            'name' => 'Guru', 'email' => 'gcm@t.id', 'password' => Hash::make('secret123'),
            'role' => 'guru', 'aktif' => true, 'school_id' => $school->id, 'nik' => 'NIKCM1', 'no_hp' => '081',
        ]);
        $post = GlobalPost::create(['user_id' => $guru->id, 'school_id' => $school->id, 'content' => 'topik']);
        $sess = ['user_id' => $guru->id, 'user_role' => 'guru', 'admin_name' => 'Guru', 'school_id' => $school->id];

        // Web biasa.
        $this->withSession($sess)
            ->post(route('global.portal.comment', $post), ['body' => 'komentar pertama'])
            ->assertRedirect();
        $this->assertDatabaseHas('global_comments', ['global_post_id' => $post->id, 'body' => 'komentar pertama']);
        $this->assertEquals(1, $post->fresh()->comments_count);

        // AJAX.
        $this->withSession($sess)
            ->postJson(route('global.portal.comment', $post), ['body' => 'komentar kedua'])
            ->assertOk()
            ->assertJsonPath('ok', true);
        $this->assertEquals(2, $post->fresh()->comments_count);

        // Komentar terblokir moderasi.
        $this->withSession($sess)
            ->post(route('global.portal.comment', $post), ['body' => 'konten bokep vulgar'])
            ->assertSessionHasErrors('body');
    }
}
