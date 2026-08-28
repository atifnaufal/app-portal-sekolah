@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<div style="background: #f8fafc; min-height: 100vh; padding-bottom: 100px;">
    <!-- Top Nav -->
    <div class="d-flex align-items-center gap-3 p-3 position-fixed top-0 start-0 end-0 bg-white shadow-sm z-3">
        <a href="{{ route('perpustakaan.index') }}" class="text-dark"><i class="bi bi-chevron-left fs-4"></i></a>
        <div class="fw-bold text-truncate">Detail Buku</div>
    </div>

    <div style="padding-top: 70px;">
        <!-- Cover Section -->
        <div class="text-center py-4" style="background: linear-gradient(to bottom, #fff, #f8fafc);">
            <div class="mx-auto shadow-lg" style="width: 180px; aspect-ratio: 2/3; border-radius: 12px; overflow: hidden; background: #e2e8f0;">
                @if($buku->cover)
                    <img src="{{ asset('storage/'.$buku->cover) }}" class="w-100 h-100 object-fit-cover">
                @else
                    <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                        <i class="bi bi-book fs-1 text-muted opacity-25"></i>
                    </div>
                @endif
            </div>

            <div class="mt-4 px-4">
                <h2 class="fw-bold fs-4 mb-1 text-dark">{{ $buku->judul }}</h2>
                <div class="text-primary fw-semibold">{{ $buku->penulis ?? 'Penulis Tidak Diketahui' }}</div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="px-4 py-3">
            <div class="d-flex gap-3 text-center mb-4">
                <div class="flex-fill p-3 rounded-4 bg-white shadow-sm border">
                    <div class="small text-muted mb-1">Tahun</div>
                    <div class="fw-bold">{{ $buku->tahun_terbit ?? '-' }}</div>
                </div>
                <div class="flex-fill p-3 rounded-4 bg-white shadow-sm border">
                    <div class="small text-muted mb-1">Penerbit</div>
                    <div class="fw-bold text-truncate" style="max-width: 80px;">{{ $buku->penerbit ?? '-' }}</div>
                </div>
                <div class="flex-fill p-3 rounded-4 bg-white shadow-sm border">
                    <div class="small text-muted mb-1">Kategori</div>
                    <div class="fw-bold">{{ $buku->kategori->nama }}</div>
                </div>
            </div>

            <div class="section-title mb-2">Deskripsi</div>
            <p class="text-muted small leading-relaxed mb-4">
                {{ $buku->deskripsi ?: 'Tidak ada deskripsi untuk buku ini.' }}
            </p>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="position-fixed bottom-0 start-0 end-0 p-3 bg-white border-top shadow-lg z-3">
        <div class="mobile-shell mx-auto">
            <a href="{{ route('perpustakaan.read', $buku->slug) }}" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-lg">
                <i class="bi bi-book-half me-2"></i> Baca Sekarang
            </a>
        </div>
    </div>
</div>
@endsection
