<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Idempoten: di DB lama kolom ini sudah ada (migrasi lawas pernah berjalan),
        // di DB baru (fresh) baru akan dibuat. Guard hasColumn mencegah error
        // "duplicate column" saat file migrasi di-rename/dijalankan ulang.
        // (File awalnya bernomor 08_28 sehingga ALTER dijalankan SEBELUM kolom
        // tabel dibuat; rename ke 08_29_000007 memperbaiki urutan untuk DB baru.)
        if (! Schema::hasColumn('eskul_members', 'status')) {
            Schema::table('eskul_members', function (Blueprint $table) {
                $table->string('status')->default('approved'); // approved, pending, rejected
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('eskul_members', 'status')) {
            Schema::table('eskul_members', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
