<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->foreignId('mata_pelajaran_id')->nullable()->after('kelas_id')->constrained('mata_pelajaran')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mata_pelajaran_id');
        });
    }
};
