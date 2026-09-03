@extends('layouts.app')

@section('content')
@php
    $sppPersen = $sppTagihan > 0 ? round(($sppTerbayar / $sppTagihan) * 100) : 0;
    $piutang = max(0, $sppTagihan - $sppTerbayar);
@endphp

<style>
    .ad-hero {
        background: linear-gradient(135deg, var(--navy) 0%, #1e293b 100%);
        border-radius: 28px; padding: 40px; color: #fff;
        position: relative; overflow: hidden; margin-bottom: 32px;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }
    .ad-hero::after {
        content: ''; position: absolute; top: -100px; right: -100px;
        width: 300px; height: 300px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36,107,254,0.15) 0%, transparent 70%);
        pointer-events: none;
    }

    .ad-hero-eyebrow { font-size: 12px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; color: #94a3b8; }
    .ad-hero-title { font-size: 32px; font-weight: 800; margin: 8px 0; letter-spacing: -0.02em; }
    .ad-hero-subtitle { font-size: 14px; color: #94a3b8; margin: 0; }

    .ad-stat {
        background: #fff; border-radius: 24px; padding: 24px;
        border: 1px solid var(--border); transition: all 0.3s ease;
    }
    .ad-stat:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
    .ad-stat-icon {
        width: 56px; height: 56px; border-radius: 18px;
        display: flex; align-items: center; justify-content: center; font-size: 24px;
    }
    .ad-stat-num { font-size: 32px; font-weight: 800; color: var(--navy); margin-top: 12px; }
    .ad-stat-lbl { font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; }

    .ad-card { border-radius: 24px; border: 1px solid var(--border); overflow: hidden; }
    .ad-card-head { padding: 24px 30px; background: #fff; border-bottom: 1px solid var(--border); }
    .ad-card-title { font-size: 18px; font-weight: 800; color: var(--navy); display: flex; align-items: center; gap: 12px; }
    .ad-card-body { padding: 30px; background: #fff; }

    .ad-toggle {
        padding: 10px 18px; border-radius: 14px; font-size: 13px; font-weight: 700;
        border: 1.5px solid; cursor: pointer; background: #fff; transition: all 0.2s;
        display: flex; align-items: center; gap: 8px;
    }
    .ad-toggle:hover { filter: brightness(0.95); transform: translateY(-1px); }

    .ad-user-row { display: flex; align-items: center; gap: 16px; padding: 14px 0; }
    .ad-user-row + .ad-user-row { border-top: 1px solid #f1f5f9; }
    .ad-user-avatar {
        width: 44px; height: 44px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 15px; color: #fff; flex-shrink: 0;
    }

    .ad-kelas-bar { height: 10px; border-radius: 99px; background: #f1f5f9; overflow: hidden; margin-top: 8px; }
    .ad-kelas-fill { height: 100%; border-radius: 99px; transition: width 1s ease-out; }

    .lms-tile {
        border: 1px solid var(--border); border-radius: 20px; padding: 26px 18px;
        height: 100%; background: #fff; transition: all 0.3s ease;
    }
    .lms-tile:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: transparent; }
    .lms-tile-ico {
        width: 52px; height: 52px; border-radius: 16px; margin: 0 auto 14px;
        display: grid; place-items: center; color: #fff; font-size: 22px;
        box-shadow: 0 8px 16px rgba(15,23,42,.18);
    }

    @media (max-width: 768px) {
        .ad-hero { padding: 30px; border-radius: 24px; text-align: center; }
        .ad-hero-title { font-size: 24px; }
        .ad-hero .d-flex.gap-2 { justify-content: center; margin-top: 20px; }
        .ad-toggle, .ad-hero .btn { width: 100%; justify-content: center; }
        .ad-stat-num { font-size: 26px; }
        .ad-user-row { flex-direction: column; align-items: flex-start; gap: 8px; }
        .ad-user-row > div:last-child { width: 100%; text-align: left !important; padding-left: 0; }
    }
</style>

{{-- cPanel shell: sidebar + konten, CSS di-scope di partial sidebar --}}
<div class="cp-shell">
@include('admin.partials.sidebar')
<div class="cp-main">

{{-- Filter sekolah (super admin) --}}
@if(!empty($isSuperAdmin))
<div class="card border-0 shadow-sm mb-4" style="border-radius:20px;">
    <div class="card-body p-3 d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-funnel-fill text-primary"></i>
            <span class="fw-bold small">Filter Sekolah:</span>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2 flex-wrap">
            <select name="school_id" class="form-select form-select-sm" style="border-radius:10px;min-width:220px;" onchange="this.form.submit()">
                <option value="">Semua Sekolah (Global)</option>
                @foreach(($allSchools ?? collect()) as $sc)
                    <option value="{{ $sc->id }}" @selected(($filterSchoolId ?? null) == $sc->id)>[ID: {{ $sc->id }}] {{ $sc->name }}</option>
                @endforeach
            </select>
            @if(!empty($filterSchoolId))
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:10px;">Reset</a>
                <a href="{{ route('admin.schools.detail', $filterSchoolId) }}" class="btn btn-sm btn-primary" style="border-radius:10px;"><i class="bi bi-eye me-1"></i>Detail Sekolah</a>
            @endif
        </form>
        @if(!empty($filterSchool))
            <span class="badge rounded-pill {{ $filterSchool->is_active ? 'bg-success' : 'bg-danger' }}">{{ $filterSchool->is_active ? 'Aktif' : 'Nonaktif' }}</span>
        @endif
    </div>
</div>
@endif

{{-- Hero Section (admin sekolah) / Header ramping (admin pusat) --}}
@if(!empty($isSuperAdmin))
<div class="ad-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-4" style="position:relative; z-index:1;">
        <div>
            <div class="ad-hero-eyebrow">Admin Pusat &bull; Control Center</div>
            <h1 class="ad-hero-title">Kelola Semua Sekolah</h1>
            <p class="ad-hero-subtitle">Aktivasi akun sekolah, atur fitur, dan pantau statistik tiap sekolah.</p>
        </div>
        <div class="d-flex gap-3 flex-wrap">
            <a href="{{ route('admin.features') }}" class="btn btn-light fw-bold px-4" style="border-radius:14px;">
                <i class="bi bi-sliders me-2"></i> Kelola Fitur
            </a>
            <a href="{{ route('global.portal') }}" class="btn btn-outline-light fw-bold px-4" style="border-radius:14px;">
                <i class="bi bi-globe me-2"></i> Global Portal
            </a>
        </div>
    </div>
</div>

{{-- Ringkasan global admin pusat --}}
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Sekolah</div>
                <div class="ad-stat-icon" style="background:#eff6ff; color:var(--blue);"><i class="bi bi-buildings-fill"></i></div>
            </div>
            <div class="ad-stat-num">{{ number_format($totalSchools ?? 0) }}</div>
            <div class="small text-muted mt-2"><i class="bi bi-check-circle text-primary"></i> Terdaftar</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Guru</div>
                <div class="ad-stat-icon" style="background:#f0fdf4; color:#16a34a;"><i class="bi bi-person-badge"></i></div>
            </div>
            <div class="ad-stat-num">{{ number_format($totalGuru) }}</div>
            <div class="small text-muted mt-2"><i class="bi bi-people text-success"></i> Semua Sekolah</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Siswa</div>
                <div class="ad-stat-icon" style="background:#fefce8; color:#d97706;"><i class="bi bi-people"></i></div>
            </div>
            <div class="ad-stat-num">{{ number_format($totalSiswa) }}</div>
            <div class="small text-muted mt-2"><i class="bi bi-mortarboard text-primary"></i> Semua Sekolah</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Pending Aktivasi</div>
                <div class="ad-stat-icon" style="background:#fef2f2; color:#dc2626;"><i class="bi bi-clock-history"></i></div>
            </div>
            <div class="ad-stat-num text-danger">{{ number_format($pendingCount) }}</div>
            <div class="small text-muted mt-2"><i class="bi bi-exclamation-circle"></i> Butuh Persetujuan</div>
        </div>
    </div>
</div>
@else
<div class="ad-hero" style="background:linear-gradient(135deg,#1e1b4b 0%,#312e81 55%,#1d4ed8 100%);">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-4" style="position:relative; z-index:1;">
        <div class="d-flex gap-3 align-items-center">
            <div style="width:64px;height:64px;border-radius:20px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);display:grid;place-items:center;font-weight:800;font-size:26px;color:#fff;flex-shrink:0;">
                {{ strtoupper(substr($filterSchool->name ?? 'S', 0, 1)) }}
            </div>
            <div>
                <div class="ad-hero-eyebrow" style="color:#c7d2fe;">Dashboard Sekolah</div>
                <h1 class="ad-hero-title">{{ $filterSchool->name ?? 'Sekolah Saya' }}</h1>
                <p class="ad-hero-subtitle" style="color:#c7d2fe;">{{ $filterSchool->city ?? '' }}{{ !empty($filterSchool->enroll_code) ? ' • Kode: '.$filterSchool->enroll_code : '' }}</p>
            </div>
        </div>
        <div class="d-flex gap-3 flex-wrap">
            <form method="POST" action="{{ route('admin.registration.toggle') }}">
                @csrf @method('PATCH') <input type="hidden" name="role" value="guru">
                <button class="ad-toggle" style="border-color:{{ $registrationGuruEnabled ? '#fecaca' : '#bbf7d0' }}; color:{{ $registrationGuruEnabled ? '#dc2626' : '#15803d' }};">
                    <i class="bi bi-{{ $registrationGuruEnabled ? 'person-dash' : 'person-check' }}"></i>
                    Guru: {{ $registrationGuruEnabled ? 'Open' : 'Closed' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.registration.toggle') }}">
                @csrf @method('PATCH') <input type="hidden" name="role" value="siswa">
                <button class="ad-toggle" style="border-color:{{ $registrationSiswaEnabled ? '#fecaca' : '#bbf7d0' }}; color:{{ $registrationSiswaEnabled ? '#dc2626' : '#15803d' }};">
                    <i class="bi bi-{{ $registrationSiswaEnabled ? 'person-dash' : 'person-check' }}"></i>
                    Siswa: {{ $registrationSiswaEnabled ? 'Open' : 'Closed' }}
                </button>
            </form>
            <a href="{{ route('pengumuman.create') }}" class="btn btn-light fw-bold px-4" style="border-radius:14px;">
                <i class="bi bi-plus-circle-fill me-2"></i> Announcement
            </a>
        </div>
    </div>
</div>
@endif

{{-- Per-school overview (cPanel table, super admin) --}}
@if(!empty($isSuperAdmin) && !empty($allSchools) && $allSchools->count())
<div class="row g-4 mb-5">
    <div class="col-12">
        <div class="ad-card shadow-sm">
            <div class="ad-card-head d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="ad-card-title"><i class="bi bi-buildings-fill text-primary"></i> Sekolah Terdaftar ({{ $allSchools->count() }})</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.features') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold"><i class="bi bi-sliders me-1"></i>Kelola Fitur</a>
                    <a href="{{ route('admin.schools.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold">Kelola Sekolah</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th class="ps-4">Sekolah</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Users</th>
                            <th class="text-center">Posts</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allSchools as $sc)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">[ID: {{ $sc->id }}] {{ $sc->name }}</div>
                                <div class="text-muted small">{{ $sc->city ?? '-' }} &bull; {{ $sc->slug }}</div>
                            </td>
                            <td class="text-center"><span class="badge rounded-pill {{ $sc->is_active ? 'bg-success' : 'bg-danger' }}">{{ $sc->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="text-center fw-bold">{{ number_format($sc->users_count ?? 0) }}</td>
                            <td class="text-center fw-bold">{{ number_format($sc->posts_count ?? 0) }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.schools.detail', $sc->id) }}" class="btn btn-sm btn-primary" style="border-radius:10px;"><i class="bi bi-eye me-1"></i>Detail</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

@if(empty($isSuperAdmin))
{{-- LMS Overview (khusus admin sekolah — disembunyikan di admin pusat) --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="ad-card shadow-sm">
            <div class="ad-card-head d-flex justify-content-between align-items-center">
                <h2 class="ad-card-title"><i class="bi bi-mortarboard text-primary"></i> E-Learning (LMS) Overview</h2>
                <span class="badge rounded-pill bg-primary-subtle text-primary px-3" style="font-size:11px; font-weight:800;">Learning Management System</span>
            </div>
            <div class="ad-card-body">
                <div class="row g-3 text-center">
                    @foreach([
                        ['n' => $totalMapel, 'l' => 'Mata Pelajaran', 'i' => 'bi-journal-bookmark-fill', 'g' => 'linear-gradient(135deg,#4f46e5,#2563eb)', 't' => '#4f46e5'],
                        ['n' => $totalMateri, 'l' => 'Materi Dibagikan', 'i' => 'bi-file-earmark-text-fill', 'g' => 'linear-gradient(135deg,#059669,#10b981)', 't' => '#059669'],
                        ['n' => $totalTugas, 'l' => 'Total Tugas', 'i' => 'bi-clipboard-check-fill', 'g' => 'linear-gradient(135deg,#d97706,#f59e0b)', 't' => '#d97706'],
                        ['n' => $tugasBelumDinilai, 'l' => 'Jawaban Perlu Dinilai', 'i' => 'bi-exclamation-circle-fill', 'g' => $tugasBelumDinilai > 0 ? 'linear-gradient(135deg,#dc2626,#ef4444)' : 'linear-gradient(135deg,#94a3b8,#cbd5e1)', 't' => $tugasBelumDinilai > 0 ? '#dc2626' : '#64748b'],
                    ] as $tile)
                    <div class="col-lg-3 col-md-6">
                        <div class="lms-tile">
                            <div class="lms-tile-ico" style="background:{{ $tile['g'] }};"><i class="bi {{ $tile['i'] }}"></i></div>
                            <div class="fw-extrabold" style="font-size:34px;color:{{ $tile['t'] }};line-height:1;">{{ number_format($tile['n']) }}</div>
                            <div class="small fw-bold text-muted text-uppercase mt-2" style="letter-spacing:0.06em;font-size:11px;">{{ $tile['l'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Statistic Grid --}}
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Teachers</div>
                <div class="ad-stat-icon" style="background:#eff6ff; color:var(--blue);"><i class="bi bi-person-badge"></i></div>
            </div>
            <div class="ad-stat-num">{{ number_format($totalGuru) }}</div>
            <div class="small text-muted mt-2"><i class="bi bi-graph-up text-success"></i> Active Faculty</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Students</div>
                <div class="ad-stat-icon" style="background:#f0fdf4; color:#16a34a;"><i class="bi bi-people"></i></div>
            </div>
            <div class="ad-stat-num">{{ number_format($totalSiswa) }}</div>
            <div class="small text-muted mt-2"><i class="bi bi-mortarboard text-primary"></i> Enrolled Pupils</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Active Classes</div>
                <div class="ad-stat-icon" style="background:#fefce8; color:#d97706;"><i class="bi bi-building"></i></div>
            </div>
            <div class="ad-stat-num">{{ number_format($totalKelas) }}</div>
            <div class="small text-muted mt-2"><i class="bi bi-check-circle text-warning"></i> Current Rooms</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Pending SPP</div>
                <div class="ad-stat-icon" style="background:#fef2f2; color:#dc2626;"><i class="bi bi-cash-stack"></i></div>
            </div>
            <div class="ad-stat-num text-danger">{{ $sppKurang }}</div>
            <div class="small text-muted mt-2"><i class="bi bi-clock-history"></i> Outstanding Debt</div>
        </div>
    </div>
</div>

{{-- KPI Premium Tiles --}}
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Total Nilai</div>
                <div class="ad-stat-icon" style="background:#f5f3ff;color:#7c3aed;"><i class="bi bi-clipboard-data"></i></div>
            </div>
            <div class="ad-stat-num">{{ number_format($totalNilai) }}</div>
            <div class="small text-muted mt-2"><i class="bi bi-trophy text-warning"></i> Records Entered</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Rata-rata Nilai</div>
                <div class="ad-stat-icon" style="background:#ecfeff;color:#0e9aa7;"><i class="bi bi-award"></i></div>
            </div>
            <div class="ad-stat-num" style="display:flex;align-items:baseline;gap:4px;">{{ $rataNilai }}<span style="font-size:14px;color:#94a3b8;font-weight:700;">/100</span></div>
            <div class="small text-muted mt-2"><i class="bi bi-stars text-primary"></i> Academic Average</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Hadir Hari Ini</div>
                <div class="ad-stat-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-person-check"></i></div>
            </div>
            <div class="ad-stat-num text-success">{{ $hadirHariIni }}</div>
            <div class="small text-muted mt-2"><i class="bi bi-calendar-check"></i> Present Today (Total {{ $absensiHariIni }})</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Pengumpulan Tugas</div>
                <div class="ad-stat-icon" style="background:#fff5f6;color:#d94b61;"><i class="bi bi-arrow-repeat"></i></div>
            </div>
            <div class="ad-stat-num">{{ number_format($totalPengumpulan) }}</div>
            <div class="small text-muted mt-2"><i class="bi bi-check2-circle text-success"></i> {{ number_format($totalPengumpulanDinilai) }} Sudah Dinilai</div>
        </div>
    </div>
</div>

{{-- Analytics: Nilai, Kelas, Absensi --}}
<div class="row g-4 mb-5">
    <div class="col-xl-4 col-md-6">
        <div class="ad-card shadow-sm h-100">
            <div class="ad-card-head d-flex justify-content-between align-items-center">
                <h2 class="ad-card-title"><i class="bi bi-pie-chart" style="color:#7c3aed;"></i> Distribusi Predikat</h2>
            </div>
            <div class="ad-card-body"><div style="height:260px;"><canvas id="gradeChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="ad-card shadow-sm h-100">
            <div class="ad-card-head"><h2 class="ad-card-title"><i class="bi bi-bar-chart" style="color:#2563eb;"></i> Siswa per Kelas</h2></div>
            <div class="ad-card-body"><div style="height:260px;"><canvas id="kelasChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="ad-card shadow-sm h-100">
            <div class="ad-card-head"><h2 class="ad-card-title"><i class="bi bi-person-check" style="color:#16a34a;"></i> Status Absensi</h2></div>
            <div class="ad-card-body"><div style="height:260px;"><canvas id="absensiChart"></canvas></div></div>
        </div>
    </div>
</div>

{{-- Registration Trend --}}
<div class="row g-4 mb-5">
    <div class="col-12">
        <div class="ad-card shadow-sm">
            <div class="ad-card-head d-flex justify-content-between align-items-center">
                <h2 class="ad-card-title"><i class="bi bi-graph-up-arrow text-primary"></i> Tren Pendaftaran (6 Bulan Terakhir)</h2>
                <span class="badge rounded-pill bg-primary-subtle text-primary px-3" style="font-size:11px;font-weight:800;">Registrations</span>
            </div>
            <div class="ad-card-body"><div style="height:280px;"><canvas id="regChart"></canvas></div></div>
        </div>
    </div>
</div>

{{-- Analytics & Financial Status --}}
<div class="row g-4 mb-5">
    <div class="col-lg-8">
        <div class="ad-card shadow-sm">
            <div class="ad-card-head d-flex justify-content-between align-items-center">
                <h2 class="ad-card-title"><i class="bi bi-bar-chart-line text-primary"></i> Payment Trends</h2>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">Last 6 Months</button>
                    <ul class="dropdown-menu shadow border-0">
                        <li><a class="dropdown-item small fw-bold" href="#">Current Year</a></li>
                    </ul>
                </div>
            </div>
            <div class="ad-card-body">
                <div style="height:320px;"><canvas id="sppChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ad-card shadow-sm">
            <div class="ad-card-head"><h2 class="ad-card-title"><i class="bi bi-pie-chart text-purple" style="color:#7c3aed;"></i> Financial Health</h2></div>
            <div class="ad-card-body text-center">
                <div style="position:relative; height:180px; margin: 0 auto 24px;">
                    <canvas id="sppDonut"></canvas>
                    <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; pointer-events:none;">
                        <div style="font-size:32px; font-weight:800; color:var(--navy);">{{ $sppPersen }}%</div>
                        <div style="font-size:12px; color:var(--muted); font-weight:700;">COLLECTED</div>
                    </div>
                </div>
                <div class="px-2">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="small fw-bold text-muted"><i class="bi bi-circle-fill me-2" style="color:#16a34a; font-size:8px;"></i> Received</span>
                        <span class="small fw-extrabold text-success">Rp {{ number_format($sppTerbayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="small fw-bold text-muted"><i class="bi bi-circle-fill me-2" style="color:#f59e0b; font-size:8px;"></i> Receivable</span>
                        <span class="small fw-extrabold text-warning">Rp {{ number_format($piutang, 0, ',', '.') }}</span>
                    </div>
                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-dark">Total Revenue</span>
                        <span class="h6 fw-extrabold mb-0">Rp {{ number_format($sppTagihan, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Lists Section --}}
<div class="row g-4">
    <div class="col-lg-6">
        <div class="ad-card shadow-sm h-100">
            <div class="ad-card-head d-flex justify-content-between align-items-center">
                <h2 class="ad-card-title"><i class="bi bi-shield-check text-primary"></i> Recent Registrations</h2>
                <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">View All</a>
            </div>
            <div class="ad-card-body">
                @forelse($recentUsers as $u)
                    <div class="ad-user-row">
                        <div class="ad-user-avatar" style="background:{{ $u->role === 'guru' ? 'linear-gradient(135deg,#3b82f6,#2563eb)' : 'linear-gradient(135deg,#22c55e,#16a34a)' }}; shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div style="flex:1;">
                            <div class="fw-bold text-dark" style="font-size:14px;">{{ $u->name }}</div>
                            <div class="text-muted small">{{ $u->email }}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge rounded-pill px-3 {{ $u->role === 'guru' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success' }} mb-1" style="font-size:10px; font-weight:800; text-transform:uppercase;">{{ $u->role }}</span>
                            <div class="small fw-bold {{ $u->aktif ? 'text-success' : 'text-muted' }}" style="font-size:10px;">{{ $u->aktif ? 'Active' : 'Inactive' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted small">No recent activity detected.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="ad-card shadow-sm h-100">
            <div class="ad-card-head d-flex justify-content-between align-items-center">
                <h2 class="ad-card-title"><i class="bi bi-bar-chart-fill text-warning"></i> Classroom Density</h2>
                <a href="{{ route('kelas.index') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold">Manage</a>
            </div>
            <div class="ad-card-body">
                @forelse($kelasSummaries as $k)
                    @php $maxSiswa = max(1, $kelasSummaries->max('siswa_count')); @endphp
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark" style="font-size:14px;">{{ $k->nama }}</span>
                            <span class="text-muted" style="font-size:11px;">
                                <strong class="text-primary">{{ $k->siswa_count }}</strong> Students &bull; <strong class="text-success">{{ $k->guru_count }}</strong> Faculty
                            </span>
                        </div>
                        <div class="ad-kelas-bar">
                            <div class="ad-kelas-fill" style="width:{{ min(100, ($k->siswa_count / $maxSiswa) * 100) }}%; background: linear-gradient(90deg, var(--blue), #60a5fa);"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted small">No classroom data available.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endif{{-- /blok academy khusus admin sekolah --}}
</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';

        var lineCtx = document.getElementById('sppChart');
        if (lineCtx) {
            var g1 = lineCtx.getContext('2d').createLinearGradient(0,0,0,300);
            g1.addColorStop(0, 'rgba(37,99,235,0.2)'); g1.addColorStop(1, 'rgba(37,99,235,0)');
            var g2 = lineCtx.getContext('2d').createLinearGradient(0,0,0,300);
            g2.addColorStop(0, 'rgba(22,163,74,0.2)'); g2.addColorStop(1, 'rgba(22,163,74,0)');

            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [{
                        label: 'Tagihan', data: {!! json_encode($chartTagihan) !!},
                        borderColor: '#2563eb', backgroundColor: g1, borderWidth: 3, fill: true, tension: 0.4,
                        pointBackgroundColor: '#fff', pointBorderColor: '#2563eb', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6
                    }, {
                        label: 'Terbayar', data: {!! json_encode($chartTerbayar) !!},
                        borderColor: '#16a34a', backgroundColor: g2, borderWidth: 3, fill: true, tension: 0.4,
                        pointBackgroundColor: '#fff', pointBorderColor: '#16a34a', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: {
                        legend: { position: 'top', align: 'end', labels: { usePointStyle: true, padding: 25, font: { weight: '700', size: 12 } } },
                        tooltip: {
                            backgroundColor: '#0f172a', titleFont: { size: 13, weight: '700' },
                            bodyFont: { size: 13 }, padding: 12, cornerRadius: 10,
                            callbacks: { label: function(c) { return ' ' + c.dataset.label + ': Rp ' + c.raw.toLocaleString('id-ID'); } }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9', drawBorder: false }, ticks: { callback: v => 'Rp ' + (v/1000) + 'k', padding: 10 } },
                        x: { grid: { display: false, drawBorder: false }, ticks: { padding: 10 } }
                    }
                }
            });
        }

        var donutCtx = document.getElementById('sppDonut');
        if (donutCtx) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Received', 'Receivable'],
                    datasets: [{ data: [{{ (float) $sppTerbayar }}, {{ $piutang }}], backgroundColor: ['#16a34a', '#f59e0b'], borderWidth: 0, cutout: '82%', hoverOffset: 8 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }

        var gradeCtx = document.getElementById('gradeChart');
        if (gradeCtx) {
            new Chart(gradeCtx, {
                type: 'doughnut',
                data: {
                    labels: ['A (≥90)', 'B (80-89)', 'C (70-79)', 'D (60-69)', 'E (<60)'],
                    datasets: [{
                        data: [{{ $gradeDist['A'] }}, {{ $gradeDist['B'] }}, {{ $gradeDist['C'] }}, {{ $gradeDist['D'] }}, {{ $gradeDist['E'] }}],
                        backgroundColor: ['#16a34a', '#3b82f6', '#f59e0b', '#f97316', '#dc2626'],
                        borderWidth: 0, hoverOffset: 8
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, font: { weight: '600', size: 11 } } } } }
            });
        }

        var kelasCtx = document.getElementById('kelasChart');
        if (kelasCtx) {
            new Chart(kelasCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($kelasNames) !!},
                    datasets: [{
                        label: 'Siswa', data: {!! json_encode($kelasSiswa) !!},
                        backgroundColor: '#3b82f6', hoverBackgroundColor: '#2563eb', borderRadius: 8, maxBarThickness: 34
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 10 }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9', drawBorder: false } },
                        x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } }
                    }
                }
            });
        }

        var absensiCtx = document.getElementById('absensiChart');
        if (absensiCtx) {
            new Chart(absensiCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha'],
                    datasets: [{
                        data: [{{ $distAbsensi['hadir'] }}, {{ $distAbsensi['terlambat'] }}, {{ $distAbsensi['izin'] }}, {{ $distAbsensi['sakit'] }}, {{ $distAbsensi['alpha'] }}],
                        backgroundColor: ['#22c55e', '#f59e0b', '#3b82f6', '#ec4899', '#ef4444'],
                        borderWidth: 0, hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { weight: '600', size: 11 } } },
                        tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 10 }
                    }
                }
            });
        }

        var regCtx = document.getElementById('regChart');
        if (regCtx) {
            var rg = regCtx.getContext('2d').createLinearGradient(0,0,0,280);
            rg.addColorStop(0, 'rgba(139,92,246,0.3)'); rg.addColorStop(1, 'rgba(139,92,246,0)');
            new Chart(regCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($regLabels) !!},
                    datasets: [{
                        label: 'Pendaftar', data: {!! json_encode($regCounts) !!},
                        backgroundColor: rg, borderColor: '#8b5cf6', borderWidth: 2, borderRadius: 8, maxBarThickness: 50
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 10 }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0, padding: 10 }, grid: { color: '#f1f5f9', drawBorder: false } },
                        x: { grid: { display: false }, ticks: { padding: 10 } }
                    }
                }
            });
        }
    });
</script>
@endsection
