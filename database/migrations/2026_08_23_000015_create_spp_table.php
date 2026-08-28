<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->decimal('nominal', 12, 2);
            $table->decimal('dibayar', 12, 2)->default(0);
            $table->enum('status', ['belum_lunas', 'lunas'])->default('belum_lunas');
            $table->date('jatuh_tempo')->nullable();
            $table->timestamps();
            $table->unique(['siswa_id', 'bulan', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spp');
    }
};
