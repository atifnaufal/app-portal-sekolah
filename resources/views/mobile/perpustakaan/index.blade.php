@extends('layouts.mobile-app')

@section('content')
<style>
    .perpus-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        padding: 40px 24px 60px;
        border-radius: 0 0 40px 40px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .perpus-hero::after {
        content: ''; position: absolute; top: -50px; right: -30px;
        width: 150px; height: 150px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36, 107, 254, 0.2) 0%, transparent 70%);
    }
    .search-bar {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 24px;
    }
    .search-input {
        background: transparent;
        border: none;
        color: #fff;
        width: 100%;
        outline: none;
        font-size: 14px;
    }
    .search-input::placeholder { color: rgba(255, 255, 255, 0.5); }

    .category-chip {
        padding: 8px 20px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
        border: 1px solid transparent;
    }
    .chip-active {
        background: var(--blue);
        color: #fff;
        box-shadow: 0 8px 16px rgba(36, 107, 254, 0.3);
    }
    .chip-inactive {
        background: #fff;
        color: #64748b;
        border-color: #f1f5f9;
    }

    .book-card {
        background: #fff;
        border-radius: 24px;
        padding: 12px;
        transition: all 0.3s;
        height: 100%;
        display: flex;
        flex-direction: column;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .book-card:active { transform: scale(0.96); }
    .book-cover-wrapper {
        aspect-ratio: 2/3;
        border-radius: 18px;
        overflow: hidden;
        background: #f8fafc;
        position: relative;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }
    .book-info { margin-top: 12px; padding: 0 4px; }
    .book-title { font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 2px; }
    .book-author { font-size: 11px; color: #94a3b8; font-weight: 600; }

    .floating-badge {
        position: absolute; top: 8px; right: 8px;
        background: rgba(255,255,255,0.9);
        backdrop-filter: blur(4px);
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 9px;
        font-weight: 800;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
</style>

<div class="perpus-hero">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <div class="eyebrow" style="opacity: 0.6;">DIGITAL LIBRARY</div>
            <h1 class="hero-title">Koleksi Buku</h1>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-white rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff;">
            <i class="bi bi-x-lg"></i>
        </a>
    </div>

    <form action="{{ route('perpustakaan.index') }}" method="GET">
        <div class="search-bar">
            <i class="bi bi-search text-white opacity-50"></i>
            <input type="text" name="search" class="search-input" placeholder="Cari pengetahuan hari ini..." value="{{ request('search') }}">
        </div>
    </form>
</div>

<div class="mobile-content" style="margin-top: -30px; padding-bottom: 40px;">
    <!-- Horizontal Categories -->
    <div class="d-flex gap-2 overflow-x-auto pb-4 no-scrollbar" style="margin: 0 -20px; padding: 0 20px;">
        <a href="{{ route('perpustakaan.index') }}" class="category-chip {{ !request('kategori') ? 'chip-active' : 'chip-inactive shadow-sm' }}">
            Semua
        </a>
        @foreach($kategoris as $kat)
            <a href="{{ route('perpustakaan.index', ['kategori' => $kat->slug]) }}" class="category-chip {{ request('kategori') == $kat->slug ? 'chip-active' : 'chip-inactive shadow-sm' }}">
                {{ $kat->nama }}
            </a>
        @endforeach
    </div>

    <div class="section-header">
        <div class="section-title mb-0">Rekomendasi</div>
        <div class="small text-muted fw-bold">Lihat Semua</div>
    </div>

    <div class="row g-4">
        @forelse($bukus as $buku)
        <div class="col-6">
            <a href="{{ route('perpustakaan.show', $buku->slug) }}" class="text-decoration-none">
                <div class="book-card">
                    <div class="book-cover-wrapper">
                        @if($buku->cover)
                            <img src="{{ asset('storage/'.$buku->cover) }}" class="w-100 h-100 object-fit-cover">
                        @else
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 text-center">
                                <i class="bi bi-book-half text-primary opacity-20 mb-2" style="font-size: 32px;"></i>
                                <div class="small fw-bold text-muted px-2" style="font-size: 10px;">{{ $buku->judul }}</div>
                            </div>
                        @endif
                        <div class="floating-badge">{{ $buku->kategori->nama }}</div>
                    </div>
                    <div class="book-info">
                        <div class="book-title text-truncate">{{ $buku->judul }}</div>
                        <div class="book-author text-truncate">{{ $buku->penulis ?? 'Intellectual Author' }}</div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="opacity-25 mb-3">
                <i class="bi bi-journal-x" style="font-size: 64px;"></i>
            </div>
            <div class="text-secondary fw-bold">Belum ada koleksi buku.</div>
        </div>
        @endforelse
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .object-fit-cover { object-fit: cover; }
</style>
@endsection
