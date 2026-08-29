@extends('layouts.mobile-app')

@section('content')
<style>
    .eskul-hero {
        background: var(--grad-primary);
        color: #fff; position: relative; overflow: hidden;
        padding: 30px 24px 34px;
        border-radius: 0 0 var(--radius-lg) var(--radius-lg);
    }
    .eskul-hero::after {
        content: ''; position: absolute; top: -20px; right: -20px;
        width: 120px; height: 120px; border-radius: 30px;
        background: rgba(255,255,255,0.15); transform: rotate(20deg);
    }
    .eskul-card { padding: 18px; }
    .eskul-card:active { transform: scale(0.98); }
    .eskul-logo {
        width: 58px; height: 58px; border-radius: 18px;
        background: var(--surface); flex-shrink: 0; overflow: hidden;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .eskul-logo img { width: 100%; height: 100%; object-fit: cover; }
    .eskul-logo-placeholder {
        width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
        background: var(--grad-primary); color: #fff; font-size: 22px;
    }
    .eskul-name { font-size: 17px; font-weight: 800; color: var(--ink); margin-bottom: 3px; line-height: 1.25; }
    .eskul-meta { font-size: 12px; color: var(--mist); font-weight: 600; }
    .eskul-desc {
        background: var(--surface); border-radius: var(--radius-sm); padding: 12px 14px;
        font-size: 12.5px; color: var(--mist); line-height: 1.55; border: 1px solid var(--line);
    }
    .btn-join { min-width: 86px; }
</style>

<div class="pui-topbar" style="padding-top:16px;">
    <a href="{{ route('dashboard') }}" class="back"><i class="bi bi-chevron-left"></i></a>
    <h1>Ekstrakurikuler</h1>
    <div class="spacer"></div>
</div>

<div class="mobile-content">
    <header class="eskul-hero">
        <div class="eyebrow" style="color: rgba(255,255,255,0.75);">MINAT & BAKAT</div>
        <h1 class="hero-title mt-2 text-white" style="font-size: 26px;">Eksplorasi Eskul</h1>
        <p class="mb-0 mt-1" style="font-size: 12px; color: rgba(255,255,255,0.88); line-height: 1.5;">
            Bergabunglah dengan komunitas favoritmu dan kembangkan potensimu bersama teman-teman.
        </p>
        @if(session('user_role') === 'siswa')
            <div class="mt-3">
                <span class="pui-chip" style="background: rgba(255,255,255,0.18); color:#fff; border:1px solid rgba(255,255,255,0.3);">
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

    <main class="px-3 mt-4">
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
            <div class="eskul-card pui-card">
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
                            <span><i class="bi bi-people-fill me-1" style="color:var(--blue);"></i> {{ $eskul->members_count }} Anggota</span>
                            @if($eskul->pembina)
                                <span class="pui-chip pui-chip-violet" style="font-size:11px;"><i class="bi bi-person-badge"></i> {{ $eskul->pembina->name }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex-shrink-0 text-end">
                        @if(!$isEskulAdmin)
                            <form action="{{ route('eskul.join', $eskul) }}" method="POST">
                                @csrf
                                <button class="pui-btn pui-btn-sm btn-join {{ ($isJoined || $isPending) ? 'pui-btn-ghost' : 'pui-btn-primary' }}"
                                        style="{{ ($isJoined || $isPending) ? 'border-color:#fee2e2; color:#dc2626;' : '' }}"
                                        @if($joinDisabled) disabled title="Maksimal {{ $maxEskul }} eskul" @endif>
                                    {{ $isPending ? 'Batal' : ($isJoined ? 'Keluar' : 'Gabung') }}
                                </button>
                            </form>
                            @if($joinDisabled)
                                <div class="small mt-1" style="color:var(--faint); font-size:9px; font-weight:600;">Maks {{ $maxEskul }} eskul</div>
                            @endif
                        @else
                            <a href="{{ route('eskul.members', $eskul) }}" class="pui-btn pui-btn-ink pui-btn-sm btn-join">
                                <i class="bi bi-shield-check me-1"></i> Kelola
                            </a>
                        @endif
                    </div>
                </div>

                @if($isPending || $isJoined)
                    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            @if($isPending)
                                <span class="pui-chip pui-chip-amber">Menunggu Persetujuan</span>
                            @elseif($isJoined)
                                <span class="pui-chip pui-chip-green">Sudah Bergabung</span>
                            @endif
                            @if($isEskulAdmin)
                                <span class="pui-chip" style="background:var(--navy); color:#fff;"><i class="bi bi-shield-fill-check"></i> Admin</span>
                            @endif
                        </div>

                        @if($isJoined)
                            @php
                                $eskulChat = \App\Models\ChatGroup::where('type', 'eskul')->where('related_id', $eskul->id)->first();
                            @endphp
                            @if($eskulChat)
                                <a href="{{ route('chat.show', $eskulChat) }}" class="pui-btn pui-btn-soft pui-btn-sm pui-btn-round" style="font-size: 11px;">
                                    <i class="bi bi-chat-dots-fill me-1"></i> Masuk Chat
                                </a>
                            @endif
                        @endif
                    </div>
                @endif

                @if($eskul->deskripsi)
                    <div class="eskul-desc mt-3">
                        <i class="bi bi-quote me-1" style="color:var(--blue);"></i>{{ \Illuminate\Support\Str::limit($eskul->deskripsi, 90) }}
                    </div>
                @endif
            </div>
        @empty
            <div class="pui-empty">
                <div class="ico"><i class="bi bi-flag"></i></div>
                <h4>Belum ada eskul</h4>
                <p>Kegiatan ekstrakurikuler belum tersedia saat ini.</p>
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
        + '<stop offset="0%" stop-color="#4f46e5"/><stop offset="100%" stop-color="#6366f1"/>'
        + '</linearGradient></defs>'
        + '<rect width="100%" height="100%" fill="url(#g)"/>'
        + '<text x="50%" y="54%" font-family="sans-serif" font-size="90" font-weight="700" fill="#fff" text-anchor="middle" dominant-baseline="middle">' + letter + '</text></svg>';
    el.onerror = null;
    el.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
}
</script>
@endsection
