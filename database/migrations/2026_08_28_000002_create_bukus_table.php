<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bukus', function (Blueprint $col) {
            $col->id();
            $col->foreignId('kategori_buku_id')->constrained('kategori_bukus')->onDelete('cascade');
            $col->string('judul');
            $col->string('slug')->unique();
            $col->string('penulis')->nullable();
            $col->string('penerbit')->nullable();
            $col->integer('tahun_terbit')->nullable();
            $col->text('deskripsi')->nullable();
            $col->string('cover')->nullable();
            $col->string('file_pdf');
            $col->integer('stok')->default(1);
            $col->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukus');
    }
};
