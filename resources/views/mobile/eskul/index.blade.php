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

    .eskul-hero {
        background: linear-gradient(135deg, #7c3aed, #a78bfa);
        padding: 32px 24px 40px; border-radius: 0 0 40px 40px;
        color: #fff; position: relative; overflow: hidden;
    }
    .eskul-hero::after {
        content: ''; position: absolute; top: -20px; right: -20px;
        width: 120px; height: 120px; border-radius: 30px;
        background: rgba(255,255,255,0.15); transform: rotate(20deg);
    }

    .eskul-card {
        background: #fff; border-radius: 24px; padding: 16px;
        margin-bottom: 16px; border: 1px solid #f1f5f9;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        transition: all 0.3s;
    }
    .eskul-card:active { transform: scale(0.97); }
    .eskul-logo {
        width: 64px; height: 64px; border-radius: 20px;
        background: #f8fafc; flex-shrink: 0; overflow: hidden;
        display: flex; align-items: center; justify-content: center;
    }
    .eskul-name { font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
    .eskul-meta { font-size: 12px; color: #94a3b8; font-weight: 600; }

    .eskul-logo img { width: 100%; height: 100%; object-fit: cover; }
    .eskul-logo-placeholder {
        width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
        background: #f1f5f9; color: #94a3b8; font-size: 24px;
    }

    .btn-join {
        padding: 8px 16px; border-radius: 12px; font-size: 12px; font-weight: 700;
        transition: all 0.2s; min-width: 80px;
    }
    .status-badge {
        font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 100px;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
</style>

<div class="page-header">
    <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px; letter-spacing: -0.4px;">Ekstrakurikuler</div>
</div>

<div class="page-container">
    <header class="eskul-hero">
        <div class="eyebrow" style="color: rgba(255,255,255,0.7);">MINAT & BAKAT</div>
        <h1 class="hero-title mt-2 text-white" style="font-size: 26px;">Eksplorasi Eskul</h1>
        <p class="mb-0 mt-1" style="font-size: 12px; color: rgba(255,255,255,0.85);">
            Bergabunglah dengan komunitas favoritmu dan kembangkan potensimu bersama teman-teman.
        </p>
    </header>

    <main class="mobile-content px-3 mt-4">
        @forelse($eskuls as $eskul)
            @php
                $myMember = \App\Models\EskulMember::where('eskul_id', $eskul->id)->where('user_id', session('user_id'))->first();
                $isJoined = $myMember && $myMember->status === 'approved';
                $isPending = $myMember && $myMember->status === 'pending';
                $isEskulAdmin = $myMember && $myMember->is_admin;
            @endphp
            <div class="eskul-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="eskul-logo">
                        @if($eskul->logo && file_exists(public_path('storage/'.$eskul->logo)))
                            <img src="{{ asset('storage/'.$eskul->logo) }}" alt="{{ $eskul->nama }}">
                        @else
                            <div class="eskul-logo-placeholder">
                                <i class="bi bi-flag-fill"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="eskul-name text-truncate">{{ $eskul->nama }}</div>
                        <div class="eskul-meta d-flex align-items-center gap-2">
                            <span><i class="bi bi-people-fill me-1"></i> {{ $eskul->members_count }} Anggota</span>
                            @if($isPending)
                                <span class="status-badge bg-warning text-dark">Pending</span>
                            @elseif($isJoined)
                                <span class="status-badge bg-success text-white">Member</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        @if(!$isEskulAdmin)
                            <form action="{{ route('eskul.join', $eskul) }}" method="POST">
                                @csrf
                                <button class="btn btn-join {{ ($isJoined || $isPending) ? 'btn-outline-danger' : 'btn-primary shadow-sm' }}">
                                    {{ $isPending ? 'Batal' : ($isJoined ? 'Keluar' : 'Gabung') }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('eskul.members', $eskul) }}" class="btn btn-join btn-dark">
                                Kelola
                            </a>
                        @endif
                    </div>
                </div>
                @if($eskul->deskripsi)
                    <div class="mt-3 pt-3 border-top small text-secondary" style="line-height: 1.5;">
                        {{ \Illuminate\Support\Str::limit($eskul->deskripsi, 80) }}
                    </div>
                @endif
            </div>
        @empty
            <div class="empty-box">
                <i class="bi bi-flag h1 text-muted opacity-25"></i>
                <div class="fw-bold mt-2">Belum ada eskul</div>
                <div class="small text-secondary mt-1">Kegiatan ekstrakurikuler belum tersedia saat ini.</div>
            </div>
        @endforelse
    </main>
</div>
@endsection
