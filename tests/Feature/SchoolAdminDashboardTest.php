<?php

namespace Tests\Feature;

use App\Models\GlobalPost;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regresi: dashboard admin sekolah (selalu ter-filter school_id) tidak boleh 500.
 * Bug nyata produksi: whereIn('post_id') padahal kolomnya global_post_id.
 */
class SchoolAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_dashboard_renders_with_posts_likes_comments(): void
    {
        $school = School::create(['name' => 'SMK', 'city' => 'K', 'slug' => 'smk-d', 'is_active' => true]);
        $admin = User::create([
            'name' => 'Admin', 'email' => 'adm@smk-d.id', 'password' => Hash::make('secret123'),
            'role' => 'admin', 'aktif' => true, 'school_id' => $school->id, 'nik' => 'ADMD1', 'no_hp' => '081',
        ]);
        $guru = User::create([
            'name' => 'Guru', 'email' => 'guru@smk-d.id', 'password' => Hash::make('secret123'),
            'role' => 'guru', 'aktif' => true, 'school_id' => $school->id, 'nik' => 'NIKD1', 'no_hp' => '081',
        ]);
        $post = GlobalPost::create(['user_id' => $guru->id, 'school_id' => $school->id, 'content' => 'halo']);
        \App\Models\GlobalLike::create(['global_post_id' => $post->id, 'user_id' => $guru->id]);
        \App\Models\GlobalComment::create(['global_post_id' => $post->id, 'user_id' => $guru->id, 'body' => 'ok']);

        $this->withSession(['user_id' => $admin->id, 'user_role' => 'admin', 'admin_name' => 'Admin',
                'is_super_admin' => false, 'school_id' => $school->id])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($school->name);
    }
}
