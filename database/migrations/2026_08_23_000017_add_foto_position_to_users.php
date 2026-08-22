<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('foto_posisi_x')->default(50)->after('foto');
            $table->unsignedTinyInteger('foto_posisi_y')->default(50)->after('foto_posisi_x');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['foto_posisi_x', 'foto_posisi_y']);
        });
    }
};
