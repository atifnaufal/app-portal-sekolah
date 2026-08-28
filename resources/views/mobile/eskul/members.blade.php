@extends('layouts.mobile-app')

@section('content')
<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.88); backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 70px; padding-bottom: 48px; }

    .member-hero {
        background: linear-gradient(135deg, #7c3aed, #a78bfa);
        border-radius: 24px; padding: 22px; color: #fff; position: relative; overflow: hidden;
    }
    .member-hero::after {
        content: ''; position: absolute; top: -24px; right: -24px;
        width: 110px; height: 110px; border-radius: 26px;
        background: rgba(255,255,255,0.14); transform: rotate(20deg);
    }

    .member-card {
        background: #fff; border-radius: 20px; padding: 14px 16px;
        margin-bottom: 12px; border: 1px solid #f1f5f9;
        display: flex; align-items: center; gap: 12px;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.03);
    }
    .member-card.admin {
        border: 1px solid #c7d2fe; background: linear-gradient(180deg, #f8fafc, #eef2ff);
    }
    .avatar {
        width: 44px; height: 44px; border-radius: 14px;
        background: linear-gradient(135deg, #ede9fe, #e0e7ff);
        color: #6d28d9; font-weight: 800; font-size: 16px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; overflow: hidden;
    }
    .avatar img { width: 100%; height: 100%; object-fit: cover; }

    .admin-chip {
        font-size: 9px; font-weight: 800; padding: 4px 10px; border-radius: 100px;
        background: #0f172a; color: #fff; text-transform: uppercase; letter-spacing: 0.05em;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .section-title {
        font-size: 12px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.06em; display: flex; align-items: center; justify-content: space-between;
    }
    .count-pill {
        font-size: 10px; font-weight: 800; padding: 2px 9px; border-radius: 100px;
    }
    .btn-action {
        width: 36px; height: 36px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        border: none; cursor: pointer; transition: all 0.2s;
    }
    .empty-box { border: 1.5px dashed #e2e8f0; border-radius: 20px; padding: 36px 20px; text-align: center; background: #fafbfc; }
</style>

<div class="page-header">
    <a href="{{ route('eskul.index') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px; letter-spacing: -0.4px;">Kelola Member</div>
</div>

<div class="page-container">
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
                        <span class="limit-chip" style="background: rgba(255,255,255,0.18); color:#fff; border:1px solid rgba(255,255,255,0.25); font-size:11px; font-weight:700; padding:4px 10px; border-radius:100px;">
                            <i class="bi bi-people-fill"></i> {{ $approved->count() }} Anggota
                        </span>
                        @if($admins->count() > 0)
                            <span class="limit-chip" style="background: #0f172a; color:#fff; font-size:11px; font-weight:700; padding:4px 10px; border-radius:100px; border:none;">
                                <i class="bi bi-shield-fill-check"></i> {{ $admins->count() }} Admin
                            </span>
                        @endif
                    </div>
                </div>
                @php
                    $eskulChat = \App\Models\ChatGroup::where('type', 'eskul')->where('related_id', $eskul->id)->first();
                @endphp
                @if($eskulChat)
                    <a href="{{ route('chat.show', $eskulChat) }}" class="btn btn-light btn-sm rounded-pill px-3 fw-bold text-primary" style="font-size: 11px;">
                        <i class="bi bi-chat-dots-fill me-1"></i> Chat
                    </a>
                @endif
            </div>
        </div>

        {{-- Admin Eskul --}}
        @if($admins->count() > 0)
            <div class="section-title mb-3 text-dark">
                <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-shield-fill-check text-primary"></i> Admin Eskul</span>
                <span class="count-pill text-white" style="background:#0f172a;">{{ $admins->count() }}</span>
            </div>
            @foreach($admins as $m)
                <div class="member-card admin">
                    <div class="avatar">
                        @if($m->user->foto)
                            <img src="{{ asset('storage/'.$m->user->foto) }}" data-name="{{ $m->user->name }}" onerror="avatarFallback(this);">
                        @else
                            {{ strtoupper(substr($m->user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-bold text-truncate" style="font-size: 14px;">{{ $m->user->name }}
                            <span class="ms-1 align-middle admin-chip" style="font-size:8px;"><i class="bi bi-check-circle-fill"></i> Admin</span>
                        </div>
                        <div class="small text-muted">{{ $m->user->kelas?->nama ?? ($m->user->role === 'guru' ? 'Guru / Pembina' : 'Anggota') }}</div>
                    </div>
                </div>
            @endforeach
        @endif

        {{-- Permohonan Gabung --}}
        @if($pending->count() > 0)
            <div class="section-title mt-4 mb-3 text-warning">
                <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-hourglass-split"></i> Permohonan Gabung</span>
                <span class="count-pill" style="background:#fef3c7; color:#92400e;">{{ $pending->count() }}</span>
            </div>
            @foreach($pending as $m)
                <div class="member-card">
                    <div class="avatar">
                        @if($m->user->foto)
                            <img src="{{ asset('storage/'.$m->user->foto) }}" data-name="{{ $m->user->name }}" onerror="avatarFallback(this);">
                        @else
                            {{ strtoupper(substr($m->user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-bold text-truncate" style="font-size: 14px;">{{ $m->user->name }}</div>
                        <div class="small text-muted">{{ $m->user->kelas?->nama ?? 'Umum' }}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('eskul.members.approve', $m) }}" method="POST">
                            @csrf
                            <button class="btn-action bg-success text-white shadow-sm" title="Setujui"><i class="bi bi-check-lg"></i></button>
                        </form>
                        <form action="{{ route('eskul.members.reject', $m) }}" method="POST">
                            @csrf
                            <button class="btn-action bg-light text-danger" title="Tolak"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif

        {{-- Daftar Anggota (non-admin approved) --}}
        @php $regularMembers = $approved->where('is_admin', false); @endphp
        <div class="section-title mt-4 mb-3 text-secondary">
            <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-people-fill text-primary"></i> Daftar Anggota</span>
            <span class="count-pill text-primary" style="background:#eef2ff;">{{ $regularMembers->count() }}</span>
        </div>
        @forelse($regularMembers as $m)
            <div class="member-card">
                <div class="avatar">
                    @if($m->user->foto)
                        <img src="{{ asset('storage/'.$m->user->foto) }}" data-name="{{ $m->user->name }}" onerror="avatarFallback(this);">
                    @else
                        {{ strtoupper(substr($m->user->name, 0, 1)) }}
                    @endif
                </div>
                <div class="flex-grow-1 min-width-0">
                    <div class="fw-bold text-truncate" style="font-size: 14px;">{{ $m->user->name }}</div>
                    <div class="small text-muted">{{ $m->user->kelas?->nama ?? 'Umum' }}</div>
                </div>
                <form action="{{ route('eskul.members.reject', $m) }}" method="POST">
                    @csrf
                    <button class="btn-action bg-white text-muted" style="border: 1px solid #f1f5f9;" title="Keluarkan anggota"
                            onclick="return confirm('Keluarkan anggota ini dari eskul?')"><i class="bi bi-person-x"></i></button>
                </form>
            </div>
        @empty
            <div class="empty-box">
                <i class="bi bi-people h1 text-muted opacity-25"></i>
                <div class="fw-bold mt-2 text-dark">Belum ada anggota</div>
                <div class="small text-muted mt-1">Belum ada siswa yang bergabung ke eskul ini.</div>
            </div>
        @endforelse
    </main>
</div>

<script>
function avatarFallback(el) {
    var name = el.getAttribute('data-name') || 'U';
    var letter = (name.charAt(0) || 'U').toUpperCase();
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
        + '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
        + '<stop offset="0%" stop-color="#ede9fe"/><stop offset="100%" stop-color="#e0e7ff"/>'
        + '</linearGradient></defs>'
        + '<rect width="100%" height="100%" fill="url(#g)"/>'
        + '<text x="50%" y="54%" font-family="sans-serif" font-size="48" font-weight="800" fill="#6d28d9" text-anchor="middle" dominant-baseline="middle">' + letter + '</text></svg>';
    el.onerror = null;
    el.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
}
</script>
@endsection
