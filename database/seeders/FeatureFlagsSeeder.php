<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class FeatureFlagsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Registrasi (bawaan: tertutup, dibuka manual oleh admin)
            'registration_guru_enabled' => '0',
            'registration_siswa_enabled' => '0',
            // Absensi
            'attendance_active' => '0',
            'attendance_start_time' => '07:00',
            'attendance_end_time' => '15:00',
            'attendance_late_time' => '07:30',
            // Feature flags admin pusat (bawaan: aktif)
            'feature_spp_enabled' => '1',
            'feature_lms_enabled' => '1',
            'feature_eskul_enabled' => '1',
            'feature_perpustakaan_enabled' => '1',
            'feature_jadwal_enabled' => '1',
            'feature_nilai_enabled' => '1',
            'feature_absensi_enabled' => '1',
            'feature_berita_enabled' => '1',
            'feature_registration_guru_enabled' => '0',
            'feature_registration_siswa_enabled' => '0',
            'feature_raport_enabled' => '1',
            'feature_communication_enabled' => '1',
        ];

        foreach ($defaults as $key => $value) {
            // Hanya isi yang belum ada — jangan timpa pilihan admin yang sudah tersimpan.
            if (Setting::where('key', $key)->doesntExist()) {
                Setting::create(['key' => $key, 'value' => $value]);
            }
        }
    }
}
