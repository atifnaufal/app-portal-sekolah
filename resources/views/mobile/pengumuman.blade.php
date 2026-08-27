@extends('layouts.mobile-app')
@section('content')
<div class="p-3 pb-0">
    <a href="javascript:history.back()" class="text-decoration-none text-muted d-inline-flex align-items-center gap-2 small fw-bold">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
        Kembali
    </a>
</div>
<header class="mobile-hero"><div class="eyebrow">INFORMASI RESMI</div><div class="hero-title mt-2">Pengumuman sekolah</div><div class="class-pill mt-3">Agenda dan berita publik</div></header><main class="mobile-content"><p class="text-secondary small mb-4">Informasi resmi dari sekolah untuk seluruh warga sekolah.</p><div class="stagger">@forelse($pengumumans as $item)<article class="card mobile-card mb-3 overflow-hidden">@if($item->gambar)<img src="{{ asset('storage/'.$item->gambar) }}" alt="{{ $item->judul }}" style="width:100%;height:180px;object-fit:cover">@endif<div class="card-body"><div class="small text-primary fw-bold">{{ $item->tanggal_acara?->format('d M Y') ?? $item->created_at->format('d M Y') }}</div><h2 class="h5 fw-bold mt-2">{{ $item->judul }}</h2><p class="small text-secondary mb-0 mt-2" style="white-space:pre-line">{{ $item->isi }}</p></div></article>@empty<div class="card mobile-card"><div class="card-body text-secondary">Belum ada pengumuman sekolah.</div></div>@endforelse</div></main>@endsection
