<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_bukus', function (Blueprint $col) {
            $col->id();
            $col->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $col->foreignId('buku_id')->constrained('bukus')->onDelete('cascade');
            $col->date('tanggal_pinjam');
            $col->date('tanggal_kembali')->nullable();
            $col->enum('status', ['pinjam', 'kembali'])->default('pinjam');
            $col->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_bukus');
    }
};
