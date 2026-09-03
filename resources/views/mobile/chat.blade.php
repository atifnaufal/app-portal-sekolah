@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .chat-app { min-height: 100vh; background: #f8fafc; display: flex; flex-direction: column; }

    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.8); backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--line);
        padding: 10px 16px; padding-top: calc(10px + env(safe-area-inset-top));
        display: flex; align-items: center; justify-content: space-between;
    }
    .back-btn {
        width: 38px; height: 38px; border-radius: 12px; background: var(--surface);
        display: flex; align-items: center; justify-content: center;
        color: var(--ink); text-decoration: none;
    }

    .page-container { padding-top: 76px; padding-bottom: 100px; }

    .search-bar-wrap { margin: 0 16px 8px; }
    .search-box {
        background: #fff; border-radius: 16px; display: flex; align-items: center;
        gap: 10px; padding: 4px 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        border: 1px solid var(--line);
    }
    .search-box input { border: 0; outline: 0; width: 100%; padding: 10px 0; font-size: 14px; background: transparent; color: var(--ink); }
    .search-box input::placeholder { color: #94a3b8; }

    .header-block {
        background: var(--grad-hero);
        padding: 24px 20px 32px; color: #fff; position: relative; overflow: hidden;
        border-radius: var(--radius-lg); margin: 0 16px 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
    }
    .header-block::after {
        content: ''; position: absolute; top: -40px; right: -30px;
        width: 160px; height: 160px; border-radius: 50%;
        background: radial-gradient(circle, rgba(99,102,241,.3) 0%, transparent 70%);
    }

    .header-title {
        padding: 24px 20px 12px; display: flex; align-items: center; gap: 10px;
        font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em;
        color: #94a3b8;
    }
    .header-title i { font-size: 16px; }

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

    .cg-fab {
        position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
        width: 40px; height: 40px; border-radius: 14px; background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 20px; text-decoration: none; box-shadow: 0 8px 16px rgba(99,102,241,.35);
    }
    .invite-card {
        margin: 0 16px 12px; background: linear-gradient(135deg, #eef2ff, #f5f3ff);
        border-radius: 20px; padding: 14px 16px; border: 1px solid #e0e7ff;
        display: flex; align-items: center; gap: 14px;
    }
    .invite-card .ic-icon {
        width: 44px; height: 44px; border-radius: 14px; flex-shrink: 0;
        background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .invite-card .ic-actions { display: flex; gap: 8px; margin-left: auto; flex-shrink: 0; }
    .ic-btn {
        border: 0; border-radius: 12px; padding: 8px 14px; font-size: 12px; font-weight: 800;
        color: #fff; cursor: pointer;
    }
    .ic-btn.accept { background: #22c55e; }
    .ic-btn.reject { background: #cbd5e1; color: #475569; }
</style>

<div class="chat-app">
    <div class="page-header">
        <a href="{{ route('dashboard') }}" class="back-btn">
            <i class="bi bi-chevron-left"></i>
        </a>
        <div style="font-weight: 800; font-size: 16px; color: var(--ink);">Pesan</div>
        <a href="{{ route('chat.create') }}" class="cg-fab" title="Buat grup baru"><i class="bi bi-person-plus-fill"></i></a>
    </div>

    <div class="page-container">
        <header class="header-block">
            <div style="font-size: 10px; font-weight: 700; letter-spacing: 0.1em; color: rgba(255,255,255,0.6); text-transform: uppercase;">Message Center</div>
            <h1 class="mt-2 text-white" style="font-size: 24px; font-weight: 900; letter-spacing: -0.02em;">Percakapan</h1>
            <p class="mb-0 mt-1" style="font-size: 13px; color: rgba(255,255,255,0.8); font-weight: 500;">
                Terhubung dengan warga sekolah.
            </p>
        </header>
        <div class="search-bar-wrap">
            <div class="search-box">
                <i class="bi bi-search" style="color: #94a3b8;"></i>
                <input type="text" id="searchChat" placeholder="Cari teman atau grup...">
            </div>
        </div>

        <main>
            {{-- Undangan grup pending --}}
            @if(isset($pendingInvites) && $pendingInvites->count() > 0)
                <div class="section-label animate-up" style="animation-delay: 0.05s;">
                    <i class="bi bi-envelope-plus-fill" style="color: #6366f1;"></i> Undangan Masuk
                </div>
                @foreach($pendingInvites as $pinv)
                    <div class="invite-card animate-up">
                        <div class="ic-icon"><i class="bi bi-people-fill"></i></div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:800; font-size:14px; color:var(--navy);">{{ $pinv->name }}</div>
                            <div style="font-size:11px; color:#64748b; font-weight:600;">
                                {{ $pinv->approvedMembers->count() }} anggota · diundang {{ $pinv->owner?->name ?? 'seseorang' }}
                            </div>
                        </div>
                        <div class="ic-actions">
                            <button class="ic-btn accept" onclick="acceptInvite({{ $pinv->id }}, this)"><i class="bi bi-check-lg"></i> Terima</button>
                            <button class="ic-btn reject" onclick="rejectInvite({{ $pinv->id }}, this)"><i class="bi bi-x-lg"></i></button>
                        </div>
                    </div>
                @endforeach
            @endif

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
                        <div class="chat-avatar" style="background: #f1f5f9;position:relative;">
                            <img src="{{ $avatar }}">
                            @if($other && $other->isOnline())
                                <span style="position:absolute;bottom:2px;right:2px;width:12px;height:12px;background:#22c55e;border:2px solid #fff;border-radius:50%;"></span>
                            @endif
                        </div>
                        <div class="chat-info">
                            <div class="chat-name text-truncate" style="display:flex;gap:6px;align-items:center">{{ $name }} @if($other && $other->isOnline())<span style="font-size:9px;padding:2px 6px;border-radius:999px;background:#dcfce7;color:#166534;font-weight:800">ONLINE</span>@endif</div>
                            <div class="chat-msg text-truncate" style="font-size:11px;color:{{ ($other && $other->isOnline())?'#22c55e':'#94a3b8' }};font-weight:600">{{ $other ? $other->last_seen : '' }}</div>
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
                            @if(isset($unreadMap[$g->id]) && $unreadMap[$g->id] > 0)
                                <div class="unread-badge">{{ $unreadMap[$g->id] }}</div>
                            @endif
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
                            @if(isset($unreadMap[$g->id]) && $unreadMap[$g->id] > 0)
                                <div class="unread-badge">{{ $unreadMap[$g->id] }}</div>
                            @endif
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
                            @if(isset($unreadMap[$g->id]) && $unreadMap[$g->id] > 0)
                                <div class="unread-badge">{{ $unreadMap[$g->id] }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            @endif
            {{-- Section Grup Custom (fitur WhatsApp-like) --}}
            @if(isset($customGroups) && $customGroups->count() > 0)
                <div class="section-label animate-up" style="animation-delay: 0.32s;">
                    <i class="bi bi-people-fill" style="color: #6366f1;"></i> Grup Saya
                </div>
                @foreach($customGroups as $g)
                    <a href="{{ route('chat.show', $g) }}" class="chat-card chat-row animate-up" data-name="{{ strtolower($g->name) }}" style="animation-delay: 0.34s;">
                        <div class="chat-avatar" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                            @if($g->avatar)
                                <img src="{{ asset('storage/'.$g->avatar) }}">
                            @else
                                {{ strtoupper(substr($g->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="chat-info">
                            <div class="chat-name text-truncate">{{ $g->name }}</div>
                            <div class="chat-msg text-truncate">
                                @if($g->lastMessage && $g->lastMessage->user_id === $user->id)
                                    <span style="font-weight: 700; color: var(--navy);">Anda: </span>@if($g->lastMessage->deleted_at) Pesan dihapus @else{{ $g->lastMessage->pesan }}@endif
                                @elseif($g->lastMessage)
                                    <span style="font-weight: 700; color: var(--navy);">{{ explode(' ', $g->lastMessage->user->name)[0].': ' }}</span>@if($g->lastMessage->deleted_at) Pesan dihapus @else{{ $g->lastMessage->pesan }}@endif
                                @else
                                    <span style="opacity: 0.5; font-style: italic;">Grup dibuat</span>
                                @endif
                            </div>
                        </div>
                        <div class="chat-meta">
                            <div class="chat-time">{{ $g->lastMessage ? $g->lastMessage->created_at->format('H:i') : '' }}</div>
                            @if(isset($unreadMap[$g->id]) && $unreadMap[$g->id] > 0)
                                <div class="unread-badge">{{ $unreadMap[$g->id] }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            @endif
        </main>
    </div>
</div>

<script>
    (function(){
        var searchInput = document.getElementById('searchChat');
        var rows = document.querySelectorAll('.chat-row');
        if(!searchInput) return;
        searchInput.addEventListener('input', function () {
            var q = (this.value || '').toLowerCase().trim();
            rows.forEach(function(r){
                var n=(r.dataset.name||'').toLowerCase();
                r.style.display = n.includes(q) ? 'flex' : 'none';
            });
        });
    })();

    function acceptInvite(groupId, btn) {
        if (btn) { btn.disabled = true; }
        fetch('/chat/' + groupId + '/accept', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
            .then(r => r.json())
            .then(d => { if (d.ok) { window.location = '/chat/' + groupId; } else { alert('Gagal menerima undangan'); btn.disabled = false; } })
            .catch(() => { alert('Terjadi kesalahan'); btn.disabled = false; });
    }
    function rejectInvite(groupId, btn) {
        if (btn) { btn.disabled = true; }
        fetch('/chat/' + groupId + '/reject', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
            .then(r => r.json())
            .then(d => { if (d.ok) { const card = btn.closest('.invite-card'); if (card) card.style.display = 'none'; } else { btn.disabled = false; } })
            .catch(() => { btn.disabled = false; });
    }
</script>
@endsection
