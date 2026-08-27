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
            $completedTugas = $tugas->filter(fn ($item) => $item->pengumpulan->first() && ! $item->pengumpulan->first()->revisi_aktif && $item->pengumpulan->first()->nilai !== null);
            return view('mobile.tugas', ['tugas' => $activeTugas, 'completedTugas' => $completedTugas, 'user' => $user]);
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
        $data = $request->validate(['judul' => ['required', 'max:255'], 'deskripsi' => ['nullable'], 'batas_pengumpulan' => ['nullable', 'date'], 'kelas_id' => ['required', 'exists:kelas,id'], 'lampiran' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120']]);
        $data['user_id'] = $request->session()->get('user_id');
        $data['kelas_id'] = $request->session()->get('user_kelas_id') ?: $data['kelas_id'];
        if ($request->hasFile('lampiran')) { $data['lampiran'] = $request->file('lampiran')->store('tugas', 'public'); $data['lampiran_nama'] = $request->file('lampiran')->getClientOriginalName(); }
        $tugas = Tugas::create($data);

        NotificationHelper::sendToClass($tugas->kelas_id, 'Tugas Baru: ' . $tugas->judul, 'Guru telah menambahkan tugas baru di kelas Anda.', route('tugas.show', $tugas), 'task');

        return redirect()->route('tugas.index')->with('success', 'Tugas berhasil dibuat.');
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
        $data = $request->validate(['catatan' => ['required', 'string'], 'jawaban_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,zip', 'max:10240']]);
        if ($request->hasFile('jawaban_file')) { $data['jawaban_file'] = $request->file('jawaban_file')->store('jawaban', 'public'); $data['jawaban_nama'] = $request->file('jawaban_file')->getClientOriginalName(); }
        $submission = PengumpulanTugas::updateOrCreate(['tugas_id' => $tugas->id, 'siswa_id' => $user->id], array_merge($data, ['status' => 'terkirim', 'revisi_aktif' => false, 'dikumpulkan_pada' => now()]));

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
