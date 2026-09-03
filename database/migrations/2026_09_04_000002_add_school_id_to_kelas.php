<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->nullOnDelete();
            $table->index('school_id');
        });

        // Backfill: isi school_id kelas dari sekolah mayoritas user di kelas itu.
        // Kelas tanpa user bersekolah tetap null (diisi manual oleh admin).
        try {
            $kelasIds = DB::table('kelas')->whereNull('school_id')->pluck('id');
            foreach ($kelasIds as $kid) {
                $sid = DB::table('users')
                    ->where('kelas_id', $kid)
                    ->whereNotNull('school_id')
                    ->select('school_id', DB::raw('COUNT(*) as c'))
                    ->groupBy('school_id')
                    ->orderByDesc('c')
                    ->value('school_id');
                if ($sid) {
                    DB::table('kelas')->where('id', $kid)->update(['school_id' => $sid]);
                }
            }
        } catch (\Throwable $e) {
            // Backfill best-effort: kolom sudah ada, admin bisa isi manual.
        }
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
        });
    }
};
