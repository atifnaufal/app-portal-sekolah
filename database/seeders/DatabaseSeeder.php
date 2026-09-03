<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sekolah.com'],
            [
                'name' => 'Admin Sekolah',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'aktif' => true,
            ]
        );

        $this->call([
            FeatureFlagsSeeder::class,
            LmsSeeder::class,
            TugasTestDataSeeder::class,
            CompleteTugasSeeder::class,
            NilaiSeeder::class,
        ]);
    }
}
