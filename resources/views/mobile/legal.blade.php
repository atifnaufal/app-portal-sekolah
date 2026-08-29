@extends('layouts.mobile-app')

@section('content')
<div class="pui-topbar">
    <a href="{{ route('about.show') }}" class="back"><i class="bi bi-chevron-left"></i> Kembali</a>
    <h1>{{ $title }}</h1>
</div>

<div class="p-4 stagger">
    <div class="pui-card p-4" style="line-height:1.8; color:var(--mist);">
        <h4 class="fw-bold text-dark mb-3">{{ $title }}</h4>
        <p class="small">Terakhir diperbarui: 23 Agustus 2026</p>

        <div class="mt-4" style="font-size:13px;">
            {!! $content !!}
        </div>
    </div>

    <div class="mt-4 text-center">
        <p class="small text-muted">Portal Sekolah Digital - Premium Edition</p>
    </div>
</div>
@endsection
