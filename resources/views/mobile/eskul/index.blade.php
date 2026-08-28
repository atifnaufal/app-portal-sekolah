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
        padding: 32px 24px 36px; border-radius: 0 0 40px 40px;
        color: #fff; position: relative; overflow: hidden;
    }
    .eskul-hero::after {
        content: ''; position: absolute; top: -20px; right: -20px;
        width: 120px; height: 120px; border-radius: 30px;
        background: rgba(255,255,255,0.15); transform: rotate(20deg);
    }
    .eskul-hero::before {
        content: ''; position: absolute; bottom: -40px; left: -30px;
        width: 140px; height: 140px; border-radius: 50%;
        background: rgba(255,255,255,0.08);
    }

    .limit-chip {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.25);
        color: #fff; font-size: 11px; font-weight: 700;
        padding: 6px 12px; border-radius: 100px; backdrop-filter: blur(4px);
    }

    .eskul-card {
        background: #fff; border-radius: 24px; padding: 18px;
        margin-bottom: 16px; border: 1px solid #f1f5f9;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative; overflow: hidden;
    }
    .eskul-card:active { transform: scale(0.98); }
    .eskul-logo {
        width: 58px; height: 58px; border-radius: 18px;
        background: #f8fafc; flex-shrink: 0; overflow: hidden;
        display: flex; align-items: center; justify-content: center;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        position: relative;
    }
    .eskul-logo img { width: 100%; height: 100%; object-fit: cover; }
    .eskul-logo-placeholder {
        width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #ede9fe, #e0e7ff); color: #7c3aed; font-size: 22px;
    }
    .eskul-name { font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 3px; line-height: 1.25; }
    .eskul-meta { font-size: 12px; color: #64748b; font-weight: 600; }

    .pembina-line {
        display: inline-flex; align-items: center; gap: 5px;
        background: #f5f3ff; color: #6d28d9;
        font-size: 11px; font-weight: 700;
        padding: 4px 10px; border-radius: 100px;
    }

    .btn-join {
        padding: 9px 16px; border-radius: 13px; font-size: 13px; font-weight: 700;
        transition: all 0.2s; min-width: 86px; border: none; text-align: center;
    }
    .btn-join:disabled { opacity: 0.55; cursor: not-allowed; }

    .status-badge {
        font-size: 9px; font-weight: 800; padding: 4px 11px; border-radius: 100px;
        text-transform: uppercase; letter-spacing: 0.05em;
    }

    .eskul-desc {
        background: #f8fafc; border-radius: 14px; padding: 12px 14px;
        font-size: 12.5px; color: #475569; line-height: 1.55; border: 1px solid #f1f5f9;
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
        <div class="eyebrow" style="color: rgba(255,255,255,0.75);">MINAT & BAKAT</div>
        <h1 class="hero-title mt-2 text-white" style="font-size: 26px;">Eksplorasi Eskul</h1>
        <p class="mb-0 mt-1" style="font-size: 12px; color: rgba(255,255,255,0.88); line-height: 1.5;">
            Bergabunglah dengan komunitas favoritmu dan kembangkan potensimu bersama teman-teman.
        </p>
        @if(session('user_role') === 'siswa')
            <div class="mt-3">
                <span class="limit-chip">
                    <i class="bi bi-patch-check-fill"></i>
                    {{ $myCount }}/{{ $maxEskul }} eskul maksimal
                </span>
            </div>
        @endif
    </header>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mx-3 mt-3 mb-0">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mx-3 mt-3 mb-0">{{ session('error') }}</div>
    @endif

    <main class="mobile-content px-3 mt-4">
        @php
            $atLimit = session('user_role') === 'siswa' && $myCount >= $maxEskul;
        @endphp
        @forelse($eskuls as $eskul)
            @php
                $myMember = \App\Models\EskulMember::where('eskul_id', $eskul->id)->where('user_id', session('user_id'))->first();
                $isJoined = $myMember && $myMember->status === 'approved';
                $isPending = $myMember && $myMember->status === 'pending';
                $isEskulAdmin = $myMember && $myMember->is_admin;
                $joinDisabled = !$isJoined && !$isPending && !$isEskulAdmin && $atLimit;
            @endphp
            <div class="eskul-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="eskul-logo">
                        @if($eskul->logo)
                            <img src="{{ asset('storage/'.$eskul->logo) }}" alt="{{ $eskul->nama }}"
                                 data-name="{{ $eskul->nama }}" onerror="eskulLogoFallback(this);">
                        @else
                            <div class="eskul-logo-placeholder">
                                <i class="bi bi-flag-fill"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="eskul-name text-truncate">{{ $eskul->nama }}</div>
                        <div class="eskul-meta d-flex align-items-center gap-2 flex-wrap">
                            <span><i class="bi bi-people-fill me-1 text-primary"></i> {{ $eskul->members_count }} Anggota</span>
                            @if($eskul->pembina)
                                <span class="pembina-line"><i class="bi bi-person-badge"></i> {{ $eskul->pembina->name }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex-shrink-0 text-end">
                        @if(!$isEskulAdmin)
                            <form action="{{ route('eskul.join', $eskul) }}" method="POST">
                                @csrf
                                <button class="btn btn-join {{ ($isJoined || $isPending) ? 'btn-light text-danger' : 'btn-primary shadow-sm' }}"
                                        style="{{ ($isJoined || $isPending) ? 'background:#fef2f2; border:1px solid #fee2e2;' : '' }}"
                                        @if($joinDisabled) disabled title="Maksimal {{ $maxEskul }} eskul" @endif>
                                    {{ $isPending ? 'Batal' : ($isJoined ? 'Keluar' : 'Gabung') }}
                                </button>
                            </form>
                            @if($joinDisabled)
                                <div class="small text-muted mt-1" style="font-size: 9px; font-weight: 600;">Maks {{ $maxEskul }} eskul</div>
                            @endif
                        @else
                            <a href="{{ route('eskul.members', $eskul) }}" class="btn btn-join btn-dark shadow-sm">
                                <i class="bi bi-shield-check me-1"></i> Kelola
                            </a>
                        @endif
                    </div>
                </div>

                @if($isPending || $isJoined)
                    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            @if($isPending)
                                <span class="status-badge text-dark" style="background: #fef3c7 !important; color:#92400e !important;">Menunggu Persetujuan</span>
                            @elseif($isJoined)
                                <span class="status-badge text-white" style="background: #10b981 !important;">Sudah Bergabung</span>
                            @endif
                            @if($isEskulAdmin)
                                <span class="status-badge text-white" style="background: #0f172a !important;"><i class="bi bi-shield-fill-check"></i> Admin</span>
                            @endif
                        </div>

                        @if($isJoined)
                            @php
                                $eskulChat = \App\Models\ChatGroup::where('type', 'eskul')->where('related_id', $eskul->id)->first();
                            @endphp
                            @if($eskulChat)
                                <a href="{{ route('chat.show', $eskulChat) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" style="font-size: 11px;">
                                    <i class="bi bi-chat-dots-fill me-1"></i> Masuk Chat
                                </a>
                            @endif
                        @endif
                    </div>
                @endif

                @if($eskul->deskripsi)
                    <div class="eskul-desc mt-3">
                        <i class="bi bi-quote me-1 text-primary"></i>{{ \Illuminate\Support\Str::limit($eskul->deskripsi, 90) }}
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

<script>
function eskulLogoFallback(el) {
    var name = el.getAttribute('data-name') || 'E';
    var letter = (name.charAt(0) || 'E').toUpperCase();
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200">'
        + '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
        + '<stop offset="0%" stop-color="#7c3aed"/><stop offset="100%" stop-color="#a78bfa"/>'
        + '</linearGradient></defs>'
        + '<rect width="100%" height="100%" fill="url(#g)"/>'
        + '<text x="50%" y="54%" font-family="sans-serif" font-size="90" font-weight="700" fill="#fff" text-anchor="middle" dominant-baseline="middle">' + letter + '</text></svg>';
    el.onerror = null;
    el.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
}
</script>
@endsection
