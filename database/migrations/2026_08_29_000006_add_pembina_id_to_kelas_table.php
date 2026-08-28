<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('pembina_id')->nullable()->after('tahun_ajaran')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['pembina_id']);
            $table->dropColumn('pembina_id');
        });
    }
};
