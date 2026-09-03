<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const FEATURE_KEYS = [
        'feature_spp_enabled',
        'feature_lms_enabled',
        'feature_eskul_enabled',
        'feature_perpustakaan_enabled',
        'feature_jadwal_enabled',
        'feature_nilai_enabled',
        'feature_absensi_enabled',
        'feature_berita_enabled',
        'feature_registration_guru_enabled',
        'feature_registration_siswa_enabled',
        'feature_raport_enabled',
        'feature_communication_enabled',
    ];

    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->boolean('reg_guru_open')->default(false)->after('is_active');
            $table->boolean('reg_siswa_open')->default(false)->after('reg_guru_open');
        });

        Schema::create('school_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('feature_key', 60);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'feature_key']);
        });

        // Seed default: semua fitur AKTIF untuk sekolah yang sudah ada.
        $schoolIds = DB::table('schools')->pluck('id');
        $now = now();
        $rows = [];
        foreach ($schoolIds as $sid) {
            foreach (self::FEATURE_KEYS as $key) {
                $rows[] = [
                    'school_id' => $sid,
                    'feature_key' => $key,
                    'is_enabled' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('school_features')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_features');
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['reg_guru_open', 'reg_siswa_open']);
        });
    }
};
