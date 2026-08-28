@extends('layouts.mobile-app')

@section('content')
<div class="mobile-hero">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <div class="eyebrow">PERPUSTAKAAN DIGITAL</div>
            <h1 class="hero-title">Katalog Buku</h1>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle p-2" style="width: 40px; height: 40px; display: grid; place-items: center;">
            <i class="bi bi-x-lg"></i>
        </a>
    </div>

    <form action="{{ route('perpustakaan.index') }}" method="GET" class="mt-4">
        <div class="input-group bg-white rounded-pill px-3 py-1 shadow-sm">
            <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
            <input type="text" name="search" class="form-control bg-transparent border-0" placeholder="Cari judul atau penulis..." value="{{ request('search') }}">
        </div>
    </form>
</div>

<div class="mobile-content" style="margin-top: -20px;">
    <!-- Kategori Horizontal -->
    <div class="d-flex gap-2 overflow-x-auto pb-3 mb-4 no-scrollbar" style="white-space: nowrap; -ms-overflow-style: none; scrollbar-width: none;">
        <a href="{{ route('perpustakaan.index') }}" class="btn {{ !request('kategori') ? 'btn-primary' : 'btn-white shadow-sm' }} rounded-pill px-4 py-2 text-sm fw-bold">
            Semua
        </a>
        @foreach($kategoris as $kat)
            <a href="{{ route('perpustakaan.index', ['kategori' => $kat->slug]) }}" class="btn {{ request('kategori') == $kat->slug ? 'btn-primary' : 'btn-white shadow-sm' }} rounded-pill px-4 py-2 text-sm fw-bold">
                {{ $kat->nama }}
            </a>
        @endforeach
    </div>

    <div class="section-title mb-3">Buku Terbaru</div>

    <div class="row g-3">
        @forelse($bukus as $buku)
        <div class="col-6">
            <a href="{{ route('perpustakaan.show', $buku->slug) }}" class="text-decoration-none">
                <div class="mobile-card h-100 border-0 p-0 overflow-hidden shadow-sm" style="border-radius: 16px; background: #fff;">
                    <div style="aspect-ratio: 2/3; background: #f1f5f9; position: relative;">
                        @if($buku->cover)
                            <img src="{{ asset('storage/'.$buku->cover) }}" class="w-100 h-100 object-fit-cover">
                        @else
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 text-center">
                                <i class="bi bi-book text-muted opacity-20 mb-2" style="font-size: 40px;"></i>
                                <div class="small fw-bold text-muted">{{ $buku->judul }}</div>
                            </div>
                        @endif
                        <div class="position-absolute top-0 end-0 p-2">
                            <span class="badge bg-white text-dark shadow-sm rounded-pill" style="font-size: 9px; opacity: 0.9;">
                                {{ $buku->kategori->nama }}
                            </span>
                        </div>
                    </div>
                    <div class="p-3">
                        <div class="fw-bold text-dark text-truncate mb-1" style="font-size: 13px;">{{ $buku->judul }}</div>
                        <div class="text-muted text-truncate" style="font-size: 11px;">{{ $buku->penulis ?? 'Anonim' }}</div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <img src="https://illustrations.popsy.co/gray/crashed-error.svg" style="width: 150px; opacity: 0.5;">
            <div class="mt-3 text-muted small">Tidak ada buku ditemukan.</div>
        </div>
        @endforelse
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .btn-white { background: #fff; color: #64748b; }
    .btn-white:hover { color: #246bfe; }
    .object-fit-cover { object-fit: cover; }
</style>
@endsection
