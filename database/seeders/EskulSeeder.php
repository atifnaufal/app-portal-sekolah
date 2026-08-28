<?php

namespace Database\Seeders;

use App\Models\ChatGroup;
use App\Models\Eskul;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EskulSeeder extends Seeder
{
    public function run()
    {
        $eskuls = [
            ['nama' => 'Pramuka', 'deskripsi' => 'Kegiatan kepanduan sekolah.'],
            ['nama' => 'Basket', 'deskripsi' => 'Klub basket putra dan putri.'],
            ['nama' => 'PMR', 'deskripsi' => 'Palang Merah Remaja.'],
            ['nama' => 'Futsal', 'deskripsi' => 'Klub futsal sekolah.'],
        ];

        foreach ($eskuls as $e) {
            $eskul = Eskul::create([
                'nama' => $e['nama'],
                'slug' => Str::slug($e['nama']),
                'deskripsi' => $e['deskripsi'],
                'aktif' => true,
            ]);

            ChatGroup::create([
                'name' => 'Group '.$eskul->nama,
                'type' => 'eskul',
                'related_id' => $eskul->id,
            ]);
        }
    }
}
