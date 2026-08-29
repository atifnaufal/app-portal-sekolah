@extends('layouts.mobile-app')

@section('content')
<style>
    .member-hero {
        background: var(--grad-primary);
        border-radius: var(--radius-lg); padding: 22px; color: #fff; position: relative; overflow: hidden;
    }
    .member-hero::after {
        content: ''; position: absolute; top: -24px; right: -24px;
        width: 110px; height: 110px; border-radius: 26px;
        background: rgba(255,255,255,0.14); transform: rotate(20deg);
    }
    .member-card { padding: 14px 16px; }
    .member-card.admin {
        border: 1px solid #c7d2fe; background: linear-gradient(180deg, #f8fafc, #eef2ff);
    }
    .avatar {
        width: 44px; height: 44px; border-radius: 14px;
        background: var(--surface); color: var(--indigo); font-weight: 800; font-size: 16px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; overflow: hidden;
    }
    .avatar img { width: 100%; height: 100%; object-fit: cover; }
    .btn-action {
        width: 36px; height: 36px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        border: none; cursor: pointer; transition: all 0.2s;
    }
    .section-title {
        font-size: 12px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.06em; display: flex; align-items: center; justify-content: space-between;
        color: var(--ink);
    }
</style>

<div class="pui-topbar" style="padding-top:16px;">
    <a href="{{ route('eskul.index') }}" class="back"><i class="bi bi-chevron-left"></i></a>
    <h1>Kelola Member</h1>
    <div class="spacer"></div>
</div>

<main class="mobile-content px-3">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-3">{{ session('error') }}</div>
    @endif

    <div class="member-hero mb-4">
        <div class="d-flex justify-content-between align-items-start position-relative">
            <div>
                <div style="font-size: 11px; color: rgba(255,255,255,0.75); font-weight: 700; letter-spacing: 0.05em;">EKSTRAKURIKULER</div>
                <h5 class="fw-bold mt-1 mb-1 text-white">{{ $eskul->nama }}</h5>
                @php
                    $approved = $members->where('status', 'approved');
                    $pending = $members->where('status', 'pending');
                    $admins = $approved->where('is_admin', true);
                @endphp
                <div class="d-flex gap-2 mt-2">
                    <span class="pui-chip" style="background: rgba(255,255,255,0.18); color:#fff; border:1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-people-fill"></i> {{ $approved->count() }} Anggota
                    </span>
                    @if($admins->count() > 0)
                        <span class="pui-chip" style="background:#0f172a; color:#fff; border:none;">
                            <i class="bi bi-shield-fill-check"></i> {{ $admins->count() }} Admin
                        </span>
                    @endif
                </div>
            </div>
            @php
                $eskulChat = \App\Models\ChatGroup::where('type', 'eskul')->where('related_id', $eskul->id)->first();
            @endphp
            @if($eskulChat)
                <a href="{{ route('chat.show', $eskulChat) }}" class="pui-btn pui-btn-ghost pui-btn-sm pui-btn-round text-primary" style="font-size: 11px; background:#fff;">
                    <i class="bi bi-chat-dots-fill me-1"></i> Chat
                </a>
            @endif
        </div>
    </div>

    {{-- Admin Eskul --}}
    @if($admins->count() > 0)
        <div class="section-title mb-3">
            <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-shield-fill-check" style="color:var(--blue);"></i> Admin Eskul</span>
            <span class="pui-chip" style="background:var(--navy); color:#fff; padding:2px 9px; font-size:10px;">{{ $admins->count() }}</span>
        </div>
        @foreach($admins as $m)
            <div class="member-card pui-card admin mb-3">
                <div class="avatar">
                    @if($m->user->foto)
                        <img src="{{ asset('storage/'.$m->user->foto) }}" data-name="{{ $m->user->name }}" onerror="avatarFallback(this);">
                    @else
                        {{ strtoupper(substr($m->user->name, 0, 1)) }}
                    @endif
                </div>
                <div class="flex-grow-1 min-width-0">
                    <div class="fw-bold text-truncate" style="font-size: 14px; color:var(--ink);">{{ $m->user->name }}
                        <span class="ms-1 align-middle pui-chip" style="background:var(--navy); color:#fff; padding:1px 7px; font-size:8px;"><i class="bi bi-check-circle-fill"></i> Admin</span>
                    </div>
                    <div class="small" style="color:var(--mist);">{{ $m->user->kelas?->nama ?? ($m->user->role === 'guru' ? 'Guru / Pembina' : 'Anggota') }}</div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Permohonan Gabung --}}
    @if($pending->count() > 0)
        <div class="section-title mt-4 mb-3 text-warning">
            <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-hourglass-split"></i> Permohonan Gabung</span>
            <span class="pui-chip pui-chip-amber" style="padding:2px 9px; font-size:10px;">{{ $pending->count() }}</span>
        </div>
        @foreach($pending as $m)
            <div class="member-card pui-card mb-3">
                <div class="avatar">
                    @if($m->user->foto)
                        <img src="{{ asset('storage/'.$m->user->foto) }}" data-name="{{ $m->user->name }}" onerror="avatarFallback(this);">
                    @else
                        {{ strtoupper(substr($m->user->name, 0, 1)) }}
                    @endif
                </div>
                <div class="flex-grow-1 min-width-0">
                    <div class="fw-bold text-truncate" style="font-size: 14px; color:var(--ink);">{{ $m->user->name }}</div>
                    <div class="small" style="color:var(--mist);">{{ $m->user->kelas?->nama ?? 'Umum' }}</div>
                </div>
                <div class="d-flex gap-2">
                    <form action="{{ route('eskul.members.approve', $m) }}" method="POST">
                        @csrf
                        <button class="btn-action" style="background:#059669; color:#fff;" title="Setujui"><i class="bi bi-check-lg"></i></button>
                    </form>
                    <form action="{{ route('eskul.members.reject', $m) }}" method="POST">
                        @csrf
                        <button class="btn-action" style="background:#fef2f2; color:#dc2626;" title="Tolak"><i class="bi bi-x-lg"></i></button>
                    </form>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Daftar Anggota (non-admin approved) --}}
    @php $regularMembers = $approved->where('is_admin', false); @endphp
    <div class="section-title mt-4 mb-3">
        <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-people-fill" style="color:var(--blue);"></i> Daftar Anggota</span>
        <span class="pui-chip pui-chip-primary" style="padding:2px 9px; font-size:10px;">{{ $regularMembers->count() }}</span>
    </div>
    @forelse($regularMembers as $m)
        <div class="member-card pui-card mb-3">
            <div class="avatar">
                @if($m->user->foto)
                    <img src="{{ asset('storage/'.$m->user->foto) }}" data-name="{{ $m->user->name }}" onerror="avatarFallback(this);">
                @else
                    {{ strtoupper(substr($m->user->name, 0, 1)) }}
                @endif
            </div>
            <div class="flex-grow-1 min-width-0">
                <div class="fw-bold text-truncate" style="font-size: 14px; color:var(--ink);">{{ $m->user->name }}</div>
                <div class="small" style="color:var(--mist);">{{ $m->user->kelas?->nama ?? 'Umum' }}</div>
            </div>
            <form action="{{ route('eskul.members.reject', $m) }}" method="POST">
                @csrf
                <button class="btn-action" style="background:#fff; color:var(--faint); border:1px solid var(--line);" title="Keluarkan anggota"
                        onclick="return confirm('Keluarkan anggota ini dari eskul?')"><i class="bi bi-person-x"></i></button>
            </form>
        </div>
    @empty
        <div class="pui-empty">
            <div class="ico"><i class="bi bi-people"></i></div>
            <h4>Belum ada anggota</h4>
            <p>Belum ada siswa yang bergabung ke eskul ini.</p>
        </div>
    @endforelse
</main>

<script>
function avatarFallback(el) {
    var name = el.getAttribute('data-name') || 'U';
    var letter = (name.charAt(0) || 'U').toUpperCase();
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
        + '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
        + '<stop offset="0%" stop-color="#6366f1"/><stop offset="100%" stop-color="#6366f1"/>'
        + '</linearGradient></defs>'
        + '<rect width="100%" height="100%" fill="url(#g)"/>'
        + '<text x="50%" y="54%" font-family="sans-serif" font-size="48" font-weight="800" fill="#fff" text-anchor="middle" dominant-baseline="middle">' + letter + '</text></svg>';
    el.onerror = null;
    el.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
}
</script>
@endsection
