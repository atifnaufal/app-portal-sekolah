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

    .member-card {
        background: #fff; border-radius: 20px; padding: 16px;
        margin-bottom: 12px; border: 1px solid #f1f5f9;
        display: flex; align-items: center; gap: 12px;
    }
    .avatar {
        width: 44px; height: 44px; border-radius: 12px;
        background: #f1f5f9; color: #475569; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; overflow: hidden;
    }
    .btn-action {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        border: none; cursor: pointer; transition: all 0.2s;
    }
</style>

<div class="page-header">
    <a href="{{ route('eskul.index') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px; letter-spacing: -0.4px;">Kelola Member</div>
</div>

<div class="page-container">
    <header class="px-4 py-3 d-flex justify-content-between align-items-start">
        <div>
            <h5 class="fw-bold mb-1">{{ $eskul->nama }}</h5>
            <p class="small text-muted mb-0">Manajemen permohonan bergabung dan daftar anggota.</p>
        </div>
        @php
            $eskulChat = \App\Models\ChatGroup::where('type', 'eskul')->where('related_id', $eskul->id)->first();
        @endphp
        @if($eskulChat)
            <a href="{{ route('chat.show', $eskulChat) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-chat-dots-fill me-1"></i> Chat Grup
            </a>
        @endif
    </header>

    <main class="mobile-content px-3 mt-2">
        @php $pending = $members->where('status', 'pending'); @endphp

        @if($pending->count() > 0)
            <div class="section-title mb-3 px-1 fw-bold text-primary" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em;">Permohonan Gabung ({{ $pending->count() }})</div>
            @foreach($pending as $m)
                <div class="member-card">
                    <div class="avatar">
                        @if($m->user->foto)
                            <img src="{{ asset('storage/'.$m->user->foto) }}" class="w-100 h-100 object-fit-cover">
                        @else
                            {{ strtoupper(substr($m->user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold" style="font-size: 14px;">{{ $m->user->name }}</div>
                        <div class="small text-muted">{{ $m->user->kelas?->nama ?? 'Umum' }}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('eskul.members.approve', $m) }}" method="POST">
                            @csrf
                            <button class="btn-action bg-primary text-white" title="Setujui"><i class="bi bi-check-lg"></i></button>
                        </form>
                        <form action="{{ route('eskul.members.reject', $m) }}" method="POST">
                            @csrf
                            <button class="btn-action bg-light text-danger" title="Tolak"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif

        @php $approved = $members->where('status', 'approved'); @endphp
        <div class="section-title mt-4 mb-3 px-1 fw-bold text-secondary" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em;">Daftar Anggota ({{ $approved->count() }})</div>
        @foreach($approved as $m)
            <div class="member-card">
                <div class="avatar">
                    @if($m->user->foto)
                        <img src="{{ asset('storage/'.$m->user->foto) }}" class="w-100 h-100 object-fit-cover">
                    @else
                        {{ strtoupper(substr($m->user->name, 0, 1)) }}
                    @endif
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold" style="font-size: 14px;">{{ $m->user->name }}</div>
                    <div class="small text-muted">{{ $m->user->kelas?->nama ?? 'Umum' }}</div>
                </div>
                @if($m->is_admin)
                    <span class="badge bg-dark rounded-pill" style="font-size: 9px;">ADMIN ESKUL</span>
                @else
                    <form action="{{ route('eskul.members.reject', $m) }}" method="POST">
                        @csrf
                        <button class="btn-action bg-white text-muted" style="border: 1px solid #f1f5f9;" onclick="return confirm('Keluarkan anggota ini?')"><i class="bi bi-person-x"></i></button>
                    </form>
                @endif
            </div>
        @endforeach
    </main>
</div>
@endsection
