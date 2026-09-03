<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Eskul;
use App\Models\GlobalPost;
use App\Models\GlobalStory;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\School;
use App\Models\Spp;
use App\Models\Tugas;
use App\Models\User;
use Database\Seeders\PortalFullSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalFullSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_seed_counts_and_files(): void
    {
        Storage::fake('public');

        $this->seed(PortalFullSeeder::class);

        foreach (School::whereIn('slug', ['portal-pusat', 'sman1-jkt', 'smk-telkom'])->get() as $school) {
            $this->assertCount(3, Kelas::where('school_id', $school->id)->get(), "kelas {$school->slug}");
            $this->assertCount(8, User::where('school_id', $school->id)->where('role', 'guru')->get(), "guru {$school->slug}");
            $siswaIds = User::where('school_id', $school->id)->where('role', 'siswa')->pluck('id');
            $this->assertCount(10, $siswaIds, "siswa {$school->slug}");
            $this->assertEquals(48, Tugas::whereHas('kelas', fn ($q) => $q->where('school_id', $school->id))->count(), "tugas {$school->slug}");
            $this->assertEquals(120, Jadwal::whereHas('kelas', fn ($q) => $q->where('school_id', $school->id))->count(), "jadwal {$school->slug}");
            foreach ($siswaIds as $sid) {
                $this->assertEquals(1, Spp::where('siswa_id', $sid)->where('bulan', 8)->where('tahun', 2026)->where('status', 'belum_lunas')->count(), "spp $sid");
                $this->assertGreaterThanOrEqual(5, GlobalPost::where('user_id', $sid)->count(), "posts $sid");
                $this->assertGreaterThanOrEqual(2, GlobalStory::where('user_id', $sid)->count(), "stories $sid");
            }
        }

        $this->assertEquals(10, Buku::count());
        $this->assertEquals(10, Eskul::count());
        $this->assertGreaterThanOrEqual(3, \App\Models\PengumpulanTugas::whereNotNull('nilai')->count());

        // File fisik benar-benar ada di storage (bukan link).
        foreach (Buku::pluck('cover') as $cover) {
            $this->assertTrue(Storage::disk('public')->exists($cover), "cover missing: $cover");
            $this->assertGreaterThan(1024, Storage::disk('public')->size($cover), "cover too small: $cover");
        }
        foreach (GlobalStory::pluck('image')->unique() as $img) {
            $this->assertTrue(Storage::disk('public')->exists($img), "story img missing: $img");
        }
    }
}
