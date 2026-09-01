<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $safe = function(callable $fn){ try{$fn();}catch(Throwable $e){} };
        $safe(fn()=> Schema::table('users', function (Blueprint $t) {
            try{$t->index(['role','aktif']);}catch(Throwable $e){}
            try{$t->index('kelas_id');}catch(Throwable $e){}
            try{$t->index('school_id');}catch(Throwable $e){}
            try{$t->index('last_activity_at');}catch(Throwable $e){}
            try{$t->index('created_at');}catch(Throwable $e){}
        }));
        $safe(fn()=> Schema::table('absensi', function (Blueprint $t) {
            try{$t->index(['user_id','tanggal']);}catch(Throwable $e){}
            try{$t->index('status');}catch(Throwable $e){}
            try{$t->index('tanggal');}catch(Throwable $e){}
        }));
        $safe(fn()=> Schema::table('nilais', function (Blueprint $t) { try{$t->index('siswa_id');}catch(Throwable $e){} }));
        $safe(fn()=> Schema::table('notifikasi', function (Blueprint $t) {
            try{$t->index(['user_id','dibaca_pada']);}catch(Throwable $e){}
            try{$t->index('type');}catch(Throwable $e){}
        }));
        $safe(fn()=> Schema::table('global_posts', function (Blueprint $t) {
            try{$t->index('created_at');}catch(Throwable $e){}
            try{$t->index(['school_id','created_at']);}catch(Throwable $e){}
        }));
        $safe(fn()=> Schema::table('user_histories', function (Blueprint $t) {
            try{$t->index(['user_id','created_at']);}catch(Throwable $e){}
            try{$t->index('activity_type');}catch(Throwable $e){}
        }));
        $safe(fn()=> Schema::table('tugas', function (Blueprint $t) { try{$t->index(['kelas_id','user_id']);}catch(Throwable $e){} }));
    }
    public function down(): void {}
};
