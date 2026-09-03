<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // Kode pos/daerah kota sekolah (mis. 51372). Jadi suffix kode pendaftaran.
            $table->string('city_code', 10)->nullable()->after('city');
            // Kode Pendaftaran umum: {id}{city_code} — digenerate otomatis.
            $table->string('enroll_code', 20)->nullable()->unique()->after('city_code');
        });

        // Backfill sekolah lama: city_code default + enroll_code = id + city_code.
        try {
            DB::table('schools')->whereNull('city_code')->update(['city_code' => '00000']);
            foreach (DB::table('schools')->whereNull('enroll_code')->get(['id', 'city_code']) as $s) {
                DB::table('schools')->where('id', $s->id)->update([
                    'enroll_code' => $s->id.($s->city_code ?? '00000'),
                ]);
            }
        } catch (\Throwable $e) {
            // Best-effort; admin bisa isi manual lewat Edit.
        }
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['city_code', 'enroll_code']);
        });
    }
};
