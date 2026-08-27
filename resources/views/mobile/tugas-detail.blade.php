@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: #fff; border-bottom: 1px solid #edf2f7;
        padding: 12px 20px; display: flex; align-items: center; gap: 15px;
    }
    .page-container { padding-top: 70px; padding-bottom: 40px; }

    .ai-card {
        background: #fff; border: none; border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
    }
    .form-question {
        background: #f8fafc; border-radius: 18px; padding: 20px; margin-bottom: 15px;
    }
</style>

<div class="page-header">
    <a href="{{ route('tugas.index') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 17px;">Detail Tugas</div>
</div>

<div class="page-container px-3">
    <header class="mobile-hero" style="border-radius: 30px; margin-bottom: 25px; background: linear-gradient(135deg, #246bfe, #1e293b);">
        <div class="eyebrow" style="color: rgba(255,255,255,0.7);">{{ $tugas->tipe === 'form' ? 'FORMULIR ONLINE' : 'PENGIRIMAN FILE' }} · {{ $tugas->kelas->nama }}</div>
        <div class="hero-title mt-2 text-white" style="font-size: 24px;">{{ $tugas->judul }}</div>
        <div class="mt-3">
            <span class="badge bg-white bg-opacity-20 rounded-pill px-3 py-2 fw-normal" style="font-size: 11px;">
                Batas: {{ $tugas->batas_pengumpulan?->format('d M Y') ?? 'Terbuka' }}
            </span>
        </div>
    </header>

    <div class="card ai-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="avatar" style="width:30px; height:30px; font-size: 11px;">{{ strtoupper(substr($tugas->user->name,0,1)) }}</div>
                <div class="small fw-bold">{{ $tugas->user->name }} <span class="text-muted fw-normal">· Pengajar</span></div>
            </div>
            <p class="text-secondary small" style="line-height: 1.6; white-space: pre-line;">{{ $tugas->deskripsi ?: 'Baca instruksi dengan teliti sebelum mengerjakan.' }}</p>

            @if($tugas->lampiran)
                <div class="mt-4 p-3 border rounded-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-file-earmark-pdf-fill text-danger h3 mb-0"></i>
                        <div>
                            <div class="small fw-bold text-truncate" style="max-width: 150px;">{{ $tugas->lampiran_nama }}</div>
                            <div class="x-small text-muted uppercase">DOKUMEN PDF</div>
                        </div>
                    </div>
                    <a href="{{ asset('storage/'.$tugas->lampiran) }}" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">Buka</a>
                </div>
            @endif
        </div>
    </div>

    @if($user->role === 'siswa')
        @if($tugas->tipe === 'form')
            @php
                $formData = is_array($tugas->form_data) ? $tugas->form_data : (json_decode($tugas->form_data ?: '[]', true) ?: []);
            @endphp
            <div class="card ai-card">
                <div class="card-body p-4">
                    <h2 class="section-title mb-4" style="font-size: 18px;">Formulir Pengerjaan</h2>
                    <form method="POST" action="{{ route('tugas.submit', $tugas) }}" id="formTask">
                        @csrf
                        @foreach($formData as $index => $q)
                            @php
                                $isRequired = $q['required'] ?? true;
                            @endphp
                            <div class="form-question">
                                <label class="fw-bold text-dark mb-2 d-block" style="font-size: 14px;" for="q{{ $index }}">
                                    {{ $index + 1 }}. {{ $q['text'] }}
                                    @if($isRequired)
                                        <span class="text-danger fw-bold">*</span>
                                    @endif
                                </label>
                                @if($q['type'] === 'text')
                                    <input type="text" id="q{{ $index }}" name="jawaban[{{ $index }}]" class="form-control border-0 shadow-sm" style="border-radius: 12px;" placeholder="Jawaban singkat Anda..." @if($isRequired) required @endif>
                                @elseif($q['type'] === 'essay')
                                    <textarea id="q{{ $index }}" name="jawaban[{{ $index }}]" rows="3" class="form-control border-0 shadow-sm" style="border-radius: 12px;" placeholder="Tulis jawaban paragraf Anda..." @if($isRequired) required @endif></textarea>
                                @elseif($q['type'] === 'multiple')
                                    @foreach($q['options'] ?? [] as $optIndex => $opt)
                                        <div class="form-check mb-2 ps-0">
                                            <input class="form-check-input" type="radio" name="jawaban[{{ $index }}]" id="q{{ $index }}_{{ $optIndex }}" value="{{ $opt }}" @if($isRequired) required @endif>
                                            <label class="form-check-label small" for="q{{ $index }}_{{ $optIndex }}">{{ $opt }}</label>
                                        </div>
                                    @endforeach
                                @elseif($q['type'] === 'checkbox')
                                    @foreach($q['options'] ?? [] as $optIndex => $opt)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="jawaban[{{ $index }}][]" id="q{{ $index }}_{{ $optIndex }}" value="{{ $opt }}" @if($isRequired) required @endif>
                                            <label class="form-check-label small" for="q{{ $index }}_{{ $optIndex }}">{{ $opt }}</label>
                                        </div>
                                    @endforeach
                                    <div class="x-small text-secondary mt-1">Centang semua jawaban yang sesuai.</div>
                                @elseif($q['type'] === 'dropdown')
                                    <select id="q{{ $index }}" name="jawaban[{{ $index }}]" class="form-select border-0 shadow-sm" style="border-radius: 12px;" @if($isRequired) required @endif>
                                        <option value="">-- Pilih jawaban --</option>
                                        @foreach($q['options'] ?? [] as $opt)
                                            <option value="{{ $opt }}">{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-primary w-100 py-3 mt-3 shadow" style="border-radius: 15px; font-weight: 800;">Kirim Jawaban Formulir</button>
                    </form>
                </div>
            </div>
        @else
            <div class="card ai-card">
                <div class="card-body p-4">
                    <h2 class="section-title mb-3" style="font-size: 18px;">Kirim File Jawaban</h2>
                    @if($submission)
                        <div class="alert alert-success border-0 rounded-4 x-small d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            Terkirim pada {{ $submission->dikumpulkan_pada?->format('d M Y, H:i') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('tugas.submit', $tugas) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label x-small fw-bold text-muted">CATATAN (WAJIB)</label>
                            <textarea name="catatan" rows="3" class="form-control border-light" style="border-radius: 15px; background: #fcfcfc;" placeholder="Tulis catatan pengerjaan..." required>{{ $submission?->catatan }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label x-small fw-bold text-muted">UNGGAH FILE</label>
                            <input type="file" name="jawaban_file" class="form-control border-light" style="border-radius: 12px; background: #fcfcfc;" required>
                            <div class="x-small text-secondary mt-2">PDF, Word, atau Gambar (Max 10MB)</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 shadow" style="border-radius: 15px; font-weight: 800;">
                            {{ $submission ? 'Perbarui Jawaban' : 'Kirim Sekarang' }}
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @else
        <!-- Guru View Monitoring -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0" style="font-size: 18px;">Pengumpulan Siswa</h2>
            <a href="{{ route('tugas.export', $tugas) }}" class="btn btn-outline-success btn-sm rounded-pill px-3">
                <i class="bi bi-file-earmark-excel me-1"></i> Excel
            </a>
        </div>

        @forelse($tugas->pengumpulan as $item)
            <div class="card ai-card mb-3 border border-light">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="fw-bold" style="font-size: 14px;">{{ $item->siswa->name }}</div>
                        <span class="badge {{ $item->nilai ? 'bg-success' : 'bg-warning' }} rounded-pill x-small px-2">
                            {{ $item->nilai ? 'Dinilai: '.$item->nilai : 'Menunggu' }}
                        </span>
                    </div>
                    <div class="x-small text-muted mb-3">{{ $item->dikumpulkan_pada?->diffForHumans() }}</div>

                    {{-- Rincian jawaban siswa: tautan file atau jawaban formulir --}}
                    @if($item->jawaban_file)
                        <a href="{{ asset('storage/'.$item->jawaban_file) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 mb-3">
                            <i class="bi bi-paperclip me-1"></i> {{ \Illuminate\Support\Str::limit($item->jawaban_nama ?: 'Lihat File Jawaban', 25) }}
                        </a>
                    @endif
                    @if($item->jawaban_form)
                        @php
                            $answers = is_array($item->jawaban_form) ? $item->jawaban_form : (json_decode($item->jawaban_form ?: '[]', true) ?: []);
                            $formData = is_array($tugas->form_data) ? $tugas->form_data : (json_decode($tugas->form_data ?: '[]', true) ?: []);
                        @endphp
                        <div class="p-3 rounded-4 mb-3" style="background: #f8fafc;">
                            <div class="x-small fw-bold text-secondary mb-2"><i class="bi bi-ui-checks me-1"></i>JAWABAN FORMULIR SISWA</div>
                            @foreach($formData as $qi => $q)
                                @php
                                    $ans = $answers[$qi] ?? null;
                                @endphp
                                <div class="mb-2">
                                    <div class="x-small fw-bold text-dark">{{ $qi + 1 }}. {{ $q['text'] ?? '' }}</div>
                                    <div class="x-small text-secondary" style="white-space: pre-line;">{{ is_array($ans) ? (implode(' • ', array_filter($ans)) ?: '— tidak dijawab —') : ($ans && $ans !== '' ? $ans : '— tidak dijawab —') }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if($item->catatan && !$item->jawaban_form)
                        <div class="x-small text-secondary mb-3 fst-italic">"{{ $item->catatan }}"</div>
                    @endif

                    <form method="POST" action="{{ route('tugas.review', $item) }}" class="mt-2 pt-2 border-top">
                        @csrf
                        <div class="row g-2">
                            <div class="col-4"><input name="nilai" type="number" step="0.01" class="form-control form-control-sm" placeholder="Nilai" value="{{ $item->nilai }}" required></div>
                            <div class="col-8"><input name="feedback_guru" class="form-control form-control-sm" placeholder="Catatan/Feedback" value="{{ $item->feedback_guru }}"></div>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="revisi_aktif" id="revisi_{{ $item->id }}" value="1" @checked($item->revisi_aktif)>
                            <label class="form-check-label x-small" for="revisi_{{ $item->id }}">Minta siswa revisi jawaban</label>
                        </div>
                        <button class="btn btn-primary btn-sm w-100 mt-2 rounded-pill">Simpan</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-5 opacity-50">Belum ada pengumpulan.</div>
        @endforelse
    @endif
</div>
@endsection
