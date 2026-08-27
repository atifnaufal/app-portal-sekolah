@extends('layouts.mobile-app')
@section('content')
<div class="p-3 pb-0">
    <a href="javascript:history.back()" class="text-decoration-none text-muted d-inline-flex align-items-center gap-2 small fw-bold">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
        Kembali
    </a>
</div>
<header class="mobile-hero"><div class="eyebrow">PEMBERITAHUAN GURU</div><div class="hero-title mt-2">Notifikasi tugas</div><div class="class-pill mt-3">Jawaban siswa terbaru</div></header><main class="mobile-content"><p class="text-secondary small mb-4">Pantau siswa yang telah mengirimkan tugas.</p><div class="stagger">@forelse($notifikasis as $item)<a href="{{ $item->url ?: route('tugas.index') }}" class="card mobile-card tap-card text-decoration-none text-dark mb-3"><div class="card-body"><div class="d-flex gap-3 align-items-start"><div class="icon-box" style="background:#e5f7ef;color:#198754">&#10003;</div><div><h2 class="h6 fw-bold mb-1">{{ $item->judul }}</h2><p class="small text-secondary mb-2">{{ $item->pesan }}</p><span class="small text-secondary">{{ $item->created_at->format('d M Y, H:i') }}</span></div></div></div></a>@empty<div class="card mobile-card"><div class="card-body text-secondary">Belum ada jawaban baru.</div></div>@endforelse</div></main>@endsection
