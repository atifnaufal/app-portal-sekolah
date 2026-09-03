@extends('layouts.app', ['title' => 'Kelola Fitur'])
@section('content')
<style>
    .cp-page-header {
        background: linear-gradient(135deg, var(--navy) 0%, #1e293b 100%);
        border-radius: 24px; padding: 36px 40px; color: #fff;
        position: relative; overflow: hidden; margin-bottom: 32px;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
    }
    .cp-page-header::after {
        content: ''; position: absolute; top: -80px; right: -80px;
        width: 250px; height: 250px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36,107,254,0.15) 0%, transparent 70%);
    }
    .cp-page-title { font-size: 28px; font-weight: 800; letter-spacing: -0.02em; position: relative; z-index: 1; }
    .cp-page-sub { font-size: 14px; color: #94a3b8; position: relative; z-index: 1; }

    .cat-tabs { display: flex; gap: 8px; margin-bottom: 28px; flex-wrap: wrap; }
    .cat-tab {
        padding: 8px 20px; border-radius: 12px; font-size: 13px; font-weight: 700;
        border: 1.5px solid var(--border); background: #fff; cursor: pointer;
        transition: all 0.2s; color: #64748b;
    }
    .cat-tab:hover { border-color: var(--blue); color: var(--blue); }
    .cat-tab.active { background: var(--blue); color: #fff; border-color: var(--blue); }

    .feature-card {
        border-radius: 20px; border: 1px solid var(--border); background: #fff;
        padding: 28px; margin-bottom: 20px; transition: all 0.3s;
        display: flex; align-items: center; gap: 24px;
        box-shadow: var(--shadow);
    }
    .feature-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-2px); }
    .feature-icon {
        width: 60px; height: 60px; border-radius: 18px;
        display: grid; place-items: center; font-size: 28px; flex-shrink: 0;
    }
    .feature-icon.keuangan { background: #fef2f2; color: #dc2626; }
    .feature-icon.academy { background: #eff6ff; color: #2563eb; }
    .feature-icon.konten { background: #f0fdf4; color: #16a34a; }
    .feature-icon.registrasi { background: #fffbeb; color: #d97706; }
    .feature-icon.komunikasi { background: #fdf4ff; color: #9333ea; }

    .feature-info { flex: 1; min-width: 0; }
    .feature-name { font-size: 16px; font-weight: 800; color: var(--navy); }
    .feature-desc { font-size: 13px; color: var(--muted); margin-top: 4px; }
    .feature-badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 6px; }
    .feature-badge.aktif { background: #dcfce7; color: #166534; }
    .feature-badge.nonaktif { background: #fef2f2; color: #991b1b; }

    .toggle-wrap { position: relative; width: 56px; height: 30px; flex-shrink: 0; }
    .toggle-wrap input { opacity: 0; width: 0; height: 0; position: absolute; }
    .toggle-slider {
        position: absolute; cursor: pointer; inset: 0;
        background: #cbd5e1; border-radius: 30px; transition: 0.3s;
    }
    .toggle-slider::before {
        content: ''; position: absolute; height: 24px; width: 24px;
        left: 3px; bottom: 3px; background: #fff; border-radius: 50%;
        transition: 0.3s; box-shadow: 0 2px 4px rgb(0 0 0 / 0.15);
    }
    .toggle-wrap input:checked + .toggle-slider { background: var(--blue); }
    .toggle-wrap input:checked + .toggle-slider::before { transform: translateX(26px); }

    .form-card { border-radius: 24px; border: 1px solid var(--border); background: #fff; box-shadow: var(--shadow); }
    .form-card-head { padding: 24px 30px; border-bottom: 1px solid var(--border); }
    .form-card-title { font-size: 18px; font-weight: 800; color: var(--navy); }
    .form-card-body { padding: 30px; }

    @media (max-width: 768px) {
        .cp-page-header { padding: 24px; border-radius: 20px; }
        .cp-page-title { font-size: 22px; }
        .feature-card { flex-direction: column; align-items: flex-start; gap: 16px; }
        .feature-actions { align-self: flex-end; }
    }
</style>

<div class="cp-shell">
@include('admin.partials.sidebar')
<div class="cp-main">
<div class="cp-page-header">
    <div class="position-relative" style="z-index:1;">
        <div class="small fw-bold" style="letter-spacing:.1em;color:#94a3b8;">SUPER ADMIN ONLY</div>
        <h1 class="cp-page-title">Kelola Fitur Sistem</h1>
        <p class="cp-page-sub">Aktifkan atau nonaktifkan fitur-fitur portal sesuai kebutuhan sekolah.</p>
    </div>
</div>

<div class="form-card">
    <div class="form-card-head d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="form-card-title"><i class="bi bi-sliders me-2"></i>Feature Management</h2>
        <form method="POST" action="{{ route('admin.features.toggle') }}" id="toggleForm">
            @csrf @method('PATCH')
            <input type="hidden" name="key" id="toggleKey">
        </form>
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
        <div class="feature-card" data-category="{{ $flag['category'] }}" data-key="{{ $flag['key'] }}">
            <div class="feature-icon {{ strtolower($flag['category']) }}">
                <i class="bi {{ $flag['icon'] }}"></i>
            </div>
            <div class="feature-info">
                <div class="feature-name">{{ $flag['label'] }}</div>
                <div class="feature-desc">{{ $flag['description'] }}</div>
                <span class="feature-badge {{ $flag['value'] ? 'aktif' : 'nonaktif' }}">{{ $flag['currentStatus'] }}</span>
            </div>
            <div class="feature-actions">
                <label class="toggle-wrap">
                    <input type="checkbox" {{ $flag['value'] ? 'checked' : '' }}
                        onchange="toggleFeature('{{ $flag['key'] }}', this)">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
    function filterCategory(cat, btn) {
        document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.feature-card').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.category === cat) ? 'flex' : 'none';
        });
    }

    function toggleFeature(key, el) {
        document.getElementById('toggleKey').value = key;
        document.getElementById('toggleForm').submit();
    }
</script>
</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}
@endsection