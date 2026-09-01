@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .ah-page { padding: 18px 14px 100px; max-width: 640px; margin: 0 auto; }

    .ah-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        border-radius: var(--radius-lg); padding: 24px 20px; color: #fff;
        margin-bottom: 20px; position: relative; overflow: hidden;
    }
    .ah-hero::before {
        content: ''; position: absolute; top: -30px; right: -30px; width: 140px; height: 140px;
        background: radial-gradient(circle, rgba(99,102,241,0.35) 0%, transparent 70%);
    }

    .ah-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 18px; position: relative; z-index: 1; }
    .ah-stat {
        background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);
        border-radius: 12px; padding: 12px 6px; text-align: center;
    }
    .ah-stat .n { font-size: 18px; font-weight: 900; color: #fff; line-height: 1; }
    .ah-stat .l { font-size: 8px; font-weight: 700; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-top: 4px; }

    .ah-card { background: #fff; border-radius: 18px; padding: 18px; margin-bottom: 14px; border: 1px solid var(--line); box-shadow: var(--shadow-card); }
    .ah-card-title { font-size: 14px; font-weight: 800; color: var(--navy); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }

    .ah-filter-bar { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
    .ah-filter-bar input, .ah-filter-bar select {
        padding: 10px 12px; border-radius: 10px; border: 1px solid var(--line);
        font-size: 12px; font-weight: 600; background: #f8fafc; flex: 1; min-width: 0;
    }
    .ah-filter-bar select { flex: 0 0 auto; min-width: 100px; }

    .ah-timeline-item {
        display: flex; gap: 12px; padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .ah-timeline-item:last-child { border-bottom: none; }
    .ah-timeline-dot {
        width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 14px;
    }
    .ah-timeline-info { flex: 1; min-width: 0; }
    .ah-timeline-user { font-size: 13px; font-weight: 700; color: var(--navy); }
    .ah-timeline-desc { font-size: 11px; color: #64748b; margin-top: 2px; line-height: 1.4; }
    .ah-timeline-time { font-size: 10px; color: #94a3b8; margin-top: 4px; font-weight: 600; }
    .ah-timeline-badge {
        display: inline-block; padding: 2px 8px; border-radius: 6px;
        font-size: 9px; font-weight: 700; text-transform: uppercase;
    }

    .ah-type-badge { display: inline-block; padding: 3px 10px; border-radius: 8px; font-size: 10px; font-weight: 700; }
    .ah-type-login { background: #dcfce7; color: #166534; }
    .ah-type-logout { background: #fef2f2; color: #991b1b; }
    .ah-type-absensi { background: #dbeafe; color: #1e40af; }
    .ah-type-profile { background: #fef3c7; color: #92400e; }
    .ah-type-page_view { background: #f3e8ff; color: #7c3aed; }
    .ah-type-session_start { background: #e0e7ff; color: #4338ca; }

    .ah-chart-wrap { width: 100%; overflow-x: auto; }
    .ah-bar-chart { display: flex; align-items: flex-end; gap: 3px; height: 80px; padding-top: 10px; }
    .ah-bar {
        flex: 1; min-width: 8px; border-radius: 4px 4px 0 0; transition: height 0.3s;
        position: relative; cursor: pointer;
    }
    .ah-bar:hover::after {
        content: attr(data-count); position: absolute; top: -20px; left: 50%; transform: translateX(-50%);
        background: var(--navy); color: #fff; padding: 2px 6px; border-radius: 4px;
        font-size: 9px; font-weight: 700; white-space: nowrap;
    }

    .ah-user-row {
        display: flex; align-items: center; gap: 12px; padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .ah-user-avatar {
        width: 38px; height: 38px; border-radius: 10px; overflow: hidden; flex-shrink: 0;
    }
    .ah-user-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .ah-user-info { flex: 1; }
    .ah-user-name { font-size: 13px; font-weight: 700; color: var(--navy); }
    .ah-user-detail { font-size: 10px; color: #94a3b8; font-weight: 600; }
    .ah-user-count { font-size: 18px; font-weight: 900; color: var(--blue); }

    .ah-loc-badge {
        display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px;
        background: #f0fdf4; color: #16a34a; border-radius: 6px; font-size: 9px; font-weight: 700;
    }
</style>

<div class="ah-page">
    {{-- Header --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <a href="{{ route('admin.dashboard') }}" style="width:38px;height:38px;border-radius:12px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;text-decoration:none;color:var(--ink);">
            <i class="bi bi-chevron-left"></i>
        </a>
        <div>
            <div style="font-size:18px;font-weight:800;color:var(--navy);">Riwayat Aktivitas</div>
            <div style="font-size:11px;color:#94a3b8;font-weight:600;">Pelacakan semua aktivitas user</div>
        </div>
    </div>

    {{-- Stats Hero --}}
    <div class="ah-hero">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:rgba(255,255,255,.6);position:relative;z-index:1;">Overview Hari Ini</div>
        <div class="ah-stat-grid">
            <div class="ah-stat"><div class="n">{{ number_format($stats['total']) }}</div><div class="l">Total Log</div></div>
            <div class="ah-stat"><div class="n">{{ number_format($stats['today']) }}</div><div class="l">Hari Ini</div></div>
            <div class="ah-stat"><div class="n">{{ number_format($stats['activeUsers']) }}</div><div class="l">Aktif</div></div>
            <div class="ah-stat"><div class="n">{{ number_format($stats['logins']) }}</div><div class="l">Login</div></div>
        </div>
    </div>

    {{-- Activity Chart --}}
    <div class="ah-card">
        <div class="ah-card-title"><i class="bi bi-bar-chart-line" style="color:var(--blue);"></i> Aktivitas 30 Hari</div>
        <div class="ah-chart-wrap">
            <div class="ah-bar-chart">
                @php $maxCount = max($dailyActivity->pluck('count')->max() ?? 1, 1); @endphp
                @foreach($dailyActivity as $day)
                    <div class="ah-bar" style="height: {{ ($day->count / $maxCount) * 100 }}%; background: var(--blue); opacity: 0.7;" data-count="{{ $day->count }}"></div>
                @endforeach
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:6px;">
                <span style="font-size:9px;color:#94a3b8;font-weight:600;">30 hari lalu</span>
                <span style="font-size:9px;color:#94a3b8;font-weight:600;">Hari ini</span>
            </div>
        </div>
    </div>

    {{-- Most Active Users --}}
    <div class="ah-card">
        <div class="ah-card-title"><i class="bi bi-people-fill" style="color:#7c3aed;"></i> User Paling Aktif</div>
        @php
            $topUsers = \App\Models\UserHistory::selectRaw('user_id, COUNT(*) as total')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('user_id')
                ->orderByDesc('total')
                ->take(5)
                ->get()
                ->map(fn ($row) => (object)[
                    'user' => \App\Models\User::find($row->user_id),
                    'total' => $row->total,
                ])
                ->filter(fn ($row) => $row->user);
        @endphp
        @forelse($topUsers as $row)
            <div class="ah-user-row">
                <div class="ah-user-avatar">
                    <img src="{{ $row->user->avatar_url }}" alt="{{ $row->user->name }}">
                </div>
                <div class="ah-user-info">
                    <div class="ah-user-name">{{ $row->user->name }}</div>
                    <div class="ah-user-detail">{{ ucfirst($row->user->role) }} {{ $row->user->kelas?->nama ? ' - '.$row->user->kelas->nama : '' }}</div>
                </div>
                <div class="ah-user-count">{{ $row->total }}</div>
            </div>
        @empty
            <div style="text-align:center;padding:20px;color:#94a3b8;font-size:12px;font-weight:600;">Belum ada aktivitas</div>
        @endforelse
    </div>

    {{-- Filter & History List --}}
    <div class="ah-card">
        <div class="ah-card-title"><i class="bi bi-clock-history" style="color:#ea580c;"></i> Semua Riwayat</div>

        <form method="GET" class="ah-filter-bar">
            <input type="text" name="search" placeholder="Cari nama user..." value="{{ request('search') }}">
            <select name="type">
                <option value="">Semua Tipe</option>
                @foreach($activityTypes as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>
             <input type="date" name="date_from" value="{{ request('date_from') }}" style="flex:0 0 auto;">
             <input type="date" name="date_to" value="{{ request('date_to') }}" style="flex:0 0 auto;">
        </form>

        @forelse($histories as $h)
            <div class="ah-timeline-item">
                <div class="ah-timeline-dot" style="background: {{ match($h->activity_type) {
                    'login' => '#dcfce7', 'logout' => '#fef2f2', 'absensi' => '#dbeafe',
                    'profile_update' => '#fef3c7', 'page_view' => '#f3e8ff', 'session_start' => '#e0e7ff',
                    default => '#f1f5f9'
                } }}; color: {{ match($h->activity_type) {
                    'login' => '#166534', 'logout' => '#991b1b', 'absensi' => '#1e40af',
                    'profile_update' => '#92400e', 'page_view' => '#7c3aed', 'session_start' => '#4338ca',
                    default => '#64748b'
                } }};">
                    <i class="bi {{ match($h->activity_type) {
                        'login' => 'bi-box-arrow-in-right', 'logout' => 'bi-box-arrow-right',
                        'absensi' => 'bi-calendar-check', 'profile_update' => 'bi-person-gear',
                        'page_view' => 'bi-eye', 'session_start' => 'bi-phone',
                        default => 'bi-activity'
                    } }}"></i>
                </div>
                <div class="ah-timeline-info">
                    <div class="ah-timeline-user">{{ $h->user?->name ?? 'User #'.$h->user_id }}</div>
                    <div class="ah-timeline-desc">{{ $h->description }}</div>
                    <div style="display:flex;align-items:center;gap:6px;margin-top:4px;flex-wrap:wrap;">
                        <span class="ah-type-badge ah-type-{{ $h->activity_type }}">{{ ucfirst(str_replace('_', ' ', $h->activity_type)) }}</span>
                        @if($h->lat && $h->long)
                            <span class="ah-loc-badge"><i class="bi bi-geo-alt"></i> {{ round($h->lat, 4) }}, {{ round($h->long, 4) }}</span>
                        @endif
                        @if($h->device_info)
                            <span style="font-size:9px;color:#94a3b8;font-weight:600;">{{ $h->device_info }}</span>
                        @endif
                    </div>
                    <div class="ah-timeline-time">{{ $h->created_at->diffForHumans() }} &middot; {{ $h->created_at->format('d M Y, H:i') }}</div>
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:30px;color:#94a3b8;">
                <i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                <div style="font-size:13px;font-weight:700;">Tidak ada riwayat ditemukan</div>
            </div>
        @endforelse

        <div style="margin-top:14px;">
            {{ $histories->links() }}
        </div>
    </div>

    {{-- Recommendations --}}
    <div class="ah-card">
        <div class="ah-card-title"><i class="bi bi-lightbulb" style="color:#f59e0b;"></i> Rekomendasi Sistem</div>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <div style="display:flex;gap:10px;align-items:flex-start;padding:12px;background:#f0fdf4;border-radius:12px;">
                <i class="bi bi-shield-check" style="color:#16a34a;font-size:18px;margin-top:2px;"></i>
                <div>
                    <div style="font-size:12px;font-weight:700;color:#166534;">Keamanan Akun</div>
                    <div style="font-size:11px;color:#15803d;margin-top:2px;">Aktifkan autentikasi biometrik untuk semua guru dan siswa guna meningkatkan keamanan login.</div>
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:flex-start;padding:12px;background:#eff6ff;border-radius:12px;">
                <i class="bi bi-geo-alt-fill" style="color:#2563eb;font-size:18px;margin-top:2px;"></i>
                <div>
                    <div style="font-size:12px;font-weight:700;color:#1e40af;">Geofencing Absensi</div>
                    <div style="font-size:11px;color:#1d4ed8;margin-top:2px;">Aktifkan validasi GPS di sisi server agar absensi hanya diterima dari area sekolah (radius tertentu).</div>
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:flex-start;padding:12px;background:#fef3c7;border-radius:12px;">
                <i class="bi bi-bell-fill" style="color:#d97706;font-size:18px;margin-top:2px;"></i>
                <div>
                    <div style="font-size:12px;font-weight:700;color:#92400e;">Notifikasi Push</div>
                    <div style="font-size:11px;color:#b45309;margin-top:2px;">Kirim notifikasi push otomatis untuk tugas baru, pengumuman penting, dan напоминание pembayaran SPP.</div>
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:flex-start;padding:12px;background:#f3e8ff;border-radius:12px;">
                <i class="bi bi-graph-up" style="color:#7c3aed;font-size:18px;margin-top:2px;"></i>
                <div>
                    <div style="font-size:12px;font-weight:700;color:#6d28d9;">Analytics Mendalam</div>
                    <div style="font-size:11px;color:#7c3aed;margin-top:2px;">Pantau pola aktivitas siswa: jam aktif, rata-rata waktu di aplikasi, dan fitur yang paling sering digunakan.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
