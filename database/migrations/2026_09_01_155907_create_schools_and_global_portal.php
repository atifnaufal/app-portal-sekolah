<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('logo')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('kelas_id')->constrained('schools')->nullOnDelete();
        });

        Schema::create('global_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->text('content');
            $table->string('image')->nullable();
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->timestamps();
            $table->index(['created_at']);
        });

        Schema::create('global_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('global_post_id')->constrained('global_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['global_post_id','user_id']);
        });

        Schema::create('global_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('global_post_id')->constrained('global_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        // default schools
        DB::table('schools')->insert([
            ['name'=>'Portal Sekolah Pusat','city'=>'Nasional','slug'=>'portal-pusat','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'SMA Negeri 1','city'=>'Jakarta','slug'=>'sman1-jkt','created_at'=>now(),'updated_at'=>now()],
            ['name'=>'SMK Telkom','city'=>'Bandung','slug'=>'smk-telkom','created_at'=>now(),'updated_at'=>now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('global_comments');
        Schema::dropIfExists('global_likes');
        Schema::dropIfExists('global_posts');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
        });
        Schema::dropIfExists('schools');
    }
};
