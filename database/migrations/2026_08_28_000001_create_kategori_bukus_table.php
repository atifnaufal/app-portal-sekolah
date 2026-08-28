<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_bukus', function (Blueprint $col) {
            $col->id();
            $col->string('nama');
            $col->string('slug')->unique();
            $col->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_bukus');
    }
};
