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

    $hour = date('H');
    $greetingTime = match(true) {
        $hour < 11 => 'Selamat Pagi',
        $hour < 15 => 'Selamat Siang',
        $hour < 18 => 'Selamat Sore',
        default => 'Selamat Malam',
    };
    $honorific = $isGuru ? 'Pak' : 'Kak';
@endphp

<style>
    .db-body { padding: 12px 16px 120px; max-width: 640px; margin: 0 auto; }
    .db-section { padding: 20px 18px; }

    /* ==== Hero premium with enhanced Glassmorphism ==== */
    .hero-card {
        background: var(--grad-hero);
        border-radius: var(--radius-lg);
        padding: 28px 24px;
        margin-bottom: 24px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
    }
    .hero-card::before {
        content: ''; position: absolute; top: -50px; right: -50px; width: 220px; height: 220px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.4) 0%, transparent 70%);
        pointer-events: none;
    }
    .hero-card::after {
        content: ''; position: absolute; bottom: -40px; left: -40px; width: 180px; height: 180px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.3) 0%, transparent 70%);
        pointer-events: none;
    }

    .hero-avatar-wrap { position: relative; }
    .hero-avatar {
        width: 56px; height: 56px; border-radius: 18px;
        overflow: hidden; display: flex; align-items: center; justify-content: center;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    .hero-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .role-indicator {
        position: absolute; bottom: -6px; right: -6px;
        width: 24px; height: 24px; border-radius: 8px;
        background: {{ $isGuru ? '#4f46e5' : '#10b981' }};
        border: 2px solid var(--navy);
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; color: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .hero-greeting { font-size: 13px; opacity: 0.8; font-weight: 600; color: #cbd5e1; }
    .hero-name { font-size: 26px; font-weight: 900; letter-spacing: -0.03em; margin-top: 2px; }

    .hero-badge-row { display: flex; gap: 8px; margin-top: 18px; }
    .hero-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 12px; padding: 6px 12px; font-size: 11px; font-weight: 700; color: #f8fafc;
    }

    .hero-bell {
        width: 44px; height: 44px; border-radius: 14px;
        background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.15);
        display: flex; align-items: center; justify-content: center; color: #fff;
        text-decoration: none; position: relative; transition: all 0.2s;
    }
    .hero-bell:active { transform: scale(0.92); background: rgba(255, 255, 255, 0.15); }

    /* ==== Enhanced Stat Cards ==== */
    .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
    .stat-item {
        background: #fff; border-radius: 20px; padding: 18px 12px;
        border: 1px solid var(--line); box-shadow: var(--shadow-card);
        text-align: center; text-decoration: none; position: relative;
        overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-item:active { transform: translateY(2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .stat-item .ico {
        width: 40px; height: 40px; border-radius: 12px; margin: 0 auto 10px;
        display: flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .stat-item .val { font-size: 22px; font-weight: 900; color: var(--navy); line-height: 1; }
    .stat-item .lab { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-top: 6px; letter-spacing: 0.02em; }

    /* ==== Quick Menu Grid ==== */
    .menu-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .menu-btn {
        display: flex; flex-direction: column; align-items: center; gap: 8px;
        text-decoration: none; transition: transform 0.2s;
    }
    .menu-btn:active { transform: scale(0.9); }
    .menu-btn-ico {
        width: 56px; height: 56px; border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px; position: relative;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    }
    .menu-btn-ico::after {
        content: ''; position: absolute; inset: 0; border-radius: 18px;
        background: linear-gradient(180deg, rgba(255,255,255,0.2) 0%, transparent 100%);
    }
    .menu-btn-lab { font-size: 11px; font-weight: 700; color: #64748b; }

    .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .section-header h3 { font-size: 17px; font-weight: 800; color: var(--navy); margin: 0; }
    .section-header a { font-size: 13px; font-weight: 700; color: var(--blue); text-decoration: none; }

    .lms-row {
        background: #fff; border-radius: 18px; padding: 12px;
        display: flex; align-items: center; gap: 12px; margin-bottom: 12px;
        border: 1px solid var(--line); text-decoration: none; color: inherit;
        transition: background 0.2s;
    }
    .lms-row:active { background: #f8fafc; }
    .lms-ico {
        width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .lms-info { flex: 1; min-width: 0; }
    .lms-title { font-size: 14px; font-weight: 700; color: var(--navy); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .lms-meta { font-size: 11px; color: #94a3b8; font-weight: 600; }

    .absen-summary {
        background: #f8fafc; border-radius: 16px; padding: 12px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .absen-item { text-align: center; flex: 1; }
    .absen-val { font-size: 16px; font-weight: 800; color: var(--navy); }
    .absen-lab { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; }

    /* Bottom Sheet Classmates */
    .sheet {
        position: fixed; inset: 0; z-index: 3000; display: none;
        align-items: flex-end; justify-content: center;
        background: rgba(15, 23, 42, .45); backdrop-filter: blur(4px);
    }
    .sheet.open { display: flex; }
    .sheet-card {
        width: 100%; max-width: 640px; background: #fff;
        border-radius: 32px 32px 0 0; padding: 24px 20px 40px;
        animation: slideSheet 0.3s ease-out;
    }
    @keyframes slideSheet { from { transform: translateY(100%); } to { transform: translateY(0); } }
    .classmate-row {
        display: flex; align-items: center; gap: 12px; padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .classmate-avatar { width: 44px; height: 44px; border-radius: 14px; object-fit: cover; background: #f1f5f9; }

    @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-up { animation: slideIn 0.5s ease both; }
</style>

<div class="db-body">
    {{-- Enhanced Hero Section --}}
    <div class="hero-card animate-up">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;position:relative;z-index:1;">
            <div style="display:flex;align-items:center;gap:16px;">
                <div class="hero-avatar-wrap">
                    <div class="hero-avatar">
                        <img src="{{ $user->avatar_url }}">
                    </div>
                    <div class="role-indicator">
                        <i class="bi {{ $isGuru ? 'bi-person-badge-fill' : 'bi-mortarboard-fill' }}"></i>
                    </div>
                </div>
                <div>
                    <div class="hero-greeting">{{ $greetingTime }}, {{ $honorific }}</div>
                    <div class="hero-name">{{ explode(' ', $user->name)[0] }}!</div>
                </div>
            </div>
            <a href="{{ route('notifications.index') }}" class="hero-bell">
                <i class="bi bi-bell-fill"></i>
                @if($unreadNotificationsCount > 0)
                    <span style="position:absolute; top:-4px; right:-4px; min-width:20px; height:20px; padding:0 4px; border-radius:10px; background:#ef4444; border:3px solid var(--navy); color:#fff; font-size:10px; font-weight:900; display:flex; align-items:center; justify-content:center;">
                        {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                    </span>
                @endif
            </a>
        </div>

        <div class="hero-badge-row" style="position:relative;z-index:1;">
            @if($user->kelas)
                <div class="hero-badge">
                    <i class="bi bi-building"></i> {{ $user->kelas->nama }}
                </div>
            @endif
            <div class="hero-badge" style="background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.2); color: #34d399;">
                <i class="bi bi-patch-check-fill"></i> Akun Aktif
            </div>
            <div class="hero-badge">
                <i class="bi bi-calendar2-event"></i> {{ now()->translatedFormat('d M') }}
            </div>
        </div>
    </div>

    {{-- Refined Stat Grid --}}
    <div class="stat-grid animate-up" style="animation-delay: 0.1s;">
        <a href="{{ route('tugas.index') }}" class="stat-item">
            <div class="ico" style="background: #eff6ff; color: #2563eb;"><i class="bi bi-journal-check"></i></div>
            <div class="val">{{ $tugasAktif }}</div>
            <div class="lab">Tugas</div>
        </a>
        <div class="stat-item">
            <div class="ico" style="background: #f0fdf4; color: #16a34a;"><i class="bi bi-graph-up"></i></div>
            <div class="val">{{ $pctHadir }}%</div>
            <div class="lab">Hadir</div>
        </div>
        <a href="#" onclick="openClassmates(); return false;" class="stat-item">
            <div class="ico" style="background: #fdf2f8; color: #db2777;"><i class="bi bi-people"></i></div>
            <div class="val">{{ $totalSiswaKelas }}</div>
            <div class="lab">Siswa</div>
        </a>
    </div>

    {{-- Premium Quick Menu --}}
    <div class="pui-card db-section animate-up" style="animation-delay: 0.15s; margin-bottom: 24px;">
        <div class="section-header">
            <h3>Menu Utama</h3>
        </div>
        <div class="menu-grid">
            <a href="{{ route('absensi.index') }}" class="menu-btn">
                <div class="menu-btn-ico" style="background: linear-gradient(135deg, #60a5fa, #2563eb); color: #fff;">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="menu-btn-lab">Absensi</div>
            </a>
            <a href="{{ route('tugas.index') }}" class="menu-btn">
                <div class="menu-btn-ico" style="background: linear-gradient(135deg, #facc15, #ca8a04); color: #fff;">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div class="menu-btn-lab">Tugas</div>
            </a>
            <a href="{{ route('spp.index') }}" class="menu-btn">
                <div class="menu-btn-ico" style="background: linear-gradient(135deg, #34d399, #059669); color: #fff;">
                    <i class="bi bi-credit-card-2-front"></i>
                </div>
                <div class="menu-btn-lab">SPP</div>
            </a>
            <a href="{{ route('chat.index') }}" class="menu-btn">
                <div class="menu-btn-ico" style="background: linear-gradient(135deg, #a78bfa, #7c3aed); color: #fff;">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <div class="menu-btn-lab">Chat</div>
            </a>
            <a href="{{ route('perpustakaan.index') }}" class="menu-btn">
                <div class="menu-btn-ico" style="background: linear-gradient(135deg, #fb923c, #ea580c); color: #fff;">
                    <i class="bi bi-book"></i>
                </div>
                <div class="menu-btn-lab">Perpus</div>
            </a>
            <a href="{{ route('jadwal.index') }}" class="menu-btn">
                <div class="menu-btn-ico" style="background: linear-gradient(135deg, #818cf8, #4f46e5); color: #fff;">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div class="menu-btn-lab">Jadwal</div>
            </a>
            <a href="{{ route('nilai.index') }}" class="menu-btn">
                <div class="menu-btn-ico" style="background: linear-gradient(135deg, #f472b6, #db2777); color: #fff;">
                    <i class="bi bi-award"></i>
                </div>
                <div class="menu-btn-lab">Nilai</div>
            </a>
            <a href="{{ route('eskul.index') }}" class="menu-btn">
                <div class="menu-btn-ico" style="background: linear-gradient(135deg, #2dd4bf, #0d9488); color: #fff;">
                    <i class="bi bi-trophy"></i>
                </div>
                <div class="menu-btn-lab">Eskul</div>
            </a>
        </div>
    </div>

    {{-- Today's Summary / Absensi --}}
    @if($totalAbsen > 0)
        <div class="pui-card db-section animate-up" style="animation-delay: 0.2s; margin-bottom: 24px;">
            <div class="section-header">
                <h3>Kehadiran Bulan Ini</h3>
                <span class="badge rounded-pill bg-light text-dark fw-bold" style="font-size:10px;">{{ $totalAbsen }} Hari</span>
            </div>
            <div class="absen-summary">
                <div class="absen-item">
                    <div class="absen-val" style="color:#16a34a;">{{ $hadir }}</div>
                    <div class="absen-lab">Hadir</div>
                </div>
                <div class="absen-item">
                    <div class="absen-val" style="color:#ca8a04;">{{ $sakit + $izin }}</div>
                    <div class="absen-lab">S/I</div>
                </div>
                <div class="absen-item">
                    <div class="absen-val" style="color:#dc2626;">{{ $alpha }}</div>
                    <div class="absen-lab">Alpha</div>
                </div>
                <div class="absen-item" style="border-left: 1px solid #e2e8f0; padding-left: 12px; margin-left: 4px;">
                    <div class="absen-val" style="color:var(--blue);">{{ $pctHadir }}%</div>
                    <div class="absen-lab">Rate</div>
                </div>
            </div>
        </div>
    @endif

    {{-- LMS Section --}}
    <div class="pui-card db-section animate-up" style="animation-delay: 0.25s; margin-bottom: 24px;">
        <div class="section-header">
            <h3>{{ $isGuru ? 'Mata Pelajaran Diampu' : 'Mata Pelajaran Saya' }}</h3>
        </div>
        @forelse($mapels->take(4) as $m)
            @php
                $colors = [['#eff6ff', '#2563eb'], ['#f0fdf4', '#16a34a'], ['#fffbeb', '#d97706'], ['#fef2f2', '#dc2626']];
                $c = $colors[$loop->index % count($colors)];
            @endphp
            <a href="{{ route('mapel.show', $m->id) }}" class="lms-row">
                <div class="lms-ico" style="background:{{ $c[0] }}; color:{{ $c[1] }};">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <div class="lms-info">
                    <div class="lms-title">{{ $m->nama }}</div>
                    <div class="lms-meta">
                        @if($isGuru)
                            <i class="bi bi-people me-1"></i> {{ $m->kelas->nama }}
                        @else
                            <i class="bi bi-person me-1"></i> {{ explode(' ', $m->guru->name)[0] }}
                        @endif
                    </div>
                </div>
                <i class="bi bi-chevron-right text-muted" style="font-size: 14px;"></i>
            </a>
        @empty
            <div class="text-center py-4 text-muted small">Belum ada mata pelajaran.</div>
        @endforelse
    </div>

    {{-- Latest Announcements --}}
    @if($publicPengumumans->count() > 0)
        <div class="pui-card db-section animate-up" style="animation-delay: 0.3s; margin-bottom: 24px;">
            <div class="section-header">
                <h3>Pengumuman Terbaru</h3>
                <a href="{{ route('pengumuman.index') }}">Semua</a>
            </div>
            @foreach($publicPengumumans as $p)
                <div class="pui-row" style="padding: 10px 0;">
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: #fff7ed; color: #f97316; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bi bi-megaphone"></i>
                    </div>
                    <div class="grow ms-3">
                        <div class="fw-bold" style="font-size: 14px; color: var(--navy);">{{ $p->judul }}</div>
                        <div class="small text-muted">{{ $p->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Bottom Sheet Classmates --}}
<div class="sheet" id="classmatesSheet" onclick="if(event.target===this)closeClassmates()">
    <div class="sheet-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="h5 fw-bold mb-0">Siswa Kelas({{ $user->kelas->nama ?? '-' }})</h3>
            <button type="button" class="btn-close" onclick="closeClassmates()"></button>
        </div>
        <div style="max-height: 60vh; overflow-y: auto; padding-right: 4px;">
            @forelse($classmates as $cm)
                <div class="classmate-row">
                    <img src="{{ $cm->avatar_url }}" class="classmate-avatar">
                    <div style="flex:1;">
                        <div class="fw-bold" style="font-size:14px; color:var(--navy);">{{ $cm->name }}</div>
                        <div class="small text-muted">NIS: {{ $cm->nik ?? '-' }}</div>
                    </div>
                    @if($cm->id !== $user->id)
                        <a href="{{ route('chat.startPrivate', $cm->id) }}" class="pui-btn pui-btn-ghost pui-btn-sm pui-btn-round" style="width:36px;height:36px;padding:0;">
                            <i class="bi bi-chat-text"></i>
                        </a>
                    @endif
                </div>
            @empty
                <div class="text-center py-5 text-muted">Belum ada data teman sekelas.</div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function openClassmates() {
        var el=document.getElementById('classmatesSheet'); if(!el) return;
        el.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeClassmates() {
        var el=document.getElementById('classmatesSheet'); if(!el) return;
        el.classList.remove('open');
        document.body.style.overflow = '';
    }

    // Voice notification if any unread
    var unreadCount = {{ $unreadNotificationsCount }};
    var lastKnown = localStorage.getItem('last_notif_count');
    if (unreadCount > 0 && unreadCount > (parseInt(lastKnown) || 0)) {
        try {
            var msg = new SpeechSynthesisUtterance("Ada notifikasi baru untuk {{ $honorific }}");
            msg.lang = 'id-ID';
            window.speechSynthesis.speak(msg);
        } catch(e) {}
    }
    localStorage.setItem('last_notif_count', unreadCount);
</script>
@endsection
