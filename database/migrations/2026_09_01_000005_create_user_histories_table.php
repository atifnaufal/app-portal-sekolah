<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('activity_type', 50);
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('long', 11, 8)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_info')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['activity_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_histories');
    }
};
