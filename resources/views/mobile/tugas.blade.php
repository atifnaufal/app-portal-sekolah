@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: #fff; border-bottom: 1px solid #edf2f7;
        padding: 12px 15px; display: flex; align-items: center; gap: 15px;
    }
    .page-container { padding-top: 70px; padding-bottom: 30px; }
</style>

<div class="page-header">
    <a href="{{ route('dashboard') }}" class="text-dark">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
    </a>
    <div class="fw-bold" style="font-size: 16px;">Ruang Tugas</div>
</div>

<div class="page-container">
    <header class="mobile-hero" style="border-radius: 0 0 25px 25px;">
        <div class="eyebrow">AKADEMIK</div>
        <div class="hero-title mt-2">Daftar Tugas</div>
        <div class="class-pill mt-3">{{ $user->kelas?->nama ?? 'Umum' }}</div>
    </header>

    <main class="mobile-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">Tugas Aktif</h2>
            @if($user->role === 'guru')
                <a href="{{ route('tugas.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">+ Buat</a>
            @endif
        </div>

        <div class="stagger">
            @forelse($tugas as $item)
                @php($submission = $item->pengumpulan->where('user_id', $user->id)->first())
                <a href="{{ route('tugas.show', $item) }}" class="card mobile-card tap-card text-decoration-none text-dark mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill small">{{ $item->kelas->nama }}</span>
                            <div class="text-end">
                                <div class="x-small text-muted fw-bold">DEADLINE</div>
                                <div class="small fw-bold {{ $item->batas_pengumpulan && $item->batas_pengumpulan->isPast() ? 'text-danger' : '' }}">
                                    {{ $item->batas_pengumpulan?->format('d M') ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <h3 class="h6 fw-bold mb-2">{{ $item->judul }}</h3>
                        <p class="small text-secondary mb-3">{{ \Illuminate\Support\Str::limit($item->deskripsi ?: 'Buka untuk melihat detail tugas.', 100) }}</p>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            @if($submission)
                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">Selesai</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill">Belum Dikumpul</span>
                            @endif
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-5 opacity-50">
                    <i class="bi bi-journal-x h1"></i>
                    <div class="small mt-2">Belum ada tugas untuk kelas ini.</div>
                </div>
            @endforelse
        </div>
    </main>
</div>
@endsection
