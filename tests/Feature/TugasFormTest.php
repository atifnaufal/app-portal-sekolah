<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TugasFormTest extends TestCase
{
    use RefreshDatabase;

    private function makeKelas(): Kelas
    {
        return Kelas::create([
            'nama' => 'XII RPL 1',
            'tingkat' => 12,
            'tahun_ajaran' => '2026/2027',
        ]);
    }

    private function makeGuru(Kelas $kelas): User
    {
        return User::factory()->create(['role' => 'guru', 'kelas_id' => $kelas->id]);
    }

    private function makeSiswa(Kelas $kelas): User
    {
        return User::factory()->create(['role' => 'siswa', 'kelas_id' => $kelas->id]);
    }

    private function sampleQuestions(): array
    {
        return [
            ['text' => 'Ibu kota Indonesia adalah...', 'type' => 'multiple', 'options' => ['Jakarta', 'Bandung', 'Surabaya'], 'required' => true],
            ['text' => 'Jelaskan proses fotosintesis.', 'type' => 'essay', 'options' => [], 'required' => false],
        ];
    }

    private function createFormTugas(User $guru, Kelas $kelas): Tugas
    {
        return Tugas::create([
            'user_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'judul' => 'Kuesioner Bab 1',
            'deskripsi' => 'Kerjakan formulir berikut dengan teliti.',
            'tipe' => 'form',
            'form_data' => $this->sampleQuestions(),
        ]);
    }

    public function test_guru_can_create_form_tugas_with_questions(): void
    {
        $kelas = $this->makeKelas();
        $guru = $this->makeGuru($kelas);

        $response = $this->actingAs($guru)->post('/tugas', [
            'judul' => 'Kuesioner Bab 1',
            'deskripsi' => 'Isi formulir berikut',
            'tipe' => 'form',
            'kelas_id' => $kelas->id,
            'form_data' => json_encode($this->sampleQuestions()),
        ]);

        $response->assertRedirect(route('tugas.show', Tugas::first()));
        $tugas = Tugas::first();
        $this->assertNotNull($tugas);
        $this->assertSame('form', $tugas->tipe);

        $questions = $tugas->form_data;
        $this->assertIsArray($questions);
        $this->assertCount(2, $questions);
        $this->assertSame('Ibu kota Indonesia adalah...', $questions[0]['text']);
        $this->assertSame(['Jakarta', 'Bandung', 'Surabaya'], $questions[0]['options']);
        $this->assertTrue($questions[0]['required']);
        $this->assertFalse($questions[1]['required']);
    }

    public function test_guru_can_create_file_tugas(): void
    {
        $kelas = $this->makeKelas();
        $guru = $this->makeGuru($kelas);

        $this->actingAs($guru)->post('/tugas', [
            'judul' => 'Tugas PDF',
            'tipe' => 'file',
            'kelas_id' => $kelas->id,
        ]);

        $tugas = Tugas::first();
        $this->assertNotNull($tugas);
        $this->assertSame('file', $tugas->tipe);
        $this->assertNull($tugas->form_data);
    }

    public function test_form_tugas_requires_at_least_one_question(): void
    {
        $kelas = $this->makeKelas();
        $guru = $this->makeGuru($kelas);

        $response = $this->actingAs($guru)->post('/tugas', [
            'judul' => 'Form Kosong',
            'tipe' => 'form',
            'kelas_id' => $kelas->id,
            'form_data' => '[]',
        ]);

        $response->assertSessionHasErrors('form_data');
        $this->assertDatabaseCount('tugas', 0);
    }

    public function test_choice_question_requires_two_options(): void
    {
        $kelas = $this->makeKelas();
        $guru = $this->makeGuru($kelas);

        $response = $this->actingAs($guru)->post('/tugas', [
            'judul' => 'Form Kurang Opsi',
            'tipe' => 'form',
            'kelas_id' => $kelas->id,
            'form_data' => json_encode([
                ['text' => 'Pilih satu', 'type' => 'multiple', 'options' => ['Satu-satunya opsi']],
            ]),
        ]);

        $response->assertSessionHasErrors('form_data');
        $this->assertDatabaseCount('tugas', 0);
    }

    public function test_siswa_sees_form_questions_on_detail_page(): void
    {
        $kelas = $this->makeKelas();
        $guru = $this->makeGuru($kelas);
        $siswa = $this->makeSiswa($kelas);
        $tugas = $this->createFormTugas($guru, $kelas);

        $this->actingAs($siswa)->get(route('tugas.show', $tugas))
            ->assertOk()
            ->assertSee('Formulir Pengerjaan')
            ->assertSee('Ibu kota Indonesia adalah...')
            ->assertSee('Jelaskan proses fotosintesis.')
            ->assertSee('Jakarta')
            ->assertSee('Surabaya');
    }

    public function test_siswa_can_submit_form_answers(): void
    {
        $kelas = $this->makeKelas();
        $guru = $this->makeGuru($kelas);
        $siswa = $this->makeSiswa($kelas);
        $tugas = $this->createFormTugas($guru, $kelas);

        $response = $this->actingAs($siswa)->post(route('tugas.submit', $tugas), [
            'jawaban' => [0 => 'Jakarta', 1 => 'Fotosintesis adalah proses metabolisme tumbuhan.'],
        ]);

        $response->assertRedirect();
        $submission = PengumpulanTugas::first();
        $this->assertNotNull($submission);
        $this->assertSame($siswa->id, $submission->siswa_id);
        $this->assertSame('terkirim', $submission->status);
        $this->assertFalse((bool) $submission->revisi_aktif);
        $this->assertSame('Jakarta', $submission->jawaban_form[0]);
        $this->assertSame('Fotosintesis adalah proses metabolisme tumbuhan.', $submission->jawaban_form[1]);
    }

    public function test_siswa_cannot_submit_form_tugas_twice(): void
    {
        $kelas = $this->makeKelas();
        $guru = $this->makeGuru($kelas);
        $siswa = $this->makeSiswa($kelas);
        $tugas = $this->createFormTugas($guru, $kelas);

        $this->actingAs($siswa)->post(route('tugas.submit', $tugas), [
            'jawaban' => [0 => 'Jakarta', 1 => 'Jawaban pertama.'],
        ]);

        $this->actingAs($siswa)->post(route('tugas.submit', $tugas), [
            'jawaban' => [0 => 'Bandung', 1 => 'Jawaban kedua.'],
        ])->assertForbidden();

        $this->assertDatabaseCount('pengumpulan_tugas', 1);
        $this->assertSame('Jakarta', PengumpulanTugas::first()->jawaban_form[0]);
    }

    public function test_guru_sees_student_form_answers_in_monitoring(): void
    {
        $kelas = $this->makeKelas();
        $guru = $this->makeGuru($kelas);
        $siswa = $this->makeSiswa($kelas);
        $tugas = $this->createFormTugas($guru, $kelas);

        PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $siswa->id,
            'status' => 'terkirim',
            'revisi_aktif' => false,
            'catatan' => 'Pengerjaan via formulir online.',
            'jawaban_form' => [0 => 'Bandung', 1 => 'Jawaban esai panjang di sini.'],
            'dikumpulkan_pada' => now(),
        ]);

        $this->actingAs($guru)->get(route('tugas.show', $tugas))
            ->assertOk()
            ->assertSee('JAWABAN FORMULIR SISWA')
            ->assertSee('Bandung')
            ->assertSee('Jawaban esai panjang di sini.');
    }

    public function test_unanswered_form_question_is_displayed_as_empty(): void
    {
        $kelas = $this->makeKelas();
        $guru = $this->makeGuru($kelas);
        $siswa = $this->makeSiswa($kelas);
        $tugas = $this->createFormTugas($guru, $kelas);

        PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $siswa->id,
            'status' => 'terkirim',
            'revisi_aktif' => false,
            'catatan' => 'Pengerjaan via formulir online.',
            'jawaban_form' => [0 => 'Jakarta'],
            'dikumpulan_pada' => now(),
        ]);

        $this->actingAs($guru)->get(route('tugas.show', $tugas))
            ->assertOk()
            ->assertSee('tidak dijawab');
    }
}
