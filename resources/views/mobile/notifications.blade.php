@extends('layouts.mobile-app')

@section('content')
<div class="pui-topbar">
    <a href="{{ route('dashboard') }}" class="back"><i class="bi bi-arrow-left"></i> Kembali</a>
    <div class="spacer"></div>
</div>

<header class="p-hero" style="max-width:640px;margin:0 auto;padding:16px 20px 6px;">
    <div class="eyebrow" style="font-size:11px;letter-spacing:.14em;font-weight:800;text-transform:uppercase;color:var(--indigo);">
        PEMBERITAHUAN
    </div>
    <div class="hero-title mt-2" style="font-size:28px;font-weight:800;letter-spacing:-.02em;color:var(--ink);">Notifikasi</div>
    <div class="mt-3">
        <span class="pui-chip pui-chip-primary"><i class="bi bi-bell-fill"></i> Info penting untukmu</span>
    </div>
</header>

<main style="max-width:640px;margin:0 auto;padding:18px 20px 60px;">
    <div class="stagger">
        @forelse($notifications as $notification)
            @php
                $isSpp = str_contains(strtolower($notification->judul), 'spp');
                $isUnread = is_null($notification->dibaca_pada);
            @endphp
            <a href="{{ $notification->url ?: route('dashboard') }}"
               class="pui-card p-3 d-flex gap-3 align-items-start text-decoration-none mb-3 {{ $isUnread ? 'border-start border-4' : '' }}"
               style="border-color: {{ $isUnread ? 'var(--indigo) !important' : 'var(--line)' }}; box-shadow: var(--shadow-card); display:flex;">
                <div class="pui-icon-box flex-shrink-0"
                     style="background: {{ $isSpp ? '#fff9ed' : '#eef2ff' }}; color: {{ $isSpp ? '#a66b00' : 'var(--indigo)' }}; width: 44px; height: 44px; border-radius: 12px; display:grid; place-items:center;">
                    @if($isSpp)
                        <i class="bi bi-wallet2" style="font-size:20px;"></i>
                    @else
                        <i class="bi bi-chat-left-text" style="font-size:20px;"></i>
                    @endif
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h3 class="h6 fw-bold mb-0 text-truncate pe-2" style="color:var(--ink);">{{ $notification->judul }}</h3>
                        @if($isUnread)
                            <span class="pui-dot" style="width:8px;height:8px;border-radius:50%;background:var(--indigo);box-shadow:0 0 0 3px rgba(79,70,229,.15);flex-shrink:0;display:inline-block;"></span>
                        @endif
                    </div>
                    <p class="small mb-2 line-clamp-2" style="color:var(--mist);display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ $notification->pesan }}
                    </p>
                    <div class="d-flex align-items-center gap-1" style="font-size:10px;color:var(--faint);">
                        <i class="bi bi-clock"></i>
                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="pui-empty py-5">
                <i class="bi bi-bell-slash ico"></i>
                <h4>Belum ada notifikasi</h4>
                <p>Belum ada notifikasi baru untukmu.</p>
            </div>
        @endforelse
    </div>
</main>
@endsection
