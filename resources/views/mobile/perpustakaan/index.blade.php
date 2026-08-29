@extends('layouts.mobile-app')

@section('content')
<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.8); backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--line);
        padding: 10px 16px; display: flex; align-items: center; justify-content: space-between;
    }
    .back-btn {
        width: 38px; height: 38px; border-radius: 12px; background: var(--surface);
        display: flex; align-items: center; justify-content: center;
        color: var(--ink); text-decoration: none;
    }

    .page-container { padding-top: 60px; }

    /* E-Catalog — selaras dengan design system terpusat */
    .perpus-hero {
        background: var(--grad-hero);
        padding: 24px 20px 32px;
        border-radius: var(--radius-lg);
        color: #fff; position: relative; overflow: hidden;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
        margin: 16px 16px 24px;
    }
    .perpus-hero::before {
        content: ''; position: absolute; top: -20%; right: -10%;
        width: 250px; height: 250px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36, 107, 254, 0.18) 0%, transparent 70%);
    }
    .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; position: relative; z-index: 2; }

    .lib-badge {
        display: inline-block; padding: 3px 10px; border-radius: 999px;
        background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
        font-size: 9px; font-weight: 800; letter-spacing: 0.05em;
        text-transform: uppercase; margin-bottom: 4px;
    }

    .search-wrapper {
        position: relative; z-index: 2;
        background: rgba(255,255,255,.1);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: var(--radius-sm); padding: 1px 4px;
        display: flex; align-items: center;
    }
    .search-input {
        background: transparent; border: none; color: #fff;
        padding: 8px 10px; width: 100%; outline: none; font-size: 14px;
    }
    .search-input::placeholder { color: rgba(255,255,255,.45); }

    .category-scroll {
        display: flex; gap: 8px; overflow-x: auto;
        padding: 16px 20px 4px; -webkit-overflow-scrolling: touch; scrollbar-width: none;
    }
    .category-scroll::-webkit-scrollbar { display: none; }

    .pui-chip { flex-shrink: 0; }
    .pui-chip.active { background: var(--grad-primary); color: #fff; border-color: transparent; box-shadow: 0 6px 16px rgba(79,70,229,.28); }

    .grid-container { padding: 0 20px 100px; }

    .book-card-premium {
        background: var(--surface-card); border-radius: var(--radius-md); padding: 10px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-card);
        height: 100%; display: flex; flex-direction: column;
    }
    .book-card-premium:active { transform: scale(0.97); }

    .cover-box {
        aspect-ratio: 2/3; border-radius: var(--radius-sm); overflow: hidden;
        background: #f8fafc; position: relative;
    }
    .cover-img { width: 100%; height: 100%; object-fit: cover; }

    .type-tag {
        position: absolute; bottom: 8px; left: 8px;
        background: rgba(15,23,42,.7); backdrop-filter: blur(4px);
        padding: 2px 8px; border-radius: 6px;
        font-size: 8px; font-weight: 700; color: #fff; text-transform: uppercase;
    }

    .info-box { padding: 10px 4px 2px; }
    .title-txt { font-size: 13px; font-weight: 700; color: var(--ink); margin-bottom: 2px; line-height: 1.3; }
    .author-txt { font-size: 10px; color: var(--faint); display: flex; align-items: center; gap: 4px; }

    .section-head-pro { display: flex; align-items: center; justify-content: space-between; margin: 20px 20px 12px; }
    .section-head-pro h2 { font-size: 16px; font-weight: 800; color: var(--ink); margin: 0; }
</style>

<div class="page-header">
    <a href="{{ route('dashboard') }}" class="back-btn">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div style="font-weight: 800; font-size: 16px; color: var(--ink);">Perpustakaan Digital</div>
    <div style="width: 38px;"></div>
</div>

<div class="page-container">
    <div class="perpus-hero">
        <div class="header-top">
            <div>
                <span class="lib-badge">Library Module</span>
                <h1 class="text-white mb-0" style="font-size: 24px; letter-spacing: -0.5px; font-weight: 900;">E-Catalog</h1>
            </div>
        </div>

        <form action="{{ route('perpustakaan.index') }}" method="GET">
            <div class="search-wrapper">
                <div class="ps-3" style="opacity:.6;"><i class="bi bi-search"></i></div>
                <input type="text" name="search" class="search-input" placeholder="Temukan bacaan cerdas..." value="{{ request('search') }}">
                @if(request('search'))
                    <a href="{{ route('perpustakaan.index') }}" class="pe-3" style="opacity:.6;color:#fff;"><i class="bi bi-x-circle-fill"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Categories -->
<div class="category-scroll">
    <a href="{{ route('perpustakaan.index') }}" class="pui-chip {{ !request('kategori') ? 'active' : '' }}">
        <i class="bi bi-collection"></i> Semua Koleksi
    </a>
    @foreach($kategoris as $kat)
        <a href="{{ route('perpustakaan.index', ['kategori' => $kat->slug]) }}" class="pui-chip {{ request('kategori') == $kat->slug ? 'active' : '' }}">
            {{ $kat->nama }}
        </a>
    @endforeach
</div>

<div class="section-head-pro">
    <h2>Eksplorasi</h2>
    <div class="small fw-bold" style="color:var(--indigo);">Terbaru <i class="bi bi-sort-down"></i></div>
</div>

<div class="grid-container">
    <div class="row g-3">
        @forelse($bukus as $buku)
        <div class="col-6 col-sm-4">
            <a href="{{ route('perpustakaan.show', $buku->slug) }}" class="text-decoration-none">
                <div class="book-card-premium">
                    <div class="cover-box">
                        @if($buku->cover)
                            <img src="{{ asset('storage/'.$buku->cover) }}" class="cover-img" loading="lazy">
                        @else
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 text-center" style="background:#f8fafc;">
                                <i class="bi bi-journals mb-2" style="font-size: 32px; color: var(--indigo); opacity:.4;"></i>
                                <div style="font-size: 9px; font-weight: 800; color: var(--faint); text-transform: uppercase;">Cover Needed</div>
                            </div>
                        @endif
                        <div class="type-tag">{{ $buku->kategori->nama }}</div>
                    </div>
                    <div class="info-box">
                        <div class="title-txt text-truncate">{{ $buku->judul }}</div>
                        <div class="author-txt text-truncate">
                            <i class="bi bi-person-circle"></i>
                            {{ $buku->penulis ?? 'Karya Ilmiah' }}
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="mb-3" style="opacity:.1;">
                <i class="bi bi-book" style="font-size: 100px;"></i>
            </div>
            <h5 class="fw-bold" style="color:var(--faint);">Katalog Kosong</h5>
            <p class="small" style="color:var(--mist);">Maaf, saat ini belum ada buku digital yang tersedia untuk kategori ini.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
