@extends('layouts.mobile-app')
@section('content')
<div class="p-3 pb-0">
    <a href="javascript:history.back()" class="text-decoration-none text-muted d-inline-flex align-items-center gap-2 small fw-bold">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
        Kembali
    </a>
</div>
<header class="mobile-hero"><div class="eyebrow">PEMBERITAHUAN</div><div class="hero-title mt-2">Notifikasi</div><div class="class-pill mt-3">Info penting untukmu</div></header>
<main class="mobile-content"><div class="stagger">@forelse($notifications as $notification)<a href="{{ $notification->url ?: route('dashboard') }}" class="card mobile-card tap-card text-decoration-none text-dark mb-3 {{ is_null($notification->dibaca_pada) ? 'border-start border-4 border-primary' : '' }}">
    <div class="card-body d-flex gap-3 align-items-start">
        <div class="icon-box" style="background:{{ str_contains(strtolower($notification->judul), 'spp') ? '#fff3d6' : '#e8efff' }};color:{{ str_contains(strtolower($notification->judul), 'spp') ? '#a66b00' : '#246bfe' }}">
            {{ str_contains(strtolower($notification->judul), 'spp') ? '&#8364;' : '&#9993;' }}
        </div>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between">
                <h2 class="h6 fw-bold mb-1">{{ $notification->judul }}</h2>
                @if(is_null($notification->dibaca_pada))
                    <span class="badge bg-primary rounded-circle p-1" style="width:8px; height:8px;"> </span>
                @endif
            </div>
            <p class="small text-secondary mb-2">{{ $notification->pesan }}</p>
            <span class="small text-secondary">{{ $notification->created_at->diffForHumans() }}</span>
        </div>
    </div>
</a>@empty<div class="card mobile-card"><div class="card-body text-secondary">Belum ada notifikasi baru.</div></div>@endforelse</div></main>
@endsection
