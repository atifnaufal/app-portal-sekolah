<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['school', 'class', 'eskul']);
            $table->unsignedBigInteger('related_id')->nullable(); // ID Kelas atau Eskul
            $table->string('avatar')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_group_id')->constrained('chat_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['chat_group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_group_members');
        Schema::dropIfExists('chat_groups');
    }
};
