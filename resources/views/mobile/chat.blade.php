@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .chat-app { min-height: 100vh; background: #f8fafc; display: flex; flex-direction: column; }

    .page-container { padding-bottom: 100px; }

    .inbox-hero {
        background: var(--grad-hero);
        padding: 40px 24px 44px; color: #fff; position: relative; overflow: hidden;
        border-radius: 0 0 40px 40px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
    }
    .inbox-hero::after {
        content: ''; position: absolute; top: -40px; right: -30px;
        width: 160px; height: 160px; border-radius: 50%;
        background: radial-gradient(circle, rgba(99,102,241,.3) 0%, transparent 70%);
    }

    .search-bar-wrap { margin-top: -22px; padding: 0 20px; position: relative; z-index: 10; }
    .search-box {
        background: #fff; border-radius: 20px; display: flex; align-items: center;
        gap: 12px; padding: 4px 18px; box-shadow: 0 10px 25px rgba(0,0,0,0.06);
        border: 1px solid #f1f5f9;
    }
    .search-box input { border: 0; outline: 0; width: 100%; padding: 12px 0; font-size: 14px; background: transparent; color: var(--navy); }

    .section-label {
        padding: 24px 20px 12px; display: flex; align-items: center; gap: 10px;
        font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em;
        color: #94a3b8;
    }
    .section-label i { font-size: 16px; }

    .chat-card {
        margin: 0 16px 12px; background: #fff; border-radius: 24px;
        display: flex; align-items: center; gap: 16px; padding: 16px;
        text-decoration: none; color: inherit;
        border: 1px solid #f1f5f9; box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        transition: all 0.2s;
    }
    .chat-card:active { transform: scale(0.97); background: #f8fafc; }

    .chat-avatar {
        width: 56px; height: 56px; border-radius: 18px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 20px; position: relative;
        box-shadow: 0 8px 16px rgba(0,0,0,0.08);
    }
    .chat-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 18px; }

    .chat-info { flex: 1; min-width: 0; }
    .chat-name { font-weight: 800; font-size: 16px; color: var(--navy); margin-bottom: 2px; }
    .chat-msg { font-size: 13px; color: #64748b; font-weight: 500; }

    .chat-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
    .chat-time { font-size: 11px; color: #94a3b8; font-weight: 700; }
    .unread-badge {
        min-width: 20px; height: 20px; border-radius: 10px; background: var(--blue);
        color: #fff; font-size: 10px; font-weight: 900; display: flex;
        align-items: center; justify-content: center; padding: 0 6px;
    }

    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-up { animation: slideUp 0.4s ease both; }
</style>

<div class="chat-app">
    <div class="pui-topbar" style="background: transparent; border: 0;">
        <a href="{{ route('dashboard') }}" class="back" style="color: #fff;"><i class="bi bi-chevron-left"></i> Beranda</a>
    </div>

    <div class="page-container">
        <header class="inbox-hero">
            <div style="font-size: 12px; font-weight: 700; letter-spacing: 0.1em; color: rgba(255,255,255,0.6);">MESSAGE CENTER</div>
            <h1 class="mt-2 text-white" style="font-size: 28px; font-weight: 900; letter-spacing: -0.02em;">Percakapan</h1>
            <p class="mb-0 mt-1" style="font-size: 14px; color: rgba(255,255,255,0.8); font-weight: 500;">
                Terhubung dengan warga sekolah secara real-time.
            </p>
        </header>

        <div class="search-bar-wrap">
            <div class="search-box">
                <i class="bi bi-search" style="color: #94a3b8;"></i>
                <input type="text" id="searchChat" placeholder="Cari teman atau grup...">
            </div>
        </div>

        <main>
            {{-- Section Chat Pribadi --}}
            @if($privateGroups->count() > 0)
                <div class="section-label animate-up" style="animation-delay: 0.1s;">
                    <i class="bi bi-chat-heart-fill" style="color: #ef4444;"></i> Chat Pribadi
                </div>
                @foreach($privateGroups as $g)
                    @php
                        $other = $g->members->first(fn($m) => $m->id != $user->id);
                        $name = $other ? $other->name : 'User';
                        $avatar = $other ? $other->avatar_url : 'https://ui-avatars.com/api/?name=User&background=random';
                    @endphp
                    <a href="{{ route('chat.show', $g) }}" class="chat-card chat-row animate-up" data-name="{{ strtolower($name) }}" style="animation-delay: 0.15s;">
                        <div class="chat-avatar" style="background: #f1f5f9;">
                            <img src="{{ $avatar }}">
                        </div>
                        <div class="chat-info">
                            <div class="chat-name text-truncate">{{ $name }}</div>
                            <div class="chat-msg text-truncate">
                                @if($g->lastMessage)
                                    {{ $g->lastMessage->user_id === $user->id ? 'Anda: ' : '' }}{{ $g->lastMessage->pesan }}
                                @else
                                    <span style="opacity: 0.5; font-style: italic;">Mulai chat pribadi...</span>
                                @endif
                            </div>
                        </div>
                        <div class="chat-meta">
                            <div class="chat-time">{{ $g->lastMessage ? $g->lastMessage->created_at->format('H:i') : '' }}</div>
                        </div>
                    </a>
                @endforeach
            @endif

            {{-- Section Grup Kelas --}}
            <div class="section-label animate-up" style="animation-delay: 0.2s;">
                <i class="bi bi-mortarboard-fill" style="color: #10b981;"></i> Lingkup Kelas
            </div>
            @forelse($classGroups as $g)
                <a href="{{ route('chat.show', $g) }}" class="chat-card chat-row animate-up" data-name="{{ strtolower($g->name) }}" style="animation-delay: 0.25s;">
                    <div class="chat-avatar" style="background: linear-gradient(135deg, #10b981, #059669);">
                        @if($g->avatar)
                            <img src="{{ asset('storage/'.$g->avatar) }}">
                        @else
                            {{ strtoupper(substr($g->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="chat-info">
                        <div class="chat-name text-truncate">{{ $g->name }}</div>
                        <div class="chat-msg text-truncate">
                            @if($g->lastMessage)
                                <span style="font-weight: 700; color: var(--navy);">{{ $g->lastMessage->user_id === $user->id ? 'Anda: ' : explode(' ', $g->lastMessage->user->name)[0].': ' }}</span>{{ $g->lastMessage->pesan }}
                            @else
                                <span style="opacity: 0.5; font-style: italic;">Grup baru dibuat</span>
                            @endif
                        </div>
                    </div>
                    <div class="chat-meta">
                        <div class="chat-time">{{ $g->lastMessage ? $g->lastMessage->created_at->format('H:i') : '' }}</div>
                    </div>
                </a>
            @empty
                <div class="text-center py-4 text-muted small">Belum ada grup kelas.</div>
            @endforelse

            {{-- Section Grup Eskul --}}
            @if($eskulGroups->count() > 0)
                <div class="section-label animate-up" style="animation-delay: 0.3s;">
                    <i class="bi bi-trophy-fill" style="color: #8b5cf6;"></i> Ekstrakurikuler
                </div>
                @foreach($eskulGroups as $g)
                    <a href="{{ route('chat.show', $g) }}" class="chat-card chat-row animate-up" data-name="{{ strtolower($g->name) }}" style="animation-delay: 0.35s;">
                        <div class="chat-avatar" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                            @if($g->avatar)
                                <img src="{{ asset('storage/'.$g->avatar) }}">
                            @else
                                {{ strtoupper(substr($g->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="chat-info">
                            <div class="chat-name text-truncate">{{ $g->name }}</div>
                            <div class="chat-msg text-truncate">
                                @if($g->lastMessage)
                                    <span style="font-weight: 700; color: var(--navy);">{{ $g->lastMessage->user_id === $user->id ? 'Anda: ' : explode(' ', $g->lastMessage->user->name)[0].': ' }}</span>{{ $g->lastMessage->pesan }}
                                @else
                                    <span style="opacity: 0.5; font-style: italic;">Belum ada aktifitas</span>
                                @endif
                            </div>
                        </div>
                        <div class="chat-meta">
                            <div class="chat-time">{{ $g->lastMessage ? $g->lastMessage->created_at->format('H:i') : '' }}</div>
                        </div>
                    </a>
                @endforeach
            @endif
        </main>
    </div>
</div>

<script>
    const searchInput = document.getElementById('searchChat');
    const rows = document.querySelectorAll('.chat-row');
    searchInput.addEventListener('input', function () {
        const q = (this.value || '').toLowerCase().trim();
        rows.forEach(r => {
            r.style.display = r.dataset.name.includes(q) ? 'flex' : 'none';
        });
    });
</script>
@endsection
