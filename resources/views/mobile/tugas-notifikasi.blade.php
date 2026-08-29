@extends('layouts.mobile-app')
@section('content')
<div class="pui-topbar">
    <a href="javascript:history.back()" class="back"><i class="bi bi-chevron-left"></i> Kembali</a>
</div>
<header class="mobile-hero">
    <div class="eyebrow">PEMBERITAHUAN GURU</div>
    <div class="hero-title">Notifikasi tugas</div>
    <div class="pui-chip pui-chip-sky mt-3">Jawaban siswa terbaru</div>
</header>
<main class="mobile-content px-3 pt-0">
    <p class="text-secondary small mb-4">Pantau siswa yang telah mengirimkan tugas.</p>
    <div class="stagger">
        @forelse($notifikasis as $item)
            <a href="{{ $item->url ?: route('tugas.index') }}" class="card mobile-card tap-card text-decoration-none text-dark mb-3">
                <div class="card-body">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="pui-avatar" style="width:40px;height:40px;background:#e5f7ef;color:#198754;font-size:18px;">&#10003;</div>
                        <div>
                            <h2 class="h6 fw-bold mb-1" style="color:var(--ink);">{{ $item->judul }}</h2>
                            <p class="small text-secondary mb-2">{{ $item->pesan }}</p>
                            <span class="small text-secondary">{{ $item->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="pui-empty">
                <i class="bi bi-bell ico"></i>
                <h4>Belum ada jawaban baru</h4>
                <p>Tidak ada notifikasi untuk saat ini.</p>
            </div>
        @endforelse
    </div>
</main>
@endsection
