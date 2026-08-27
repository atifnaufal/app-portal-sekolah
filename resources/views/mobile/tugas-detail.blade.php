@extends('layouts.mobile-app')

@section('content')
<div class="p-3 pb-0">
    <a href="javascript:history.back()" class="text-decoration-none text-muted d-inline-flex align-items-center gap-2 small fw-bold">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
        Kembali
    </a>
</div>
<header class="mobile-hero">
    <div class="eyebrow mt-4">DETAIL TUGAS</div>
    <div class="hero-title mt-2">{{ $tugas->judul }}</div>
    <div class="class-pill mt-3">{{ $tugas->kelas->nama }} · Batas {{ $tugas->batas_pengumpulan?->format('d M Y') ?? 'Terbuka' }}</div>
</header>

<main class="mobile-content">
    <div class="card mobile-card mb-3">
        <div class="card-body p-4">
            <div class="small text-secondary">Dibuat oleh {{ $tugas->user->name }}</div>
            <p class="mt-3 mb-0" style="white-space:pre-line">{{ $tugas->deskripsi ?: 'Tidak ada deskripsi tambahan.' }}</p>
            @if($tugas->lampiran)
                <a href="{{ asset('storage/'.$tugas->lampiran) }}" target="_blank" class="btn btn-outline-primary btn-sm mt-4">Lihat lampiran {{ $tugas->lampiran_nama }}</a>
            @endif
        </div>
    </div>

    @if($user->role === 'siswa')
        <div class="card mobile-card">
            <div class="card-body p-4">
                <h2 class="section-title">{{ $submission ? 'Jawaban sudah terkirim' : 'Kirim jawaban' }}</h2>
                @if($submission)
                    <div class="alert alert-success small mt-3">Terkirim {{ $submission->dikumpulkan_pada?->format('d M Y, H:i') }}. Kamu masih dapat mengirim pembaruan.</div>
                @endif
                <div class="submission-progress alert alert-warning mb-3" id="submission-status" role="status">Lengkapi catatan dan file jawaban untuk mengirim.</div>
                <form method="POST" action="{{ route('tugas.submit',$tugas) }}" enctype="multipart/form-data" class="mt-3" id="submission-form" novalidate>
                    @csrf
                    <label class="form-label fw-semibold">Catatan untuk guru</label>
                    <textarea name="catatan" rows="4" class="form-control mb-3" placeholder="Tulis pesan atau keterangan jawaban..." required>{{ old('catatan',$submission?->catatan) }}</textarea>
                    <label class="form-label fw-semibold">File jawaban</label>
                    <input type="file" name="jawaban_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.zip" required>
                    <div class="small text-secondary mt-2" id="file-status">Format: PDF, Word, gambar, atau ZIP. Maksimal 10 MB.</div>
                    <button type="submit" class="btn btn-primary w-100 py-3 mt-4 profile-action" id="submit-answer" disabled>{{ $submission ? 'Kirim pembaruan' : 'Kirim jawaban ke guru' }}</button>
                </form>
            </div>
        </div>
    @else
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="section-title mb-0">Pengumpulan siswa</h2>
            <span class="badge rounded-pill text-bg-primary">{{ $tugas->pengumpulan->count() }} terkirim</span>
        </div>
        @forelse($tugas->pengumpulan as $item)
            <div class="card mobile-card mb-2">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between">
                        <div class="fw-semibold">{{ $item->siswa->name }}</div>
                        <span class="badge text-bg-success">{{ ucfirst($item->status) }}</span>
                    </div>
                    <div class="small text-secondary mt-1">{{ $item->dikumpulkan_pada?->format('d M Y, H:i') }}</div>
                    @if($item->catatan)
                        <p class="small mt-2 mb-0">{{ $item->catatan }}</p>
                    @endif
                    @if($item->jawaban_file)
                        <a href="{{ asset('storage/'.$item->jawaban_file) }}" target="_blank" class="small text-primary d-inline-block mt-2">Unduh {{ $item->jawaban_nama }}</a>
                    @endif
                    <form method="POST" action="{{ route('tugas.review', $item) }}" class="mt-3 border-top pt-3">
                        @csrf
                        <div class="row g-2">
                            <div class="col-4"><label class="form-label small">Nilai</label><input name="nilai" type="number" min="0" max="100" step="0.01" value="{{ $item->nilai }}" class="form-control form-control-sm" required></div>
                            <div class="col-8"><label class="form-label small">Feedback guru</label><input name="feedback_guru" value="{{ $item->feedback_guru }}" class="form-control form-control-sm" placeholder="Catatan penilaian"></div>
                        </div>
                        <div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="revisi_aktif" value="1" id="revisi-{{ $item->id }}" @checked($item->revisi_aktif)><label class="form-check-label small" for="revisi-{{ $item->id }}">Aktifkan revisi untuk siswa</label></div>
                        <button class="btn btn-primary btn-sm mt-2">Simpan penilaian</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-secondary small">Belum ada siswa yang mengirim jawaban.</div>
        @endforelse
    @endif
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('submission-form');
    if (!form) return;
    const note = form.elements.catatan;
    const file = form.elements.jawaban_file;
    const button = document.getElementById('submit-answer');
    const status = document.getElementById('submission-status');
    const fileStatus = document.getElementById('file-status');
    const updateState = function () {
        const complete = note.value.trim().length > 0 && file.files.length > 0;
        button.disabled = !complete;
        status.className = complete ? 'submission-progress alert alert-success mb-3' : 'submission-progress alert alert-warning mb-3';
        status.textContent = complete ? 'Form lengkap. Jawaban siap dikirim ke guru.' : 'Lengkapi catatan dan file jawaban untuk mengirim.';
        if (file.files.length > 0) fileStatus.textContent = 'File dipilih: ' + file.files[0].name;
    };
    note.addEventListener('input', updateState);
    file.addEventListener('change', updateState);
    form.addEventListener('submit', function (event) {
        updateState();
        if (button.disabled) {
            event.preventDefault();
            status.className = 'submission-progress alert alert-danger mb-3';
            status.textContent = 'Belum bisa dikirim. Isi catatan dan pilih file jawaban terlebih dahulu.';
            alert('Catatan dan file jawaban wajib diisi sebelum dikirim.');
        }
    });
    updateState();
});
</script>
@endsection
