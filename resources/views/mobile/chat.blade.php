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
    .chat-name { font-weight: 800; font-size: 16px; color: var(--navy); margin-bottom: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .chat-msg { font-size: 13px; color: #64748b; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .chat-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; }
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
        width: 40px; height: 40px; border-radius: 14px; background: #e2e8f0;
        color: #64748b; display: flex; align-items: center; justify-content: center;
        font-size: 20px; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transition: all 0.2s;
    }
    .cg-fab:active { transform: translateY(-50%) scale(0.9); background: #cbd5e1; }
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

    .empty-search {
        text-align: center; padding: 40px 20px; color: #94a3b8; display: none;
    }
    .empty-search i { font-size: 40px; margin-bottom: 12px; display: block; }
    .empty-search .es-text { font-size: 14px; font-weight: 700; }

    .ptr-indicator {
        position: fixed; top: 70px; left: 50%; transform: translateX(-50%);
        background: #fff; border-radius: 20px; padding: 8px 18px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12); font-size: 12px; font-weight: 700;
        color: var(--blue); display: none; z-index: 999;
        animation: ptrPulse 1s ease infinite;
    }
    @keyframes ptrPulse { 0%,100%{opacity:1;} 50%{opacity:0.5;} }
</style>

<div class="chat-app">
    <div class="page-header">
        <a href="{{ route('dashboard') }}" class="back-btn">
            <i class="bi bi-chevron-left"></i>
        </a>
        <div style="font-weight: 800; font-size: 16px; color: var(--ink);">Pesan</div>
        <a href="{{ route('chat.create') }}" class="cg-fab" title="Buat grup baru"><i class="bi bi-person-plus-fill"></i></a>
    </div>

    <div class="ptr-indicator" id="ptrIndicator"><i class="bi bi-arrow-clockwise me-1"></i> Memuat ulang...</div>

    <div class="page-container" id="pageContainer">
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

        <div class="empty-search" id="emptySearch">
            <i class="bi bi-search"></i>
            <div class="es-text">Tidak ditemukan</div>
        </div>

        <main id="chatListMain">
            {{-- Undangan grup pending --}}
            @if(isset($pendingInvites) && $pendingInvites->count() > 0)
                <div class="section-label animate-up invite-section" style="animation-delay: 0.05s;">
                    <i class="bi bi-envelope-plus-fill" style="color: #6366f1;"></i> Undangan Masuk
                </div>
                @foreach($pendingInvites as $pinv)
                    <div class="invite-card animate-up invite-item" data-search="{{ strtolower($pinv->name) }}">
                        <div class="ic-icon"><i class="bi bi-people-fill"></i></div>
                        <div style="flex:1; min-width:0;">
                            <div class="chat-name" style="font-size:14px;">{{ $pinv->name }}</div>
                            <div style="font-size:11px; color:#64748b; font-weight:600;">
                                {{ $pinv->approvedMembers->count() }} anggota · diundang {{ $pinv->owner?->name ?? 'seseorang' }}
                            </div>
                        </div>
                        <div class="ic-actions">
                            <button type="button" class="ic-btn accept" onclick="acceptInvite({{ $pinv->id }}, this)"><i class="bi bi-check-lg"></i> Terima</button>
                            <button type="button" class="ic-btn reject" onclick="rejectInvite({{ $pinv->id }}, this)"><i class="bi bi-x-lg"></i></button>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Section Chat Pribadi --}}
            @if($privateGroups->count() > 0)
                <div class="section-label animate-up private-section" style="animation-delay: 0.1s;">
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
                            <div class="chat-name" style="display:flex;gap:6px;align-items:center">
                                {{ $name }}
                                @if($other && $other->isOnline())<span style="font-size:9px;padding:2px 6px;border-radius:999px;background:#dcfce7;color:#166534;font-weight:800">ONLINE</span>@endif
                            </div>
                            <div class="chat-msg" style="font-size:11px;color:{{ ($other && $other->isOnline())?'#22c55e':'#94a3b8' }};font-weight:600">{{ $other ? $other->last_seen : '' }}</div>
                            <div class="chat-msg">
                                @if($g->lastMessage)
                                    @if($g->lastMessage->deleted_at)
                                        <span style="font-style:italic;opacity:0.7;">Pesan dihapus</span>
                                    @else
                                        {{ $g->lastMessage->user_id === $user->id ? 'Anda: ' : '' }}{{ $g->lastMessage->pesan }}
                                    @endif
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
            <div class="section-label animate-up class-section" style="animation-delay: 0.2s;">
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
                        <div class="chat-name">{{ $g->name }}</div>
                        <div class="chat-msg">
                            @if($g->lastMessage)
                                @if($g->lastMessage->deleted_at)
                                    <span style="font-style:italic;opacity:0.7;">Pesan dihapus</span>
                                @else
                                    <span style="font-weight: 700; color: var(--navy);">{{ $g->lastMessage->user_id === $user->id ? 'Anda: ' : explode(' ', $g->lastMessage->user->name)[0].': ' }}</span>{{ $g->lastMessage->pesan }}
                                @endif
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
                <div class="section-label animate-up eskul-section" style="animation-delay: 0.3s;">
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
                            <div class="chat-name">{{ $g->name }}</div>
                            <div class="chat-msg">
                                @if($g->lastMessage)
                                    @if($g->lastMessage->deleted_at)
                                        <span style="font-style:italic;opacity:0.7;">Pesan dihapus</span>
                                    @else
                                        <span style="font-weight: 700; color: var(--navy);">{{ $g->lastMessage->user_id === $user->id ? 'Anda: ' : explode(' ', $g->lastMessage->user->name)[0].': ' }}</span>{{ $g->lastMessage->pesan }}
                                    @endif
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

            {{-- Section Grup Custom --}}
            @if(isset($customGroups) && $customGroups->count() > 0)
                <div class="section-label animate-up custom-section" style="animation-delay: 0.32s;">
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
                            <div class="chat-name">{{ $g->name }}</div>
                            <div class="chat-msg">
                                @if($g->lastMessage)
                                    @if($g->lastMessage->deleted_at)
                                        <span style="font-style:italic;opacity:0.7;">Pesan dihapus</span>
                                    @else
                                        <span style="font-weight: 700; color: var(--navy);">{{ $g->lastMessage->user_id === $user->id ? 'Anda: ' : explode(' ', $g->lastMessage->user->name)[0].': ' }}</span>{{ $g->lastMessage->pesan }}
                                    @endif
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
    var chatRows = document.querySelectorAll('.chat-row');
    var sectionLabels = document.querySelectorAll('.section-label');
    var inviteItems = document.querySelectorAll('.invite-item');
    var inviteSections = document.querySelectorAll('.invite-section');
    var emptySearch = document.getElementById('emptySearch');
    if(!searchInput) return;

    searchInput.addEventListener('input', function () {
        var q = (this.value || '').toLowerCase().trim();
        var visibleCount = 0;

        chatRows.forEach(function(r){
            var n = (r.dataset.name || '').toLowerCase();
            var show = q === '' || n.includes(q);
            r.style.display = show ? 'flex' : 'none';
            if(show) visibleCount++;
        });

        inviteItems.forEach(function(r){
            var n = (r.dataset.search || '').toLowerCase();
            var show = q === '' || n.includes(q);
            r.style.display = show ? 'flex' : 'none';
            if(show) visibleCount++;
        });

        sectionLabels.forEach(function(s){
            if(q === '') { s.style.display = ''; return; }
            var next = s.nextElementSibling;
            var hasVisible = false;
            while(next && !next.classList.contains('section-label')){
                if(next.style.display !== 'none') hasVisible = true;
                next = next.nextElementSibling;
            }
            s.style.display = hasVisible ? '' : 'none';
        });

        inviteSections.forEach(function(s){
            if(q === '') { s.style.display = ''; return; }
            var next = s.nextElementSibling;
            var hasVisible = false;
            while(next && !next.classList.contains('section-label')){
                if(next.style.display !== 'none') hasVisible = true;
                next = next.nextElementSibling;
            }
            s.style.display = hasVisible ? '' : 'none';
        });

        if(emptySearch) emptySearch.style.display = (q !== '' && visibleCount === 0) ? 'block' : 'none';
    });

    // Pull-to-refresh
    var ptrIndicator = document.getElementById('ptrIndicator');
    var pageContainer = document.getElementById('pageContainer');
    var startY = 0;
    var pulling = false;
    if(pageContainer) {
        pageContainer.addEventListener('touchstart', function(e){
            if(window.scrollY === 0 && e.touches.length === 1) {
                startY = e.touches[0].clientY;
                pulling = true;
            }
        }, {passive:true});
        pageContainer.addEventListener('touchmove', function(e){
            if(!pulling) return;
            var diff = e.touches[0].clientY - startY;
            if(diff > 60 && window.scrollY === 0) {
                if(ptrIndicator) ptrIndicator.style.display = 'block';
            }
        }, {passive:true});
        pageContainer.addEventListener('touchend', function(){
            if(ptrIndicator && ptrIndicator.style.display === 'block') {
                setTimeout(function(){ window.location.reload(); }, 600);
            }
            pulling = false;
        }, {passive:true});
    }
})();

function acceptInvite(groupId, btn) {
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i>'; }
    fetch('/chat/' + groupId + '/accept', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
        .then(function(r){ return r.json(); })
        .then(function(d){ if (d.ok) { window.location = '/chat/' + groupId; } else { alert('Gagal menerima undangan'); if(btn){ btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg"></i> Terima'; } } })
        .catch(function(){ alert('Terjadi kesalahan'); if(btn){ btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg"></i> Terima'; } });
}
function rejectInvite(groupId, btn) {
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-arrow-repeat"></i>'; }
    fetch('/chat/' + groupId + '/reject', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
        .then(function(r){ return r.json(); })
        .then(function(d){ if (d.ok) { var card = btn.closest('.invite-card'); if (card) { card.style.transition = 'all 0.3s'; card.style.opacity = '0'; card.style.transform = 'translateX(40px)'; setTimeout(function(){ card.style.display = 'none'; }, 300); } } else { btn.disabled = false; btn.innerHTML = '<i class="bi bi-x-lg"></i>'; } })
        .catch(function(){ btn.disabled = false; btn.innerHTML = '<i class="bi bi-x-lg"></i>'; });
}
</script>
<style>
@keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
.spin { animation: spin 0.8s linear infinite; }
</style>
@endsection
