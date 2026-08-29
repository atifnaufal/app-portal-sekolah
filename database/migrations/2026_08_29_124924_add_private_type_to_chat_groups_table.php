<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chat_groups', function (Blueprint $table) {
            // Adding 'private' to the enum requires raw SQL in some DB engines or
            // a complete redefinition if using change().
            $table->enum('type', ['school', 'class', 'eskul', 'private'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_groups', function (Blueprint $table) {
            $table->enum('type', ['school', 'class', 'eskul'])->change();
        });
    }
};
