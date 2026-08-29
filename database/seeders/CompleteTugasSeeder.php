<?php

namespace Database\Seeders;

use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * DATA UJI (development/testing) — menandai semua tugas sebagai SELESAI (dinilai)
 * untuk setiap siswa di kelas tugas masing-masing.
 *
 * Tujuan: memberi data contoh yang lengkap agar fitur tugas (halaman siswa,
 * rekap PDF/Excel guru, review) bisa dites tanpa harus mengumpulkan manual.
 *
 * Idempoten: mencocokkan (tugas_id, siswa_id) via updateOrCreate, jadi aman
 * dijalankan berulang.
 */
class CompleteTugasSeeder extends Seeder
{
    public function run(): void
    {
        $tugasList = Tugas::with('kelas')->get();
        if ($tugasList->isEmpty()) {
            $this->command?->warn('Tidak ada data tugas. Jalankan dulu TugasTestDataSeeder.');

            return;
        }

        $done = 0;
        foreach ($tugasList as $tugas) {
            $siswas = User::where('role', 'siswa')
                ->where('kelas_id', $tugas->kelas_id)
                ->get();

            foreach ($siswas as $siswa) {
                $existing = PengumpulanTugas::where('tugas_id', $tugas->id)
                    ->where('siswa_id', $siswa->id)
                    ->first();

                // Jangan timpa data yang memang sudah ada entrinya (senilai/perlu_revisi)
                if ($existing && $existing->status === 'dinilai' && $existing->nilai !== null) {
                    continue;
                }

                $nilai = rand(70, 100);
                // Pengumpulan dilakukan sebelum (atau di hari) tenggat
                $dikumpulkan = $tugas->batas_pengumpulan
                    ? $tugas->batas_pengumpulan->copy()->subDays(rand(0, 3))->startOfDay()->addHours(rand(8, 15))
                    : now()->subDays(rand(1, 3));

                $payload = [
                    'status' => 'dinilai',
                    'nilai' => $nilai,
                    'feedback_guru' => 'Bagus, pertahankan!',
                    'revisi_aktif' => false,
                    'dikumpulkan_pada' => $dikumpulkan,
                    'dinilai_pada' => $dikumpulkan ? $dikumpulkan->copy()->addHours(rand(1, 24)) : now(),
                ];

                if ($tugas->tipe === 'form' && $tugas->form_data) {
                    $payload['jawaban_form'] = $this->buildFormAnswers($tugas->form_data);
                } else {
                    $payload['catatan'] = 'Dikerjakan dan dikumpulkan (data uji).';
                    $payload['jawaban_nama'] = 'jawaban-' . Str::slug($tugas->judul) . '.pdf';
                }

                PengumpulanTugas::updateOrCreate(
                    ['tugas_id' => $tugas->id, 'siswa_id' => $siswa->id],
                    $payload
                );

                $done++;
            }
        }

        $this->command?->info("Data uji selesai: {$done} pengumpulan tugas ditandai 'dinilai' untuk semua siswa.");
    }

    /**
     * Susun jawaban form mengikuti indeks pertanyaan pada form_data.
     *
     * @param  array<int, array{text?: string, type?: string, options?: array}>  $formData
     * @return array<int, mixed>
     */
    protected function buildFormAnswers(array $formData): array
    {
        $answers = [];

        foreach (array_values($formData) as $i => $q) {
            $type = $q['type'] ?? 'essay';
            $options = $q['options'] ?? [];

            $answers[$i] = match ($type) {
                'radio' => $options[0] ?? 'Ya',
                'checkbox' => $options ? array_slice($options, 0, 2) : ['Ya'],
                'select' => $options[0] ?? 'Ya',
                'number' => (string) rand(1, 100),
                default => "Jawaban data uji untuk pertanyaan " . ($i + 1) . ".",
            };
        }

        return $answers;
    }
}
