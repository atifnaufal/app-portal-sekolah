<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->enum('tipe', ['file', 'form'])->default('file')->after('deskripsi');
            $table->json('form_data')->nullable()->after('tipe');
        });
    }

    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropColumn(['tipe', 'form_data']);
        });
    }
};
