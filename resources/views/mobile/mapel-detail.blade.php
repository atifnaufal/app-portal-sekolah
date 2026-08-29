@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .lms-topbar {
        position: sticky; top: 0; z-index: 1000;
        background: rgba(255,255,255,0.9); backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--line);
        padding: 12px 16px; display: flex; align-items: center; gap: 12px;
    }
    .lms-body { max-width: 640px; margin: 0 auto; padding: 16px 16px 60px; }

    .hero-card {
        background: var(--grad-hero); color: #fff;
        border-radius: var(--radius-lg); padding: 28px 24px;
        margin-bottom: 20px; box-shadow: var(--shadow-card);
    }
    .hero-chip {
        background: rgba(255,255,255,0.08); padding: 4px 10px; border-radius: 8px;
        font-size: 9px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;
    }

    .md-tabs {
        display: flex; gap: 8px; margin-bottom: 20px;
        background: var(--surface); padding: 6px; border-radius: var(--radius-sm);
    }
    .md-tab-btn {
        flex: 1; padding: 10px; border-radius: 12px; border: none;
        font-size: 13px; font-weight: 700; color: var(--mist);
        background: transparent; transition: all 0.2s;
    }
    .md-tab-btn.active { background: var(--surface-card); color: var(--ink); box-shadow: var(--shadow-card); }

    .md-card {
        background: var(--surface-card); border: 1px solid var(--line);
        border-radius: var(--radius-md); padding: 16px;
        margin-bottom: 12px; box-shadow: var(--shadow-card);
        display: flex; align-items: center; gap: 14px;
        text-decoration: none; color: inherit;
    }
    .md-card-icon {
        width: 48px; height: 48px; border-radius: var(--radius-sm); flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .md-card-title { font-size: 14px; font-weight: 800; color: var(--ink); margin-bottom: 2px; }
    .md-card-sub { font-size: 11px; color: var(--faint); font-weight: 600; }

    @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    .fade-up { animation: fadeUp 0.3s ease both; }
</style>

<div class="lms-topbar">
    <a href="{{ route('dashboard') }}" class="pui-btn pui-btn-ghost pui-btn-sm pui-btn-round" style="padding:0;width:40px;height:40px;">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div style="font-weight:800;font-size:17px;flex:1;">E-Learning</div>
</div>

<div class="lms-body">
    <div class="hero-card fade-up">
        <div class="hero-chip" style="margin-bottom:8px;">{{ $mapel->kode }}</div>
        <h1 style="font-size:24px;font-weight:800;margin-bottom:12px;letter-spacing:-0.5px;">{{ $mapel->nama }}</h1>
        <div style="display:flex;align-items:center;gap:8px;font-size:12px;opacity:0.8;">
            <div style="width:24px;height:24px;border-radius:50%;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-person-fill"></i>
            </div>
            <span>Guru: {{ $mapel->guru->name }}</span>
        </div>
    </div>

    <div class="md-tabs fade-up" style="animation-delay:0.05s;">
        <button class="md-tab-btn active" onclick="switchTab('materi', this)">Materi</button>
        <button class="md-tab-btn" onclick="switchTab('tugas', this)">Tugas</button>
    </div>

    {{-- Content: Materi --}}
    <div id="tab-materi" class="tab-content fade-up" style="animation-delay:0.1s;">
        @forelse($materi as $m)
            <a href="{{ route('materi.show', [$mapel, $m]) }}" class="md-card">
                <div class="md-card-icon" style="background:var(--surface);color:var(--blue);">
                    @if($m->video_url)
                        <i class="bi bi-play-circle-fill"></i>
                    @elseif($m->file_materi)
                        <i class="bi bi-file-earmark-play-fill"></i>
                    @else
                        <i class="bi bi-journal-text"></i>
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="md-card-title">{{ $m->judul }}</div>
                    <div class="md-card-sub">{{ $m->created_at->translatedFormat('d M Y') }} · {{ $m->guru->name }}</div>
                </div>
                <i class="bi bi-chevron-right" style="color:var(--faint);"></i>
            </a>
        @empty
            <div class="pui-empty">
                <i class="bi bi-journal-x ico"></i>
                <h4>Belum ada materi dibagikan</h4>
            </div>
        @endforelse

        @if($user->role === 'guru')
            <a href="{{ route('materi.create', $mapel) }}" class="pui-btn pui-btn-primary pui-btn-block pui-btn-round mt-2 text-decoration-none d-block text-center" style="padding:15px;">
                <i class="bi bi-plus-lg"></i> Tambah Materi
            </a>
        @endif
    </div>

    {{-- Content: Tugas --}}
    <div id="tab-tugas" class="tab-content fade-up" style="display:none;">
        @forelse($tugas as $t)
            <a href="{{ route('tugas.show', $t->id) }}" class="md-card">
                @php $dl = $t->deadlineStatus(); @endphp
                <div class="md-card-icon" style="background:{{ $t->isForm() ? '#f5f3ff' : '#ecfdf5' }}; color:{{ $t->isForm() ? '#7c3aed' : '#10b981' }};">
                    <i class="bi {{ $t->isForm() ? 'bi-ui-checks' : 'bi-file-earmark-text' }}"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="md-card-title">{{ $t->judul }}</div>
                    <div class="md-card-sub {{ $dl['tone'] === 'danger' ? 'text-danger' : '' }}" style="{{ $dl['tone'] === 'danger' ? 'color:#ef4444' : '' }}">
                        {{ $dl['label'] }}
                    </div>
                </div>
                <i class="bi bi-chevron-right" style="color:var(--faint);"></i>
            </a>
        @empty
            <div class="pui-empty">
                <i class="bi bi-clipboard-x ico"></i>
                <h4>Belum ada tugas</h4>
            </div>
        @endforelse

        @if($user->role === 'guru')
            <a href="{{ route('tugas.create', ['mapel_id' => $mapel->id]) }}" class="pui-btn pui-btn-primary pui-btn-block pui-btn-round mt-2 text-decoration-none d-block text-center" style="padding:15px;">
                <i class="bi bi-plus-lg"></i> Buat Tugas Baru
            </a>
        @endif
    </div>
</div>

<script>
    function switchTab(tab, btn) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.md-tab-btn').forEach(el => el.classList.remove('active'));

        document.getElementById('tab-' + tab).style.display = 'block';
        btn.classList.add('active');
    }
</script>
@endsection
