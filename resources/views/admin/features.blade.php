{{-- Kelola Fitur PER SEKOLAH (Admin Pusat). Pilih ID sekolah → toggle 12 fitur
     khusus sekolah itu. Override bisa dihapus agar kembali ikut default global. --}}
@extends('layouts.app', ['title' => 'Kelola Fitur per Sekolah'])
@section('content')
<style>
    .cp-page-header {
        background: linear-gradient(135deg, var(--navy) 0%, #1e293b 100%);
        border-radius: 24px; padding: 32px 36px; color: #fff;
        position: relative; overflow: hidden; margin-bottom: 24px;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
    }
    .cp-page-header::after {
        content: ''; position: absolute; top: -70px; right: -70px;
        width: 220px; height: 220px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36,107,254,0.18) 0%, transparent 70%);
    }
    .cp-page-title { font-size: 26px; font-weight: 800; letter-spacing: -0.02em; position: relative; z-index: 1; }
    .cp-page-sub { font-size: 13px; color: #94a3b8; position: relative; z-index: 1; }

    .school-picker {
        border-radius: 20px; border: 1px solid var(--border); background: #fff;
        box-shadow: var(--shadow); padding: 20px 24px; margin-bottom: 24px;
        display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    }

    .cat-tabs { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
    .cat-tab {
        padding: 8px 20px; border-radius: 12px; font-size: 13px; font-weight: 700;
        border: 1.5px solid var(--border); background: #fff; cursor: pointer;
        transition: all 0.2s; color: #64748b;
    }
    .cat-tab:hover { border-color: var(--blue); color: var(--blue); }
    .cat-tab.active { background: var(--blue); color: #fff; border-color: var(--blue); }

    .feature-card {
        border-radius: 20px; border: 1px solid var(--border); background: #fff;
        padding: 22px 24px; margin-bottom: 16px; transition: all 0.3s;
        display: flex; align-items: center; gap: 20px;
        box-shadow: var(--shadow);
    }
    .feature-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-2px); }
    .feature-card.is-override { border-left: 5px solid var(--blue); }
    .feature-icon {
        width: 56px; height: 56px; border-radius: 18px; flex-shrink: 0;
        display: grid; place-items: center; font-size: 26px;
    }
    .feature-icon.keuangan { background: #fef2f2; color: #dc2626; }
    .feature-icon.academy { background: #eff6ff; color: #2563eb; }
    .feature-icon.konten { background: #f0fdf4; color: #16a34a; }
    .feature-icon.registrasi { background: #fffbeb; color: #d97706; }
    .feature-icon.komunikasi { background: #fdf4ff; color: #9333ea; }

    .feature-info { flex: 1; min-width: 0; }
    .feature-name { font-size: 15.5px; font-weight: 800; color: var(--navy); }
    .feature-desc { font-size: 12.5px; color: var(--muted); margin-top: 2px; }
    .feature-badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 6px; }
    .feature-badge.aktif { background: #dcfce7; color: #166534; }
    .feature-badge.nonaktif { background: #fef2f2; color: #991b1b; }
    .override-tag { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 10px; font-weight: 800; background: #eef2ff; color: #4f46e5; margin-top: 6px; margin-left: 6px; }

    .toggle-wrap { position: relative; width: 56px; height: 30px; flex-shrink: 0; }
    .toggle-wrap input { opacity: 0; width: 0; height: 0; position: absolute; }
    .toggle-slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e1; border-radius: 30px; transition: 0.3s; }
    .toggle-slider::before {
        content: ''; position: absolute; height: 24px; width: 24px;
        left: 3px; bottom: 3px; background: #fff; border-radius: 50%;
        transition: 0.3s; box-shadow: 0 2px 4px rgb(0 0 0 / 0.15);
    }
    .toggle-wrap input:checked + .toggle-slider { background: var(--blue); }
    .toggle-wrap input:checked + .toggle-slider::before { transform: translateX(26px); }

    .form-card { border-radius: 24px; border: 1px solid var(--border); background: #fff; box-shadow: var(--shadow); }
    .form-card-head { padding: 22px 28px; border-bottom: 1px solid var(--border); }
    .form-card-title { font-size: 17px; font-weight: 800; color: var(--navy); }
    .form-card-body { padding: 26px; }

    @media (max-width: 768px) {
        .cp-page-header { padding: 24px; border-radius: 20px; }
        .cp-page-title { font-size: 22px; }
        .feature-card { flex-wrap: wrap; gap: 14px; }
        .feature-actions { display: flex; gap: 8px; align-items: center; margin-left: auto; }
    }
</style>

<div class="cp-shell">
@include('admin.partials.sidebar')
<div class="cp-main">

<div class="cp-page-header">
    <div class="position-relative" style="z-index:1;">
        <div class="small fw-bold" style="letter-spacing:.1em;color:#94a3b8;">SUPER ADMIN ONLY &bull; PER ID SEKOLAH</div>
        <h1 class="cp-page-title">Kelola Fitur per Sekolah</h1>
        <p class="cp-page-sub mb-0">Pilih sekolah, lalu aktif/nonaktifkan 12 fitur khusus sekolah itu. Menu web sekolah ikut menyesuaikan otomatis.</p>
    </div>
</div>

<div class="school-picker">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-buildings-fill text-primary" style="font-size:20px;"></i>
        <span class="fw-bold small">Sekolah:</span>
    </div>
    <form method="GET" action="{{ route('admin.features') }}" class="d-flex gap-2 flex-wrap flex-fill">
        <select name="school_id" class="form-select" style="border-radius:12px;max-width:340px;" onchange="this.form.submit()">
            @foreach($schools as $sc)
                <option value="{{ $sc->id }}" @selected(($school?->id) == $sc->id)>[ID: {{ $sc->id }}] {{ $sc->name }}{{ $sc->is_active ? '' : ' (Nonaktif)' }}</option>
            @endforeach
        </select>
        @if($school)
        <a href="{{ route('admin.schools.detail', $school->id) }}" class="btn btn-outline-primary btn-sm align-self-center" style="border-radius:10px;"><i class="bi bi-eye me-1"></i>Detail Sekolah</a>
        @endif
    </form>
</div>

@if($school)
<div class="form-card">
    <div class="form-card-head d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="form-card-title"><i class="bi bi-sliders me-2"></i>[ID: {{ $school->id }}] {{ $school->name }}</h2>
        <span class="small text-muted">{{ collect($featureFlags)->where('value', true)->count() }}/{{ count($featureFlags) }} fitur aktif</span>
    </div>
    <div class="form-card-body">
        <div class="cat-tabs">
            <button class="cat-tab active" onclick="filterCategory('all', this)">Semua</button>
            <button class="cat-tab" onclick="filterCategory('Keuangan', this)">Keuangan</button>
            <button class="cat-tab" onclick="filterCategory('Academy', this)">Academy</button>
            <button class="cat-tab" onclick="filterCategory('Konten', this)">Konten</button>
            <button class="cat-tab" onclick="filterCategory('Registrasi', this)">Registrasi</button>
            <button class="cat-tab" onclick="filterCategory('Komunikasi', this)">Komunikasi</button>
        </div>

        @foreach($featureFlags as $flag)
        <div class="feature-card {{ $flag['isOverride'] ? 'is-override' : '' }}" data-category="{{ $flag['category'] }}">
            <div class="feature-icon {{ strtolower($flag['category']) }}">
                <i class="bi {{ $flag['icon'] }}"></i>
            </div>
            <div class="feature-info">
                <div class="feature-name">{{ $flag['label'] }}</div>
                <div class="feature-desc">{{ $flag['description'] }} &bull; Global: {{ $flag['globalValue'] ? 'Aktif' : 'Nonaktif' }}</div>
                <span class="feature-badge {{ $flag['value'] ? 'aktif' : 'nonaktif' }}">{{ $flag['currentStatus'] }}</span>@if($flag['isOverride'])<span class="override-tag">Khusus sekolah ini</span>@endif
            </div>
            <div class="feature-actions">
                @if($flag['isOverride'])
                <form method="POST" action="{{ route('admin.features.reset') }}" class="d-inline">@csrf @method('PATCH')
                    <input type="hidden" name="school_id" value="{{ $school->id }}">
                    <input type="hidden" name="key" value="{{ $flag['key'] }}">
                    <button class="btn btn-sm btn-outline-secondary" style="border-radius:10px;font-size:11px;" title="Hapus override, kembali ikut default global">Reset</button>
                </form>
                @endif
                <form method="POST" action="{{ route('admin.schools.feature.toggle', $school->id) }}" class="d-inline toggle-form">@csrf @method('PATCH')
                    <input type="hidden" name="key" value="{{ $flag['key'] }}">
                    <label class="toggle-wrap mb-0">
                        <input type="checkbox" {{ $flag['value'] ? 'checked' : '' }} onchange="this.form.submit()">
                        <span class="toggle-slider"></span>
                    </label>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@else
<div class="card border-0 shadow-sm text-center py-5 text-muted" style="border-radius:20px;">Belum ada sekolah. Tambahkan dulu di menu Sekolah.</div>
@endif

</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}

<script>
    function filterCategory(cat, btn) {
        document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.feature-card').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.category === cat) ? 'flex' : 'none';
        });
    }
</script>
@endsection
