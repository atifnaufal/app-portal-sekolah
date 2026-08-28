@extends('layouts.mobile-app')

@section('content')
<style>
    :root {
        --lib-navy: #0f172a;
        --lib-blue: #246bfe;
        --lib-glass: rgba(255, 255, 255, 0.08);
        --lib-border: rgba(255, 255, 255, 0.15);
    }

    .perpus-hero {
        background: linear-gradient(160deg, var(--lib-navy) 0%, #1e293b 100%);
        padding: 48px 24px 70px;
        border-radius: 0 0 48px 48px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2);
    }

    .perpus-hero::before {
        content: ''; position: absolute; top: -20%; right: -10%;
        width: 250px; height: 250px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36, 107, 254, 0.15) 0%, transparent 70%);
    }

    .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; }

    .lib-badge {
        display: inline-block; padding: 4px 12px; border-radius: 100px;
        background: var(--lib-glass); border: 1px solid var(--lib-border);
        font-size: 10px; font-weight: 800; letter-spacing: 0.1em;
        text-transform: uppercase; margin-bottom: 8px;
    }

    .search-wrapper {
        position: relative; z-index: 2;
        background: var(--lib-glass);
        backdrop-filter: blur(12px);
        border: 1px solid var(--lib-border);
        border-radius: 22px;
        padding: 2px 6px;
        display: flex;
        align-items: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .search-wrapper:focus-within {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }
    .search-input {
        background: transparent; border: none; color: #fff;
        padding: 12px 10px; width: 100%; outline: none; font-size: 15px;
    }
    .search-input::placeholder { color: rgba(255, 255, 255, 0.4); }

    .category-scroll {
        display: flex; gap: 10px; overflow-x: auto;
        padding: 4px 24px 24px; margin: -30px 0 0;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
    }
    .category-scroll::-webkit-scrollbar { display: none; }

    .chip {
        padding: 10px 24px; border-radius: 16px; font-size: 13px; font-weight: 700;
        white-space: nowrap; text-decoration: none; transition: all 0.3s;
        border: 1px solid transparent; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .chip-active { background: var(--lib-blue); color: #fff; box-shadow: 0 8px 20px rgba(36, 107, 254, 0.3); }
    .chip-inactive { background: #fff; color: #64748b; border-color: #f1f5f9; }

    .grid-container { padding: 0 20px 100px; }

    .book-card-premium {
        background: #fff; border-radius: 28px; padding: 12px;
        border: 1px solid #f8fafc;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%; display: flex; flex-direction: column;
    }
    .book-card-premium:active { transform: scale(0.95); background: #fdfdfd; }

    .cover-box {
        aspect-ratio: 2/3; border-radius: 20px; overflow: hidden;
        background: #f1f5f9; position: relative;
        box-shadow: 0 12px 24px -8px rgba(0,0,0,0.15);
    }
    .cover-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
    .book-card-premium:hover .cover-img { transform: scale(1.05); }

    .type-tag {
        position: absolute; bottom: 10px; left: 10px;
        background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(8px);
        padding: 4px 10px; border-radius: 8px;
        font-size: 9px; font-weight: 800; color: #fff;
        text-transform: uppercase; letter-spacing: 0.05em;
    }

    .info-box { padding: 12px 6px 4px; }
    .title-txt { font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 4px; line-height: 1.3; }
    .author-txt { font-size: 11px; color: #94a3b8; font-weight: 600; display: flex; align-items: center; gap: 4px; }

    .section-head-pro {
        display: flex; align-items: center; justify-content: space-between;
        margin: 12px 24px 20px;
    }
    .section-head-pro h2 { font-size: 18px; font-weight: 900; color: #0f172a; margin: 0; }

    /* Responsive adjustment for extra small devices */
    @media (max-width: 360px) {
        .grid-container { padding: 0 12px; }
        .chip { padding: 8px 16px; font-size: 12px; }
        .perpus-hero { padding: 40px 16px 60px; }
    }
</style>

<div class="perpus-hero">
    <div class="header-top">
        <div>
            <span class="lib-badge">Library Module</span>
            <h1 class="ad-hero-title text-white mb-0" style="font-size: 30px; letter-spacing: -1px;">E-Catalog</h1>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle shadow-sm" style="width: 44px; height: 44px; display: grid; place-items: center;">
            <i class="bi bi-grid-fill"></i>
        </a>
    </div>

    <form action="{{ route('perpustakaan.index') }}" method="GET">
        <div class="search-wrapper">
            <div class="ps-3"><i class="bi bi-search text-white opacity-40"></i></div>
            <input type="text" name="search" class="search-input" placeholder="Temukan bacaan cerdas..." value="{{ request('search') }}">
            @if(request('search'))
                <a href="{{ route('perpustakaan.index') }}" class="pe-3 text-white opacity-40"><i class="bi bi-x-circle-fill"></i></a>
            @endif
        </div>
    </form>
</div>

<!-- Categories -->
<div class="category-scroll">
    <a href="{{ route('perpustakaan.index') }}" class="chip {{ !request('kategori') ? 'chip-active' : 'chip-inactive' }}">
        Semua Koleksi
    </a>
    @foreach($kategoris as $kat)
        <a href="{{ route('perpustakaan.index', ['kategori' => $kat->slug]) }}" class="chip {{ request('kategori') == $kat->slug ? 'chip-active' : 'chip-inactive' }}">
            {{ $kat->nama }}
        </a>
    @endforeach
</div>

<div class="section-head-pro">
    <h2>Eksplorasi</h2>
    <div class="small text-primary fw-bold">Terbaru <i class="bi bi-sort-down"></i></div>
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
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 text-center bg-light">
                                <i class="bi bi-journals text-primary opacity-20 mb-2" style="font-size: 32px;"></i>
                                <div style="font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Cover Needed</div>
                            </div>
                        @endif
                        <div class="type-tag">{{ $buku->kategori->nama }}</div>
                    </div>
                    <div class="info-box">
                        <div class="title-txt text-truncate">{{ $buku->judul }}</div>
                        <div class="author-txt text-truncate">
                            <i class="bi bi-person-circle" style="font-size: 10px;"></i>
                            {{ $buku->penulis ?? 'Karya Ilmiah' }}
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="mb-3 opacity-10">
                <i class="bi bi-book" style="font-size: 100px;"></i>
            </div>
            <h5 class="fw-bold text-muted">Katalog Kosong</h5>
            <p class="small text-secondary px-5">Maaf, saat ini belum ada buku digital yang tersedia untuk kategori ini.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
