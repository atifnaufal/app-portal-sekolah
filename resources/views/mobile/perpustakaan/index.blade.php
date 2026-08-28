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
        padding: 32px 24px 24px;
        border-radius: 0 0 24px 24px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }

    .perpus-hero::before {
        content: ''; position: absolute; top: -20%; right: -10%;
        width: 250px; height: 250px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36, 107, 254, 0.08) 0%, transparent 70%);
    }

    .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }

    .lib-badge {
        display: inline-block; padding: 3px 10px; border-radius: 100px;
        background: var(--lib-glass); border: 1px solid var(--lib-border);
        font-size: 9px; font-weight: 800; letter-spacing: 0.05em;
        text-transform: uppercase; margin-bottom: 4px;
    }

    .search-wrapper {
        position: relative; z-index: 2;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 14px;
        padding: 1px 4px;
        display: flex;
        align-items: center;
    }
    .search-input {
        background: transparent; border: none; color: #fff;
        padding: 8px 10px; width: 100%; outline: none; font-size: 14px;
    }
    .search-input::placeholder { color: rgba(255, 255, 255, 0.4); }

    .category-scroll {
        display: flex; gap: 8px; overflow-x: auto;
        padding: 16px 24px;
        -webkit-overflow-scrolling: touch;
    }
    .category-scroll::-webkit-scrollbar { display: none; }

    .chip {
        padding: 8px 20px; border-radius: 12px; font-size: 13px; font-weight: 700;
        white-space: nowrap; text-decoration: none; transition: all 0.2s;
        border: 1px solid #f1f5f9; background: #fff; color: #64748b;
    }
    .chip-active { background: var(--lib-blue); color: #fff; border-color: var(--lib-blue); }

    .grid-container { padding: 0 20px 100px; }

    .book-card-premium {
        background: #fff; border-radius: 20px; padding: 10px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        height: 100%; display: flex; flex-direction: column;
    }
    .book-card-premium:active { transform: scale(0.97); }

    .cover-box {
        aspect-ratio: 2/3; border-radius: 14px; overflow: hidden;
        background: #f8fafc; position: relative;
    }
    .cover-img { width: 100%; height: 100%; object-fit: cover; }

    .type-tag {
        position: absolute; bottom: 8px; left: 8px;
        background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px);
        padding: 2px 8px; border-radius: 6px;
        font-size: 8px; font-weight: 700; color: #fff;
        text-transform: uppercase;
    }

    .info-box { padding: 10px 4px 2px; }
    .title-txt { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 2px; line-height: 1.3; }
    .author-txt { font-size: 10px; color: #94a3b8; display: flex; align-items: center; gap: 4px; }

    .section-head-pro {
        display: flex; align-items: center; justify-content: space-between;
        margin: 0 24px 16px;
    }
    .section-head-pro h2 { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0; }

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
