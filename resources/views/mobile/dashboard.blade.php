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
    .db-body { padding: 12px 16px 120px; max-width: 640px; margin: 0 auto; }

    /* Kartu section = `.pui-card` dari kit + spacing saja di sini */
    .db-section { padding: 20px 18px; }

    /* ==== Hero premium (background dialihkan ke token --grad-hero) ==== */
    .hero-card {
        background: var(--grad-hero);
        border-radius: var(--radius-lg);
        padding: 26px;
        margin-bottom: 20px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-hover);
    }
    .hero-card::before, .hero-card::after {
        content: ''; position: absolute; border-radius: 50%; pointer-events: none;
    }
    .hero-card::before {
        top: -50%; right: -30%; width: 200px; height: 200px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.32) 0%, transparent 70%);
    }
    .hero-card::after {
        bottom: -40%; left: -20%; width: 180px; height: 180px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.24) 0%, transparent 70%);
    }
    .hero-avatar {
        width: 52px; height: 52px; border-radius: var(--radius-md);
        overflow: hidden; display: flex; align-items: center; justify-content: center;
        font-size: 20px; font-weight: 800; flex-shrink: 0;
        background: var(--grad-primary);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3);
    }
    .hero-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-md); }
    .hero-greeting { font-size: 12.5px; opacity: 0.65; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; }
    .hero-name { font-size: 24px; font-weight: 800; letter-spacing: -0.02em; margin-top: 2px; }
    .hero-class {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 999px; padding: 7px 14px; font-size: 12px; font-weight: 700; margin-top: 14px;
    }
    .hero-bell {
        width: 42px; height: 42px; border-radius: var(--radius-sm);
        background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.14);
        display: flex; align-items: center; justify-content: center; color: #fff;
        text-decoration: none; position: relative;
    }

    /* ==== Stat chips (radius/palet diseragamkan lewat token) ==== */
    .stat-row { display: flex; gap: 10px; margin-bottom: 16px; }
    .stat-card {
        flex: 1; position: relative; overflow: hidden;
        padding: 16px 10px 14px; border-radius: var(--radius-md);
        background: var(--surface-card); border: 1px solid var(--line);
        box-shadow: var(--shadow-card); transition: transform 0.13s;
    }
    .stat-card:active { transform: scale(0.96); }
    .stat-card::before {
        content: ''; position: absolute; top: -18px; right: -18px;
        width: 60px; height: 60px; border-radius: 50%;
        background: var(--stat-glow, rgba(99, 102, 241, 0.12)); filter: blur(3px);
    }
    .stat-icon {
        width: 36px; height: 36px; border-radius: var(--radius-sm); margin: 0 auto 8px;
        display: flex; align-items: center; justify-content: center; font-size: 15px;
        color: #fff; background: var(--grad-primary);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }
    .stat-num { font-size: 21px; font-weight: 800; letter-spacing: -0.02em; color: var(--ink); line-height: 1.05; }
    .stat-lbl { font-size: 9.5px; font-weight: 700; letter-spacing: 0.04em; color: var(--faint); margin-top: 5px; text-transform: uppercase; }

    /* ==== Quick Menu grid ==== */
    .menu-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
    .menu-item {
        display: flex; flex-direction: column; align-items: center;
        padding: 14px 2px; border-radius: var(--radius-md); text-decoration: none;
        background: var(--surface-card); border: 1px solid var(--line);
        box-shadow: var(--shadow-card); transition: transform 0.16s; color: var(--ink);
    }
    .menu-item:active { transform: scale(0.94); }
    .menu-icon {
        width: 50px; height: 50px; border-radius: var(--radius-md);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; margin-bottom: 8px;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.1); transition: transform 0.16s;
    }
    .menu-item:hover .menu-icon { transform: translateY(-2px); }
    .menu-label { font-size: 11px; font-weight: 700; color: var(--mist); }

    /* ==== Mapel grid ==== */
    .mapel-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .mapel-card {
        background: var(--surface-card); border-radius: var(--radius-md); padding: 16px;
        text-decoration: none; border: 1px solid var(--line); box-shadow: var(--shadow-card);
        transition: transform 0.16s;
    }
    .mapel-card:active { transform: scale(0.96); }
    .mapel-icon {
        width: 44px; height: 44px; border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; margin-bottom: 12px;
    }
    .mapel-name { font-size: 14px; font-weight: 800; color: var(--ink); line-height: 1.3; margin-bottom: 4px; }
    .mapel-meta { font-size: 10px; color: var(--faint); font-weight: 600; }

    /* ==== Icon kecil untuk list item (.pui-row) ==== */
    .list-ico {
        width: 40px; height: 40px; border-radius: var(--radius-sm); flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 18px;
    }

    /* ==== Absensi bar + legend ==== */
    .absen-bar {
        height: 10px; border-radius: 99px; background: var(--line);
        overflow: hidden; display: flex; margin-bottom: 10px;
    }
    .absen-legend { display: flex; gap: 12px; flex-wrap: wrap; }
    .absen-legend-item { display: flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; color: var(--mist); }
    .absen-dot { width: 8px; height: 8px; border-radius: 50%; }

    /* ==== Alert SPP (warning amber) ==== */
    .alert-spp {
        border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 14px;
        display: flex; align-items: center; gap: 12px;
        background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1px solid #fde68a;
    }
    .alert-spp .ico {
        width: 40px; height: 40px; border-radius: var(--radius-sm); flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 18px;
        background: #fef3c7; color: #d97706;
    }

    @keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    .fade-up { animation: fadeUp 0.45s ease both; }
</style>

<div class="db-body">
    {{-- Hero Card --}}
    <div class="hero-card fade-up">
        <div style="display:flex;align-items:center;justify-content:space-between;position:relative;z-index:1;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="hero-avatar">
                    @if($user->foto)
                        <img src="{{ asset('storage/'.$user->foto) }}">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <div class="hero-greeting">Portal Akademik</div>
                    <div class="hero-name">Halo, {{ explode(' ', $user->name)[0] }}!</div>
                </div>
            </div>
            <div>
                <a href="{{ route('notifications.index') }}" class="hero-bell">
                    <i class="bi bi-bell-fill" style="font-size:17px;"></i>
                    <span data-live-dot id="notif-dot" style="display:none; position:absolute; top:8px; right:8px; min-width:18px; height:18px; padding:0 5px; border-radius:9px; background:#ef4444; border:2px solid var(--navy-2); color:#fff; font-size:10px; font-weight:800; place-items:center; line-height:1;">0</span>
                </a>
            </div>
        </div>
        @if($user->kelas)
            <div class="hero-class" style="position:relative;z-index:1;">
                <i class="bi bi-mortarboard-fill" style="color:#fbbf24;font-size:14px;"></i>
                {{ $user->kelas->nama }}
            </div>
        @endif
    </div>

    {{-- Stat Cards (premium chips, aksen diseragamkan indigo/blue) --}}
    <div class="stat-row fade-up" style="animation-delay:0.05s;">
        <a href="{{ route('tugas.index') }}" class="stat-card text-decoration-none" style="--stat-glow:rgba(37,99,235,0.14);">
            <div class="stat-icon"><i class="bi bi-journal-check"></i></div>
            <div class="stat-num">{{ $tugasAktif }}</div>
            <div class="stat-lbl">Tugas Aktif</div>
        </a>
        @if($spp)
            <a href="{{ route('spp.index') }}" class="stat-card text-decoration-none" style="--stat-glow:rgba(16,185,129,0.14);">
                <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
                <div class="stat-num">{{ $spp['lunas'] }}<span style="font-size:12px;opacity:0.5;">/{{ $spp['total'] }}</span></div>
                <div class="stat-lbl">SPP Lunas</div>
            </a>
        @endif
        <div class="stat-card" style="--stat-glow:rgba(245,158,11,0.15);">
            <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-num">{{ $pctHadir }}<span style="font-size:12px;">%</span></div>
            <div class="stat-lbl">Hadir</div>
        </div>
    </div>

    {{-- Quick Menu --}}
    <div class="pui-card db-section fade-up" style="animation-delay:0.1s;">
        <div class="pui-section" style="margin-top:0;">
            <h3>Menu Cepat</h3>
        </div>
        <div class="menu-grid">
            <a href="{{ route('absensi.index') }}" class="menu-item">
                <div class="menu-icon" style="background:linear-gradient(135deg,#4f7cff,#2563eb);color:#fff;"><i class="bi bi-calendar-check-fill"></i></div>
                <div class="menu-label">Absensi</div>
            </a>
            <a href="{{ route('tugas.index') }}" class="menu-item">
                <div class="menu-icon" style="background:linear-gradient(135deg,#fbbf24,#f59e0b);color:#fff;"><i class="bi bi-journal-check"></i></div>
                <div class="menu-label">Tugas</div>
            </a>
            <a href="{{ route('spp.index') }}" class="menu-item">
                <div class="menu-icon" style="background:linear-gradient(135deg,#34d399,#10b981);color:#fff;"><i class="bi bi-wallet2"></i></div>
                <div class="menu-label">SPP</div>
            </a>
            <a href="{{ route('chat.index') }}" class="menu-item">
                <div class="menu-icon" style="background:linear-gradient(135deg,#a78bfa,#7c3aed);color:#fff;"><i class="bi bi-chat-dots-fill"></i></div>
                <div class="menu-label">Chat</div>
            </a>
            <a href="{{ route('perpustakaan.index') }}" class="menu-item">
                <div class="menu-icon" style="background:linear-gradient(135deg,#60a5fa,#2563eb);color:#fff;"><i class="bi bi-book-half"></i></div>
                <div class="menu-label">Perpus</div>
            </a>
            <a href="{{ route('jadwal.index') }}" class="menu-item">
                <div class="menu-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;"><i class="bi bi-calendar3"></i></div>
                <div class="menu-label">Jadwal</div>
            </a>
            <a href="{{ route('nilai.index') }}" class="menu-item">
                <div class="menu-icon" style="background:linear-gradient(135deg,#f472b6,#db2777);color:#fff;"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="menu-label">Nilai</div>
            </a>
            <a href="{{ route('eskul.index') }}" class="menu-item">
                <div class="menu-icon" style="background:linear-gradient(135deg,#ec4899,#be185d);color:#fff;"><i class="bi bi-flag-fill"></i></div>
                <div class="menu-label">Eskul</div>
            </a>
            <!-- <a href="{{ route('pengumuman.index') }}" class="menu-item">
                <div class="menu-icon" style="background:linear-gradient(135deg,#fee2e2,#fecaca);color:#dc2626;"><i class="bi bi-megaphone-fill"></i></div>
                <div class="menu-label">Info</div>
            </a>

            @if($isGuru)
               <a href="{{ route('mahasiswa.index') }}" class="menu-item">
                <div class="menu-icon" style="background:linear-gradient(135deg,#cffafe,#a5f3fc);color:#0891b2;"><i class="bi bi-people-fill"></i></div>
                <div class="menu-label">Siswa</div>
                </a>
                <a href="{{ route('tugas.create') }}" class="menu-item">
                    <div class="menu-icon" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#059669;"><i class="bi bi-plus-circle-fill"></i></div>
                    <div class="menu-label">Buat Tugas</div>
                </a>
            @endif
            <a href="{{ route('profile.show') }}" class="menu-item">
                <div class="menu-icon" style="background:linear-gradient(135deg,#f1f5f9,#e2e8f0);color:#64748b;"><i class="bi bi-gear-fill"></i></div>
                <div class="menu-label">Profil</div>
            </a> -->

        </div>
    </div>

    {{-- Absensi Bulan Ini --}}
    @if($totalAbsen > 0)
        <div class="pui-card db-section fade-up" style="animation-delay:0.15s;">
            <div class="pui-section" style="margin-top:0;">
                <h3>Absensi Bulan Ini</h3>
                <p style="margin-top:0;">{{ $totalAbsen }} hari</p>
            </div>
            <div class="absen-bar">
                <span style="width:{{ ($hadir/$totalAbsen)*100 }}%;background:linear-gradient(90deg,#16a34a,#4ade80);"></span>
                <span style="width:{{ ($sakit/$totalAbsen)*100 }}%;background:linear-gradient(90deg,#f59e0b,#fbbf24);"></span>
                <span style="width:{{ ($izin/$totalAbsen)*100 }}%;background:linear-gradient(90deg,#3b82f6,#60a5fa);"></span>
                <span style="width:{{ ($alpha/$totalAbsen)*100 }}%;background:linear-gradient(90deg,#ef4444,#f87171);"></span>
            </div>
            <div class="absen-legend">
                <div class="absen-legend-item"><span class="absen-dot" style="background:#16a34a;"></span> Hadir {{ $hadir }}</div>
                <div class="absen-legend-item"><span class="absen-dot" style="background:#f59e0b;"></span> Sakit {{ $sakit }}</div>
                <div class="absen-legend-item"><span class="absen-dot" style="background:#3b82f6;"></span> Izin {{ $izin }}</div>
                <div class="absen-legend-item"><span class="absen-dot" style="background:#ef4444;"></span> Alpha {{ $alpha }}</div>
            </div>
        </div>
    @endif


    {{-- LMS Section --}}
    <div class="pui-card db-section fade-up" style="animation-delay:0.08s;">
        <div class="pui-section" style="margin-top:0;">
            <h3>{{ $isGuru ? 'Mata Pelajaran Diampu' : 'Mata Pelajaran Saya' }}</h3>
        </div>
        <div class="mapel-grid">
            @forelse($mapels as $m)
                <a href="{{ route('mapel.show', $m->id) }}" class="mapel-card">
                    @php
                        $colors = [
                            ['#eff6ff', '#2563eb'], ['#f0fdf4', '#16a34a'],
                            ['#fefce8', '#ca8a04'], ['#fef2f2', '#dc2626'],
                            ['#f5f3ff', '#7c3aed'], ['#fff1f2', '#db2777']
                        ];
                        $c = $colors[$loop->index % count($colors)];
                    @endphp
                    <div class="mapel-icon" style="background:{{ $c[0] }}; color:{{ $c[1] }};">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <div class="mapel-name">{{ $m->nama }}</div>
                    <div class="mapel-meta">
                        @if($isGuru)
                            <i class="bi bi-people-fill"></i> {{ $m->kelas->nama }}
                        @else
                            <i class="bi bi-person-badge-fill"></i> {{ explode(' ', $m->guru->name)[0] }}
                        @endif
                    </div>
                </a>
            @empty
                <div class="text-center py-3 w-100" style="grid-column: span 2;">
                    <p class="small" style="color:var(--faint);">Belum ada mata pelajaran.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Tugas Terbaru --}}
    <div class="pui-card db-section fade-up" style="animation-delay:0.2s;">
        <div class="pui-section" style="margin-top:0;">
            <h3>Tugas Terbaru</h3>
            <a href="{{ route('tugas.index') }}" class="link">Lihat Semua</a>
        </div>
        @forelse($tugas as $t)
            @php
                $dl = $t->deadlineStatus();
                $bgColors = ['ok' => '#eff6ff', 'soon' => '#fef3c7', 'today' => '#fee2e2', 'expired' => '#f1f5f9', 'open' => '#ecfdf5'];
                $txColors = ['ok' => '#2563eb', 'soon' => '#d97706', 'today' => '#dc2626', 'expired' => '#94a3b8', 'open' => '#059669'];
                $bg = $bgColors[$dl['key']] ?? '#f8fafc';
                $tx = $txColors[$dl['key']] ?? '#64748b';
            @endphp
            <a href="{{ route('tugas.show', $t) }}" class="pui-row">
                <div class="list-ico" style="background:{{ $bg }};color:{{ $tx }};">
                    <i class="bi {{ $t->isForm() ? 'bi-ui-checks-grid' : 'bi-file-earmark-text-fill' }}"></i>
                </div>
                <div class="grow">
                    <div class="t">{{ $t->judul }}</div>
                    <div class="s">{{ $dl['label'] }}</div>
                </div>
                <i class="bi bi-chevron-right" style="font-size:12px;color:var(--faint);"></i>
            </a>
        @empty
            <div style="text-align:center;padding:24px;color:var(--faint);font-size:13px;">Belum ada tugas</div>
        @endforelse
    </div>

    {{-- Pengumuman --}}
    <div class="pui-card db-section fade-up" style="animation-delay:0.25s;">
        <div class="pui-section" style="margin-top:0;">
            <h3>Pengumuman</h3>
            <a href="{{ route('pengumuman.index') }}" class="link">Lihat Semua</a>
        </div>
        @forelse($publicPengumumans as $p)
            <a href="{{ route('pengumuman.index') }}" class="pui-row">
                <div class="list-ico" style="background:linear-gradient(135deg,#fef3c7,#fde68a);color:#d97706;">
                    <i class="bi bi-megaphone-fill"></i>
                </div>
                <div class="grow">
                    <div class="t">{{ $p->judul }}</div>
                    <div class="s">{{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 50) }}</div>
                </div>
            </a>
        @empty
            <div style="text-align:center;padding:24px;color:var(--faint);font-size:13px;">Belum ada pengumuman</div>
        @endforelse
    </div>

    {{-- Alert SPP --}}
    @if($spp && $spp['kekurangan'] > 0)
        <div class="alert-spp fade-up" style="animation-delay:0.3s;">
            <div class="ico">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div style="flex:1;">
                <div style="font-size:11px;font-weight:700;color:#92400e;">Tunggakan SPP</div>
                <div style="font-size:17px;font-weight:800;color:#b45309;">Rp {{ number_format($spp['kekurangan'], 0, ',', '.') }}</div>
            </div>
            <a href="{{ route('spp.index') }}" style="font-size:11px;font-weight:700;color:#d97706;text-decoration:none;">Detail →</a>
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
