<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Tugas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Models\PengumpulanTugas;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Events\NotificationEvent;
use App\Helpers\NotificationHelper;

class TugasController extends Controller
{
    public function index(Request $request): View
    {
        $kelasId = $request->session()->get('user_kelas_id');
        $user = User::with('kelas')->findOrFail($request->session()->get('user_id'));
        $tugas = Tugas::with(['kelas', 'user', 'pengumpulan' => fn ($query) => $query->where('siswa_id', $user->id)])
            ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))->latest()->get();
        if ($request->session()->get('user_role') !== 'admin') {
            $activeTugas = $tugas->filter(fn ($item) => ! $item->batas_pengumpulan?->isBefore(today()) && (! $item->pengumpulan->first() || $item->pengumpulan->first()->revisi_aktif));
            $pendingTugas = $tugas->filter(fn ($item) => $item->pengumpulan->first() && ! $item->pengumpulan->first()->revisi_aktif && $item->pengumpulan->first()->nilai === null);
            $completedTugas = $tugas->filter(fn ($item) => $item->pengumpulan->first() && ! $item->pengumpulan->first()->revisi_aktif && $item->pengumpulan->first()->nilai !== null);
            return view('mobile.tugas', ['tugas' => $activeTugas, 'pendingTugas' => $pendingTugas, 'completedTugas' => $completedTugas, 'user' => $user]);
        }
        return view('tugas.index', compact('tugas'));
    }

    public function create(): View { return view('tugas.form', ['tugas' => new Tugas, 'kelases' => Kelas::orderBy('nama')->get()]); }

    public function show(Request $request, Tugas $tugas): View
    {
        $user = User::with('kelas')->findOrFail($request->session()->get('user_id'));
        abort_unless($user->role === 'guru' && $tugas->user_id === $user->id || $user->role === 'siswa' && $tugas->kelas_id === $user->kelas_id, 403);
        $tugas->load(['kelas', 'user', 'pengumpulan.siswa']);
        $submission = $tugas->pengumpulan->firstWhere('siswa_id', $user->id);
        if ($user->role === 'siswa' && ($tugas->batas_pengumpulan?->isBefore(today()) || ($submission && ! $submission->revisi_aktif))) return redirect()->route('tugas.index')->with('error', $submission?->nilai !== null ? 'Tugas sudah dinilai dan tidak dapat dibuka kembali.' : 'Tugas ini sudah terkirim atau melewati batas waktu.');
        return view('mobile.tugas-detail', ['tugas' => $tugas, 'submission' => $submission, 'user' => $user]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'judul' => ['required', 'max:255'],
            'deskripsi' => ['nullable'],
            'tipe' => ['required', 'in:file,form'],
            'batas_pengumpulan' => ['nullable', 'date'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'lampiran' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120']
        ]);
        $data['user_id'] = $request->session()->get('user_id');
        $data['kelas_id'] = $request->session()->get('user_kelas_id') ?: $data['kelas_id'];

        // Validasi & normalisasi pertanyaan formulir online (tipe: form).
        // form_data dikirim sebagai string JSON dari form builder, lalu disimpan
        // sebagai array terstruktur (cast "array" pada model Tugas).
        if ($data['tipe'] === 'form') {
            $data['form_data'] = $this->validateFormData((string) $request->input('form_data', '[]'));
        } else {
            $data['form_data'] = null;
        }

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('tugas', 'public');
            $data['lampiran_nama'] = $request->file('lampiran')->getClientOriginalName();

            // Send email notification if attachment is PDF
            if ($request->file('lampiran')->getClientOriginalExtension() === 'pdf') {
                $students = User::where('kelas_id', $data['kelas_id'])->where('role', 'siswa')->get();
                foreach ($students as $student) {
                    if ($student->email) {
                        \Illuminate\Support\Facades\Mail::to($student->email)->queue(new \App\Mail\TugasBaruMail(new Tugas($data)));
                    }
                }
            }
        }

        $tugas = Tugas::create($data);

        NotificationHelper::sendToClass($tugas->kelas_id, 'Tugas Baru: ' . $tugas->judul, 'Guru telah menambahkan tugas baru di kelas Anda.', route('tugas.show', $tugas), 'task');

        return redirect()->route('tugas.index')->with('success', $data['tipe'] === 'form'
            ? 'Formulir online berhasil diterbitkan untuk siswa.'
            : 'Tugas berhasil dibuat.');
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
                    'form_data' => 'Pertanyaan ke-' . ($i + 1) . ' belum lengkap. Isi teks pertanyaan dan pilih jenis jawaban yang valid.',
                ]);
            }

            if (in_array($type, ['multiple', 'checkbox', 'dropdown'], true) && count($options) < 2) {
                throw ValidationException::withMessages([
                    'form_data' => 'Pertanyaan ke-' . ($i + 1) . ' bertipe pilihan dan memerlukan minimal 2 opsi jawaban.',
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

    public function exportGrades(Tugas $tugas)
    {
        $request = request();
        abort_unless($tugas->user_id === $request->session()->get('user_id'), 403);

        $submissions = PengumpulanTugas::with('siswa')
            ->where('tugas_id', $tugas->id)
            ->get();

        $filename = "nilai_tugas_" . \Illuminate\Support\Str::slug($tugas->judul) . "_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($submissions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Nama Siswa', 'NIK', 'Status', 'Nilai', 'Dikumpulkan Pada']);

            foreach ($submissions as $key => $sub) {
                fputcsv($file, [
                    $key + 1,
                    $sub->siswa->name,
                    $sub->siswa->nik,
                    $sub->status,
                    $sub->nilai ?: 'Belum dinilai',
                    $sub->dikumpulkan_pada ? $sub->dikumpulkan_pada->format('d/m/Y H:i') : '-'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(Request $request, Tugas $tugas): RedirectResponse
    {
        abort_unless($tugas->user_id === $request->session()->get('user_id'), 403);
        $tugas->delete();
        return back()->with('success', 'Tugas berhasil dihapus.');
    }

    public function submit(Request $request, Tugas $tugas): RedirectResponse
    {
        $user = User::findOrFail($request->session()->get('user_id'));
        abort_unless($user->role === 'siswa' && $user->kelas_id === $tugas->kelas_id, 403);
        abort_if($tugas->batas_pengumpulan?->isBefore(today()), 403, 'Batas pengumpulan tugas telah berakhir.');

        $existing = PengumpulanTugas::where('tugas_id', $tugas->id)->where('siswa_id', $user->id)->first();
        abort_if($existing && ! $existing->revisi_aktif, 403, 'Tugas ini masih menunggu penilaian guru.');

        $rules = [];
        if ($tugas->tipe === 'form') {
            // Jika seluruh pertanyaan bersifat opsional, jawaban kosong tetap diperbolehkan.
            $hasRequired = collect($tugas->form_data ?? [])->contains(fn ($q) => ($q['required'] ?? true) !== false);
            $rules['jawaban'] = [$hasRequired ? 'required' : 'nullable', 'array'];
        } else {
            $rules['catatan'] = ['required', 'string'];
            $rules['jawaban_file'] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,zip', 'max:10240'];
        }

        $data = $request->validate($rules);

        $updateData = [
            'status' => 'terkirim',
            'revisi_aktif' => false,
            'dikumpulkan_pada' => now()
        ];

        if ($tugas->tipe === 'form') {
            $updateData['jawaban_form'] = array_values($data['jawaban'] ?? []);
            $updateData['catatan'] = 'Pengerjaan via formulir online.';
        } else {
            $updateData['catatan'] = $data['catatan'];
            if ($request->hasFile('jawaban_file')) {
                $updateData['jawaban_file'] = $request->file('jawaban_file')->store('jawaban', 'public');
                $updateData['jawaban_nama'] = $request->file('jawaban_file')->getClientOriginalName();
            }
        }

        $submission = PengumpulanTugas::updateOrCreate(
            ['tugas_id' => $tugas->id, 'siswa_id' => $user->id],
            $updateData
        );

        NotificationHelper::send($tugas->user_id, 'Jawaban Tugas Baru', $user->name . ' mengirim jawaban untuk tugas ' . $tugas->judul, route('tugas.show', $tugas), 'task');

        return back()->with('success', 'Jawaban berhasil dikirim ke guru.');
    }

    public function review(Request $request, PengumpulanTugas $pengumpulan): RedirectResponse
    {
        $user = User::findOrFail($request->session()->get('user_id'));
        $pengumpulan->load('tugas');
        abort_unless($user->role === 'guru' && $pengumpulan->tugas->user_id === $user->id, 403);
        $data = $request->validate(['nilai' => ['required', 'numeric', 'between:0,100'], 'feedback_guru' => ['nullable', 'string'], 'revisi_aktif' => ['nullable', 'boolean']]);
        $data['status'] = $request->boolean('revisi_aktif') ? 'perlu_revisi' : 'dinilai';
        $data['revisi_aktif'] = $request->boolean('revisi_aktif');
        $data['dinilai_pada'] = now();
        $pengumpulan->update($data);

        $judulNotif = $data['revisi_aktif'] ? 'Tugas Perlu Revisi' : 'Tugas Sudah Dinilai';
        $pesanNotif = 'Guru memberi nilai ' . $data['nilai'] . ' untuk tugas ' . $pengumpulan->tugas->judul . '.';

        NotificationHelper::send($pengumpulan->siswa_id, $judulNotif, $pesanNotif, route('tugas.show', $pengumpulan->tugas), 'task');

        return back()->with('success', 'Penilaian tersimpan.');
    }
}
