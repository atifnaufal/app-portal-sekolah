<?php

namespace Tests\Feature;

use App\Models\GlobalPost;
use App\Models\GlobalStory;
use App\Models\Kelas;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GlobalPortalTest extends TestCase
{
    use RefreshDatabase;

    private function makeGuru(School $school, string $email): User
    {
        $kelas = Kelas::firstOrCreate(
            ['nama' => 'X-T', 'school_id' => $school->id],
            ['tingkat' => 10, 'tahun_ajaran' => '2026/2027']
        );

        return User::create([
            'name' => 'Guru '.$email, 'email' => $email, 'password' => Hash::make('secret123'),
            'role' => 'guru', 'aktif' => true, 'school_id' => $school->id, 'kelas_id' => $kelas->id,
            'nik' => 'NIK'.$email, 'no_hp' => '081',
        ]);
    }

    private function sessionFor(User $u): array
    {
        return ['user_id' => $u->id, 'user_role' => $u->role, 'admin_name' => $u->name, 'school_id' => $u->school_id];
    }

    public function test_guru_can_post_with_auto_school(): void
    {
        Storage::fake('public');
        $school = School::create(['name' => 'S1', 'city' => 'C', 'slug' => 's1-t', 'is_active' => true]);
        $guru = $this->makeGuru($school, 'g1@t.id');

        $this->withSession($this->sessionFor($guru))
            ->post(route('global.portal.store'), ['content' => 'Halo dunia sekolah'])
            ->assertRedirect();

        $this->assertDatabaseHas('global_posts', [
            'user_id' => $guru->id, 'school_id' => $school->id, 'content' => 'Halo dunia sekolah',
        ]);
    }

    public function test_blocked_caption_rejected(): void
    {
        Storage::fake('public');
        $school = School::create(['name' => 'S1', 'city' => 'C', 'slug' => 's1-b', 'is_active' => true]);
        $guru = $this->makeGuru($school, 'g2@t.id');

        $this->withSession($this->sessionFor($guru))
            ->post(route('global.portal.store'), ['content' => 'ini konten bokep vulgar'])
            ->assertSessionHasErrors('content');

        $this->assertDatabaseCount('global_posts', 0);
    }

    public function test_reports_auto_hide_at_threshold(): void
    {
        $school = School::create(['name' => 'S1', 'city' => 'C', 'slug' => 's1-r', 'is_active' => true]);
        $author = $this->makeGuru($school, 'author@t.id');
        $post = GlobalPost::create(['user_id' => $author->id, 'school_id' => $school->id, 'content' => 'spam?']);

        foreach (['r1@t.id', 'r2@t.id', 'r3@t.id'] as $email) {
            $reporter = $this->makeGuru($school, $email);
            $this->withSession($this->sessionFor($reporter))
                ->post(route('global.portal.report', $post))
                ->assertRedirect();
        }

        $this->assertTrue($post->fresh()->is_hidden);
        $this->assertEquals(3, $post->fresh()->reports_count);
    }

    public function test_story_lifecycle(): void
    {
        Storage::fake('public');
        $school = School::create(['name' => 'S1', 'city' => 'C', 'slug' => 's1-s', 'is_active' => true]);
        $guru = $this->makeGuru($school, 'g3@t.id');

        $this->withSession($this->sessionFor($guru))
            ->post(route('global.portal.story.store'), [
                'image' => UploadedFile::fake()->image('story.jpg', 400, 400),
                'caption' => 'pagi!',
            ])
            ->assertRedirect();

        $this->assertEquals(1, GlobalStory::active()->count());

        // Cerita kedaluwarsa tidak masuk scope aktif.
        GlobalStory::query()->update(['expires_at' => now()->subHour()]);
        $this->assertEquals(0, GlobalStory::active()->count());
    }

    public function test_composer_has_no_school_dropdown(): void
    {
        $school = School::create(['name' => 'S1', 'city' => 'C', 'slug' => 's1-c', 'is_active' => true]);
        $guru = $this->makeGuru($school, 'g4@t.id');

        // Desktop UA (bukan mobile) → view global-portal.index
        $this->withServerVariables(['HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'])
            ->withSession($this->sessionFor($guru))
            ->get(route('global.portal'))
            ->assertOk()
            ->assertDontSee('name="school_id"', false);
    }
}
