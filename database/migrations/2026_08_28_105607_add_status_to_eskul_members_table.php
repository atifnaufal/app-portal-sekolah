<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eskul_members', function (Blueprint $table) {
            $table->string('status')->default('approved'); // approved, pending, rejected
        });
    }

    public function down(): void
    {
        Schema::table('eskul_members', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
