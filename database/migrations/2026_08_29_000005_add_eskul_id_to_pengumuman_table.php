<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengumuman', function (Blueprint $table) {
            $table->foreignId('eskul_id')->nullable()->after('kelas_id')->constrained('eskuls')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::table('pengumuman', function (Blueprint $table) {
            $table->dropForeign(['eskul_id']);
            $table->dropColumn('eskul_id');
        });
    }
};
