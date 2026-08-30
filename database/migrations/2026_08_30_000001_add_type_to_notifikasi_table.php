<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->string('type')->default('general')->after('url');
            $table->string('actor_name')->nullable()->after('type');
            $table->string('actor_photo')->nullable()->after('actor_name');
        });
    }

    public function down(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->dropColumn(['type', 'actor_name', 'actor_photo']);
        });
    }
};
