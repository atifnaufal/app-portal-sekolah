<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('image');
            $table->string('caption', 200)->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
            $table->index(['user_id', 'expires_at']);
        });

        Schema::table('global_posts', function (Blueprint $table) {
            $table->unsignedInteger('reports_count')->default(0)->after('comments_count');
            $table->boolean('is_hidden')->default(false)->after('reports_count');
            $table->index('is_hidden');
        });
    }

    public function down(): void
    {
        Schema::table('global_posts', function (Blueprint $table) {
            $table->dropColumn(['reports_count', 'is_hidden']);
        });
        Schema::dropIfExists('global_stories');
    }
};
