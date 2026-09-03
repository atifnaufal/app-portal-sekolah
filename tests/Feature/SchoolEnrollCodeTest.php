<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SchoolEnrollCodeTest extends TestCase
{
    use RefreshDatabase;

    private function superSession(): array
    {
        $admin = User::where('email', 'adminpusat@pusat.com')->first()
            ?? User::create([
                'name' => 'Pusat', 'email' => 'pusat-ec@pusat.com', 'password' => Hash::make('secret123'),
                'role' => 'admin', 'aktif' => true, 'nik' => 'ADMEC1', 'no_hp' => '081', 'school_id' => null,
            ]);

        return ['user_id' => $admin->id, 'user_role' => 'admin', 'admin_name' => 'Pusat',
            'is_super_admin' => true, 'school_id' => null];
    }

    public function test_store_generates_enroll_code(): void
    {
        $this->withSession($this->superSession())
            ->post(route('admin.schools.store'), [
                'name' => 'SMA Baru', 'city' => 'Solo', 'city_code' => '57111',
                'slug' => 'sma-baru-solo', 'is_active' => '1',
            ])
            ->assertRedirect();

        $school = School::where('slug', 'sma-baru-solo')->first();
        $this->assertNotNull($school);
        $this->assertSame($school->id.'57111', $school->enroll_code);
    }

    public function test_check_endpoint_returns_school(): void
    {
        $school = School::create([
            'name' => 'SMA Cek', 'city' => 'Yogya', 'city_code' => '55281', 'slug' => 'sma-cek',
            'is_active' => true, 'reg_guru_open' => false, 'reg_siswa_open' => true,
            'enroll_code' => 'X',
        ]);
        $school->update(['enroll_code' => \App\Models\School::makeEnrollCode($school->id, '55281')]);

        $this->getJson(route('register.check', ['code' => $school->enroll_code]))
            ->assertOk()
            ->assertJsonPath('name', 'SMA Cek')
            ->assertJsonPath('reg_siswa_open', true)
            ->assertJsonPath('reg_guru_open', false);
    }

    public function test_check_endpoint_404_for_unknown_code(): void
    {
        $this->getJson(route('register.check', ['code' => '99999999']))->assertNotFound();
    }

    public function test_check_endpoint_423_when_closed(): void
    {
        $school = School::create([
            'name' => 'SMA Tutup', 'city' => 'Z', 'city_code' => '11111', 'slug' => 'sma-tutup',
            'is_active' => true, 'reg_guru_open' => false, 'reg_siswa_open' => false,
            'enroll_code' => 'Y',
        ]);
        $school->update(['enroll_code' => \App\Models\School::makeEnrollCode($school->id, '11111')]);

        $this->getJson(route('register.check', ['code' => $school->enroll_code]))->assertStatus(423);
    }
}
