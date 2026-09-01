<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('users')->where('email','adminpusat@pusat.com')->exists();
        if(!$exists){
            DB::table('users')->insert([
                'name'=>'Admin Pusat',
                'email'=>'adminpusat@pusat.com',
                'password'=>Hash::make('admin123'),
                'role'=>'admin',
                'nik'=>'ADM001',
                'no_hp'=>'0811111111',
                'aktif'=>1,
                'school_id'=>null,
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);
        } else {
            DB::table('users')->where('email','adminpusat@pusat.com')->update([
                'password'=>Hash::make('admin123'),
                'aktif'=>1,
                'role'=>'admin',
            ]);
        }
    }
    public function down(): void {}
};
