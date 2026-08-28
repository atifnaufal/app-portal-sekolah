@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .chat-app { min-height: 100vh; background: #f0f2f5; display: flex; flex-direction: column; }
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.88); backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 64px; flex: 1; }
    .inbox-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
        padding: 28px 24px 32px; color: #fff; position: relative; overflow: hidden;
    }
    .inbox-hero::after {
        content: ''; position: absolute; top: -30px; right: -20px;
        width: 130px; height: 130px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36,107,254,.25) 0%, transparent 70%);
    }
    .search-box {
        background: #fff; border-radius: 18px; display: flex; align-items: center;
        gap: 10px; padding: 4px 16px; box-shadow: 0 6px 18px rgba(15,23,42,.08);
    }
    .search-box input { border: 0; outline: 0; width: 100%; padding: 10px 0; font-size: 14px; background: transparent; }
    .search-box input::placeholder { color: #94a3b8; }
    .section-label {
        padding: 16px 20px 8px; display: flex; align-items: center; gap: 8px;
        font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .12em;
        color: #64748b;
    }
    .section-label .pill {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 20px; height: 20px; border-radius: 8px; font-size: 10px;
        padding: 0 5px; color: #fff;
    }
    .section-label::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
    .group-item {
        display: flex; align-items: center; gap: 14px; padding: 13px 20px;
        background: #fff; text-decoration: none; color: inherit;
        border-bottom: 1px solid #f1f5f9; transition: background .15s;
    }
    .group-item:active { background: #f8fafc; }
    .group-avatar {
        width: 50px; height: 50px; border-radius: 16px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 19px; overflow: hidden;
    }
    .group-avatar.school { background: linear-gradient(135deg, #0088cc, #00aaff); }
    .group-avatar.class { background: linear-gradient(135deg, #10b981, #059669); }
    .group-avatar.eskul { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .group-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .group-info { flex: 1; min-width: 0; }
    .group-name { font-weight: 800; font-size: 15px; color: #0f172a; }
    .group-last-msg { font-size: 13px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .group-time { font-size: 11px; color: #94a3b8; font-weight: 600; flex-shrink: 0; }
    .chev {
        width: 26px; height: 26px; border-radius: 50%; background: #f8fafc;
        display: flex; align-items: center; justify-content: center; color: #94a3b8; flex-shrink: 0;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="chat-app">
    <div class="page-header">
        <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="bi bi-chevron-left h5 mb-0"></i>
        </a>
        <div class="fw-bold" style="font-size: 18px; letter-spacing: -0.4px;">Pesan Grup</div>
    </div>

    <div class="page-container">
        <header class="inbox-hero">
            <div class="eyebrow" style="color: rgba(255,255,255,.6);">KOMUNITAS</div>
            <h1 class="hero-title mt-2 text-white" style="font-size: 24px;">Chat Grup</h1>
            <p class="mb-0 mt-1" style="font-size: 12px; color: rgba(255,255,255,.7);">
                Pilih grup kelas atau eskul untuk mulai mengobrol.
            </p>
            <div class="mt-3 search-box">
                <i class="bi bi-search text-muted"></i>
                <input type="text" id="searchChat" placeholder="Cari percakapan...">
            </div>
        </header>

        <main>
            {{-- Grup Kelas --}}
            <div class="section-label">
                <i class="bi bi-person-lines-fill" style="color: var(--blue);"></i> Grup Kelas
                <span class="pill" style="background: #2563eb;">{{ $classGroups->count() }}</span>
            </div>
            @forelse($classGroups as $g)
                <a href="{{ route('chat.show', $g) }}" class="group-item chat-row" data-name="{{ strtolower($g->name) }}" style="animation: fadeIn .3s ease both;">
                    <div class="group-avatar {{ $g->type }}">
                        @if($g->avatar)
                            <img src="{{ asset('storage/'.$g->avatar) }}">
                        @else
                            {{ strtoupper(substr($g->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="group-info">
                        <div class="group-name text-truncate">{{ $g->name }}</div>
                        <div class="group-last-msg">
                            @if($g->lastMessage)
                                <span class="fw-bold text-dark">{{ $g->lastMessage->user_id === $user->id ? 'Anda: ' : (explode(' ', $g->lastMessage->user->name)[0] . ': ') }}</span>
                                {{ $g->lastMessage->pesan }}
                            @else
                                <span class="fst-italic opacity-50">Belum ada pesan baru</span>
                            @endif
                        </div>
                    </div>
                    <div class="group-time">
                        {{ $g->lastMessage ? $g->lastMessage->created_at->format('H:i') : '' }}
                    </div>
                    <div class="chev"><i class="bi bi-chevron-right" style="font-size: 12px;"></i></div>
                </a>
            @empty
                <div class="text-center py-4 opacity-40">
                    <i class="bi bi-people" style="font-size: 40px;"></i>
                    <div class="fw-bold mt-1 small">Belum ada grup kelas</div>
                </div>
            @endforelse

            {{-- Grup Eskul --}}
            <div class="section-label">
                <i class="bi bi-flag-fill" style="color: #8b5cf6;"></i> Grup Eskul
                <span class="pill" style="background: #8b5cf6;">{{ $eskulGroups->count() }}</span>
            </div>
            @forelse($eskulGroups as $g)
                <a href="{{ route('chat.show', $g) }}" class="group-item chat-row" data-name="{{ strtolower($g->name) }}" style="animation: fadeIn .3s ease both;">
                    <div class="group-avatar eskul">
                        @if($g->avatar)
                            <img src="{{ asset('storage/'.$g->avatar) }}">
                        @else
                            {{ strtoupper(substr($g->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="group-info">
                        <div class="group-name text-truncate">{{ $g->name }}</div>
                        <div class="group-last-msg">
                            @if($g->lastMessage)
                                <span class="fw-bold text-dark">{{ $g->lastMessage->user_id === $user->id ? 'Anda: ' : (explode(' ', $g->lastMessage->user->name)[0] . ': ') }}</span>
                                {{ $g->lastMessage->pesan }}
                            @else
                                <span class="fst-italic opacity-50">Belum ada pesan baru</span>
                            @endif
                        </div>
                    </div>
                    <div class="group-time">
                        {{ $g->lastMessage ? $g->lastMessage->created_at->format('H:i') : '' }}
                    </div>
                    <div class="chev"><i class="bi bi-chevron-right" style="font-size: 12px;"></i></div>
                </a>
            @empty
                <div class="text-center py-4 opacity-40">
                    <i class="bi bi-flag" style="font-size: 40px;"></i>
                    <div class="fw-bold mt-1 small">Belum ada grup eskul</div>
                </div>
            @endforelse
        </main>
    </div>
</div>

<script>
    const searchInput = document.getElementById('searchChat');
    const rows = document.querySelectorAll('.chat-row');
    searchInput.addEventListener('input', function () {
        const q = (this.value || '').toLowerCase().trim();
        rows.forEach(r => {
            r.style.display = r.dataset.name.includes(q) ? '' : 'none';
        });
    });
</script>
@endsection
