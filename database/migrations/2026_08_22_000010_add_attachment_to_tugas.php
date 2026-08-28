<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->string('lampiran')->nullable()->after('deskripsi');
            $table->string('lampiran_nama')->nullable()->after('lampiran');
        });
    }

    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropColumn(['lampiran', 'lampiran_nama']);
        });
    }
};
