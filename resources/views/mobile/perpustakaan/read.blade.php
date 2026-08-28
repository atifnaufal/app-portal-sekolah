@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    body { background: #1e293b; margin: 0; padding: 0; height: 100vh; overflow: hidden; }
    .reader-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(10px);
        color: white; padding: 12px 15px; display: flex; align-items: center; gap: 15px;
    }
    .reader-container {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        padding-top: 60px;
    }
    iframe { width: 100%; height: 100%; border: none; }
</style>

<div class="reader-header">
    <a href="{{ route('perpustakaan.show', $buku->slug) }}" class="text-white"><i class="bi bi-arrow-left fs-4"></i></a>
    <div class="text-truncate fw-bold small">{{ $buku->judul }}</div>
</div>

<div class="reader-container">
    @if(str_contains(request()->userAgent(), 'iPhone') || str_contains(request()->userAgent(), 'iPad'))
        <!-- iOS often needs a different approach for PDFs, but for now we try standard iframe -->
        <iframe src="{{ asset('storage/'.$buku->file_pdf) }}#toolbar=0" type="application/pdf"></iframe>
    @else
        <iframe src="{{ asset('storage/'.$buku->file_pdf) }}#toolbar=0" type="application/pdf"></iframe>
    @endif
</div>
@endsection
