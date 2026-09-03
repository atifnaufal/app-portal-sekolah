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

    public function test_mobile_portal_renders_for_guru(): void
    {
        $school = School::create(['name' => 'S1', 'city' => 'C', 'slug' => 's1-m', 'is_active' => true]);
        $guru = $this->makeGuru($school, 'gm@t.id');
        GlobalPost::create(['user_id' => $guru->id, 'school_id' => $school->id, 'content' => 'halo mobile']);

        $this->withServerVariables(['HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 13; Mobile)'])
            ->withSession($this->sessionFor($guru))
            ->get(route('global.portal'))
            ->assertOk()
            ->assertSee('halo mobile');
    }

    public function test_super_admin_sees_hidden_posts_with_badge(): void
    {
        $school = School::create(['name' => 'S1', 'city' => 'C', 'slug' => 's1-h', 'is_active' => true]);
        $guru = $this->makeGuru($school, 'gh@t.id');
        $post = GlobalPost::create(['user_id' => $guru->id, 'school_id' => $school->id,
            'content' => 'rahasia', 'is_hidden' => true, 'reports_count' => 5]);
        $super = User::create([
            'name' => 'Pusat', 'email' => 'pusat@gp.id', 'password' => Hash::make('secret123'),
            'role' => 'admin', 'aktif' => true, 'nik' => 'ADMGPH', 'no_hp' => '081', 'school_id' => null,
        ]);

        // Guru tidak melihat postingan tersembunyi.
        $this->withServerVariables(['HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'])
            ->withSession($this->sessionFor($guru))
            ->get(route('global.portal'))
            ->assertOk()
            ->assertDontSee('rahasia');

        // Super admin melihat + badge + bisa unhide.
        $this->withServerVariables(['HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'])
            ->withSession(['user_id' => $super->id, 'user_role' => 'admin', 'admin_name' => 'Pusat',
                'is_super_admin' => true, 'school_id' => null])
            ->get(route('global.portal'))
            ->assertOk()
            ->assertSee('rahasia');

        $this->withSession(['user_id' => $super->id, 'user_role' => 'admin', 'admin_name' => 'Pusat',
                'is_super_admin' => true, 'school_id' => null])
            ->patch(route('admin.portal.unhide', $post))
            ->assertRedirect();

        $this->assertFalse($post->fresh()->is_hidden);
    }

    public function test_story_privacy_follow_school_admin_rules(): void
    {
        $a = School::create(['name' => 'A', 'city' => 'C', 'slug' => 'pv-a', 'is_active' => true]);
        $b = School::create(['name' => 'B', 'city' => 'C', 'slug' => 'pv-b', 'is_active' => true]);
        $author = $this->makeGuru($a, 'author@pv.id');
        $sameSchool = $this->makeGuru($a, 'same@pv.id');
        $otherSchool = $this->makeGuru($b, 'other@pv.id');
        $super = User::create([
            'name' => 'Pusat', 'email' => 'pusat@pv.id', 'password' => Hash::make('secret123'),
            'role' => 'admin', 'aktif' => true, 'nik' => 'ADMPV1', 'no_hp' => '081', 'school_id' => null,
        ]);
        $story = \App\Models\GlobalStory::create([
            'user_id' => $author->id, 'school_id' => $a->id,
            'image' => 'stories/x.jpg', 'expires_at' => now()->addDay(),
        ]);

        $this->assertTrue(\App\Http\Controllers\GlobalPortalController::canSeeStory($author, false, [], $story));
        $this->assertTrue(\App\Http\Controllers\GlobalPortalController::canSeeStory($sameSchool, false, [], $story));
        $this->assertFalse(\App\Http\Controllers\GlobalPortalController::canSeeStory($otherSchool, false, [], $story));
        $this->assertTrue(\App\Http\Controllers\GlobalPortalController::canSeeStory($super, true, [], $story));

        // Setelah follow, sekolah lain bisa melihat.
        \App\Models\GlobalFollow::create(['follower_id' => $otherSchool->id, 'followed_id' => $author->id]);
        $this->assertTrue(\App\Http\Controllers\GlobalPortalController::canSeeStory($otherSchool, false, [$author->id], $story));
    }

    public function test_activation_auto_follows_admins(): void
    {
        $school = School::create(['name' => 'S1', 'city' => 'C', 'slug' => 'af-s', 'is_active' => true]);
        $schoolAdmin = User::create([
            'name' => 'Adm', 'email' => 'adm@af.id', 'password' => Hash::make('secret123'),
            'role' => 'admin', 'aktif' => true, 'school_id' => $school->id, 'nik' => 'ADMAF1', 'no_hp' => '081',
        ]);
        $pusat = User::create([
            'name' => 'Pusat', 'email' => 'pusat@af.id', 'password' => Hash::make('secret123'),
            'role' => 'admin', 'aktif' => true, 'nik' => 'ADMAF0', 'no_hp' => '081', 'school_id' => null,
        ]);
        $siswa = User::create([
            'name' => 'Sis', 'email' => 'sis@af.id', 'password' => Hash::make('secret123'),
            'role' => 'siswa', 'aktif' => false, 'school_id' => $school->id, 'nik' => 'SWAF1', 'no_hp' => '081',
        ]);
        $super = User::create([
            'name' => 'Op', 'email' => 'op@af.id', 'password' => Hash::make('secret123'),
            'role' => 'admin', 'aktif' => true, 'nik' => 'ADMAF9', 'no_hp' => '081', 'school_id' => null,
        ]);

        $this->withSession(['user_id' => $super->id, 'user_role' => 'admin', 'admin_name' => 'Op',
                'is_super_admin' => true, 'school_id' => null])
            ->patch(route('admin.user.toggle', $siswa))
            ->assertRedirect();

        $this->assertDatabaseHas('global_follows', ['follower_id' => $siswa->id, 'followed_id' => $schoolAdmin->id]);
        $this->assertDatabaseHas('global_follows', ['follower_id' => $siswa->id, 'followed_id' => $pusat->id]);
    }

    public function test_check_endpoint_reports_new_posts(): void
    {
        $school = School::create(['name' => 'S1', 'city' => 'C', 'slug' => 's1-k', 'is_active' => true]);
        $guru = $this->makeGuru($school, 'gk@t.id');
        $p1 = GlobalPost::create(['user_id' => $guru->id, 'school_id' => $school->id, 'content' => 'lama']);
        GlobalPost::create(['user_id' => $guru->id, 'school_id' => $school->id, 'content' => 'baru']);

        $this->withSession($this->sessionFor($guru))
            ->getJson(route('global.portal.check', ['after_id' => $p1->id]))
            ->assertOk()
            ->assertJsonPath('new_count', 1);

        $this->withSession($this->sessionFor($guru))
            ->getJson(route('global.portal.check', ['after_id' => 999999]))
            ->assertOk()
            ->assertJsonPath('new_count', 0);
    }

    public function test_activity_page_renders_mobile_and_desktop(): void
    {
        $school = School::create(['name' => 'S1', 'city' => 'C', 'slug' => 's1-a', 'is_active' => true]);
        $guru = $this->makeGuru($school, 'ga@t.id');
        $fan = $this->makeGuru($school, 'fan@t.id');
        $post = GlobalPost::create(['user_id' => $guru->id, 'school_id' => $school->id, 'content' => 'punyaku']);
        \App\Models\GlobalLike::create(['global_post_id' => $post->id, 'user_id' => $fan->id]);
        \App\Models\GlobalFollow::create(['follower_id' => $fan->id, 'followed_id' => $guru->id]);
        \App\Models\GlobalComment::create(['global_post_id' => $post->id, 'user_id' => $fan->id, 'body' => 'keren!']);

        $this->withServerVariables(['HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 13; Mobile)'])
            ->withSession($this->sessionFor($guru))
            ->get(route('global.portal.activity'))
            ->assertOk()
            ->assertSee('keren!');

        $this->withServerVariables(['HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'])
            ->withSession($this->sessionFor($guru))
            ->get(route('global.portal.activity'))
            ->assertOk()
            ->assertSee('keren!');
    }

    public function test_portal_renders_when_new_tables_missing(): void
    {
        // Simulasi migrasi cerita/moderasi belum jalan di server.
        \Illuminate\Support\Facades\Schema::dropIfExists('global_stories');
        \Illuminate\Support\Facades\Schema::table('global_posts', function ($t) {
            $t->dropIndex(['is_hidden']);
        });
        \Illuminate\Support\Facades\Schema::table('global_posts', function ($t) {
            $t->dropColumn(['reports_count', 'is_hidden']);
        });

        $school = School::create(['name' => 'S1', 'city' => 'C', 'slug' => 's1-n', 'is_active' => true]);
        $guru = $this->makeGuru($school, 'gn@t.id');

        $this->withServerVariables(['HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'])
            ->withSession($this->sessionFor($guru))
            ->get(route('global.portal'))
            ->assertOk();

        $this->withServerVariables(['HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 13; Mobile)'])
            ->withSession($this->sessionFor($guru))
            ->get(route('global.portal'))
            ->assertOk();
    }
}
