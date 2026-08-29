@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    body { background: var(--navy); margin: 0; padding: 0; height: 100vh; overflow: hidden; position: relative; }

    .reader-top-bar {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: var(--navy-2); backdrop-filter: blur(20px);
        padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: #fff;
    }

    .btn-exit-reader {
        width: 36px; height: 36px; border-radius: 12px;
        background: rgba(255, 255, 255, 0.1);
        display: flex; align-items: center; justify-content: center;
        color: #fff; text-decoration: none; border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.2s;
    }
    .btn-exit-reader:active { transform: scale(0.9); background: rgba(255, 255, 255, 0.2); }

    .reader-canvas {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        padding-top: 64px; display: flex; flex-direction: column;
    }

    .pdf-frame { flex: 1; border: none; width: 100%; height: 100%; background: var(--surface); }

    .pdf-fallback {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 100%; padding: 40px; text-align: center; color: var(--faint);
    }

    .immersive-header-txt { text-align: center; flex: 1; padding: 0 10px; }
    .book-name-small { font-size: 13px; font-weight: 800; color: #fff; margin-bottom: 1px; }
    .reader-badge-pro { font-size: 9px; font-weight: 800; color: var(--faint); text-transform: uppercase; letter-spacing: 0.15em; }

    .reader-hint {
        position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
        background: rgba(15,23,42,.8); backdrop-filter: blur(10px);
        padding: 8px 16px; border-radius: 999px;
        color: #fff; font-size: 11px; font-weight: 700;
        z-index: 1001; pointer-events: none;
        box-shadow: 0 10px 20px rgba(0,0,0,.2);
        animation: fadeOut 3s forwards ease-in-out;
    }
    @keyframes fadeOut { 0% { opacity: 1; } 70% { opacity: 1; } 100% { opacity: 0; } }
</style>

<div class="reader-top-bar animate__animated animate__fadeInDown">
    <a href="{{ route('perpustakaan.show', $buku->slug) }}" class="btn-exit-reader">
        <i class="bi bi-x-lg"></i>
    </a>
    <div class="immersive-header-txt">
        <div class="book-name-small text-truncate">{{ $buku->judul }}</div>
        <div class="reader-badge-pro"><i class="bi bi-stars me-1" style="color:var(--indigo);"></i> Intellectual Reader</div>
    </div>
    <div style="width: 36px;"></div>
</div>

<div class="reader-canvas">
    @if($buku->file_pdf)
        <iframe
            class="pdf-frame"
            src="https://docs.google.com/viewer?url={{ urlencode($pdfUrl) }}&embedded=true"
            allow="fullscreen"
        ></iframe>

        {{-- Floating Download Button as Fallback --}}
        <div style="position: fixed; bottom: 80px; right: 20px; z-index: 1002;">
            <a href="{{ $pdfUrl }}" onclick="puiExportFile(this.href,'{{ $buku->judul }}','pdf'); return false;" class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                <i class="bi bi-download"></i>
            </a>
        </div>
    @else
        <div class="pdf-fallback">
            <i class="bi bi-file-earmark-x mb-3" style="font-size: 48px;"></i>
            <div class="fw-bold text-white mb-2">File PDF Tidak Ditemukan</div>
            <p class="small">Konten digital untuk buku ini belum tersedia atau sedang dalam proses upload.</p>
            <a href="{{ route('perpustakaan.show', $buku->slug) }}" class="btn btn-primary rounded-pill px-4 mt-3">Kembali</a>
        </div>
    @endif
</div>

<div class="reader-hint">
    <i class="bi bi-phone-flip me-2"></i> Scroll untuk membaca
</div>

<script>
    document.body.addEventListener('touchmove', function (e) {
        if (!e.target.closest('.pdf-frame')) {
            e.preventDefault();
        }
    }, { passive: false });
</script>
@endsection
