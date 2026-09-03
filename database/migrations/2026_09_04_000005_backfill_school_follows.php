<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill satu kali: user aktif (guru/siswa) otomatis mengikuti
     * admin sekolahnya + Admin Pusat. Idempotent via insert-ignore logika
     * firstOrCreate (cek eksistensi dulu).
     */
    public function up(): void
    {
        try {
            $admins = DB::table('users')->where('role', 'admin')
                ->select('id', 'school_id')->get();
            $supers = $admins->whereNull('school_id')->pluck('id')->all();

            $users = DB::table('users')->whereIn('role', ['guru', 'siswa'])
                ->where('aktif', true)->select('id', 'school_id')->get();

            foreach ($users as $u) {
                $targets = $supers;
                foreach ($admins as $a) {
                    if ($a->school_id && (int) $a->school_id === (int) $u->school_id) {
                        $targets[] = $a->id;
                    }
                }
                foreach (array_unique($targets) as $adminId) {
                    if ((int) $adminId === (int) $u->id) {
                        continue;
                    }
                    $exists = DB::table('global_follows')
                        ->where('follower_id', $u->id)->where('followed_id', $adminId)->exists();
                    if (! $exists) {
                        DB::table('global_follows')->insert([
                            'follower_id' => $u->id, 'followed_id' => $adminId,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Best-effort: follow bisa terbentuk otomatis saat aktivasi berikutnya.
        }
    }

    public function down(): void
    {
        // Sengaja tidak menghapus follows (data sosial milik user).
    }
};
