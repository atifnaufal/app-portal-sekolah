@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    body { background: #0f172a; margin: 0; padding: 0; height: 100vh; overflow: hidden; }

    .reader-ui-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(20px);
        color: white; padding: 16px 20px;
        display: flex; align-items: center; justify-content: space-between;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .reader-btn-back {
        width: 38px; height: 38px; border-radius: 12px;
        background: rgba(255,255,255,0.1);
        display: flex; align-items: center; justify-content: center;
        color: #fff; text-decoration: none;
    }

    .reader-viewport {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        padding-top: 70px;
        background: #f1f5f9; /* Contrast for the PDF */
    }

    iframe { width: 100%; height: 100%; border: none; }

    .immersive-info { text-align: center; flex: 1; padding: 0 15px; }
    .immersive-title { font-size: 13px; font-weight: 800; color: #fff; margin-bottom: 2px; }
    .immersive-status { font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; }
</style>

<div class="reader-ui-header animate__animated animate__fadeInDown">
    <a href="{{ route('perpustakaan.show', $buku->slug) }}" class="reader-btn-back">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div class="immersive-info">
        <div class="immersive-title text-truncate">{{ $buku->judul }}</div>
        <div class="immersive-status"><i class="bi bi-eye-fill me-1"></i> Mode Baca Digital</div>
    </div>
    <div style="width: 38px;"></div>
</div>

<div class="reader-viewport">
    <iframe src="{{ asset('storage/'.$buku->file_pdf) }}#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf"></iframe>
</div>

<script>
    // Prevent some common mobile iframe issues
    document.addEventListener('touchmove', function (e) {
        if (e.target.tagName !== 'IFRAME') {
            // e.preventDefault();
        }
    }, { passive: false });
</script>
@endsection
