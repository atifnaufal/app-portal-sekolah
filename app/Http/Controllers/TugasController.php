<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Mail\TugasBaruMail;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TugasController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->session()->get('user_id');
        if (! $userId && Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        }

        $user = User::with('kelas')->findOrFail($userId);

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

            foreach ($tugas as $t) {
                $this->autoRecordNonSubmitters($t);
            }

            $uniqueKelasIds = $tugas->pluck('kelas_id')->unique()->filter();
            $siswaCounts = $uniqueKelasIds->isNotEmpty()
                ? User::where('role', 'siswa')
                    ->whereIn('kelas_id', $uniqueKelasIds)
                    ->selectRaw('kelas_id, count(*) as total')
                    ->groupBy('kelas_id')
                    ->pluck('total', 'kelas_id')
                : collect();

            return view('mobile.tugas', [
                'tugas' => $tugas,
                'user' => $user,
                'siswaCounts' => $siswaCounts,
                'pendingTugas' => collect(),
                'completedTugas' => collect(),
                'expiredTugas' => collect(),
            ]);
        }

        $tugas = Tugas::with(['kelas', 'user', 'mataPelajaran', 'pengumpulan' => fn ($query) => $query->where('siswa_id', $user->id)])
            ->where('kelas_id', $user->kelas_id)
            ->latest()
            ->get();

        foreach ($tugas as $t) {
            $this->autoRecordNonSubmitters($t);
        }

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
        $userId = session('user_id') ?: Auth::id();
        $mapels = MataPelajaran::where('guru_id', $userId)->get();

        return view('mobile.tugas-form', [
            'tugas' => new Tugas(['tipe' => 'file', 'mata_pelajaran_id' => request('mapel_id')]),
            'kelases' => Kelas::orderBy('nama')->get(),
            'mapels' => $mapels,
            'hasSubmissions' => false,
        ]);
    }

    public function edit(Request $request, Tugas $tugas): View
    {
        $this->assertOwner($request, $tugas);
        $userId = session('user_id') ?: Auth::id();
        $mapels = MataPelajaran::where('guru_id', $userId)->get();

        return view('mobile.tugas-form', [
            'tugas' => $tugas,
            'kelases' => Kelas::orderBy('nama')->get(),
            'mapels' => $mapels,
            'hasSubmissions' => $tugas->pengumpulan()->exists(),
        ]);
    }

    public function show(Request $request, Tugas $tugas): View
    {
        $userId = $request->session()->get('user_id');
        if (! $userId && Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        }

        $user = User::with('kelas')->findOrFail($userId);
        abort_unless(
            ($user->role === 'guru' && (int) $tugas->user_id === (int) $user->id)
            || ($user->role === 'siswa' && (int) $tugas->kelas_id === (int) $user->kelas_id)
            || $user->role === 'admin',
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

        $userId = $request->session()->get('user_id');
        if (! $userId && Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        }

        $data['user_id'] = $userId;

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


    public function exportPdf(Tugas $tugas)
    {
        $request = request();

        $userId = $request->session()->get('user_id');
        $sessionUserId = $userId;
        $authUserId = null;
        if (! $userId) {
            if (Auth::guard('web')->check()) {
                $authUserId = Auth::guard('web')->id();
            }
        } else {
            $authUserId = $userId;
        }

        // Debug: Log the comparison
        \Illuminate\Support\Facades\Log::info('Export PDF Debug:', [
            'tugas_user_id' => $tugas->user_id,
            'session_user_id' => $sessionUserId,
            'auth_user_id' => $authUserId,
            'user_matches' => (int) $tugas->user_id === (int) ($authUserId ?? $sessionUserId),
            'user_role' => $request->session()->get('user_role'),
        ]);

        // Allow access if: admin OR creator matches
        $userRole = $request->session()->get('user_role') ?? optional(Auth::user())->role;
        $isAdmin = $userRole === 'admin';
        $isCreator = (int) $tugas->user_id === (int) ($authUserId ?? $sessionUserId);

        abort_unless($isAdmin || $isCreator, 403);

        $submissions = PengumpulanTugas::with('siswa')
            ->where('tugas_id', $tugas->id)
            ->get();

        $siswaIds = $submissions->pluck('siswa_id');
        $belum = User::where('role', 'siswa')
            ->where('kelas_id', $tugas->kelas_id)
            ->whereNotIn('id', $siswaIds)
            ->orderBy('name')
            ->get();

        $data = [
            'tugas'       => $tugas,
            'submissions' => $submissions,
            'belum'       => $belum,
            'today'       => now()->translatedFormat('d F Y'),
        ];

        try {
            app()->setLocale('id');
            \Carbon\Carbon::setLocale('id');

            // Optimasi memori
            ini_set('memory_limit', '256M');
            ini_set('max_execution_time', '120');

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rekap-tugas', $data);
            $pdf->setPaper('a4', 'portrait');

            $filename = 'rekap-tugas-'.\Illuminate\Support\Str::slug($tugas->judul).'-'.date('Y-m-d').'.pdf';
            return $pdf->download($filename);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Export PDF tugas gagal: '.$e->getMessage());
            return back()->with('error', 'Gagal membuat PDF. Silakan coba download Excel.');
        }
    }

    public function exportExcel(Tugas $tugas)
    {
        $request = request();

        $userId = $request->session()->get('user_id');
        $sessionUserId = $userId;
        $authUserId = null;
        if (! $userId) {
            if (Auth::guard('web')->check()) {
                $authUserId = Auth::guard('web')->id();
            }
        } else {
            $authUserId = $userId;
        }

        // Debug: Log the comparison
        \Illuminate\Support\Facades\Log::info('Export Excel Debug:', [
            'tugas_user_id' => $tugas->user_id,
            'session_user_id' => $sessionUserId,
            'auth_user_id' => $authUserId,
            'user_matches' => (int) $tugas->user_id === (int) ($authUserId ?? $sessionUserId),
            'user_role' => $request->session()->get('user_role'),
        ]);

        // Allow access if: admin OR creator matches
        $userRole = $request->session()->get('user_role') ?? optional(Auth::user())->role;
        $isAdmin = $userRole === 'admin';
        $isCreator = (int) $tugas->user_id === (int) ($authUserId ?? $sessionUserId);

        abort_unless($isAdmin || $isCreator, 403);

        $submissions = PengumpulanTugas::with('siswa')
            ->where('tugas_id', $tugas->id)
            ->get();

        $siswaIds = $submissions->pluck('siswa_id');
        $belum = User::where('role', 'siswa')
            ->where('kelas_id', $tugas->kelas_id)
            ->whereNotIn('id', $siswaIds)
            ->orderBy('name')
            ->get();

        $filename = 'rekap-tugas-'.\Illuminate\Support\Str::slug($tugas->judul).'-'.date('Y-m-d').'.xls';

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>'."\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:html="http://www.w3.org/TR/REC-html40">';

        $xml .= '<Styles>';
        $xml .= '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="11"/></Style>';
        $xml .= '<Style ss:ID="title"><Font ss:Bold="1" ss:Size="16" ss:Color="#FFFFFF"/><Interior ss:Color="#0F172A" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="subtitle"><Font ss:Color="#475569" ss:Size="11"/><Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center"/></Style>';
        $xml .= '<Style ss:ID="header"><Font ss:Bold="1" ss:Color="#FFFFFF" ss:Size="11"/><Interior ss:Color="#2563EB" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="bordered"><Borders>'
            .'<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'</Borders></Style>';
        $xml .= '<Style ss:ID="cell"><Alignment ss:Horizontal="Center"/></Style>';
        $xml .= '<Style ss:ID="alt"><Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/></Style>';
        $xml .= '<Style ss:ID="left"><Alignment ss:Horizontal="Left"/></Style>';
        $xml .= '<Style ss:ID="footer"><Font ss:Italic="1" ss:Color="#64748B"/></Style>';
        $xml .= '<Style ss:ID="score"><Font ss:Bold="1" ss:Color="#0F172A"/></Style>';
        $xml .= '</Styles>';

        $xml .= '<Worksheet ss:Name="Rekap Tugas"><Table>';
        $xml .= '<Column ss:Width="50"/><Column ss:Width="240"/><Column ss:Width="120"/><Column ss:Width="90"/><Column ss:Width="200"/><Column ss:Width="140"/>';

        $judulEsc = htmlspecialchars($tugas->judul, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $mapelEsc = htmlspecialchars($tugas->mataPelajaran?->nama ?? 'Umum', ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $kelasEsc = htmlspecialchars($tugas->kelas?->nama ?? '-', ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $xml .= '<Row ss:Height="30"><Cell ss:StyleID="title" ss:MergeAcross="5"><Data ss:Type="String">REKAPITULASI PENUMPULAN TUGAS</Data></Cell></Row>';
        $xml .= '<Row ss:Height="20"><Cell ss:StyleID="subtitle" ss:MergeAcross="5"><Data ss:Type="String">'.$judulEsc.' | '.$mapelEsc.' | Kelas '.$kelasEsc.'</Data></Cell></Row>';

        $xml .= '<Row ss:Height="22">';
        foreach (['No','Nama Siswa','Status','Nilai','Feedback Guru','Dikumpulkan Pada'] as $hdr) {
            $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">'.$hdr.'</Data></Cell>';
        }
        $xml .= '</Row>';

        $rowNum = 1;
        foreach ($submissions as $sub) {
            $rowStyle = (($rowNum % 2) === 0) ? 'alt' : 'default';
            $status = match($sub->status) {
                'terkirim' => 'Terkirim',
                'dinilai' => 'Dinilai',
                'perlu_revisi' => 'Perlu Revisi',
                'tidak_mengumpulkan' => 'Tidak Mengumpulkan',
                default => ucfirst(str_replace('_', ' ', $sub->status)),
            };
            $xml .= '<Row>';
            $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="Number">'.$rowNum.'</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered left '.$rowStyle.'"><Data ss:Type="String">'.htmlspecialchars($sub->siswa->name ?? '-', ENT_XML1 | ENT_QUOTES, 'UTF-8').'</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="String">'.htmlspecialchars($status, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="String">'.($sub->nilai !== null ? $sub->nilai : '-').'</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered left '.$rowStyle.'"><Data ss:Type="String">'.htmlspecialchars($sub->feedback_guru ?: '-', ENT_XML1 | ENT_QUOTES, 'UTF-8').'</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="String">'.($sub->dikumpulkan_pada ? $sub->dikumpulkan_pada->format('d/m/Y H:i') : '-').'</Data></Cell>';
            $xml .= '</Row>';
            $rowNum++;
        }

        foreach ($belum as $siswa) {
            $rowStyle = (($rowNum % 2) === 0) ? 'alt' : 'default';
            $xml .= '<Row>';
            $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="Number">'.$rowNum.'</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered left '.$rowStyle.'"><Data ss:Type="String">'.htmlspecialchars($siswa->name, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="String">Belum Mengumpulkan</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="String">-</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered left '.$rowStyle.'"><Data ss:Type="String">-</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="String">-</Data></Cell>';
            $xml .= '</Row>';
            $rowNum++;
        }

        $xml .= '<Row ss:Height="30"><Cell ss:StyleID="footer" ss:MergeAcross="5"><Data ss:Type="String">Dicetak pada '.htmlspecialchars(now()->translatedFormat('d F Y'), ENT_XML1 | ENT_QUOTES, 'UTF-8').'</Data></Cell></Row>';
        $xml .= '</Table></Worksheet></Workbook>';

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }


    public function submit(Request $request, Tugas $tugas): RedirectResponse
    {
        $userId = $request->session()->get('user_id');
        if (! $userId && Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        }

        $user = User::findOrFail($userId);
        abort_unless($user->role === 'siswa' && (int) $user->kelas_id === (int) $tugas->kelas_id, 403);
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
        $userId = $request->session()->get('user_id');
        if (! $userId && Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        }

        $user = User::findOrFail($userId);
        $pengumpulan->load('tugas');
        abort_unless($user->role === 'guru' && (int) $pengumpulan->tugas->user_id === (int) $user->id, 403);

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
        $userId = $request->session()->get('user_id');

        if (! $userId && Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        }

        $userRole = $request->session()->get('user_role');

        if (! $userRole && Auth::guard('web')->check()) {
            $userRole = Auth::guard('web')->user()?->role;
        }

        $isOwner = (int) $tugas->user_id === (int) $userId;
        $isAdmin = $userRole === 'admin';

        abort_unless($isOwner || $isAdmin, 403);
    }

    private function validatedTugasPayload(Request $request): array
    {
        $data = $request->validate([
            'judul' => ['required', 'max:255'],
            'deskripsi' => ['nullable'],
            'tipe' => ['required', 'in:file,form'],
            'batas_pengumpulan' => ['nullable', 'date'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mata_pelajaran_id' => ['nullable', 'exists:mata_pelajaran,id'],
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
            // Kirim sinkron: QUEUE_CONNECTION=database tapi tidak ada proses
            // queue:work yang dijalankan di Railway, jadi ->queue() tidak akan
            // pernah dieksekusi dan email hilang tanpa suara.
            try {
                Mail::to($student->email)->send(new TugasBaruMail($tugas));
            } catch (\Throwable $e) {
                Log::error(
                    'Gagal kirim email tugas ke '.$student->email.': '.$e->getMessage()
                );
            }
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

    /**
     * When a homework passes its deadline, auto-record every student of the
     * class who did not submit as "not submitted" with a null score. Idempotent:
     * students who already have a submission (including an empty/0 record) are
     * never overwritten.
     */
    private function autoRecordNonSubmitters(Tugas $tugas): void
    {
        if (! $tugas->isExpired() || ! $tugas->kelas_id) {
            return;
        }

        $submittedIds = PengumpulanTugas::where('tugas_id', $tugas->id)->pluck('siswa_id');

        $missing = User::where('role', 'siswa')
            ->where('kelas_id', $tugas->kelas_id)
            ->whereNotIn('id', $submittedIds)
            ->pluck('id');

        foreach ($missing as $siswaId) {
            PengumpulanTugas::firstOrCreate(
                ['tugas_id' => $tugas->id, 'siswa_id' => $siswaId],
                [
                    'status' => 'tidak_mengumpulkan',
                    'nilai' => null,
                    'revisi_aktif' => false,
                    'dinilai_pada' => now(),
                ]
            );
        }
    }
}
