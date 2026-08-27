@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.8); backdrop-filter: blur(15px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.5);
        padding: 12px 20px; display: flex; align-items: center; gap: 15px;
    }
    .page-container { padding-top: 70px; padding-bottom: 40px; }

    .ai-card {
        background: #fff; border: none; border-radius: 28px;
        transition: transform 0.3s, box-shadow 0.3s;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        position: relative; overflow: hidden;
    }
    .ai-card::before {
        content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%;
        background: var(--blue); opacity: 0.8;
    }
    .ai-card.urgent::before { background: var(--danger); }
    .ai-card.completed::before { background: #10b981; }

    .status-glow {
        width: 10px; height: 10px; border-radius: 50%;
        display: inline-block; margin-right: 6px;
        box-shadow: 0 0 10px currentColor;
    }

    .glass-pill {
        background: rgba(36, 107, 254, 0.05); color: var(--blue);
        border: 1px solid rgba(36, 107, 254, 0.1);
        padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 700;
    }
</style>

<div class="page-header">
    <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px; letter-spacing: -0.5px;">Ruang Belajar AI</div>
</div>

<div class="page-container">
    <header class="mobile-hero" style="border-radius: 0 0 45px 45px; margin-bottom: 25px; background: linear-gradient(135deg, #1e293b, #334155);">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="eyebrow" style="color: #94a3b8;">{{ $user->kelas?->nama ?? 'Akademik' }}</div>
                <div class="hero-title mt-2 text-white" style="font-size: 28px;">Pusat Tugas</div>
            </div>
            @if($user->role === 'guru')
                <a href="{{ route('tugas.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm" style="font-weight: 700;">+ Buat</a>
            @endif
        </div>
    </header>

    <main class="mobile-content px-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0" style="font-size: 19px; color: #1e293b;">Tugas Aktif</h2>
            <div class="badge bg-light text-secondary rounded-pill px-3 py-2 fw-normal">{{ $tugas->count() }} Tugas</div>
        </div>

        <div class="stagger">
            @forelse($tugas as $item)
                @php
                    $isExpired = $item->batas_pengumpulan && $item->batas_pengumpulan->isPast();
                    $isForm = $item->tipe === 'form';
                @endphp
                <a href="{{ route('tugas.show', $item) }}" class="card ai-card mb-3 text-decoration-none text-dark {{ $isExpired ? 'urgent' : '' }}">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="glass-pill">
                                <i class="bi {{ $isForm ? 'bi-ui-checks' : 'bi-file-earmark-text' }} me-1"></i>
                                {{ $isForm ? 'FORMULIR ONLINE' : 'PENGIRIMAN FILE' }}
                            </div>
                            <div class="text-end">
                                <div class="small fw-bold {{ $isExpired ? 'text-danger' : 'text-muted' }}" style="font-size: 10px;">DEADLINE</div>
                                <div class="fw-bold" style="font-size: 13px;">{{ $item->batas_pengumpulan?->format('d M') ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <h3 class="h6 fw-bold mb-2" style="font-size: 16px; line-height: 1.4;">{{ $item->judul }}</h3>
                        <p class="small text-secondary mb-4 opacity-75" style="line-height: 1.6;">{{ \Illuminate\Support\Str::limit($item->deskripsi ?: 'Buka modul untuk panduan lengkap.', 85) }}</p>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center text-{{ $isExpired ? 'danger' : 'primary' }}" style="font-size: 12px; font-weight: 700;">
                                <span class="status-glow" style="color: {{ $isExpired ? '#ef4444' : '#246bfe' }}"></span>
                                {{ $isExpired ? 'Waktu Habis' : 'Sedang Berjalan' }}
                            </div>
                            <i class="bi bi-arrow-right-short h4 mb-0 text-muted"></i>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-5 border rounded-4 bg-white opacity-50">
                    <i class="bi bi-stars h1 text-primary"></i>
                    <div class="fw-bold mt-2">Semua tugas selesai!</div>
                    <div class="small mt-1">Belum ada tugas baru untukmu.</div>
                </div>
            @endforelse
        </div>

        @if($user->role === 'siswa' && $completedTugas->isNotEmpty())
            <h2 class="section-title mt-5 mb-4 px-1" style="font-size: 19px;">Arsip & Nilai</h2>
            @foreach($completedTugas as $item)
                @php($submission = $item->pengumpulan->first())
                <div class="card ai-card completed mb-2 shadow-none border border-light">
                    <div class="card-body p-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-check2-circle h5 mb-0"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 14px;">{{ $item->judul }}</div>
                                <div class="small text-muted" style="font-size: 11px;">Dinilai pada {{ $submission->dinilai_pada?->format('d M Y') ?? 'Baru saja' }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="h5 fw-bold text-success mb-0">{{ $submission->nilai }}</div>
                            <div class="fw-bold text-muted" style="font-size: 9px; letter-spacing: 0.5px;">SKOR</div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </main>
</div>
@endsection
