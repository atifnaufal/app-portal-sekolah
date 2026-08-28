<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumpulan_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['terkirim', 'dinilai'])->default('terkirim');
            $table->text('catatan')->nullable();
            $table->string('jawaban_file')->nullable();
            $table->string('jawaban_nama')->nullable();
            $table->timestamp('dikumpulkan_pada')->nullable();
            $table->timestamps();
            $table->unique(['tugas_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_tugas');
    }
};
