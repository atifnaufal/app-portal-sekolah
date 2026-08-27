@extends('layouts.mobile-app')

@section('content')
<div class="p-3 pb-0">
    <a href="{{ route('dashboard') }}" class="text-decoration-none text-muted d-inline-flex align-items-center gap-2 small fw-bold">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
        Kembali
    </a>
</div>

<header class="mobile-hero mt-3" style="border-radius: 25px;">
    <div class="eyebrow">PEMBERITAHUAN</div>
    <div class="hero-title mt-2">Notifikasi</div>
    <div class="mt-3">
        <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-2 fw-normal" style="font-size: 11px;">
            Info penting untukmu
        </span>
    </div>
</header>

<main class="mobile-content">
    <div class="stagger">
        @forelse($notifications as $notification)
            @php
                $isSpp = str_contains(strtolower($notification->judul), 'spp');
                $isUnread = is_null($notification->dibaca_pada);
            @endphp
            <a href="{{ $notification->url ?: route('dashboard') }}" class="card mobile-card shadow-sm border-0 mb-3 {{ $isUnread ? 'bg-primary-subtle bg-opacity-10' : '' }}" style="border-radius: 20px;">
                <div class="card-body p-3">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="icon-box flex-shrink-0" style="background: {{ $isSpp ? '#fff9ed' : '#f0f7ff' }}; color: {{ $isSpp ? '#a66b00' : '#246bfe' }}; width: 44px; height: 44px; border-radius: 12px;">
                            @if($isSpp)
                                <i class="bi bi-wallet2 h5 mb-0"></i>
                            @else
                                <i class="bi bi-chat-left-text h5 mb-0"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h3 class="h6 fw-bold mb-0 text-truncate pe-2">{{ $notification->judul }}</h3>
                                @if($isUnread)
                                    <span class="badge bg-primary p-1 rounded-circle" style="width: 8px; height: 8px;"></span>
                                @endif
                            </div>
                            <p class="small text-secondary mb-2 line-clamp-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $notification->pesan }}
                            </p>
                            <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 10px;">
                                <i class="bi bi-clock"></i>
                                <span>{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="text-center py-5 opacity-50">
                <i class="bi bi-bell-slash h1"></i>
                <div class="small mt-2">Belum ada notifikasi baru.</div>
            </div>
        @endforelse
    </div>
</main>
@endsection
