@extends('layouts.mobile-app')
@section('content')
@php
    $isGuru = $user->role === 'guru';
    $spp = $sppStats ?? null;
    $hadir = (int) ($absensiBulan['hadir'] ?? $absensiBulan['Hadir'] ?? 0);
    $izin = (int) ($absensiBulan['izin'] ?? $absensiBulan['Izin'] ?? 0);
    $sakit = (int) ($absensiBulan['sakit'] ?? $absensiBulan['Sakit'] ?? 0);
    $alpha = (int) ($absensiBulan['alpha'] ?? $absensiBulan['Alpha'] ?? 0);
    $totalAbsen = $hadir + $izin + $sakit + $alpha;
    $pctHadir = $totalAbsen > 0 ? round(($hadir / $totalAbsen) * 100) : 0;
@endphp

<style>
    .dash-body { padding: 0 14px 120px; max-width: 640px; margin: 0 auto; }

    .dash-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 40%, #1d4ed8 100%);
        border-radius: 0 0 36px 36px;
        padding: 20px 20px 28px;
        color: #fff; position: relative; overflow: hidden;
        margin-bottom: 18px;
    }
    .dash-hero::before {
        content:''; position:absolute; top:-40px; right:-40px;
        width:160px; height:160px; border-radius:50%;
        background: radial-gradient(circle, rgba(59,130,246,0.3) 0%, transparent 70%);
    }
    .dash-hero::after {
        content:''; position:absolute; bottom:-30px; left:-20px;
        width:120px; height:120px; border-radius:50%;
        background: radial-gradient(circle, rgba(234,179,8,0.15) 0%, transparent 70%);
    }

    .dash-avatar {
        width: 52px; height: 52px; border-radius: 18px; overflow: hidden;
        border: 2.5px solid rgba(255,255,255,0.3);
        background: rgba(255,255,255,0.15);
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; font-weight: 800; flex-shrink: 0;
    }
    .dash-avatar img { width:100%; height:100%; object-fit:cover; }

    .dash-card {
        background: #fff; border-radius: 20px; padding: 16px;
        margin-bottom: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .dash-stat {
        border-radius: 18px; padding: 14px; text-align: center;
        position: relative; overflow: hidden; flex: 1;
    }
    .dash-stat::before {
        content:''; position:absolute; top:-8px; right:-8px;
        width:40px; height:40px; border-radius:50%; opacity:0.15;
    }
    .dash-stat .num { font-size: 22px; font-weight: 800; line-height: 1.1; }
    .dash-stat .lbl { font-size: 9px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; margin-top: 3px; opacity: 0.7; }

    .dash-menu {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;
    }
    .dash-menu-item {
        text-align: center; padding: 14px 4px; border-radius: 16px;
        text-decoration: none; transition: all 0.2s;
        -webkit-tap-highlight-color: transparent;
    }
    .dash-menu-item:active { transform: scale(0.95); }
    .dash-menu-icon {
        width: 44px; height: 44px; border-radius: 14px; margin: 0 auto 6px;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .dash-menu-label { font-size: 10px; font-weight: 700; color: #475569; }

    .dash-tugas-item {
        display: flex; align-items: center; gap: 10px; padding: 10px 0;
        text-decoration: none; color: #1e293b;
    }
    .dash-tugas-item + .dash-tugas-item { border-top: 1px solid #f1f5f9; }
    .dash-tugas-icon {
        width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 16px;
    }

    .dash-pengumuman {
        display: flex; gap: 10px; padding: 12px 0;
        text-decoration: none; color: #1e293b;
    }
    .dash-pengumuman + .dash-pengumuman { border-top: 1px solid #f1f5f9; }

    .dash-absen-bar { height: 8px; border-radius: 99px; background: #eef2f7; overflow: hidden; display: flex; }

    @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    .fade-up { animation: fadeUp 0.35s ease both; }
</style>

{{-- Hero --}}
<div class="dash-hero">
    <div style="display:flex;align-items:center;justify-content:space-between;position:relative;z-index:1;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="dash-avatar">
                @if($user->foto)
                    <img src="{{ asset('storage/'.$user->foto) }}">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
            </div>
            <div>
                <div style="font-size:11px;opacity:0.6;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;">Portal Akademik</div>
                <div style="font-size:20px;font-weight:800;line-height:1.2;">Halo, {{ explode(' ', $user->name)[0] }}!</div>
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('notifications.index') }}" style="width:40px;height:40px;border-radius:14px;background:rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;position:relative;">
                <i class="bi bi-bell-fill" style="font-size:17px;"></i>
                @if($unreadNotificationsCount > 0)
                    <span style="position:absolute;top:6px;right:6px;width:8px;height:8px;border-radius:50%;background:#ef4444;border:1.5px solid rgba(15,23,42,0.8);"></span>
                @endif
            </a>
            <a href="{{ route('profile.show') }}" style="width:40px;height:40px;border-radius:14px;background:rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;">
                <i class="bi bi-gear-fill" style="font-size:17px;"></i>
            </a>
        </div>
    </div>

    @if($user->kelas)
        <div style="display:inline-flex;align-items:center;gap:6px;margin-top:14px;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:10px;padding:6px 12px;font-size:12px;font-weight:600;position:relative;z-index:1;">
            <i class="bi bi-mortarboard-fill" style="font-size:14px;color:#fbbf24;"></i>
            {{ $user->kelas->nama }}
            @if($user->kelas->jurusan)
                <span style="opacity:0.5;">·</span>
                <span style="opacity:0.7;">{{ $user->kelas->jurusan->nama ?? '' }}</span>
            @endif
        </div>
    @endif
</div>

<div class="dash-body">
    {{-- Quick Stats --}}
    <div class="d-flex gap-2 mb-3 fade-up">
        <a href="{{ route('tugas.index') }}" class="dash-stat text-decoration-none" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);color:#1e40af;">
            <div class="num">{{ $tugasAktif }}</div>
            <div class="lbl" style="color:#3b82f6;">Tugas Aktif</div>
        </a>
        @if($spp)
            <a href="{{ route('spp.index') }}" class="dash-stat text-decoration-none" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);color:#166534;">
                <div class="num">{{ $spp['lunas'] }}/{{ $spp['total'] }}</div>
                <div class="lbl" style="color:#16a34a;">SPP Lunas</div>
            </a>
        @endif
        <div class="dash-stat" style="background:linear-gradient(135deg,#fefce8,#fef9c3);color:#854d0e;">
            <div class="num">{{ $pctHadir }}%</div>
            <div class="lbl" style="color:#ca8a04;">Kehadiran</div>
        </div>
    </div>

    {{-- Quick Menu --}}
    <div class="dash-card fade-up" style="animation-delay:0.05s;">
        <div class="dash-menu">
            <a href="{{ route('absensi.index') }}" class="dash-menu-item">
                <div class="dash-menu-icon" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-calendar-check-fill"></i></div>
                <div class="dash-menu-label">Absensi</div>
            </a>
            <a href="{{ route('tugas.index') }}" class="dash-menu-item">
                <div class="dash-menu-icon" style="background:#fef3c7;color:#d97706;"><i class="bi bi-journal-check"></i></div>
                <div class="dash-menu-label">Tugas</div>
            </a>
            <a href="{{ route('spp.index') }}" class="dash-menu-item">
                <div class="dash-menu-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-wallet2"></i></div>
                <div class="dash-menu-label">SPP</div>
            </a>
            <a href="{{ route('chat.index') }}" class="dash-menu-item">
                <div class="dash-menu-icon" style="background:#ede9fe;color:#7c3aed;"><i class="bi bi-chat-dots-fill"></i></div>
                <div class="dash-menu-label">Chat</div>
            </a>
            <a href="{{ route('pengumuman.index') }}" class="dash-menu-item">
                <div class="dash-menu-icon" style="background:#fef2f2;color:#dc2626;"><i class="bi bi-megaphone-fill"></i></div>
                <div class="dash-menu-label">Info</div>
            </a>
            <a href="{{ route('mahasiswa.index') }}" class="dash-menu-item">
                <div class="dash-menu-icon" style="background:#ecfeff;color:#0891b2;"><i class="bi bi-people-fill"></i></div>
                <div class="dash-menu-label">Siswa</div>
            </a>
            @if($isGuru)
                <a href="{{ route('tugas.create') }}" class="dash-menu-item">
                    <div class="dash-menu-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-plus-circle-fill"></i></div>
                    <div class="dash-menu-label">Buat Tugas</div>
                </a>
            @endif
            <a href="{{ route('profile.show') }}" class="dash-menu-item">
                <div class="dash-menu-icon" style="background:#f8fafc;color:#64748b;"><i class="bi bi-gear-fill"></i></div>
                <div class="dash-menu-label">Setting</div>
            </a>
        </div>
    </div>

    {{-- Absensi Ringkasan --}}
    @if($totalAbsen > 0)
        <div class="dash-card fade-up" style="animation-delay:0.1s;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <div style="font-size:14px;font-weight:800;">Absensi Bulan Ini</div>
                <span style="font-size:11px;color:#94a3b8;">{{ $totalAbsen }} hari</span>
            </div>
            <div class="dash-absen-bar mb-2">
                <span style="width:{{ $totalAbsen > 0 ? ($hadir/$totalAbsen)*100 : 0 }}%;background:#16a34a;"></span>
                <span style="width:{{ $totalAbsen > 0 ? ($sakit/$totalAbsen)*100 : 0 }}%;background:#f59e0b;"></span>
                <span style="width:{{ $totalAbsen > 0 ? ($izin/$totalAbsen)*100 : 0 }}%;background:#3b82f6;"></span>
                <span style="width:{{ $totalAbsen > 0 ? ($alpha/$totalAbsen)*100 : 0 }}%;background:#ef4444;"></span>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:4px;font-size:11px;"><span style="width:8px;height:8px;border-radius:50%;background:#16a34a;"></span> Hadir {{ $hadir }}</div>
                <div style="display:flex;align-items:center;gap:4px;font-size:11px;"><span style="width:8px;height:8px;border-radius:50%;background:#f59e0b;"></span> Sakit {{ $sakit }}</div>
                <div style="display:flex;align-items:center;gap:4px;font-size:11px;"><span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;"></span> Izin {{ $izin }}</div>
                <div style="display:flex;align-items:center;gap:4px;font-size:11px;"><span style="width:8px;height:8px;border-radius:50%;background:#ef4444;"></span> Alpha {{ $alpha }}</div>
            </div>
        </div>
    @endif

    {{-- Tugas Terbaru --}}
    <div class="dash-card fade-up" style="animation-delay:0.15s;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div style="font-size:14px;font-weight:800;">Tugas Terbaru</div>
            <a href="{{ route('tugas.index') }}" style="font-size:12px;font-weight:700;color:#246bfe;text-decoration:none;">Semua</a>
        </div>
        @forelse($tugas as $t)
            @php
                $dl = $t->deadlineStatus();
                $colors = ['ok' => '#246bfe', 'soon' => '#d97706', 'today' => '#dc2626', 'expired' => '#94a3b8', 'open' => '#16a34a'];
                $c = $colors[$dl['key']] ?? '#64748b';
            @endphp
            <a href="{{ route('tugas.show', $t) }}" class="dash-tugas-item">
                <div class="dash-tugas-icon" style="background:{{ $c }}12;color:{{ $c }};">
                    <i class="bi {{ $t->isForm() ? 'bi-ui-checks-grid' : 'bi-file-earmark-text-fill' }}"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $t->judul }}</div>
                    <div style="font-size:10px;color:#94a3b8;">{{ $dl['label'] }}</div>
                </div>
                <i class="bi bi-chevron-right" style="font-size:12px;color:#cbd5e1;"></i>
            </a>
        @empty
            <div style="text-align:center;padding:16px;color:#94a3b8;font-size:13px;">Belum ada tugas</div>
        @endforelse
    </div>

    {{-- Pengumuman --}}
    <div class="dash-card fade-up" style="animation-delay:0.2s;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
            <div style="font-size:14px;font-weight:800;">Pengumuman</div>
            <a href="{{ route('pengumuman.index') }}" style="font-size:12px;font-weight:700;color:#246bfe;text-decoration:none;">Semua</a>
        </div>
        @forelse($publicPengumumans as $p)
            <a href="{{ route('pengumuman.index') }}" class="dash-pengumuman">
                <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#fef3c7,#fde68a);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-megaphone-fill" style="color:#d97706;font-size:18px;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $p->judul }}</div>
                    <div style="font-size:11px;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 60) }}</div>
                </div>
            </a>
        @empty
            <div style="text-align:center;padding:16px;color:#94a3b8;font-size:13px;">Belum ada pengumuman</div>
        @endforelse
    </div>

    {{-- SPP Summary (siswa only) --}}
    @if($spp && $spp['kekurangan'] > 0)
        <div class="dash-card fade-up" style="animation-delay:0.25s;border-left:4px solid #f59e0b;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:40px;height:40px;border-radius:12px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#d97706;font-size:18px;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:12px;font-weight:700;color:#92400e;">Tunggakan SPP</div>
                    <div style="font-size:16px;font-weight:800;color:#b45309;">Rp {{ number_format($spp['kekurangan'], 0, ',', '.') }}</div>
                </div>
                <a href="{{ route('spp.index') }}" style="font-size:11px;font-weight:700;color:#d97706;text-decoration:none;">Detail</a>
            </div>
        </div>
    @endif
</div>

<script>
    var unreadCount = {{ $unreadNotificationsCount }};
    var lastSpoken = localStorage.getItem('last_notif_spoken');
    if (unreadCount > 0 && unreadCount > (parseInt(lastSpoken) || 0)) {
        try { var msg = new SpeechSynthesisUtterance(); msg.text = "Ada notifikasi untukmu"; msg.lang = 'id-ID'; msg.rate = 1.0; window.speechSynthesis.speak(msg); } catch(e) {}
    }
    localStorage.setItem('last_notif_spoken', unreadCount || 0);
</script>
@endsection
