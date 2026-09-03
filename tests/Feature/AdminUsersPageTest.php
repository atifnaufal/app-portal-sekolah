<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUsersPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_filtered_users_table_renders_rows(): void
    {
        $school = School::create(['name' => 'SMK', 'city' => 'K', 'slug' => 'smk-u', 'is_active' => true]);
        $guru = User::create([
            'name' => 'Guru Tampil', 'email' => 'tampil@u.id', 'password' => Hash::make('secret123'),
            'role' => 'guru', 'aktif' => true, 'school_id' => $school->id,
            'nik' => 'NIKU1', 'no_hp' => '081',
        ]);
        $super = User::create([
            'name' => 'Pusat', 'email' => 'pusat@u.id', 'password' => Hash::make('secret123'),
            'role' => 'admin', 'aktif' => true, 'nik' => 'ADMU1', 'no_hp' => '081', 'school_id' => null,
        ]);

        $this->withSession(['user_id' => $super->id, 'user_role' => 'admin', 'admin_name' => 'Pusat',
                'is_super_admin' => true, 'school_id' => null])
            ->get(route('admin.users', ['school_id' => $school->id]))
            ->assertOk()
            ->assertSee('Guru Tampil')
            ->assertSee('Tambah Kelas')
            ->assertSee('Identitas Guru');
    }
}
