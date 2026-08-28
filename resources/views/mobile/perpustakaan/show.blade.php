@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .book-detail-shell { background: #fff; min-height: 100vh; position: relative; }

    .nav-bar-premium {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .nav-bar-premium.scrolled {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border-bottom: 1px solid #f1f5f9;
    }

    .btn-circle-blur {
        width: 42px; height: 42px; border-radius: 50%;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        display: flex; align-items: center; justify-content: center;
        color: #0f172a; text-decoration: none; border: 1px solid #f1f5f9;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .hero-backdrop {
        background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
        padding: 80px 24px 40px; text-align: center;
    }

    .cover-premium {
        width: 200px; max-width: 60%; aspect-ratio: 2/3;
        margin: 0 auto; border-radius: 24px;
        box-shadow: 0 30px 60px -12px rgba(15, 23, 42, 0.25);
        overflow: hidden; background: #e2e8f0;
        position: relative;
    }
    .cover-premium img { width: 100%; height: 100%; object-fit: cover; }

    .meta-box-container {
        display: flex; gap: 8px; padding: 0 24px;
        margin-top: 20px; position: relative; z-index: 10;
    }
    .meta-card-item {
        flex: 1; background: #fff; border-radius: 16px; padding: 12px 6px;
        text-align: center; border: 1px solid #f1f5f9;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .meta-val-txt { font-size: 15px; font-weight: 900; color: #0f172a; margin-bottom: 2px; }
    .meta-lbl-txt { font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; }

    .main-content-pro { padding: 20px 24px 130px; }

    .b-title-txt { font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 6px; line-height: 1.2; }
    .b-author-txt { font-size: 14px; font-weight: 600; color: var(--blue); margin-bottom: 24px; display: flex; align-items: center; gap: 6px; }

    .synopsis-wrapper { margin-top: 0; }
    .synopsis-header {
        display: flex; align-items: center; gap: 10px;
        font-size: 16px; font-weight: 900; color: #0f172a; margin-bottom: 16px;
    }
    .synopsis-body { font-size: 14px; color: #475569; line-height: 1.8; letter-spacing: 0.2px; text-align: justify; }

    .bottom-action-shell {
        position: fixed; bottom: 0; left: 0; right: 0;
        background: linear-gradient(to top, #fff 80%, rgba(255,255,255,0));
        padding: 24px; z-index: 100;
    }
    .btn-read-pro {
        width: 100%; padding: 18px; border-radius: 22px;
        background: var(--blue); color: #fff; font-weight: 900; font-size: 16px;
        border: none; display: flex; align-items: center; justify-content: center; gap: 12px;
        box-shadow: 0 15px 35px rgba(36, 107, 254, 0.35);
        text-decoration: none; transition: all 0.2s;
    }
    .btn-read-pro:active { transform: scale(0.96); box-shadow: 0 8px 20px rgba(36, 107, 254, 0.3); }

    /* Fix for scrolling issues on small screens */
    @media (max-height: 600px) {
        .hero-backdrop { padding: 60px 20px 30px; }
        .cover-premium { width: 140px; }
    }
</style>

<div class="book-detail-shell">
    <div class="nav-bar-premium" id="proNav">
        <a href="{{ route('perpustakaan.index') }}" class="btn-circle-blur">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="fw-bolder d-none" id="navBookTitle" style="font-size: 15px; color: #0f172a;">{{ $buku->judul }}</div>
        <div style="width: 42px;"></div>
    </div>

    <div class="hero-backdrop">
        <div class="cover-premium">
            @if($buku->cover)
                <img src="{{ asset('storage/'.$buku->cover) }}" alt="Cover">
            @else
                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                    <i class="bi bi-journal-text text-primary opacity-20" style="font-size: 80px;"></i>
                </div>
            @endif
        </div>
    </div>

    <div class="meta-box-container">
        <div class="meta-card-item">
            <div class="meta-val-txt">{{ $buku->tahun_terbit ?? 'N/A' }}</div>
            <div class="meta-lbl-txt">Tahun</div>
        </div>
        <div class="meta-card-item">
            <div class="meta-val-txt text-truncate" style="max-width: 80px;">{{ $buku->penerbit ?? 'Intellectual' }}</div>
            <div class="meta-lbl-txt">Penerbit</div>
        </div>
        <div class="meta-card-item">
            <div class="meta-val-txt">{{ $buku->kategori->nama }}</div>
            <div class="meta-lbl-txt">Topik</div>
        </div>
    </div>

    <div class="main-content-pro">
        <h1 class="b-title-txt">{{ $buku->judul }}</h1>
        <div class="b-author-txt">
            <i class="bi bi-person-check-fill"></i>
            {{ $buku->penulis ?? 'Intellectual Author' }}
        </div>

        <div class="synopsis-wrapper">
            <div class="synopsis-header">
                <i class="bi bi-card-text text-primary"></i>
                Sinopsis Buku
            </div>
            <div class="synopsis-body">
                {{ $buku->deskripsi ?: 'Buku ini merupakan karya literatur digital berkualitas tinggi yang dikurasi khusus untuk mendukung ekosistem pembelajaran cerdas di sekolah. Silakan akses versi digital lengkapnya untuk mendalami wawasan intelektual yang terkandung di dalamnya.' }}
            </div>
        </div>
    </div>

    <div class="bottom-action-shell">
        <div class="mobile-shell mx-auto">
            <a href="{{ route('perpustakaan.read', $buku->slug) }}" class="btn-read-pro">
                <i class="bi bi-book-half"></i>
                Buka Reader Digital
            </a>
        </div>
    </div>
</div>

<script>
    window.addEventListener('scroll', function() {
        const nav = document.getElementById('proNav');
        const title = document.getElementById('navBookTitle');
        if (window.scrollY > 80) {
            nav.classList.add('scrolled');
            title.classList.remove('d-none');
        } else {
            nav.classList.remove('scrolled');
            title.classList.add('d-none');
        }
    });
</script>
@endsection
