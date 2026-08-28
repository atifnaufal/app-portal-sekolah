@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .book-details-page { background: #fff; min-height: 100vh; position: relative; }

    .detail-nav {
        position: fixed; top: 0; left: 0; right: 0; z-index: 100;
        padding: 20px 24px; display: flex; align-items: center; justify-content: space-between;
        transition: all 0.3s;
    }

    .blur-header {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .book-hero-bg {
        height: 400px;
        background: linear-gradient(180deg, #f1f5f9 0%, #fff 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 80px 24px 40px;
    }

    .hero-cover {
        width: 180px;
        aspect-ratio: 2/3;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        overflow: hidden;
        background: #e2e8f0;
    }

    .book-meta-grid {
        display: flex;
        gap: 12px;
        margin: -30px 24px 30px;
    }
    .meta-item {
        flex: 1;
        background: #fff;
        padding: 16px 8px;
        border-radius: 20px;
        text-align: center;
        border: 1px solid #f1f5f9;
        box-shadow: 0 8px 24px rgba(0,0,0,0.04);
    }
    .meta-val { font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 2px; }
    .meta-lbl { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }

    .content-area { padding: 0 24px 120px; }
    .book-title-large { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
    .book-author-large { font-size: 16px; font-weight: 600; color: var(--blue); margin-bottom: 24px; }

    .synopsis-title { font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    .synopsis-text { font-size: 14px; color: #64748b; line-height: 1.7; }

    .action-bar {
        position: fixed; bottom: 0; left: 0; right: 0;
        padding: 24px;
        background: linear-gradient(to top, #fff 70%, rgba(255,255,255,0));
        z-index: 100;
    }
    .read-btn {
        background: var(--blue);
        color: #fff;
        border: none;
        border-radius: 20px;
        padding: 18px;
        width: 100%;
        font-weight: 800;
        font-size: 16px;
        box-shadow: 0 12px 30px rgba(36, 107, 254, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        text-decoration: none;
    }
    .read-btn:active { transform: scale(0.96); }
</style>

<div class="book-details-page">
    <div class="detail-nav" id="topNav">
        <a href="{{ route('perpustakaan.index') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: #fff; border: 1px solid #f1f5f9; color: #0f172a;">
            <i class="bi bi-chevron-left"></i>
        </a>
        <div class="fw-bold d-none" id="navTitle">Detail Buku</div>
        <div style="width: 44px;"></div>
    </div>

    <div class="book-hero-bg">
        <div class="hero-cover">
            @if($buku->cover)
                <img src="{{ asset('storage/'.$buku->cover) }}" class="w-100 h-100 object-fit-cover">
            @else
                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                    <i class="bi bi-book-half fs-1 text-primary opacity-20"></i>
                </div>
            @endif
        </div>
    </div>

    <div class="book-meta-grid">
        <div class="meta-item">
            <div class="meta-val">{{ $buku->tahun_terbit ?? '-' }}</div>
            <div class="meta-lbl">Tahun</div>
        </div>
        <div class="meta-item">
            <div class="meta-val text-truncate" style="max-width: 80px;">{{ $buku->penerbit ?? '-' }}</div>
            <div class="meta-lbl">Penerbit</div>
        </div>
        <div class="meta-item">
            <div class="meta-val">{{ $buku->kategori->nama }}</div>
            <div class="meta-lbl">Kategori</div>
        </div>
    </div>

    <div class="content-area">
        <h1 class="book-title-large">{{ $buku->judul }}</h1>
        <div class="book-author-large">{{ $buku->penulis ?? 'Intellectual Writer' }}</div>

        <div class="synopsis-title">
            <i class="bi bi-text-left text-primary"></i> Sinopsis
        </div>
        <p class="synopsis-text">
            {{ $buku->deskripsi ?: 'Tidak ada deskripsi yang tersedia untuk karya ini. Silakan baca langsung untuk mengeksplorasi isinya.' }}
        </p>
    </div>

    <div class="action-bar">
        <div class="mobile-shell mx-auto">
            <a href="{{ route('perpustakaan.read', $buku->slug) }}" class="read-btn">
                <i class="bi bi-book-half"></i>
                Baca Digital Sekarang
            </a>
        </div>
    </div>
</div>

<script>
    window.addEventListener('scroll', function() {
        const nav = document.getElementById('topNav');
        const title = document.getElementById('navTitle');
        if (window.scrollY > 100) {
            nav.classList.add('blur-header');
            title.classList.remove('d-none');
        } else {
            nav.classList.remove('blur-header');
            title.classList.add('d-none');
        }
    });
</script>
@endsection
