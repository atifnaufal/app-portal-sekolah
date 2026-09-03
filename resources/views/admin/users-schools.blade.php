{{-- Admin pusat: pilih sekolah dulu, baru lihat data guru/siswa (drill-down). --}}
@extends('layouts.app', ['title' => 'Akun per Sekolah'])
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
    .sum-pill {
        padding: 20px 24px; border-radius: 20px; border: 1px solid var(--border);
        background: #fff; display: flex; align-items: center; gap: 16px;
        box-shadow: var(--shadow); transition: all .3s;
    }
    .sum-pill:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
    .sum-icon { width: 48px; height: 48px; border-radius: 14px; display: grid; place-items: center; font-size: 20px; flex-shrink: 0; }
    .school-card {
        border-radius: 20px; border: 1px solid var(--border); background: #fff;
        box-shadow: var(--shadow); overflow: hidden; transition: all .3s; height: 100%;
        display: flex; flex-direction: column;
    }
    .school-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
    .school-card-top { height: 6px; background: linear-gradient(90deg, #4f46e5, #22c55e); }
    .school-card-body { padding: 22px; flex: 1; }
    .school-avatar {
        width: 52px; height: 52px; border-radius: 16px; flex-shrink: 0;
        background: linear-gradient(135deg, #4f46e5, #2563eb);
        display: grid; place-items: center; color: #fff; font-weight: 800; font-size: 20px;
    }
    .mini-stat { background: #f8fafc; border-radius: 12px; padding: 10px; text-align: center; flex: 1; }
    .mini-stat .num { font-size: 18px; font-weight: 800; color: var(--navy); }
    .mini-stat .lb { font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
    @media (max-width: 768px) {
        .cp-page-header { padding: 24px; border-radius: 20px; }
        .cp-page-title { font-size: 22px; }
    }
</style>

<div class="cp-shell">
@include('admin.partials.sidebar')
<div class="cp-main">

<div class="cp-page-header">
    <div class="position-relative" style="z-index:1;">
        <div class="small fw-bold" style="letter-spacing:.1em;color:#94a3b8;">ACCOUNT MANAGEMENT</div>
        <h1 class="cp-page-title">Akun per Sekolah</h1>
        <p class="cp-page-sub mb-0">Pilih sekolah untuk melihat data guru & siswa, lalu kelola akunnya.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3 col-6">
        <div class="sum-pill">
            <div class="sum-icon" style="background:#eef2ff;color:#4f46e5;"><i class="bi bi-buildings-fill"></i></div>
            <div><div class="text-muted small fw-bold text-uppercase">Sekolah</div><div class="h4 fw-extrabold mb-0">{{ number_format($totalSchools) }}</div></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="sum-pill">
            <div class="sum-icon" style="background:#eff6ff;color:#3b82f6;"><i class="bi bi-person-badge"></i></div>
            <div><div class="text-muted small fw-bold text-uppercase">Total Guru</div><div class="h4 fw-extrabold mb-0">{{ number_format($totalGuru) }}</div></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="sum-pill">
            <div class="sum-icon" style="background:#f0fdf4;color:#22c55e;"><i class="bi bi-people"></i></div>
            <div><div class="text-muted small fw-bold text-uppercase">Total Siswa</div><div class="h4 fw-extrabold mb-0">{{ number_format($totalSiswa) }}</div></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="sum-pill">
            <div class="sum-icon" style="background:#fffbeb;color:#d97706;"><i class="bi bi-clock-history"></i></div>
            <div><div class="text-muted small fw-bold text-uppercase">Pending</div><div class="h4 fw-extrabold mb-0 text-warning">{{ number_format($pendingUsers) }}</div></div>
        </div>
    </div>
</div>

<div class="row g-4">
    @forelse($schools as $s)
    @php $admins = $s->users->where('role', 'admin'); $admin = $admins->first(); @endphp
    <div class="col-md-6 col-xl-4">
        <div class="school-card">
            <div class="school-card-top"></div>
            <div class="school-card-body">
                <div class="d-flex gap-3 align-items-center mb-2">
                    <div class="school-avatar">{{ strtoupper(substr($s->name, 0, 1)) }}</div>
                    <div class="flex-fill" style="min-width:0;">
                        <div class="fw-bold text-truncate">[ID: {{ $s->id }}] {{ $s->name }}</div>
                        <div class="text-muted small">{{ $s->city ?? '-' }} &bull; {{ $s->slug }}</div>
                    </div>
                    <span class="badge rounded-pill {{ $s->is_active ? 'bg-success' : 'bg-danger' }}">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <div class="small mb-3" style="background:#f8fafc;border-radius:10px;padding:8px 12px;">
                    <i class="bi bi-shield-check me-1 text-primary"></i>
                    @if($admin)
                        <b>{{ $admin->name }}</b> <span class="text-muted">(admin ID: {{ $admin->id }})</span>
                    @else
                        <span class="text-muted">Belum ada admin sekolah</span>
                    @endif
                </div>
                <div class="d-flex gap-2 mb-3">
                    <div class="mini-stat"><div class="num text-primary">{{ number_format($s->guru_count) }}</div><div class="lb">Guru</div></div>
                    <div class="mini-stat"><div class="num text-success">{{ number_format($s->siswa_count) }}</div><div class="lb">Siswa</div></div>
                    <div class="mini-stat"><div class="num {{ $s->pending_count ? 'text-danger' : '' }}">{{ number_format($s->pending_count) }}</div><div class="lb">Pending</div></div>
                </div>
                <a href="{{ route('admin.schools.detail', $s->id) }}" class="btn btn-primary w-100 fw-bold" style="border-radius:12px;">
                    <i class="bi bi-eye me-1"></i> Detail Sekolah
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="card border-0 shadow-sm text-center py-5 text-muted" style="border-radius:20px;">Belum ada sekolah terdaftar.</div></div>
    @endforelse
</div>

</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}
@endsection
