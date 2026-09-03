<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur chat premium (WhatsApp-like):
 *  - chat_groups: tipe 'custom' + kolom owner (created_by).
 *  - chat_group_members: status (pending/approved) untuk undangan + role (admin/member).
 *  - chat_messages: dukungan edit (edited, edited_at) & hapus permanen (deleted_at, deleted_by).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_groups', function (Blueprint $table) {
            $table->enum('type', ['school', 'class', 'eskul', 'private', 'custom'])->change();
            $table->foreignId('created_by')->nullable()->after('avatar')->constrained('users')->nullOnDelete();
        });

        Schema::table('chat_group_members', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved'])->default('approved')->after('user_id');
            $table->enum('role', ['admin', 'member'])->default('member')->after('status');
            $table->unsignedBigInteger('invited_by')->nullable()->after('role');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->boolean('edited')->default(false)->after('file');
            $table->timestamp('edited_at')->nullable()->after('edited');
            $table->timestamp('deleted_at')->nullable()->after('edited_at');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['edited', 'edited_at', 'deleted_at', 'deleted_by']);
        });
        Schema::table('chat_group_members', function (Blueprint $table) {
            $table->dropColumn(['status', 'role', 'invited_by']);
        });
        Schema::table('chat_groups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->enum('type', ['school', 'class', 'eskul', 'private'])->change();
        });
    }
};
