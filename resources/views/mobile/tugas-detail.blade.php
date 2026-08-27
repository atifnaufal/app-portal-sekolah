@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
@php
    $isGuru = $user->role === 'guru';
    $deadline = $tugas->deadlineStatus();
    $submission = $submission ?? null;
    $siswaKelas = $siswaKelas ?? collect();
    $canSubmit = $canSubmit ?? false;
    $totalSiswa = $isGuru ? $siswaKelas->count() : 0;
    $totalSubmitted = $tugas->pengumpulan->count();
    $totalGraded = $tugas->pengumpulan->whereNotNull('nilai')->where('revisi_aktif', false)->count();
    $totalPending = $tugas->pengumpulan->whereNull('nilai')->where('revisi_aktif', false)->count();
    $totalRevisi = $tugas->pengumpulan->where('revisi_aktif', true)->count();
@endphp

<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.92); backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(226,232,240,0.7);
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 70px; padding-bottom: 48px; }

    .glass-card {
        background: #fff; border: none; border-radius: 24px;
        box-shadow: 0 8px 28px rgba(0,0,0,0.04);
        overflow: hidden; margin-bottom: 16px;
    }

    .deadline-ring {
        width: 72px; height: 72px; border-radius: 50%; position: relative;
        display: flex; align-items: center; justify-content: center;
    }
    .deadline-ring svg { position: absolute; top: 0; left: 0; transform: rotate(-90deg); }
    .deadline-ring .ring-text { text-align: center; }
    .deadline-ring .ring-num { font-size: 20px; font-weight: 800; line-height: 1; }
    .deadline-ring .ring-label { font-size: 8px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; opacity: 0.7; }

    .stat-pill {
        background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);
        border-radius: 16px; padding: 12px 10px; text-align: center; flex: 1;
    }
    .stat-pill .sp-num { font-size: 20px; font-weight: 800; color: #fff; line-height: 1.1; }
    .stat-pill .sp-lbl { font-size: 9px; font-weight: 700; letter-spacing: 0.04em; color: rgba(255,255,255,0.7); margin-top: 4px; text-transform: uppercase; }

    .file-attach-card {
        display: flex; align-items: center; gap: 14px; padding: 16px;
        background: #f8fafc; border: 1px solid #e8ecf1; border-radius: 18px;
    }

    .upload-zone {
        border: 2px dashed #cbd5e1; border-radius: 24px; padding: 32px 20px;
        text-align: center; cursor: pointer; transition: all 0.3s ease;
        background: linear-gradient(135deg, #fafbfc, #f1f5f9);
        position: relative; overflow: hidden;
    }
    .upload-zone:hover, .upload-zone.dragover {
        border-color: #246bfe; background: linear-gradient(135deg, #eef4ff, #f0f5ff);
        transform: translateY(-2px); box-shadow: 0 8px 24px rgba(36,107,254,0.1);
    }
    .upload-zone.has-file {
        border-color: #16a34a; border-style: solid;
        background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
    }
    .upload-zone .upload-icon {
        width: 64px; height: 64px; border-radius: 20px; margin: 0 auto 12px;
        background: rgba(36,107,254,0.08); color: #246bfe;
        display: flex; align-items: center; justify-content: center; font-size: 28px;
        transition: all 0.3s;
    }
    .upload-zone:hover .upload-icon { background: rgba(36,107,254,0.15); transform: scale(1.05); }

    .form-question {
        background: #f8fafc; border-radius: 20px; padding: 20px; margin-bottom: 14px;
        border: 1px solid #e8ecf1; transition: all 0.2s;
    }
    .form-question:hover { border-color: #cbd5e1; }
    .form-question:focus-within { border-color: #246bfe; box-shadow: 0 0 0 3px rgba(36,107,254,0.08); }

    .grade-circle {
        width: 120px; height: 120px; border-radius: 50%; margin: 0 auto;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        position: relative;
    }
    .grade-circle::before {
        content: ''; position: absolute; inset: 0; border-radius: 50%;
        border: 4px solid #e2e8f0;
    }
    .grade-circle .grade-num { font-size: 36px; font-weight: 800; line-height: 1; }
    .grade-circle .grade-label { font-size: 10px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; margin-top: 4px; }

    .grade-excellent { background: linear-gradient(135deg, #f0fdf4, #dcfce7); color: #15803d; }
    .grade-excellent::before { border-color: #86efac; }
    .grade-good { background: linear-gradient(135deg, #eef4ff, #dbeafe); color: #1d4ed8; }
    .grade-good::before { border-color: #93c5fd; }
    .grade-average { background: linear-gradient(135deg, #fefce8, #fef9c3); color: #a16207; }
    .grade-average::before { border-color: #fde047; }
    .grade-low { background: linear-gradient(135deg, #fef2f2, #fee2e2); color: #dc2626; }
    .grade-low::before { border-color: #fca5a5; }

    .submission-student {
        background: #fff; border: 1px solid #e8ecf1; border-radius: 20px;
        padding: 18px; margin-bottom: 12px; transition: all 0.2s;
    }
    .submission-student:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }

    .grade-slider { -webkit-appearance: none; width: 100%; height: 8px; border-radius: 99px; background: #e2e8f0; outline: none; }
    .grade-slider::-webkit-slider-thumb { -webkit-appearance: none; width: 24px; height: 24px; border-radius: 50%; background: #246bfe; cursor: pointer; box-shadow: 0 2px 8px rgba(36,107,254,0.3); }

    .status-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 12px; border-radius: 99px; font-size: 10px; font-weight: 800;
        letter-spacing: 0.03em; text-transform: uppercase;
    }
    .status-submitted { background: #dbeafe; color: #1d4ed8; }
    .status-graded { background: #dcfce7; color: #15803d; }
    .status-revision { background: #fef3c7; color: #b45309; }
    .status-late { background: #fee2e2; color: #dc2626; }

    .feedback-bubble {
        background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
        border: 1px solid #bbf7d0; border-radius: 18px; padding: 16px;
        position: relative;
    }
    .feedback-bubble::before {
        content: ''; position: absolute; top: -8px; left: 20px;
        width: 16px; height: 16px; background: #f0fdf4;
        border-left: 1px solid #bbf7d0; border-bottom: 1px solid #bbf7d0;
        transform: rotate(45deg);
    }

    @keyframes gradeReveal {
        from { transform: scale(0.5); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .grade-animate { animation: gradeReveal 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both; }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .slide-up { animation: slideUp 0.4s ease both; }
    .slide-up-d1 { animation-delay: 0.1s; }
    .slide-up-d2 { animation-delay: 0.2s; }
    .slide-up-d3 { animation-delay: 0.3s; }
</style>

<div class="page-header">
    <a href="{{ route('tugas.index') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold flex-grow-1" style="font-size: 17px;">Detail Tugas</div>
    @if($isGuru)
        <a href="{{ route('tugas.edit', $tugas) }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
            <i class="bi bi-pencil-square" style="font-size:16px;"></i>
        </a>
    @endif
</div>

<div class="page-container px-3 pt-3">
    {{-- Hero Section --}}
    <div class="slide-up" style="background: linear-gradient(135deg, {{ $deadline['tone'] === 'danger' ? '#991b1b, #dc2626' : ($deadline['tone'] === 'warning' ? '#92400e, #d97706' : '#1e293b, #246bfe') }}); border-radius: 28px; padding: 24px 20px; margin-bottom: 18px; color: #fff; position: relative; overflow: hidden;">
        <div style="position:absolute;top:-20px;right:-20px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,0.06);"></div>
        <div style="position:absolute;bottom:-30px;right:40px;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.04);"></div>

        <div class="d-flex align-items-start gap-3">
            <div class="flex-grow-1">
                <div class="d-flex gap-2 mb-2 flex-wrap">
                    <span style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2);padding:4px 10px;border-radius:8px;font-size:10px;font-weight:700;">
                        <i class="bi {{ $tugas->isForm() ? 'bi-ui-checks-grid' : 'bi-cloud-arrow-up' }} me-1"></i>
                        {{ $tugas->isForm() ? 'FORMULIR ONLINE' : 'UPLOAD FILE' }}
                    </span>
                    <span style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2);padding:4px 10px;border-radius:8px;font-size:10px;font-weight:700;">
                        <i class="bi bi-people me-1"></i>{{ $tugas->kelas->nama }}
                    </span>
                </div>
                <h1 style="font-size: 22px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.3; margin-bottom: 8px;">{{ $tugas->judul }}</h1>
                <div class="d-flex align-items-center gap-2" style="font-size: 12px; opacity: 0.8;">
                    <div style="width:22px;height:22px;border-radius:8px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;">{{ strtoupper(substr($tugas->user->name, 0, 1)) }}</div>
                    {{ $tugas->user->name }}
                </div>
            </div>
            @php
                $daysLeft = $tugas->batas_pengumpulan ? (int) today()->startOfDay()->diffInDays($tugas->batas_pengumpulan->copy()->startOfDay(), false) : null;
                $ringColor = $daysLeft === null ? '#60a5fa' : ($daysLeft < 0 ? '#f87171' : ($daysLeft <= 3 ? '#fbbf24' : '#60a5fa'));
                $ringPct = $daysLeft === null ? 100 : max(0, min(100, ($daysLeft / 14) * 100));
            @endphp
            <div class="deadline-ring" style="flex-shrink:0;">
                <svg width="72" height="72" viewBox="0 0 72 72">
                    <circle cx="36" cy="36" r="30" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="5"/>
                    <circle cx="36" cy="36" r="30" fill="none" stroke="{{ $ringColor }}" stroke-width="5" stroke-linecap="round"
                        stroke-dasharray="{{ round($ringPct * 1.885) }} 188.5"/>
                </svg>
                <div class="ring-text text-white">
                    @if($daysLeft === null)
                        <div class="ring-num" style="font-size:14px;"><i class="bi bi-infinity"></i></div>
                        <div class="ring-label">Terbuka</div>
                    @elseif($daysLeft < 0)
                        <div class="ring-num">{{ abs($daysLeft) }}</div>
                        <div class="ring-label">Hari lewat</div>
                    @else
                        <div class="ring-num">{{ $daysLeft }}</div>
                        <div class="ring-label">Hari lagi</div>
                    @endif
                </div>
            </div>
        </div>

        @if($isGuru)
            <div class="d-flex gap-2 mt-3">
                <div class="stat-pill"><div class="sp-num">{{ $totalSiswa }}</div><div class="sp-lbl">Siswa</div></div>
                <div class="stat-pill"><div class="sp-num">{{ $totalSubmitted }}</div><div class="sp-lbl">Kumpul</div></div>
                <div class="stat-pill"><div class="sp-num">{{ $totalGraded }}</div><div class="sp-lbl">Dinilai</div></div>
                <div class="stat-pill"><div class="sp-num">{{ $totalPending + $totalRevisi }}</div><div class="sp-lbl">Pending</div></div>
            </div>
        @endif
    </div>

    {{-- Deskripsi & Lampiran --}}
    <div class="glass-card slide-up slide-up-d1">
        <div class="p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div style="width:28px;height:28px;border-radius:10px;background:#eef4ff;color:#246bfe;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-info-circle" style="font-size:14px;"></i>
                </div>
                <span class="fw-bold" style="font-size:14px;">Instruksi Tugas</span>
            </div>
            <p class="text-secondary mb-0" style="font-size:13px; line-height:1.7; white-space:pre-line;">{{ $tugas->deskripsi ?: 'Tidak ada deskripsi tambahan. Kerjakan sesuai judul tugas.' }}</p>

            @if($tugas->lampiran)
                @php
                    $ext = strtolower(pathinfo($tugas->lampiran_nama ?? '', PATHINFO_EXTENSION));
                    $fileIcons = ['pdf' => ['bi-file-earmark-pdf-fill', '#dc2626'], 'doc' => ['bi-file-earmark-word-fill', '#2563eb'], 'docx' => ['bi-file-earmark-word-fill', '#2563eb'], 'xlsx' => ['bi-file-earmark-excel-fill', '#16a34a'], 'xls' => ['bi-file-earmark-excel-fill', '#16a34a'], 'ppt' => ['bi-file-earmark-ppt-fill', '#ea580c'], 'pptx' => ['bi-file-earmark-ppt-fill', '#ea580c'], 'zip' => ['bi-file-earmark-zip-fill', '#7c3aed'], 'jpg' => ['bi-file-earmark-image-fill', '#0891b2'], 'jpeg' => ['bi-file-earmark-image-fill', '#0891b2'], 'png' => ['bi-file-earmark-image-fill', '#0891b2']];
                    $fi = $fileIcons[$ext] ?? ['bi-file-earmark-fill', '#64748b'];
                @endphp
                <a href="{{ asset('storage/' . $tugas->lampiran) }}" target="_blank" class="file-attach-card mt-3 text-decoration-none" style="display:flex;">
                    <div style="width:44px;height:44px;border-radius:14px;background:{{ $fi[1] }}15;color:{{ $fi[1] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi {{ $fi[0] }}" style="font-size:22px;"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-bold text-dark text-truncate" style="font-size:13px;">{{ $tugas->lampiran_nama ?: 'Lampiran' }}</div>
                        <div class="x-small text-muted">{{ strtoupper($ext) }} - Tap untuk membuka</div>
                    </div>
                    <i class="bi bi-download text-muted"></i>
                </a>
            @endif
        </div>
    </div>

    @if($isGuru)
        {{-- ===================== GURU VIEW ===================== --}}

        {{-- Action Bar --}}
        <div class="d-flex gap-2 mb-3 slide-up slide-up-d2">
            <a href="{{ route('tugas.export', $tugas) }}" class="btn btn-outline-success rounded-pill px-3 flex-grow-1" style="font-size:12px;font-weight:700;">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Nilai
            </a>
            <button type="button" class="btn btn-outline-primary rounded-pill px-3" style="font-size:12px;font-weight:700;" onclick="document.getElementById('filterBar').classList.toggle('d-none')">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
        </div>

        {{-- Filter Bar --}}
        <div id="filterBar" class="d-none mb-3">
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-sm rounded-pill fw-bold filter-btn active" data-filter="all" style="font-size:11px;">Semua</button>
                <button class="btn btn-sm btn-outline-primary rounded-pill fw-bold filter-btn" data-filter="pending" style="font-size:11px;">Belum Dinilai ({{ $totalPending }})</button>
                <button class="btn btn-sm btn-outline-success rounded-pill fw-bold filter-btn" data-filter="graded" style="font-size:11px;">Dinilai ({{ $totalGraded }})</button>
                <button class="btn btn-sm btn-outline-warning rounded-pill fw-bold filter-btn" data-filter="revision" style="font-size:11px;">Revisi ({{ $totalRevisi }})</button>
            </div>
        </div>

        {{-- Submissions List --}}
        <div class="slide-up slide-up-d3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="fw-bold mb-0" style="font-size:16px;">Pengumpulan Siswa</h2>
                <span class="x-small text-muted">{{ $totalSubmitted }}/{{ $totalSiswa }} siswa</span>
            </div>

            {{-- Progress bar --}}
            <div style="height:8px;border-radius:99px;background:#eef2f7;overflow:hidden;margin-bottom:18px;display:flex;">
                @if($totalSiswa > 0)
                    @php
                        $pctGraded = round(($totalGraded / $totalSiswa) * 100);
                        $pctPending = round(($totalPending / $totalSiswa) * 100);
                        $pctRevisi = round(($totalRevisi / $totalSiswa) * 100);
                    @endphp
                    <div style="width:{{ $pctGraded }}%;background:#16a34a;height:100%;"></div>
                    <div style="width:{{ $pctPending }}%;background:#f59e0b;height:100%;"></div>
                    <div style="width:{{ $pctRevisi }}%;background:#d94b61;height:100%;"></div>
                @endif
            </div>

            @forelse($tugas->pengumpulan as $item)
                @php
                    $filterKey = $item->revisi_aktif ? 'revision' : ($item->nilai !== null ? 'graded' : 'pending');
                @endphp
                <div class="submission-student" data-submission data-status="{{ $filterKey }}">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:40px;height:40px;border-radius:14px;background:linear-gradient(135deg,#eef4ff,#dbeafe);display:flex;align-items:center;justify-content:center;font-weight:800;color:#246bfe;font-size:14px;flex-shrink:0;">
                            {{ strtoupper(substr($item->siswa->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-bold text-dark" style="font-size:14px;">{{ $item->siswa->name ?? 'Siswa' }}</div>
                            <div class="x-small text-muted">
                                <i class="bi bi-clock me-1"></i>{{ $item->dikumpulkan_pada?->diffForHumans() ?? 'Baru saja' }}
                            </div>
                        </div>
                        @if($item->revisi_aktif)
                            <span class="status-badge status-revision"><i class="bi bi-arrow-repeat"></i> Revisi</span>
                        @elseif($item->nilai !== null)
                            <span class="status-badge status-graded"><i class="bi bi-check-circle"></i> {{ $item->nilai }}</span>
                        @else
                            <span class="status-badge status-submitted"><i class="bi bi-hourglass-split"></i> Pending</span>
                        @endif
                    </div>

                    {{-- Student Answer --}}
                    @if($item->jawaban_file)
                        @php
                            $ext = strtolower(pathinfo($item->jawaban_nama ?? '', PATHINFO_EXTENSION));
                            $fileIcons2 = ['pdf' => ['bi-file-earmark-pdf-fill', '#dc2626'], 'doc' => ['bi-file-earmark-word-fill', '#2563eb'], 'docx' => ['bi-file-earmark-word-fill', '#2563eb'], 'xlsx' => ['bi-file-earmark-excel-fill', '#16a34a'], 'xls' => ['bi-file-earmark-excel-fill', '#16a34a'], 'ppt' => ['bi-file-earmark-ppt-fill', '#ea580c'], 'pptx' => ['bi-file-earmark-ppt-fill', '#ea580c'], 'zip' => ['bi-file-earmark-zip-fill', '#7c3aed'], 'jpg' => ['bi-file-earmark-image-fill', '#0891b2'], 'jpeg' => ['bi-file-earmark-image-fill', '#0891b2'], 'png' => ['bi-file-earmark-image-fill', '#0891b2']];
                            $fi2 = $fileIcons2[$ext] ?? ['bi-file-earmark-fill', '#64748b'];
                        @endphp
                        <a href="{{ asset('storage/' . $item->jawaban_file) }}" target="_blank" class="file-attach-card mb-3 text-decoration-none" style="display:flex;">
                            <div style="width:36px;height:36px;border-radius:12px;background:{{ $fi2[1] }}15;color:{{ $fi2[1] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi {{ $fi2[0] }}" style="font-size:18px;"></i>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-bold text-dark text-truncate" style="font-size:12px;">{{ $item->jawaban_nama ?: 'File Jawaban' }}</div>
                                <div class="x-small text-muted">Tap untuk melihat</div>
                            </div>
                            <i class="bi bi-box-arrow-up-right text-muted" style="font-size:12px;"></i>
                        </a>
                    @endif

                    @if($item->jawaban_form)
                        @php
                            $answers = is_array($item->jawaban_form) ? $item->jawaban_form : (json_decode($item->jawaban_form ?: '[]', true) ?: []);
                            $formData = is_array($tugas->form_data) ? $tugas->form_data : (json_decode($tugas->form_data ?: '[]', true) ?: []);
                        @endphp
                        <div class="p-3 rounded-4 mb-3" style="background:#f8fafc;border:1px solid #e8ecf1;">
                            <div class="x-small fw-bold text-muted mb-2"><i class="bi bi-ui-checks me-1"></i>JAWABAN FORMULIR</div>
                            @foreach($formData as $qi => $q)
                                @php $ans = $answers[$qi] ?? null; @endphp
                                <div class="mb-2">
                                    <div class="x-small fw-bold text-dark">{{ $qi + 1 }}. {{ $q['text'] ?? '' }}</div>
                                    <div class="x-small text-secondary" style="white-space:pre-line;">{{ is_array($ans) ? (implode(', ', array_filter($ans)) ?: '--') : ($ans ?: '--') }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($item->catatan && !$item->jawaban_form)
                        <div class="x-small text-secondary mb-3 fst-italic p-2 rounded-3" style="background:#f8fafc;">"{{ $item->catatan }}"</div>
                    @endif

                    {{-- Review Form --}}
                    <form method="POST" action="{{ route('tugas.review', $item) }}" class="pt-3" style="border-top:1px solid #e8ecf1;">
                        @csrf
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="x-small fw-bold text-muted">NILAI (0-100)</label>
                                <span class="fw-bold" style="font-size:18px;color:#246bfe;" id="gradeDisplay_{{ $item->id }}">{{ $item->nilai ?? '0' }}</span>
                            </div>
                            <input type="range" name="nilai" class="grade-slider" min="0" max="100" step="1" value="{{ $item->nilai ?? 0 }}" oninput="document.getElementById('gradeDisplay_{{ $item->id }}').textContent=this.value">
                        </div>
                        <div class="mb-3">
                            <label class="x-small fw-bold text-muted mb-1">FEEDBACK / CATATAN</label>
                            <textarea name="feedback_guru" rows="2" class="form-control border-0 shadow-sm" style="border-radius:14px;background:#f8fafc;font-size:13px;" placeholder="Tulis feedback untuk siswa...">{{ $item->feedback_guru }}</textarea>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="x-small fw-bold text-dark">Minta Revisi?</div>
                                <div class="x-small text-muted">Siswa bisa kirim ulang jawaban</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="revisi_aktif" value="1" @checked($item->revisi_aktif)>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <button class="btn btn-primary w-100 py-2 rounded-pill fw-bold" style="font-size:14px;">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Penilaian
                        </button>
                    </form>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size:40px;color:#cbd5e1;"></i>
                    <div class="fw-bold mt-2 text-muted">Belum ada pengumpulan</div>
                    <div class="x-small text-muted">Siswa belum mengirimkan jawaban.</div>
                </div>
            @endforelse

            {{-- Siswa yang belum mengumpulkan --}}
            @php
                $submittedIds = $tugas->pengumpulan->pluck('siswa_id');
                $belumKumpul = $siswaKelas->whereNotIn('id', $submittedIds);
            @endphp
            @if($belumKumpul->count() > 0)
                <div class="mt-4 mb-3">
                    <div class="fw-bold text-muted" style="font-size:13px;">
                        <i class="bi bi-exclamation-circle me-1"></i> Belum Mengumpulkan ({{ $belumKumpul->count() }})
                    </div>
                </div>
                @foreach($belumKumpul as $siswa)
                    <div class="d-flex align-items-center gap-3 py-2 mb-1" style="border-bottom:1px solid #f1f5f9;">
                        <div style="width:32px;height:32px;border-radius:10px;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-weight:800;color:#dc2626;font-size:12px;">
                            {{ strtoupper(substr($siswa->name, 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark" style="font-size:13px;">{{ $siswa->name }}</div>
                        </div>
                        <span class="x-small text-muted">Belum kirim</span>
                    </div>
                @endforeach
            @endif
        </div>

    @else
        {{-- ===================== SISWA VIEW ===================== --}}

        {{-- Status Submission --}}
        @if($submission && !$submission->revisi_aktif && $submission->nilai !== null)
            {{-- GRADED --}}
            @php
                $gradeClass = $submission->nilai >= 85 ? 'grade-excellent' : ($submission->nilai >= 70 ? 'grade-good' : ($submission->nilai >= 55 ? 'grade-average' : 'grade-low'));
                $gradeEmoji = $submission->nilai >= 85 ? 'Luar Biasa!' : ($submission->nilai >= 70 ? 'Bagus!' : ($submission->nilai >= 55 ? 'Cukup' : 'Perlu Belajar Lagi'));
            @endphp
            <div class="glass-card slide-up slide-up-d2">
                <div class="p-4 text-center">
                    <div class="x-small fw-bold text-muted mb-3" style="letter-spacing:0.08em;">NILAI TUGAS KAMU</div>
                    <div class="grade-circle {{ $gradeClass }} grade-animate mb-3">
                        <div class="grade-num">{{ $submission->nilai }}</div>
                        <div class="grade-label">dari 100</div>
                    </div>
                    <div class="fw-bold mb-1" style="font-size:18px;">{{ $gradeEmoji }}</div>
                    <div class="x-small text-muted">Dinilai {{ $submission->dinilai_pada?->format('d M Y, H:i') ?? 'baru saja' }}</div>

                    @if($submission->feedback_guru)
                        <div class="feedback-bubble mt-4 text-start">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div style="width:24px;height:24px;border-radius:8px;background:#16a34a;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;">
                                    {{ strtoupper(substr($tugas->user->name, 0, 1)) }}
                                </div>
                                <span class="fw-bold" style="font-size:12px;">{{ $tugas->user->name }}</span>
                                <span class="x-small text-muted">guru</span>
                            </div>
                            <p class="small text-secondary mb-0" style="line-height:1.6;white-space:pre-line;">{{ $submission->feedback_guru }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($submission && $submission->revisi_aktif)
            {{-- REVISION REQUESTED --}}
            <div class="glass-card slide-up slide-up-d2" style="border:2px solid #fde68a;">
                <div class="p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:44px;height:44px;border-radius:14px;background:#fef3c7;color:#b45309;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-arrow-repeat" style="font-size:20px;"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:15px;">Perlu Revisi</div>
                            <div class="x-small text-muted">Guru meminta kamu memperbaiki jawaban</div>
                        </div>
                    </div>
                    @if($submission->feedback_guru)
                        <div class="p-3 rounded-4 mb-3" style="background:#fffbeb;border:1px solid #fde68a;">
                            <div class="x-small fw-bold mb-1" style="color:#92400e;">Catatan Guru:</div>
                            <div class="small text-dark" style="line-height:1.6;white-space:pre-line;">{{ $submission->feedback_guru }}</div>
                        </div>
                    @endif
                    @if($submission->jawaban_file)
                        <a href="{{ asset('storage/' . $submission->jawaban_file) }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mb-2">
                            <i class="bi bi-eye me-1"></i> Lihat jawaban terakhir
                        </a>
                    @endif
                </div>
            </div>
        @elseif($submission && $submission->nilai === null && !$submission->revisi_aktif)
            {{-- PENDING REVIEW --}}
            <div class="glass-card slide-up slide-up-d2">
                <div class="p-4 text-center">
                    <div style="width:64px;height:64px;border-radius:20px;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                        <i class="bi bi-hourglass-split" style="font-size:28px;"></i>
                    </div>
                    <div class="fw-bold mb-1" style="font-size:16px;">Menunggu Penilaian</div>
                    <div class="x-small text-muted mb-3">Jawaban kamu sudah diterima guru pada</div>
                    <div class="fw-bold" style="font-size:13px;color:#246bfe;">{{ $submission->dikumpulkan_pada?->format('d M Y, H:i') ?? 'Baru saja' }}</div>
                    <div class="mt-3 p-3 rounded-4" style="background:#f8fafc;">
                        <div class="x-small text-muted"><i class="bi bi-info-circle me-1"></i>Kamu akan diberitahu saat guru selesai menilai.</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Submission / Answer Form --}}
        @if($canSubmit)
            @if($tugas->tipe === 'form')
                {{-- FORM TYPE --}}
                @php
                    $formData = is_array($tugas->form_data) ? $tugas->form_data : (json_decode($tugas->form_data ?: '[]', true) ?: []);
                @endphp
                <div class="glass-card slide-up slide-up-d2">
                    <div class="p-4">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div style="width:32px;height:32px;border-radius:10px;background:#ede9fe;color:#7c3aed;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-ui-checks-grid" style="font-size:16px;"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:15px;">Formulir Pengerjaan</div>
                                <div class="x-small text-muted">Jawab semua pertanyaan di bawah</div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('tugas.submit', $tugas) }}">
                            @csrf
                            @foreach($formData as $index => $q)
                                @php $isRequired = $q['required'] ?? true; @endphp
                                <div class="form-question">
                                    <div class="d-flex align-items-start gap-2 mb-3">
                                        <span style="min-width:26px;height:26px;border-radius:8px;background:#246bfe;color:#fff;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;">{{ $index + 1 }}</span>
                                        <div class="flex-grow-1">
                                            <label class="fw-bold text-dark d-block" style="font-size:14px;line-height:1.4;">
                                                {{ $q['text'] }}
                                                @if($isRequired) <span class="text-danger">*</span> @endif
                                            </label>
                                            <div class="x-small text-muted mt-1">
                                                @if($q['type'] === 'text') Jawaban singkat
                                                @elseif($q['type'] === 'essay') Jawaban panjang / paragraf
                                                @elseif($q['type'] === 'multiple') Pilih satu jawaban
                                                @elseif($q['type'] === 'checkbox') Pilih semua yang sesuai
                                                @elseif($q['type'] === 'dropdown') Pilih dari daftar
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if($q['type'] === 'text')
                                        <input type="text" name="jawaban[{{ $index }}]" class="form-control border-0 shadow-sm" style="border-radius:14px;background:#fff;font-size:14px;" placeholder="Ketik jawaban..." @if($isRequired) required @endif>
                                    @elseif($q['type'] === 'essay')
                                        <textarea name="jawaban[{{ $index }}]" rows="4" class="form-control border-0 shadow-sm" style="border-radius:14px;background:#fff;font-size:14px;line-height:1.6;" placeholder="Tulis jawaban kamu di sini..." @if($isRequired) required @endif></textarea>
                                    @elseif($q['type'] === 'multiple')
                                        @foreach($q['options'] ?? [] as $oi => $opt)
                                            <div class="d-flex align-items-center gap-2 p-3 mb-2 rounded-3" style="background:#fff;border:1px solid #e8ecf1;cursor:pointer;transition:all 0.2s;" onclick="this.querySelector('input').checked=true;this.parentElement.querySelectorAll('.opt-row').forEach(r=>r.style.borderColor='#e8ecf1');this.style.borderColor='#246bfe';">
                                                <input class="form-check-input" type="radio" name="jawaban[{{ $index }}]" value="{{ $opt }}" @if($isRequired) required @endif style="flex-shrink:0;">
                                                <span style="font-size:13px;">{{ $opt }}</span>
                                            </div>
                                        @endforeach
                                    @elseif($q['type'] === 'checkbox')
                                        @foreach($q['options'] ?? [] as $oi => $opt)
                                            <div class="d-flex align-items-center gap-2 p-3 mb-2 rounded-3 opt-row" style="background:#fff;border:1px solid #e8ecf1;cursor:pointer;transition:all 0.2s;" onclick="const cb=this.querySelector('input');cb.checked=!cb.checked;this.style.borderColor=cb.checked?'#246bfe':'#e8ecf1';">
                                                <input class="form-check-input" type="checkbox" name="jawaban[{{ $index }}][]" value="{{ $opt }}" @if($isRequired) required @endif style="flex-shrink:0;">
                                                <span style="font-size:13px;">{{ $opt }}</span>
                                            </div>
                                        @endforeach
                                    @elseif($q['type'] === 'dropdown')
                                        <select name="jawaban[{{ $index }}]" class="form-select border-0 shadow-sm" style="border-radius:14px;background:#fff;font-size:14px;" @if($isRequired) required @endif>
                                            <option value="">-- Pilih jawaban --</option>
                                            @foreach($q['options'] ?? [] as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            @endforeach
                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold mt-2" style="font-size:15px;box-shadow:0 8px 24px rgba(36,107,254,0.25);">
                                <i class="bi bi-send-fill me-1"></i> Kirim Jawaban
                            </button>
                        </form>
                    </div>
                </div>
            @else
                {{-- FILE UPLOAD TYPE --}}
                <div class="glass-card slide-up slide-up-d2">
                    <div class="p-4">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div style="width:32px;height:32px;border-radius:10px;background:#eef4ff;color:#246bfe;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-cloud-arrow-up-fill" style="font-size:16px;"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:15px;">Kirim Jawaban</div>
                                <div class="x-small text-muted">Upload file tugas kamu</div>
                            </div>
                        </div>

                        @if($submission && $submission->jawaban_file)
                            <div class="d-flex align-items-center gap-2 p-3 mb-3 rounded-4" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <div class="flex-grow-1">
                                    <div class="small fw-bold text-dark">Jawaban terkirim</div>
                                    <div class="x-small text-muted">{{ $submission->dikumpulkan_pada?->format('d M Y, H:i') }}</div>
                                </div>
                                <a href="{{ asset('storage/' . $submission->jawaban_file) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">Lihat</a>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('tugas.submit', $tugas) }}" enctype="multipart/form-data" id="submitForm">
                            @csrf
                            <div class="mb-3">
                                <label class="x-small fw-bold text-muted mb-1">CATATAN PENGERJAAN</label>
                                <textarea name="catatan" rows="3" class="form-control border-0 shadow-sm" style="border-radius:14px;background:#f8fafc;font-size:13px;line-height:1.6;" placeholder="Tulis catatan singkat tentang pengerjaan kamu..." required>{{ $submission?->catatan }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="x-small fw-bold text-muted mb-2">FILE JAWABAN</label>
                                <div class="upload-zone" id="answerZone" onclick="document.getElementById('answerFile').click()">
                                    <input type="file" name="jawaban_file" id="answerFile" class="d-none" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.xls,.ppt,.pptx,.csv,.txt,.zip" {{ $submission && $submission->jawaban_file ? '' : 'required' }} onchange="handleAnswerFile(this)">
                                    <div id="answerPreview">
                                        <div class="upload-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                                        <div class="fw-bold" style="font-size:14px;color:#1e293b;">Tap untuk pilih file</div>
                                        <div class="x-small text-muted mt-1">PDF, Word, Excel, PPT, Gambar, ZIP (Maks 10MB)</div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold" style="font-size:15px;box-shadow:0 8px 24px rgba(36,107,254,0.25);">
                                <i class="bi bi-send-fill me-1"></i> {{ $submission ? 'Perbarui Jawaban' : 'Kirim Sekarang' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @elseif(!$submission && !$canSubmit && $tugas->isExpired())
            {{-- EXPIRED, NO SUBMISSION --}}
            <div class="glass-card slide-up slide-up-d2">
                <div class="p-4 text-center">
                    <div style="width:64px;height:64px;border-radius:20px;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                        <i class="bi bi-x-circle" style="font-size:28px;"></i>
                    </div>
                    <div class="fw-bold mb-1" style="font-size:16px;">Batas Waktu Terlewat</div>
                    <div class="x-small text-muted">Kamu belum mengumpulkan tugas ini dan batas waktu sudah berakhir.</div>
                </div>
            </div>
        @elseif(!$submission && $canSubmit === false && !$tugas->isExpired())
            {{-- NO SUBMISSION BUT CAN'T SUBMIT (shouldn't happen normally) --}}
        @endif

        {{-- Previous submission info when can't submit and not graded --}}
        @if($submission && !$canSubmit && $submission->nilai === null && !$submission->revisi_aktif)
            <div class="glass-card slide-up slide-up-d3">
                <div class="p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div style="width:28px;height:28px;border-radius:10px;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-paperclip" style="font-size:14px;"></i>
                        </div>
                        <span class="fw-bold" style="font-size:14px;">Jawaban Kamu</span>
                    </div>
                    @if($submission->jawaban_file)
                        <a href="{{ asset('storage/' . $submission->jawaban_file) }}" target="_blank" class="file-attach-card text-decoration-none" style="display:flex;">
                            <div style="width:36px;height:36px;border-radius:12px;background:#eef4ff;color:#246bfe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-file-earmark-fill" style="font-size:18px;"></i>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-bold text-dark text-truncate" style="font-size:12px;">{{ $submission->jawaban_nama ?: 'File Jawaban' }}</div>
                                <div class="x-small text-muted">Tap untuk melihat</div>
                            </div>
                        </a>
                    @endif
                    @if($submission->catatan)
                        <div class="x-small text-secondary mt-2 fst-italic">"{{ $submission->catatan }}"</div>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>

{{-- Toggle switch CSS (reused from form) --}}
<style>
    .toggle-switch { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; inset: 0; background: #e2e8f0; border-radius: 99px; cursor: pointer; transition: 0.3s; }
    .toggle-slider::before { content: ''; position: absolute; width: 18px; height: 18px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
    .toggle-switch input:checked + .toggle-slider { background: #246bfe; }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }
    .filter-btn { border: 1px solid #e2e8f0; }
    .filter-btn.active { background: #14213d !important; color: #fff !important; border-color: #14213d !important; }
</style>

<script>
    function handleAnswerFile(input) {
        const zone = document.getElementById('answerZone');
        const preview = document.getElementById('answerPreview');
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const ext = file.name.split('.').pop().toLowerCase();
            const icons = { pdf: 'bi-file-earmark-pdf-fill', doc: 'bi-file-earmark-word-fill', docx: 'bi-file-earmark-word-fill', xlsx: 'bi-file-earmark-excel-fill', xls: 'bi-file-earmark-excel-fill', ppt: 'bi-file-earmark-ppt-fill', pptx: 'bi-file-earmark-ppt-fill', zip: 'bi-file-earmark-zip-fill', jpg: 'bi-file-earmark-image-fill', jpeg: 'bi-file-earmark-image-fill', png: 'bi-file-earmark-image-fill', csv: 'bi-file-earmark-spreadsheet-fill', txt: 'bi-file-earmark-text-fill' };
            const icon = icons[ext] || 'bi-file-earmark-fill';
            const size = (file.size / 1024 / 1024).toFixed(1);
            zone.classList.add('has-file');
            preview.innerHTML = `
                <i class="bi ${icon}" style="font-size:36px;color:#16a34a;"></i>
                <div class="fw-bold mt-2" style="font-size:14px;color:#15803d;">${file.name}</div>
                <div class="x-small text-muted mt-1">${size} MB - Tap untuk ganti file</div>
            `;
        }
    }

    // Guru filter
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;
            document.querySelectorAll('[data-submission]').forEach(el => {
                if (filter === 'all') { el.style.display = ''; }
                else { el.style.display = el.dataset.status === filter ? '' : 'none'; }
            });
        });
    });
</script>
@endsection
