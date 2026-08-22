<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE pengumpulan_tugas MODIFY status VARCHAR(30) NOT NULL DEFAULT 'terkirim'");
        Schema::table('pengumpulan_tugas', function (Blueprint $table) {
            $table->decimal('nilai', 5, 2)->nullable()->after('status');
            $table->text('feedback_guru')->nullable()->after('nilai');
            $table->boolean('revisi_aktif')->default(false)->after('feedback_guru');
            $table->timestamp('dinilai_pada')->nullable()->after('dikumpulkan_pada');
        });
    }

    public function down(): void
    {
        Schema::table('pengumpulan_tugas', function (Blueprint $table) {
            $table->dropColumn(['nilai', 'feedback_guru', 'revisi_aktif', 'dinilai_pada']);
        });
        DB::statement("ALTER TABLE pengumpulan_tugas MODIFY status ENUM('terkirim','dinilai') NOT NULL DEFAULT 'terkirim'");
    }
};
