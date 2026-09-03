@extends('layouts.app', ['title' => 'Detail Sekolah: ' . $school->name])
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

    .back-link { color: #94a3b8; font-size: 13px; font-weight: 600; text-decoration: none; }
    .back-link:hover { color: var(--blue); }

    .school-stat {
        border-radius: 20px; border: 1px solid var(--border); background: #fff;
        padding: 24px; box-shadow: var(--shadow); transition: all 0.3s;
    }
    .school-stat:hover { box-shadow: var(--shadow-lg); transform: translateY(-2px); }
    .school-stat-icon {
        width: 50px; height: 50px; border-radius: 16px;
        display: grid; place-items: center; font-size: 22px;
    }
    .school-stat-num { font-size: 28px; font-weight: 800; color: var(--navy); margin-top: 12px; }
    .school-stat-lbl { font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; }

    .activity-row { display: flex; align-items: center; gap: 16px; padding: 14px 0; }
    .activity-row + .activity-row { border-top: 1px solid #f1f5f9; }
    .activity-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .activity-text { flex: 1; font-size: 14px; color: var(--navy); font-weight: 600; }
    .activity-time { font-size: 12px; color: var(--muted); }

    .section-card { border-radius: 24px; border: 1px solid var(--border); background: #fff; box-shadow: var(--shadow); overflow: hidden; }
    .section-card-head { padding: 24px 30px; border-bottom: 1px solid var(--border); }
    .section-card-title { font-size: 18px; font-weight: 800; color: var(--navy); }
    .section-card-body { padding: 30px; }

    .status-badge { padding: 6px 16px; border-radius: 99px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
    .status-badge.aktif { background: #dcfce7; color: #166534; }
    .status-badge.nonaktif { background: #fef2f2; color: #991b1b; }

    .action-btn {
        padding: 8px 18px; border-radius: 12px; font-size: 13px; font-weight: 700;
        border: 1.5px solid; cursor: pointer; transition: all 0.2s;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .action-btn.btn-active { border-color: #bbf7d0; color: #15803d; background: #f0fdf4; }
    .action-btn.btn-active:hover { background: #bbf7d0; }
    .action-btn.btn-inactive { border-color: #fecaca; color: #dc2626; background: #fef2f2; }
    .action-btn.btn-inactive:hover { background: #fecaca; }

    .school-stat-link { text-decoration: none; display: block; height: 100%; }
    .school-stat-link .school-stat:hover { border-color: var(--blue); }
    .school-stat-cta { font-size: 11px; font-weight: 800; color: var(--blue); margin-top: 8px; }

    @media (max-width: 768px) {
        .cp-page-header { padding: 24px; border-radius: 20px; }
        .cp-page-title { font-size: 22px; }
        .school-stat-num { font-size: 22px; }
    }
</style>

<div class="cp-shell">
@include('admin.partials.sidebar')
<div class="cp-main">
<a href="{{ route('admin.schools.index') }}" class="back-link mb-3 d-inline-block">
    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Sekolah
</a>

<div class="cp-page-header">
    <div class="position-relative" style="z-index:1;">
        <div class="small fw-bold" style="letter-spacing:.1em;color:#94a3b8;">DETAIL SEKOLAH</div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="cp-page-title">{{ $school->name }}</h1>
                <p class="cp-page-sub">{{ $school->city }} • ID: {{ $school->id }} • {{ $school->slug }}</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.users', ['school_id' => $school->id]) }}" class="action-btn" style="border-color:#bfdbfe;color:#1d4ed8;background:#eff6ff;text-decoration:none;">
                    <i class="bi bi-people"></i> Kelola Akun
                </a>
                <a href="{{ route('admin.school-admins.index') }}" class="action-btn" style="border-color:#e2e8f0;color:var(--navy);background:#fff;text-decoration:none;">
                    <i class="bi bi-shield-check"></i> Admin Sekolah
                </a>
                <form method="POST" action="{{ route('admin.schools.toggle', $school) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="action-btn {{ $school->is_active ? 'btn-inactive' : 'btn-active' }}">
                        <i class="bi bi-{{ $school->is_active ? 'person-dash' : 'person-check' }}"></i>
                        {{ $school->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.schools.destroy', $school) }}" onsubmit="return confirm('Hapus sekolah ini? Semua data terkait akan terhapus.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn" style="border-color:#fecaca;color:#dc2626;background:#fef2f2;">
                        <i class="bi bi-trash3"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <a href="{{ route('admin.users', ['school_id' => $school->id]) }}" class="school-stat-link">
        <div class="school-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="school-stat-icon" style="background:#eff6ff;color:var(--blue);"><i class="bi bi-person-badge"></i></div>
            </div>
            <div class="school-stat-num">{{ number_format($guruCount) }}</div>
            <div class="school-stat-lbl">Guru</div>
            <div class="school-stat-cta">Kelola <i class="bi bi-arrow-right"></i></div>
        </div>
        </a>
    </div>
    <div class="col-lg-3 col-md-6">
        <a href="{{ route('admin.users', ['school_id' => $school->id]) }}" class="school-stat-link">
        <div class="school-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="school-stat-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-people"></i></div>
            </div>
            <div class="school-stat-num">{{ number_format($siswaCount) }}</div>
            <div class="school-stat-lbl">Siswa</div>
            <div class="school-stat-cta">Kelola <i class="bi bi-arrow-right"></i></div>
        </div>
        </a>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="school-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="school-stat-icon" style="background:#fefce8;color:#d97706;"><i class="bi bi-building"></i></div>
            </div>
            <div class="school-stat-num">{{ number_format($totalUsers) }}</div>
            <div class="school-stat-lbl">Total User</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="school-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="school-stat-icon" style="background:#fff5f6;color:#d94b61;"><i class="bi bi-file-earmark-text"></i></div>
            </div>
            <div class="school-stat-num">{{ number_format($totalPosts) }}</div>
            <div class="school-stat-lbl">Total Post</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-lg-4 col-md-6">
        <div class="school-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="school-stat-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-person-check"></i></div>
            </div>
            <div class="school-stat-num text-success">{{ number_format($activeUsers) }}</div>
            <div class="school-stat-lbl">User Aktif</div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <a href="{{ route('admin.users', ['school_id' => $school->id]) }}" class="school-stat-link">
        <div class="school-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="school-stat-icon" style="background:#fef2f2;color:#dc2626;"><i class="bi bi-clock"></i></div>
            </div>
            <div class="school-stat-num text-danger">{{ number_format($pendingUsers) }}</div>
            <div class="school-stat-lbl">Menunggu Aktivasi</div>
            <div class="school-stat-cta">Setujui sekarang <i class="bi bi-arrow-right"></i></div>
        </div>
        </a>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="school-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="school-stat-icon" style="background:#eff6ff;color:var(--blue);"><i class="bi bi-journal-text"></i></div>
            </div>
            <div class="school-stat-num">{{ number_format($totalAbsensi) }}</div>
            <div class="school-stat-lbl">Total Absensi</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-lg-4 col-md-6">
        <div class="school-stat">
            <div class="school-stat-icon" style="background:#f5f3ff;color:#7c3aed;"><i class="bi bi-clipboard-data"></i></div>
            <div class="school-stat-num">{{ number_format($totalNilai) }}</div>
            <div class="school-stat-lbl">Total Nilai</div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="school-stat">
            <div class="school-stat-icon" style="background:#fffbeb;color:#d97706;"><i class="bi bi-arrow-repeat"></i></div>
            <div class="school-stat-num">{{ number_format($totalTugas) }}</div>
            <div class="school-stat-lbl">Total Tugas</div>
        </div>
    </div>
</div>

<div class="section-card mb-5">
    <div class="section-card-body d-flex align-items-center gap-3 flex-wrap" style="padding:20px 30px;">
        <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#4f46e5,#2563eb);display:grid;place-items:center;color:#fff;font-size:20px;flex-shrink:0;"><i class="bi bi-shield-check"></i></div>
        <div class="flex-fill" style="min-width:200px;">
            <div class="fw-bold" style="font-size:14px;">Admin Sekolah Ini</div>
            @if($schoolAdmins->count())
                <div class="small text-muted">{{ $schoolAdmins->map(fn($a) => $a->name.' [ID: '.$a->id.']'.($a->aktif ? '' : ' (nonaktif)'))->join(', ') }}</div>
            @else
                <div class="small text-muted">Belum ada admin — hanya Admin Pusat yang bisa mengatur sekolah ini.</div>
            @endif
        </div>
        <a href="{{ route('admin.school-admins.index') }}" class="btn btn-sm btn-primary" style="border-radius:10px;"><i class="bi bi-gear me-1"></i>Kelola Admin</a>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-card-head">
                <h2 class="section-card-title"><i class="bi bi-person-plus me-2" style="color:var(--blue);"></i>Pendaftaran Sekolah Ini</h2>
            </div>
            <div class="section-card-body">
                <p class="small text-muted mb-3">Buka/tutup pendaftaran untuk sekolah ini. Setelah dibuka, admin sekolah juga bisa mengaturnya dari Pengaturan.</p>
                @foreach([['role' => 'guru', 'label' => 'Pendaftaran Guru', 'open' => $school->reg_guru_open], ['role' => 'siswa', 'label' => 'Pendaftaran Siswa', 'open' => $school->reg_siswa_open]] as $reg)
                <form method="POST" action="{{ route('admin.schools.registration', $school) }}" class="d-flex align-items-center gap-3 py-3 {{ !$loop->first ? 'border-top' : '' }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="role" value="{{ $reg['role'] }}">
                    <div class="flex-fill">
                        <div class="fw-bold" style="font-size:14px;">{{ $reg['label'] }}</div>
                        <span class="badge rounded-pill {{ $reg['open'] ? 'bg-success' : 'bg-secondary' }}">{{ $reg['open'] ? 'DIBUKA' : 'DITUTUP' }}</span>
                    </div>
                    <button class="btn btn-sm {{ $reg['open'] ? 'btn-outline-danger' : 'btn-success' }}" style="border-radius:10px;">{{ $reg['open'] ? 'Tutup' : 'Buka' }}</button>
                </form>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-card-head d-flex justify-content-between align-items-center">
                <h2 class="section-card-title"><i class="bi bi-sliders me-2" style="color:var(--blue);"></i>Fitur Sekolah Ini</h2>
                <a href="{{ route('admin.features') }}" class="btn btn-sm btn-outline-primary" style="border-radius:10px;">Default Global</a>
            </div>
            <div class="section-card-body" style="max-height:420px;overflow-y:auto;">
                @foreach($featureFlags as $flag)
                <form method="POST" action="{{ route('admin.schools.feature.toggle', $school) }}" class="d-flex align-items-center gap-3 py-2 {{ !$loop->first ? 'border-top' : '' }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="key" value="{{ $flag['key'] }}">
                    <div class="flex-fill">
                        <div class="fw-bold" style="font-size:13.5px;">{{ $flag['label'] }}</div>
                        <div class="text-muted" style="font-size:11.5px;">{{ $flag['category'] }}</div>
                    </div>
                    <span class="badge rounded-pill {{ $flag['value'] ? 'bg-success' : 'bg-danger' }}">{{ $flag['currentStatus'] }}</span>
                    <button class="btn btn-sm {{ $flag['value'] ? 'btn-outline-danger' : 'btn-outline-success' }}" style="border-radius:10px;">{{ $flag['value'] ? 'Off' : 'On' }}</button>
                </form>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="section-card">
            <div class="section-card-head">
                <h2 class="section-card-title"><i class="bi bi-clock-history me-2" style="color:var(--blue);"></i>Aktivitas Terakhir</h2>
            </div>
            <div class="section-card-body">
                @forelse($recentActivity as $act)
                    <div class="activity-row">
                        <div class="activity-dot" style="background:{{ $act->activity_type === 'login' ? '#22c55e' : ($act->activity_type === 'logout' ? '#ef4444' : '#3b82f6') }};"></div>
                        <div class="flex-1">
                            <div class="activity-text">{{ $act->description ?? ucfirst($act->activity_type) }} — {{ $act->user->name }}</div>
                            <div class="small text-muted">{{ $act->user->email }}</div>
                        </div>
                        <div class="activity-time">{{ $act->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted small">Belum ada aktivitas terkini untuk sekolah ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}
@endsection