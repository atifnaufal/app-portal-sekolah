@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .chat-app { min-height: 100vh; background: var(--surface); display: flex; flex-direction: column; }
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.88); backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--line-strong);
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 64px; flex: 1; }
    .inbox-hero {
        background: var(--grad-hero);
        padding: 28px 24px 32px; color: #fff; position: relative; overflow: hidden;
    }
    .inbox-hero::after {
        content: ''; position: absolute; top: -30px; right: -20px;
        width: 130px; height: 130px; border-radius: 50%;
        background: radial-gradient(circle, rgba(99,102,241,.25) 0%, transparent 70%);
    }
    .search-box {
        background: #fff; border-radius: var(--radius-sm); display: flex; align-items: center;
        gap: 10px; padding: 4px 16px; box-shadow: var(--shadow-card);
    }
    .search-box input { border: 0; outline: 0; width: 100%; padding: 10px 0; font-size: 14px; background: transparent; color: var(--ink); }
    .search-box input::placeholder { color: var(--faint); }
    .section-label {
        padding: 16px 20px 8px; display: flex; align-items: center; gap: 8px;
        font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .12em;
        color: var(--mist);
    }
    .section-label .pill {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 20px; height: 20px; border-radius: 8px; font-size: 10px;
        padding: 0 5px; color: #fff; background: var(--blue);
    }
    .section-label::after { content: ''; flex: 1; height: 1px; background: var(--line-strong); }
    .group-item {
        display: flex; align-items: center; gap: 14px; padding: 13px 20px;
        background: var(--surface-card); text-decoration: none; color: inherit;
        border-bottom: 1px solid var(--line); transition: background .15s;
    }
    .group-item:active { background: var(--surface); }
    .group-avatar {
        width: 50px; height: 50px; border-radius: var(--radius-sm); flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 19px; overflow: hidden;
    }
    .group-avatar.school { background: linear-gradient(135deg, #0088cc, #00aaff); }
    .group-avatar.class { background: linear-gradient(135deg, #10b981, #059669); }
    .group-avatar.eskul { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .group-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .group-info { flex: 1; min-width: 0; }
    .group-name { font-weight: 800; font-size: 15px; color: var(--ink); }
    .group-last-msg { font-size: 13px; color: var(--mist); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .group-time { font-size: 11px; color: var(--faint); font-weight: 600; flex-shrink: 0; }
    .chev {
        width: 26px; height: 26px; border-radius: 50%; background: var(--surface);
        display: flex; align-items: center; justify-content: center; color: var(--faint); flex-shrink: 0;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="chat-app">
    <div class="pui-topbar mt-2">
        <a href="{{ route('dashboard') }}" class="back"><i class="bi bi-chevron-left"></i> Beranda</a>
        <h1 class="spacer">Pesan Grup</h1>
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
                <span class="pill">{{ $classGroups->count() }}</span>
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
                <div class="pui-empty">
                    <i class="bi bi-people ico"></i>
                    <h4>Belum ada grup kelas</h4>
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
                <div class="pui-empty">
                    <i class="bi bi-flag ico"></i>
                    <h4>Belum ada grup eskul</h4>
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
