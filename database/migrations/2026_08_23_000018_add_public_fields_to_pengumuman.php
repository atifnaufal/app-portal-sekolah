<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengumuman', function (Blueprint $table) {
            $table->string('gambar')->nullable()->after('isi');
            $table->string('gambar_nama')->nullable()->after('gambar');
            $table->boolean('publik')->default(true)->after('gambar_nama');
            $table->boolean('is_landing')->default(false)->after('publik');
            $table->date('tanggal_acara')->nullable()->after('is_landing');
        });
    }

    public function down(): void
    {
        Schema::table('pengumuman', function (Blueprint $table) {
            $table->dropColumn(['gambar', 'gambar_nama', 'publik', 'is_landing', 'tanggal_acara']);
        });
    }
};
