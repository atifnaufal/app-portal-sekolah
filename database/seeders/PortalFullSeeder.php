<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Eskul;
use App\Models\GlobalPost;
use App\Models\GlobalStory;
use App\Models\Jadwal;
use App\Models\KategoriBuku;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PengumpulanTugas;
use App\Models\School;
use App\Models\Spp;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seed data manual skala penuh (idempotent via firstOrCreate):
 * - 3 sekolah BAWAAAN (tidak dibuat): portal-pusat, sman1-jkt, smk-telkom
 * - per sekolah: 3 kelas, 8 guru, 10 siswa
 * - per guru: 1 mapel + 6 tugas + 15 jadwal (Senin–Jumat × 3 slot)
 * - 10 buku (cover DI-DOWNLOAD ke storage, bukan hotlink)
 * - 3 pengumpulan selesai (dinilai)
 * - 10 eskul (4+3+3)
 * - SPP Agustus 2026 belum lunas untuk SEMUA siswa
 * - portal per siswa: 5 postingan + 2 cerita (gambar di-download)
 */
class PortalFullSeeder extends Seeder
{
    private const GURU = [
        ['Budi Santoso', 'Matematika'], ['Citra Dewi', 'Bahasa Indonesia'],
        ['Doni Wijaya', 'Bahasa Inggris'], ['Eka Putri', 'IPA'],
        ['Fajar Ramadhan', 'IPS'], ['Gita Puspita', 'PPKn'],
        ['Hendra Gunawan', 'Sejarah'], ['Intan Permata', 'Seni Budaya'],
    ];

    private const SISWA = [
        'Aldi Pratama', 'Bela Sari', 'Charlie Oktavia', 'Dewi Putri', 'Evan Saputra',
        'Fitri Risma', 'Galih Pratama', 'Hana Syafira', 'Irfan Hakim', 'Jihan Aulia',
    ];

    private const KELAS = [
        ['X-A', 10], ['XI-A', 11], ['XII-A', 12],
    ];

    private const SLOTS = [
        ['07:00:00', '08:30:00'], ['08:30:00', '10:00:00'], ['10:30:00', '12:00:00'],
    ];

    private const HARI = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];

    private const BUKU_QUERY = [
        'Laskar Pelangi Andrea Hirata', 'Bumi Manusia Pramoedya', 'Ronggeng Dukuh Paruk',
        'Sang Pemimpi Andrea Hirata', 'Negeri 5 Menara', 'Ayat Ayat Cinta',
        'Perahu Kertas Dee Lestari', 'Dilan Pidi Baiq', 'Matematika SMA kelas 10',
        'IPA Terpadu SMP BSE',
    ];

    private const ESKUL = [
        'Pramuka', 'Paskibra', 'Basket', 'Futsal', 'Voli', 'Badminton',
        'Paduan Suara', 'Tari Tradisional', 'Robotik', 'PMR',
    ];

    public function run(): void
    {
        $schoolSlugs = ['portal-pusat', 'sman1-jkt', 'smk-telkom'];
        $schools = School::whereIn('slug', $schoolSlugs)->get();

        // Jangan abort keras: jika salah satu sekolah bawaan belum ada (mis. migrasi
        // belum jalan di DB baru), tetap seed sekolah yang tersedia dan beri peringatan.
        foreach ($schoolSlugs as $slug) {
            if (! $schools->contains('slug', $slug)) {
                $this->command->warn("Sekolah bawaan '{$slug}' belum ada — lewati. Pastikan migrasi create_schools_and_global_portal sudah jalan.");
            }
        }
        if ($schools->isEmpty()) {
            $this->command->error('Tidak ada sekolah bawaan sama sekali — seeding tidak dilanjutkan. Jalankan migrasi dulu.');

            return;
        }

        try {
            $this->seedBuku();
        } catch (\Throwable $e) {
            $this->command->warn('Perpustakaan seed dilewati karena error: '.$e->getMessage());
        }
        // 10 eskul dibagi 4 + 3 + 3 per sekolah.
        $eskulChunks = [array_slice(self::ESKUL, 0, 4), array_slice(self::ESKUL, 4, 3), array_slice(self::ESKUL, 7, 3)];

        foreach ($schools as $si => $school) {
            try {
                $this->seedSchool($school, $si, $eskulChunks[$si % count($eskulChunks)]);
            } catch (\Throwable $e) {
                $this->command->error("Sekolah {$school->slug} GAGAL: ".$e->getMessage());
            }
        }

        $this->command->info('PortalFullSeeder selesai: 3 sekolah × (3 kelas, 8 guru, 10 siswa, 48 tugas, 120 jadwal) + 10 buku + 10 eskul + SPP Agu + portal.');
    }

    public static function expectedSlugs(): array
    {
        return ['portal-pusat', 'sman1-jkt', 'smk-telkom'];
    }

    /** Ringkasan isi DB per sekolah (read-only, untuk panel Cek Seed). */
    public static function audit(): array
    {
        $rows = [];
        foreach (self::expectedSlugs() as $slug) {
            $school = School::where('slug', $slug)->first();
            if (! $school) {
                $rows[] = ['slug' => $slug, 'ada' => false];
                continue;
            }
            $siswaIds = User::where('school_id', $school->id)->where('role', 'siswa')->pluck('id');
            $rows[] = [
                'slug' => $slug, 'ada' => true, 'id' => $school->id, 'name' => $school->name,
                'kelas' => Kelas::where('school_id', $school->id)->count(),
                'guru' => User::where('school_id', $school->id)->where('role', 'guru')->count(),
                'siswa' => $siswaIds->count(),
                'tugas' => Tugas::whereHas('kelas', fn ($q) => $q->where('school_id', $school->id))->count(),
                'jadwal' => Jadwal::whereHas('kelas', fn ($q) => $q->where('school_id', $school->id))->count(),
                'spp_agustus' => Spp::whereIn('siswa_id', $siswaIds)->where('bulan', 8)->where('tahun', 2026)->count(),
                'posts' => GlobalPost::whereIn('user_id', $siswaIds)->count(),
                'stories' => GlobalStory::whereIn('user_id', $siswaIds)->count(),
            ];
        }
        $rows[] = ['slug' => '_global', 'buku' => Buku::count(), 'eskul' => Eskul::count()];

        return $rows;
    }

    private function seedSchool(School $school, int $si, array $eskulNames): void
    {
            $tag = preg_replace('/[^a-z0-9]/', '', strtolower($school->slug));

            // 3 kelas.
            $kelasIds = [];
            foreach (self::KELAS as $ki => [$nama, $tingkat]) {
                $k = Kelas::firstOrCreate(
                    ['nama' => $nama, 'school_id' => $school->id],
                    ['tingkat' => $tingkat, 'tahun_ajaran' => '2026/2027']
                );
                $kelasIds[] = $k->id;
            }

            // 8 guru + 10 siswa.
            $gurus = [];
            foreach (self::GURU as $gi => [$gname, $mapel]) {
                $gurus[] = User::firstOrCreate(
                    ['email' => "guru{$gi}.{$tag}@sekolah.sch.id"],
                    ['name' => "{$gname} ({$school->city})", 'password' => Hash::make('password123'),
                        'role' => 'guru', 'aktif' => true, 'school_id' => $school->id,
                        'kelas_id' => $kelasIds[$gi % 3], 'nik' => "GR{$school->id}0{$gi}", 'no_hp' => '081300000'.$gi]
                );
            }
            $siswas = [];
            foreach (self::SISWA as $sj => $sname) {
                $siswas[] = User::firstOrCreate(
                    ['email' => "siswa{$sj}.{$tag}@sekolah.sch.id"],
                    ['name' => "{$sname} ({$school->city})", 'password' => Hash::make('password123'),
                        'role' => 'siswa', 'aktif' => true, 'school_id' => $school->id,
                        'kelas_id' => $kelasIds[$sj % 3], 'nik' => "SW{$school->id}0{$sj}", 'no_hp' => '081400000'.$sj]
                );
            }

            // Per guru: 1 mapel + 6 tugas + 15 jadwal.
            foreach ($gurus as $gi => $guru) {
                $mapel = MataPelajaran::firstOrCreate(
                    ['nama' => self::GURU[$gi][1], 'kelas_id' => $kelasIds[$gi % 3], 'guru_id' => $guru->id],
                    ['kode' => 'SCH'.$school->id.'-MP'.$gi, 'kkm' => 75]
                );
                for ($t = 1; $t <= 6; $t++) {
                    Tugas::firstOrCreate(
                        ['judul' => "Tugas {$mapel->nama} #{$t} ({$school->city})", 'mata_pelajaran_id' => $mapel->id],
                        ['user_id' => $guru->id, 'kelas_id' => $mapel->kelas_id,
                            'deskripsi' => "Kerjakan latihan {$mapel->nama} bagian {$t} dengan teliti.",
                            'batas_pengumpulan' => now()->addWeeks($t)->toDateString(), 'tipe' => 'file']
                    );
                }
                $slot = 0;
                foreach (self::HARI as $hari) {
                    foreach (self::SLOTS as $sli => [$mulai, $selesai]) {
                        $kid = $kelasIds[($slot + $gi) % 3];
                        Jadwal::firstOrCreate(
                            ['guru_id' => $guru->id, 'hari' => $hari, 'jam_mulai' => $mulai],
                            ['mata_pelajaran_id' => $mapel->id, 'kelas_id' => $kid,
                                'jam_selesai' => $selesai, 'ruangan' => 'R-'.($kid * 10 + $sli + 1)]
                        );
                        $slot++;
                    }
                }
            }

            // 3 pengumpulan selesai (dinilai) dari 3 siswa berbeda.
            $done = 0;
            foreach (Tugas::whereIn('kelas_id', $kelasIds)->orderBy('id')->get() as $tugas) {
                if ($done >= 3) {
                    break;
                }
                $siswa = $siswas[$done % count($siswas)];
                $pg = PengumpulanTugas::firstOrCreate(
                    ['tugas_id' => $tugas->id, 'siswa_id' => $siswa->id],
                    ['status' => 'dinilai', 'nilai' => 85 + $done, 'feedback_guru' => 'Bagus, pertahankan!',
                        'dikumpulkan_pada' => now()->subDays(2), 'dinilai_pada' => now()->subDay()]
                );
                if ($pg->wasRecentlyCreated) {
                    $done++;
                } elseif ($pg->nilai !== null) {
                    $done++;
                }
            }

            // Eskul jatah sekolah ini.
            $pembinaPool = $gurus;
            foreach ($eskulNames as $ei => $nama) {
                $pembina = $pembinaPool[($si + $ei) % count($pembinaPool)];
                Eskul::firstOrCreate(
                    ['slug' => Str::slug($nama.'-'.$tag)],
                    ['nama' => "{$nama} {$school->city}", 'deskripsi' => "Ekstrakurikuler {$nama} {$school->name}.",
                        'pembina_id' => $pembina->id, 'aktif' => true]
                );
            }

            // SPP Agustus 2026 belum lunas untuk SEMUA siswa sekolah ini.
            $allSiswaIds = User::where('school_id', $school->id)->where('role', 'siswa')->pluck('id');
            foreach ($allSiswaIds as $sid) {
                Spp::firstOrCreate(
                    ['siswa_id' => $sid, 'bulan' => 8, 'tahun' => 2026],
                    ['nominal' => 150000, 'dibayar' => 0, 'status' => 'belum_lunas', 'jatuh_tempo' => '2026-08-10']
                );
            }

            // Portal per siswa: 5 postingan + 2 cerita.
            $storyPool = $this->storyImagePool();
            foreach ($allSiswaIds as $sidx => $sid) {
                $siswa = User::find($sid);
                for ($p = 1; $p <= 5; $p++) {
                    GlobalPost::firstOrCreate(
                        ['user_id' => $sid, 'content' => $this->postText($p, $siswa->name, $school->name)],
                        ['school_id' => $school->id]
                    );
                }
                $existingStories = GlobalStory::where('user_id', $sid)->count();
                for ($st = $existingStories; $st < 2; $st++) {
                    GlobalStory::create([
                        'user_id' => $sid, 'school_id' => $school->id,
                        'image' => $storyPool[($sidx + $st) % count($storyPool)],
                        'caption' => "Cerita {$siswa->name} #".($st + 1),
                        'expires_at' => now()->addDay(),
                    ]);
                }
            }

            $guruCount = User::where('school_id', $school->id)->where('role', 'guru')->count();
            $siswaCount = User::where('school_id', $school->id)->where('role', 'siswa')->count();
            $this->command->info("  [{$school->slug}] guru={$guruCount} siswa={$siswaCount} — OK");
    }

    private function postText(int $n, string $name, string $school): string
    {
        return [
            1 => "Halo! Saya {$name} dari {$school}. Semangat belajar hari ini!",
            2 => "Kegiatan upacara bendera {$school} pagi ini berjalan khidmat.",
            3 => "Tips belajar: cicil tugas tiap hari biar tidak menumpuk. — {$name}",
            4 => "Tim basket {$school} latihan sore ini. Dukung kami!",
            5 => "Terima kasih untuk guru-guru {$school} atas bimbingannya minggu ini.",
        ][$n] ?? "Postingan {$name}";
    }

    /** Pool gambar cerita: download sekali, pakai ulang (file fisik di storage). */
    private function storyImagePool(): array
    {
        $pool = [];
        for ($i = 1; $i <= 6; $i++) {
            $path = "stories/seed-pool-{$i}.jpg";
            if (! Storage::disk('public')->exists($path)) {
                $bin = $this->download("https://picsum.photos/seed/sekolah{$i}/600/800");
                Storage::disk('public')->put($path, $bin ?? $this->placeholder(600, 800, "Cerita {$i}"));
            }
            $pool[] = $path;
        }

        return $pool;
    }

    private function seedBuku(): void
    {
        $katNames = ['Fiksi', 'Pelajaran', 'Sains', 'Sejarah', 'Agama'];
        $kats = [];
        foreach ($katNames as $kn) {
            $kats[$kn] = KategoriBuku::firstOrCreate(['slug' => Str::slug($kn)], ['nama' => $kn]);
        }
        $katKeys = array_keys($kats);

        foreach (self::BUKU_QUERY as $bi => $query) {
            $info = $this->openLibraryCover($query);
            $judul = $info['title'] ?? $query;
            $slug = Str::slug(mb_substr($judul, 0, 60)).'-seed-'.$bi;
            if (Buku::where('slug', $slug)->exists()) {
                continue;
            }
            $coverPath = "perpustakaan/covers/seed-{$bi}.jpg";
            if (! Storage::disk('public')->exists($coverPath)) {
                $bin = $this->download($info['cover_url'] ?? null);
                Storage::disk('public')->put($coverPath, $bin ?? $this->placeholder(400, 600, $judul));
            }
            Buku::create([
                'kategori_buku_id' => $kats[$katKeys[$bi % count($katKeys)]]->id,
                'judul' => $judul, 'slug' => $slug,
                'penulis' => $info['author'] ?? 'Anonim',
                'penerbit' => $info['publisher'] ?? 'Penerbit Umum',
                'tahun_terbit' => $info['year'] ?? 2020,
                'deskripsi' => "Koleksi perpustakaan digital: {$judul}.",
                'cover' => $coverPath, 'file_pdf' => '', 'stok' => 5,
            ]);
        }
    }

    /** Cari cover buku asli via Open Library (di-download, bukan hotlink). */
    private function openLibraryCover(string $query): array
    {
        try {
            $res = Http::timeout(15)->get('https://openlibrary.org/search.json', [
                'q' => $query, 'limit' => 1, 'fields' => 'title,author_name,publisher,first_publish_year,cover_i',
            ]);
            $doc = $res->json('docs.0');
            if (! $doc) {
                return [];
            }

            return [
                'title' => $doc['title'] ?? $query,
                'author' => isset($doc['author_name'][0]) ? $doc['author_name'][0] : 'Anonim',
                'publisher' => isset($doc['publisher'][0]) ? $doc['publisher'][0] : 'Penerbit Umum',
                'year' => $doc['first_publish_year'] ?? 2020,
                'cover_url' => isset($doc['cover_i'])
                    ? "https://covers.openlibrary.org/b/id/{$doc['cover_i']}-L.jpg" : null,
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function download(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        try {
            $res = Http::timeout(20)->get($url);
            $bin = $res->successful() && strlen($res->body()) > 1024 ? $res->body() : null;
            // Pastikan benar gambar.
            if ($bin && @getimagesizefromstring($bin) === false) {
                return null;
            }

            return $bin;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Placeholder GD bila unduhan gagal (tetap file fisik lokal). */
    private function placeholder(int $w, int $h, string $text): string
    {
        $img = imagecreatetruecolor($w, $h);
        $bg = imagecolorallocate($img, 79, 70, 229);
        $fg = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $bg);
        imagestring($img, 5, 20, (int) ($h / 2 - 10), substr($text, 0, 28), $fg);
        ob_start();
        imagejpeg($img);
        $bin = (string) ob_get_clean();
        imagedestroy($img);

        return $bin;
    }
}
