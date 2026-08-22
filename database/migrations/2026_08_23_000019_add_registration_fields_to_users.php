<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 30)->nullable()->unique()->after('name');
            $table->string('no_hp', 25)->nullable()->after('nik');
            $table->boolean('aktif')->default(true)->after('foto_posisi_y');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nik']);
            $table->dropColumn(['nik', 'no_hp', 'aktif']);
        });
    }
};
