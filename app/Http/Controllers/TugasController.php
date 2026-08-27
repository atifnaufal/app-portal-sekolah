<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Mail\TugasBaruMail;
use App\Models\Kelas;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TugasController extends Controller
{
    public function index(Request $request): View
    {
        $user = User::with('kelas')->findOrFail($request->session()->get('user_id'));

        if ($user->role === 'guru') {
            $tugas = Tugas::with(['kelas', 'user'])
                ->withCount([
                    'pengumpulan',
                    'pengumpulan as dinilai_count' => fn ($q) => $q->whereNotNull('nilai')->where('revisi_aktif', false),
                    'pengumpulan as revisi_count' => fn ($q) => $q->where('revisi_aktif', true),
                    'pengumpulan as pending_count' => fn ($q) => $q->whereNull('nilai')->where('revisi_aktif', false),
                ])
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            $siswaCounts = User::where('role', 'siswa')
                ->whereIn('kelas_id', $tugas->pluck('kelas_id')->unique()->filter())
                ->selectRaw('kelas_id, count(*) as total')
                ->groupBy('kelas_id')
                ->pluck('total', 'kelas_id');

            return view('mobile.tugas', [
                'tugas' => $tugas,
                'user' => $user,
                'siswaCounts' => $siswaCounts,
                'pendingTugas' => collect(),
                'completedTugas' => collect(),
                'expiredTugas' => collect(),
            ]);
        }

        $tugas = Tugas::with(['kelas', 'user', 'pengumpulan' => fn ($query) => $query->where('siswa_id', $user->id)])
            ->where('kelas_id', $user->kelas_id)
            ->latest()
            ->get();

        $activeTugas = $tugas->filter(function ($item) {
            $sub = $item->pengumpulan->first();

            return $item->isOpen() && (! $sub || $sub->revisi_aktif);
        });

        $pendingTugas = $tugas->filter(function ($item) {
            $sub = $item->pengumpulan->first();

            return $sub && ! $sub->revisi_aktif && $sub->nilai === null;
        });

        $completedTugas = $tugas->filter(function ($item) {
            $sub = $item->pengumpulan->first();

            return $sub && ! $sub->revisi_aktif && $sub->nilai !== null;
        });

        $expiredTugas = $tugas->filter(function ($item) {
            $sub = $item->pengumpulan->first();

            return $item->isExpired() && (! $sub || $sub->revisi_aktif);
        });

        return view('mobile.tugas', [
            'tugas' => $activeTugas,
            'allTugas' => $tugas,
            'pendingTugas' => $pendingTugas,
            'completedTugas' => $completedTugas,
            'expiredTugas' => $expiredTugas,
            'user' => $user,
            'siswaCounts' => collect(),
        ]);
    }

    public function create(): View
    {
        return view('mobile.tugas-form', [
            'tugas' => new Tugas(['tipe' => 'file']),
            'kelases' => Kelas::orderBy('nama')->get(),
            'hasSubmissions' => false,
        ]);
    }

    public function edit(Request $request, Tugas $tugas): View
    {
        $this->assertOwner($request, $tugas);

        return view('mobile.tugas-form', [
            'tugas' => $tugas,
            'kelases' => Kelas::orderBy('nama')->get(),
            'hasSubmissions' => $tugas->pengumpulan()->exists(),
        ]);
    }

    public function show(Request $request, Tugas $tugas): View
    {
        $user = User::with('kelas')->findOrFail($request->session()->get('user_id'));
        abort_unless(
            ($user->role === 'guru' && $tugas->user_id === $user->id)
            || ($user->role === 'siswa' && $tugas->kelas_id === $user->kelas_id),
            403
        );

        $tugas->load(['kelas', 'user', 'pengumpulan.siswa']);
        $submission = $tugas->pengumpulan->firstWhere('siswa_id', $user->id);

        $siswaKelas = collect();
        if ($user->role === 'guru') {
            $siswaKelas = User::where('role', 'siswa')
                ->where('kelas_id', $tugas->kelas_id)
                ->orderBy('name')
                ->get();
        }

        $canSubmit = $user->role === 'siswa'
            && $tugas->isOpen()
            && (! $submission || $submission->revisi_aktif);

        return view('mobile.tugas-detail', [
            'tugas' => $tugas,
            'submission' => $submission,
            'user' => $user,
            'siswaKelas' => $siswaKelas,
            'canSubmit' => $canSubmit,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedTugasPayload($request);
        $data['user_id'] = $request->session()->get('user_id');

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('tugas', 'public');
            $data['lampiran_nama'] = $request->file('lampiran')->getClientOriginalName();
        }

        $tugas = Tugas::create($data);

        $this->notifyClassOfNewTugas($tugas);

        return redirect()->route('tugas.show', $tugas)->with('success', $data['tipe'] === 'form'
            ? 'Formulir online berhasil diterbitkan untuk siswa.'
            : 'Tugas berhasil dibuat.');
    }

    public function update(Request $request, Tugas $tugas): RedirectResponse
    {
        $this->assertOwner($request, $tugas);

        $hasSubmissions = $tugas->pengumpulan()->exists();
        $data = $this->validatedTugasPayload($request);

        if ($hasSubmissions) {
            $data['tipe'] = $tugas->tipe;
            if ($tugas->tipe === 'form') {
                $data['form_data'] = $tugas->form_data;
            }
        }

        if ($request->boolean('hapus_lampiran') && $tugas->lampiran) {
            Storage::disk('public')->delete($tugas->lampiran);
            $data['lampiran'] = null;
            $data['lampiran_nama'] = null;
        }

        if ($request->hasFile('lampiran')) {
            if ($tugas->lampiran) {
                Storage::disk('public')->delete($tugas->lampiran);
            }
            $data['lampiran'] = $request->file('lampiran')->store('tugas', 'public');
            $data['lampiran_nama'] = $request->file('lampiran')->getClientOriginalName();
        }

        $tugas->update($data);

        NotificationHelper::sendToClass(
            $tugas->kelas_id,
            'Tugas Diperbarui: '.$tugas->judul,
            'Guru memperbarui detail tugas. Periksa instruksi dan batas waktu terbaru.',
            route('tugas.show', $tugas),
            'task'
        );

        return redirect()->route('tugas.show', $tugas)->with('success', 'Perubahan tugas berhasil disimpan.');
    }

    public function destroy(Request $request, Tugas $tugas): RedirectResponse
    {
        $this->assertOwner($request, $tugas);

        $judul = $tugas->judul;
        $kelasId = $tugas->kelas_id;

        $tugas->load('pengumpulan');
        $this->deleteTugasFiles($tugas);
        $tugas->delete();

        NotificationHelper::sendToClass(
            $kelasId,
            'Tugas Dihapus',
            'Tugas "'.$judul.'" telah dihapus oleh guru.',
            route('tugas.index'),
            'task'
        );

        return redirect()->route('tugas.index')->with('success', 'Tugas berhasil dihapus beserta seluruh pengumpulan siswa.');
    }

    public function exportGrades(Tugas $tugas)
    {
        $request = request();
        abort_unless($tugas->user_id === $request->session()->get('user_id'), 403);

        $submissions = PengumpulanTugas::with('siswa')
            ->where('tugas_id', $tugas->id)
            ->get();

        $filename = 'nilai_tugas_'.Str::slug($tugas->judul).'_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename='.$filename,
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($submissions, $tugas) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Nama Siswa', 'NIK', 'Status', 'Nilai', 'Feedback Guru', 'Dikumpulkan Pada']);

            foreach ($submissions as $key => $sub) {
                fputcsv($file, [
                    $key + 1,
                    $sub->siswa->name ?? '-',
                    $sub->siswa->nik ?? '-',
                    $sub->status,
                    $sub->nilai !== null ? $sub->nilai : 'Belum dinilai',
                    $sub->feedback_guru ?: '-',
                    $sub->dikumpulkan_pada ? $sub->dikumpulkan_pada->format('d/m/Y H:i') : '-',
                ]);
            }

            $siswaIds = $submissions->pluck('siswa_id');
            $belum = User::where('role', 'siswa')
                ->where('kelas_id', $tugas->kelas_id)
                ->whereNotIn('id', $siswaIds)
                ->orderBy('name')
                ->get();

            foreach ($belum as $siswa) {
                fputcsv($file, [
                    $submissions->count() + $belum->search($siswa) + 1,
                    $siswa->name,
                    $siswa->nik ?? '-',
                    'belum_mengumpulkan',
                    '-',
                    '-',
                    '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function submit(Request $request, Tugas $tugas): RedirectResponse
    {
        $user = User::findOrFail($request->session()->get('user_id'));
        abort_unless($user->role === 'siswa' && $user->kelas_id === $tugas->kelas_id, 403);
        abort_if($tugas->isExpired(), 403, 'Batas pengumpulan tugas telah berakhir.');

        $existing = PengumpulanTugas::where('tugas_id', $tugas->id)->where('siswa_id', $user->id)->first();
        abort_if($existing && ! $existing->revisi_aktif, 403, 'Tugas ini masih menunggu penilaian guru.');

        $updateData = [
            'status' => 'terkirim',
            'revisi_aktif' => false,
            'dikumpulkan_pada' => now(),
        ];

        if ($tugas->tipe === 'form') {
            $updateData['jawaban_form'] = $this->validatedFormAnswers($request, $tugas);
            $updateData['catatan'] = 'Pengerjaan via formulir online.';
        } else {
            $rules = [
                'catatan' => ['required', 'string'],
                'jawaban_file' => [$existing && $existing->jawaban_file ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xlsx,xls,ppt,pptx,csv,txt,zip', 'max:10240'],
            ];
            $data = $request->validate($rules);
            $updateData['catatan'] = $data['catatan'];

            if ($request->hasFile('jawaban_file')) {
                if ($existing?->jawaban_file) {
                    Storage::disk('public')->delete($existing->jawaban_file);
                }
                $updateData['jawaban_file'] = $request->file('jawaban_file')->store('jawaban', 'public');
                $updateData['jawaban_nama'] = $request->file('jawaban_file')->getClientOriginalName();
            }
        }

        PengumpulanTugas::updateOrCreate(
            ['tugas_id' => $tugas->id, 'siswa_id' => $user->id],
            $updateData
        );

        NotificationHelper::send(
            $tugas->user_id,
            'Jawaban Tugas Baru',
            $user->name.' mengirim jawaban untuk tugas '.$tugas->judul,
            route('tugas.show', $tugas),
            'task'
        );

        return redirect()->route('tugas.show', $tugas)->with('success', 'Jawaban berhasil dikirim ke guru.');
    }

    public function review(Request $request, PengumpulanTugas $pengumpulan): RedirectResponse
    {
        $user = User::findOrFail($request->session()->get('user_id'));
        $pengumpulan->load('tugas');
        abort_unless($user->role === 'guru' && $pengumpulan->tugas->user_id === $user->id, 403);

        $data = $request->validate([
            'nilai' => ['required', 'numeric', 'between:0,100'],
            'feedback_guru' => ['nullable', 'string'],
            'revisi_aktif' => ['nullable', 'boolean'],
        ]);

        $data['status'] = $request->boolean('revisi_aktif') ? 'perlu_revisi' : 'dinilai';
        $data['revisi_aktif'] = $request->boolean('revisi_aktif');
        $data['dinilai_pada'] = now();
        $pengumpulan->update($data);

        $judulNotif = $data['revisi_aktif'] ? 'Tugas Perlu Revisi' : 'Tugas Sudah Dinilai';
        $pesanNotif = 'Guru memberi nilai '.$data['nilai'].' untuk tugas '.$pengumpulan->tugas->judul.'.';

        NotificationHelper::send($pengumpulan->siswa_id, $judulNotif, $pesanNotif, route('tugas.show', $pengumpulan->tugas), 'task');

        return back()->with('success', 'Penilaian tersimpan.');
    }

    private function assertOwner(Request $request, Tugas $tugas): void
    {
        abort_unless($tugas->user_id === $request->session()->get('user_id'), 403);
    }

    private function validatedTugasPayload(Request $request): array
    {
        $data = $request->validate([
            'judul' => ['required', 'max:255'],
            'deskripsi' => ['nullable'],
            'tipe' => ['required', 'in:file,form'],
            'batas_pengumpulan' => ['nullable', 'date'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'lampiran' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xlsx,xls,ppt,pptx,csv,txt,zip', 'max:10240'],
        ]);

        if ($data['tipe'] === 'form') {
            $data['form_data'] = $this->validateFormData((string) $request->input('form_data', '[]'));
        } else {
            $data['form_data'] = null;
        }

        return $data;
    }

    /**
     * Validasi struktur pertanyaan formulir online dan kembalikan versi yang
     * sudah dinormalisasi: [ ['text' => ..., 'type' => ..., 'options' => [...], 'required' => bool], ... ]
     */
    private function validateFormData(string $json): array
    {
        $questions = json_decode($json, true);

        if (! is_array($questions) || $questions === []) {
            throw ValidationException::withMessages([
                'form_data' => 'Tipe Formulir Online memerlukan minimal satu pertanyaan. Tambahkan pertanyaan pada Form Builder.',
            ]);
        }

        $allowedTypes = ['text', 'essay', 'multiple', 'checkbox', 'dropdown'];
        $normalized = [];

        foreach ($questions as $i => $q) {
            $text = trim((string) ($q['text'] ?? ''));
            $type = (string) ($q['type'] ?? '');
            $options = array_values(array_filter(array_map('trim', (array) ($q['options'] ?? []))));

            if ($text === '' || ! in_array($type, $allowedTypes, true)) {
                throw ValidationException::withMessages([
                    'form_data' => 'Pertanyaan ke-'.($i + 1).' belum lengkap. Isi teks pertanyaan dan pilih jenis jawaban yang valid.',
                ]);
            }

            if (in_array($type, ['multiple', 'checkbox', 'dropdown'], true) && count($options) < 2) {
                throw ValidationException::withMessages([
                    'form_data' => 'Pertanyaan ke-'.($i + 1).' bertipe pilihan dan memerlukan minimal 2 opsi jawaban.',
                ]);
            }

            $normalized[] = [
                'text' => $text,
                'type' => $type,
                'options' => in_array($type, ['multiple', 'checkbox', 'dropdown'], true) ? $options : [],
                'required' => array_key_exists('required', $q) ? (bool) $q['required'] : true,
            ];
        }

        return $normalized;
    }

    /**
     * Keep original question indexes so optional unanswered items do not shift answers.
     */
    private function validatedFormAnswers(Request $request, Tugas $tugas): array
    {
        $questions = $tugas->questions();
        $hasRequired = collect($questions)->contains(fn ($q) => ($q['required'] ?? true) !== false);
        $request->validate([
            'jawaban' => [$hasRequired ? 'required' : 'nullable', 'array'],
        ]);

        $jawaban = $request->input('jawaban', []);
        $normalized = [];

        foreach ($questions as $i => $q) {
            $required = ($q['required'] ?? true) !== false;
            $raw = $jawaban[$i] ?? null;

            if (is_array($raw)) {
                $value = array_values(array_filter(array_map(fn ($v) => trim((string) $v), $raw), fn ($v) => $v !== ''));
            } else {
                $value = is_null($raw) ? '' : trim((string) $raw);
            }

            $empty = is_array($value) ? $value === [] : $value === '';

            if ($required && $empty) {
                throw ValidationException::withMessages([
                    "jawaban.$i" => 'Pertanyaan ke-'.($i + 1).' wajib diisi.',
                ]);
            }

            $normalized[$i] = $value;
        }

        return $normalized;
    }

    private function notifyClassOfNewTugas(Tugas $tugas): void
    {
        NotificationHelper::sendToClass(
            $tugas->kelas_id,
            'Tugas Baru: '.$tugas->judul,
            'Guru telah menambahkan tugas baru di kelas Anda.',
            route('tugas.show', $tugas),
            'task'
        );

        $isPdf = $tugas->lampiran && str_ends_with(strtolower((string) $tugas->lampiran_nama), '.pdf');
        if (! $isPdf) {
            return;
        }

        $students = User::where('kelas_id', $tugas->kelas_id)
            ->where('role', 'siswa')
            ->whereNotNull('email')
            ->get();

        foreach ($students as $student) {
            Mail::to($student->email)->queue(new TugasBaruMail($tugas));
        }
    }

    private function deleteTugasFiles(Tugas $tugas): void
    {
        if ($tugas->lampiran) {
            Storage::disk('public')->delete($tugas->lampiran);
        }

        foreach ($tugas->pengumpulan as $item) {
            if ($item->jawaban_file) {
                Storage::disk('public')->delete($item->jawaban_file);
            }
        }
    }
}
