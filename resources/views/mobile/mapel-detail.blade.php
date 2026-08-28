@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .md-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.9); backdrop-filter: blur(16px);
        border-bottom: 1px solid #f1f5f9;
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .md-body { padding: 70px 16px 100px; max-width: 640px; margin: 0 auto; }

    .md-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 28px; padding: 28px 24px; color: #fff;
        margin-bottom: 20px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.1);
    }

    .md-tabs {
        display: flex; gap: 8px; margin-bottom: 20px;
        background: #f1f5f9; padding: 6px; border-radius: 16px;
    }
    .md-tab-btn {
        flex: 1; padding: 10px; border-radius: 12px; border: none;
        font-size: 13px; font-weight: 700; color: #64748b;
        background: transparent; transition: all 0.2s;
    }
    .md-tab-btn.active { background: #fff; color: #0f172a; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }

    .md-card {
        background: #fff; border-radius: 22px; padding: 16px;
        margin-bottom: 12px; border: 1px solid rgba(15,23,42,0.03);
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        display: flex; align-items: center; gap: 14px;
        text-decoration: none; color: inherit;
    }
    .md-card-icon {
        width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .md-card-title { font-size: 14px; font-weight: 800; color: #1e293b; margin-bottom: 2px; }
    .md-card-sub { font-size: 11px; color: #94a3b8; font-weight: 600; }

    @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    .fade-up { animation: fadeUp 0.3s ease both; }
</style>

<div class="md-header">
    <a href="{{ route('dashboard') }}" style="width:40px;height:40px;border-radius:50%;background:#f8fafc;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#475569;">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div style="font-weight:800;font-size:17px;flex:1;">E-Learning</div>
</div>

<div class="md-body">
    <div class="md-hero fade-up">
        <div style="font-size:10px; font-weight:800; opacity:0.6; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:8px;">{{ $mapel->kode }}</div>
        <h1 style="font-size:24px; font-weight:800; margin-bottom:12px; letter-spacing:-0.5px;">{{ $mapel->nama }}</h1>
        <div style="display:flex; align-items:center; gap:8px; font-size:12px; opacity:0.8;">
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
            <div class="md-card">
                <div class="md-card-icon" style="background:#eff6ff; color:#2563eb;">
                    <i class="bi bi-file-earmark-play-fill"></i>
                </div>
                <div style="flex:1;">
                    <div class="md-card-title">{{ $m->judul }}</div>
                    <div class="md-card-sub">{{ $m->created_at->translatedFormat('d M Y') }}</div>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </div>
        @empty
            <div style="text-align:center; padding:40px 20px;">
                <i class="bi bi-journal-x" style="font-size:48px; color:#cbd5e1;"></i>
                <p style="margin-top:12px; color:#94a3b8; font-weight:600;">Belum ada materi dibagikan.</p>
            </div>
        @endforelse

        @if($user->role === 'guru')
            <button class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm mt-2">
                + Tambah Materi
            </button>
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
                <div style="flex:1;">
                    <div class="md-card-title">{{ $t->judul }}</div>
                    <div class="md-card-sub" style="color:{{ $dl['tone'] === 'danger' ? '#ef4444' : '#94a3b8' }};">
                        {{ $dl['label'] }}
                    </div>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </a>
        @empty
            <div style="text-align:center; padding:40px 20px;">
                <i class="bi bi-clipboard-x" style="font-size:48px; color:#cbd5e1;"></i>
                <p style="margin-top:12px; color:#94a3b8; font-weight:600;">Belum ada tugas.</p>
            </div>
        @endforelse

        @if($user->role === 'guru')
            <a href="{{ route('tugas.create', ['mapel_id' => $mapel->id]) }}" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm mt-2 text-decoration-none d-block text-center">
                + Buat Tugas Baru
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
