<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Note: waktu might have been renamed to waktu_masuk in a previous failed run
        if (Schema::hasColumn('absensi', 'waktu') && !Schema::hasColumn('absensi', 'waktu_masuk')) {
            Schema::table('absensi', function (Blueprint $table) {
                $table->renameColumn('waktu', 'waktu_masuk');
            });
        }

        Schema::table('absensi', function (Blueprint $table) {
            if (!Schema::hasColumn('absensi', 'waktu_pulang')) {
                $table->time('waktu_pulang')->nullable()->after('waktu_masuk');
            }
            if (!Schema::hasColumn('absensi', 'foto_masuk')) {
                $table->string('foto_masuk')->nullable()->after('waktu_pulang');
            }
            if (!Schema::hasColumn('absensi', 'foto_pulang')) {
                $table->string('foto_pulang')->nullable()->after('foto_masuk');
            }
            if (!Schema::hasColumn('absensi', 'lat_masuk')) {
                $table->decimal('lat_masuk', 10, 8)->nullable()->after('foto_pulang');
            }
            if (!Schema::hasColumn('absensi', 'long_masuk')) {
                $table->decimal('long_masuk', 11, 8)->nullable()->after('lat_masuk');
            }
            if (!Schema::hasColumn('absensi', 'lat_pulang')) {
                $table->decimal('lat_pulang', 10, 8)->nullable()->after('long_masuk');
            }
            if (!Schema::hasColumn('absensi', 'long_pulang')) {
                $table->decimal('long_pulang', 11, 8)->nullable()->after('lat_pulang');
            }
            $table->enum('status', ['hadir', 'terlambat', 'bolos'])->default('hadir')->change();
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn(['waktu_pulang', 'foto_masuk', 'foto_pulang', 'lat_masuk', 'long_masuk', 'lat_pulang', 'long_pulang']);
            $table->renameColumn('waktu_masuk', 'waktu');
            $table->enum('status', ['hadir'])->default('hadir')->change();
        });
    }
};
