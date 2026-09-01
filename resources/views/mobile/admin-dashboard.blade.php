@php
    $hideNav = true;
    $hour = date('H');
    $greetingTime = match(true) {
        $hour < 11 => 'Selamat Pagi',
        $hour < 15 => 'Selamat Siang',
        $hour < 18 => 'Selamat Sore',
        default => 'Selamat Malam',
    };
@endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .am-body { padding: 18px 14px 100px; max-width: 640px; margin: 0 auto; }

    /* Premium Hero Section */
    .am-hero {
        background: var(--grad-hero);
        border-radius: var(--radius-lg); padding: 30px 24px; color: #fff;
        margin-bottom: 24px; position: relative; overflow: hidden;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.25);
    }
    .am-hero::before {
        content: ''; position: absolute; top: -50px; right: -50px; width: 200px; height: 200px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.4) 0%, transparent 70%);
    }
    .am-hero::after {
        content: ''; position: absolute; bottom: -40px; left: -40px; width: 160px; height: 160px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, transparent 70%);
    }

    .am-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 24px; position: relative; z-index: 1; }
    .am-stat {
        background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);
        backdrop-filter: blur(10px);
        border-radius: 16px; padding: 14px 8px; text-align: center;
    }
    .am-stat .n { font-size: 20px; font-weight: 900; line-height: 1; color: #fff; }
    .am-stat .l { font-size: 9px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-top: 6px; }

    .am-card { border-radius: 22px; padding: 22px; margin-bottom: 16px; border: 1px solid var(--line); box-shadow: var(--shadow-card); background: #fff; }

    .kpi-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .kpi-item {
        background: #f8fafc; border-radius: 16px; padding: 16px; text-align: center;
        border: 1px solid #f1f5f9; transition: transform 0.2s;
    }
    .kpi-item:active { transform: scale(0.96); }
    .kpi-val { font-size: 26px; font-weight: 900; line-height: 1; margin-bottom: 6px; }
    .kpi-lab { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }

    .quick-action {
        display: flex; align-items: center; gap: 14px; padding: 16px;
        background: #fff; border-radius: 20px; text-decoration: none; color: var(--navy);
        border: 1px solid var(--line); box-shadow: var(--shadow-card);
        margin-bottom: 12px; transition: all 0.2s;
    }
    .quick-action:active { transform: scale(0.97); background: #f8fafc; }
    .qa-ico {
        width: 48px; height: 48px; border-radius: 16px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 22px;
    }

    .alert-banner {
        background: #fff1f2; border: 1px solid #fecdd3; border-radius: 18px;
        padding: 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 20px;
        text-decoration: none; animation: pulse 2s infinite;
    }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.2); } 70% { box-shadow: 0 0 0 10px rgba(220, 38, 38, 0); } 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); } }

    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-up { animation: slideUp 0.5s ease both; }
</style>

<div class="am-body">
    {{-- Admin Hero Section --}}
    <div class="am-hero animate-up">
        <div style="position:relative;z-index:1;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <div style="width:56px;height:56px;border-radius:18px;background:rgba(255,255,255,0.15);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,0.2);overflow:hidden;">
                        <img src="{{ $user->avatar_url }}" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(255,255,255,0.7);">{{ $greetingTime }}</div>
                        <div style="font-size:26px;font-weight:900;margin-top:2px;letter-spacing:-0.02em;">Halo, Admin!</div>
                    </div>
                </div>
                <div style="width:44px;height:44px;border-radius:14px;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,0.15);">
                    <i class="bi bi-shield-lock-fill" style="font-size:18px;"></i>
                </div>
            </div>

            <div class="am-stat-grid">
                <div class="am-stat"><div class="n">{{ $totalGuru }}</div><div class="l">Guru</div></div>
                <div class="am-stat"><div class="n">{{ $totalSiswa }}</div><div class="l">Siswa</div></div>
                <div class="am-stat"><div class="n">{{ $totalKelas }}</div><div class="l">Kelas</div></div>
                <div class="am-stat" style="border-color:rgba(248,113,113,0.3);"><div class="n" style="color:#f87171;">{{ $pendingCount }}</div><div class="l">Pending</div></div>
            </div>
            @if($isSuperAdmin ?? false)
            <form method="GET" style="margin-top:16px;position:relative;z-index:1;display:flex;gap:8px;align-items:center">
                <select name="school_id" onchange="this.form.submit()" style="flex:1;padding:10px 12px;border-radius:12px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.12);color:#fff;font-weight:700;font-size:12px">
                    <option value="" style="color:#0f172a" {{ !$filterSchoolId?'selected':'' }}>🌐 Semua Sekolah (Pusat)</option>
                    @foreach($allSchools as $sc)<option value="{{ $sc->id }}" style="color:#0f172a" {{ $filterSchoolId==$sc->id?'selected':'' }}>[ID {{ $sc->id }}] {{ $sc->name }} {{ $sc->is_active?'':'— NONAKTIF' }}</option>@endforeach
                </select>
                <a href="{{ route('admin.schools.index') }}" style="padding:8px 12px;background:rgba(255,255,255,.15);color:#fff;border-radius:10px;text-decoration:none;font-size:11px;font-weight:800">Kelola Sekolah</a>
            </form>
            @if($filterSchool) <div style="margin-top:8px;font-size:11px;opacity:.8">Menampilkan: <b>{{ $filterSchool->name }}</b> {{ $filterSchool->is_active?'🟢 Aktif':'🔴 Nonaktif — pendaftaran tertutup' }}</div> @endif
            @endif
        </div>
    </div>

    {{-- Urgent Alerts --}}
    @if($pendingCount > 0)
        <a href="{{ route('admin.users') }}" class="alert-banner animate-up" style="animation-delay: 0.1s;">
            <div style="width:44px;height:44px;border-radius:14px;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-person-fill-exclamation" style="font-size:20px;"></i>
            </div>
            <div style="flex:1;">
                <div style="font-size:14px;font-weight:800;color:#991b1b;">Pendaftaran Baru</div>
                <div style="font-size:12px;color:#b91c1c;">Ada {{ $pendingCount }} akun menunggu persetujuan.</div>
            </div>
            <i class="bi bi-arrow-right-short" style="font-size:24px;color:#dc2626;"></i>
        </a>
    @endif

    {{-- LMS KPI --}}
    <div class="am-card animate-up" style="animation-delay: 0.15s;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <h3 style="font-size:16px;font-weight:800;margin:0;"><i class="bi bi-mortarboard-fill me-2" style="color:#7c3aed;"></i> E-Learning Stats</h3>
            <span class="badge bg-soft-primary" style="background:#f5f3ff;color:#7c3aed;font-size:10px;padding:6px 12px;border-radius:10px;">{{ now()->translatedFormat('M Y') }}</span>
        </div>
        <div class="kpi-grid">
            <div class="kpi-item"><div class="kpi-val" style="color:#2563eb;">{{ $totalMapel }}</div><div class="kpi-lab">Mapel</div></div>
            <div class="kpi-item" style="background:#f0fdf4;"><div class="kpi-val" style="color:#16a34a;">{{ $totalMateri }}</div><div class="kpi-lab">Materi</div></div>
            <div class="kpi-item" style="background:#fffbeb;"><div class="kpi-val" style="color:#d97706;">{{ $totalTugas }}</div><div class="kpi-lab">Tugas</div></div>
            <div class="kpi-item" style="background:#fff5f6;"><div class="kpi-val" style="color:#dc2626;">{{ $tugasBelumDinilai }}</div><div class="kpi-lab">Pending Nilai</div></div>
        </div>
    </div>

    {{-- Financial Overview --}}
    <div class="am-card animate-up" style="animation-delay: 0.2s;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <h3 style="font-size:16px;font-weight:800;margin:0;"><i class="bi bi-wallet-fill me-2" style="color:#16a34a;"></i> Keuangan & SPP</h3>
        </div>
        @php $pct = $sppTagihan > 0 ? round(($sppTerbayar / $sppTagihan) * 100) : 0; @endphp
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <div style="font-size:12px;font-weight:700;color:#64748b;">Progress Penagihan</div>
            <div style="font-size:14px;font-weight:900;color:#16a34a;">{{ $pct }}%</div>
        </div>
        <div style="height:12px;border-radius:6px;background:#f1f5f9;overflow:hidden;margin-bottom:20px;border:1px solid #e2e8f0;">
            <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#16a34a,#22c55e);border-radius:6px;"></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
                <div style="font-size:10px;font-weight:800;color:#94a3b8;text-transform:uppercase;">Terkumpul</div>
                <div style="font-size:16px;font-weight:900;color:var(--navy);">Rp {{ number_format($sppTerbayar, 0, ',', '.') }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:10px;font-weight:800;color:#94a3b8;text-transform:uppercase;">Piutang</div>
                <div style="font-size:16px;font-weight:900;color:#dc2626;">Rp {{ number_format(max(0, $sppTagihan - $sppTerbayar), 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    {{-- Global Portal Premium --}}
    <div class="am-card animate-up" style="animation-delay: 0.21s;overflow:hidden">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <h3 style="font-size:16px;font-weight:800;margin:0"><i class="bi bi-globe2 me-2" style="color:#6366f1"></i> Global Portal</h3>
            <a href="{{ route('global.portal') }}" style="font-size:11px;font-weight:700;color:var(--blue);text-decoration:none">Lihat Portal</a>
        </div>
        <div class="kpi-grid" style="margin-bottom:14px">
            <div class="kpi-item" style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border:0"><div class="kpi-val" style="color:#fff">{{ $totalGlobalPosts }}</div><div class="kpi-lab" style="color:rgba(255,255,255,.8)">Post</div></div>
            <div class="kpi-item"><div class="kpi-val" style="color:#16a34a">{{ $globalPostsHariIni }}</div><div class="kpi-lab">Hari Ini</div></div>
            <div class="kpi-item"><div class="kpi-val" style="color:#dc2626">{{ $totalGlobalLikes }}</div><div class="kpi-lab">Suka</div></div>
            <div class="kpi-item"><div class="kpi-val" style="color:#7c3aed">{{ $totalSchools }}</div><div class="kpi-lab">Sekolah</div></div>
        </div>
        @if($topSchool)
        <div style="display:flex;gap:10px;align-items:center;background:#f8fafc;border:1px solid #eef2f7;border-radius:14px;padding:10px 12px;margin-bottom:12px">
            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#4f46e5,#2563eb);display:grid;place-items:center;color:#fff"><i class="bi bi-trophy-fill"></i></div>
            <div style="flex:1"><div style="font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase">Top Sekolah</div><div style="font-size:13px;font-weight:800">{{ $topSchool->name }} <span style="color:#64748b">({{ $topSchool->posts_count }} post)</span></div></div>
        </div>
        @endif
        @forelse($recentGlobalPosts as $gp)
        <a href="{{ route('global.portal') }}" style="display:flex;gap:10px;align-items:center;padding:10px 0;{{ !$loop->last?'border-bottom:1px solid #f1f5f9':'' }};text-decoration:none;color:inherit">
            <img src="{{ $gp->user->avatar_url }}" style="width:32px;height:32px;border-radius:10px;object-fit:cover;flex-shrink:0">
            <div style="flex:1;min-width:0"><div style="font-size:12px;font-weight:700" class="text-truncate">{{ $gp->user->name }} <span style="font-weight:600;color:#6366f1">@ {{ $gp->school->name ?? $gp->user->school->name ?? 'Umum' }}</span></div><div style="font-size:11px;color:#64748b" class="text-truncate">{{ \Illuminate\Support\Str::limit($gp->content,55) }}</div></div>
            <div style="font-size:10px;color:#94a3b8">{{ $gp->created_at->diffForHumans() }}</div>
        </a>
        @empty
        <div style="text-align:center;padding:12px;color:#94a3b8;font-size:12px">Belum ada post — jadilah yang pertama!</div>
        @endforelse
        <div style="display:flex;gap:8px;margin-top:14px"><div style="flex:1;background:#eef2ff;border:1px solid #e0e7ff;border-radius:12px;padding:10px;text-align:center"><div style="font-size:12px;font-weight:800;color:#4f46e5">{{ $totalGlobalComments }} Komentar</div></div><div style="flex:1;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:10px;text-align:center"><div style="font-size:12px;font-weight:800;color:#dc2626">{{ $totalGlobalLikes }} Suka total</div></div></div>
    </div>

    {{-- Online Users Widget --}}
    @php
        $onlineUsers = \App\Models\User::whereIn('role', ['guru', 'siswa'])
            ->where('aktif', true)
            ->where('last_activity_at', '>=', now()->subMinutes(1))
            ->with('kelas')
            ->get();
        $offlineUsers = \App\Models\User::whereIn('role', ['guru', 'siswa'])
            ->where('aktif', true)
            ->where('last_activity_at', '<', now()->subMinutes(1))
            ->orWhereNull('last_activity_at')
            ->count();
        $recentLogs = \App\Models\UserHistory::with('user')->latest()->take(5)->get();
    @endphp
    <div class="am-card animate-up" style="animation-delay: 0.22s;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="font-size:16px;font-weight:800;margin:0;"><i class="bi bi-broadcast me-2" style="color:#22c55e;"></i> Status Online</h3>
            <span style="background:#dcfce7;color:#166534;padding:4px 10px;border-radius:8px;font-size:11px;font-weight:800;">{{ $onlineUsers->count() }} online</span>
        </div>
        @if($onlineUsers->count() > 0)
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                @foreach($onlineUsers->take(8) as $ou)
                    <div style="display:flex;align-items:center;gap:6px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:6px 10px;">
                        <div style="width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 6px #22c55e;"></div>
                        <span style="font-size:11px;font-weight:700;color:#166534;">{{ explode(' ', $ou->name)[0] }}</span>
                    </div>
                @endforeach
                @if($onlineUsers->count() > 8)
                    <div style="display:flex;align-items:center;background:#f1f5f9;border-radius:10px;padding:6px 10px;">
                        <span style="font-size:11px;font-weight:700;color:#64748b;">+{{ $onlineUsers->count() - 8 }} lagi</span>
                    </div>
                @endif
            </div>
        @else
            <div style="text-align:center;padding:16px;color:#94a3b8;font-size:12px;font-weight:600;">Tidak ada user online saat ini</div>
        @endif
        @if($offlineUsers > 0)
            <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-top:8px;">
                {{ $offlineUsers }} offline
            </div>
        @endif
    </div>

    {{-- Recent Activity Widget --}}
    <div class="am-card animate-up" style="animation-delay: 0.25s;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="font-size:16px;font-weight:800;margin:0;"><i class="bi bi-clock-history me-2" style="color:#ea580c;"></i> Aktivitas Terbaru</h3>
            <a href="{{ route('admin.history') }}" style="font-size:11px;font-weight:700;color:var(--blue);text-decoration:none;">Lihat Semua</a>
        </div>
        @forelse($recentLogs as $log)
            <div style="display:flex;align-items:center;gap:10px;padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}">
                <div style="width:32px;height:32px;border-radius:10px;flex-shrink:0;display:grid;place-items:center;font-size:13px;background:{{ match($log->activity_type) {
                    'login' => '#dcfce7', 'logout' => '#fef2f2', 'absensi' => '#dbeafe',
                    'profile_update' => '#fef3c7', default => '#f1f5f9'
                } }}; color:{{ match($log->activity_type) {
                    'login' => '#166534', 'logout' => '#991b1b', 'absensi' => '#1e40af',
                    'profile_update' => '#92400e', default => '#64748b'
                } }};">
                    <i class="bi {{ match($log->activity_type) {
                        'login' => 'bi-box-arrow-in-right', 'logout' => 'bi-box-arrow-right',
                        'absensi' => 'bi-calendar-check', 'profile_update' => 'bi-person-gear',
                        default => 'bi-activity'
                    } }}"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:var(--navy);">{{ $log->user?->name ?? 'User #'.$log->user_id }}</div>
                    <div style="font-size:10px;color:#94a3b8;font-weight:600;">{{ $log->description }}</div>
                </div>
                <div style="font-size:10px;color:#94a3b8;font-weight:600;white-space:nowrap;">{{ $log->created_at->diffForHumans() }}</div>
            </div>
        @empty
            <div style="text-align:center;padding:16px;color:#94a3b8;font-size:12px;font-weight:600;">Belum ada aktivitas</div>
        @endforelse
    </div>

    {{-- Quick Actions --}}
    <div style="font-size:13px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em;margin:24px 0 12px;">Navigasi Cepat</div>

    <a href="{{ route('admin.users') }}" class="quick-action animate-up">
        <div class="qa-ico" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-people-fill"></i></div>
        <div style="flex:1;">
            <div style="font-size:15px;font-weight:800;">Manajemen User</div>
            <div style="font-size:11px;color:#94a3b8;">Kelola Guru, Siswa, dan Persetujuan</div>
        </div>
        <i class="bi bi-chevron-right text-muted"></i>
    </a>

    <a href="{{ route('admin.history') }}" class="quick-action animate-up">
        <div class="qa-ico" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-clock-history"></i></div>
        <div style="flex:1;">
            <div style="font-size:15px;font-weight:800;">Riwayat Aktivitas</div>
            <div style="font-size:11px;color:#94a3b8;">Pelacakan login, absensi, dan aktivitas user</div>
        </div>
        <i class="bi bi-chevron-right text-muted"></i>
    </a>

    <a href="{{ route('pengumuman.create') }}" class="quick-action animate-up">
        <div class="qa-ico" style="background:#fff7ed;color:#ea580c;"><i class="bi bi-megaphone-fill"></i></div>
        <div style="flex:1;">
            <div style="font-size:15px;font-weight:800;">Buat Pengumuman</div>
            <div style="font-size:11px;color:#94a3b8;">Kirim info penting ke seluruh aplikasi</div>
        </div>
        <i class="bi bi-chevron-right text-muted"></i>
    </a>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}" style="margin-top:24px;" class="animate-up">
        @csrf
        <button class="pui-btn pui-btn-block" style="background:#fff1f2;color:#dc2626;border:1px solid #fecdd3;border-radius:18px;font-weight:800;padding:16px;">
            <i class="bi bi-box-arrow-right me-2"></i> Keluar dari Sesi
        </button>
    </form>
</div>
@endsection
