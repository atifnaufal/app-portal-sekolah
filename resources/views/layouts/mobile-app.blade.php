<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Portal Sekolah' }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    @vite(['resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --navy: #0f172a;
            --navy-2: #1e1b4b;
            --indigo: #6366f1;
            --blue: #2563eb;
            --blue-bright: #246bfe;
            --surface: #f6f7fb;
            --surface-card: #ffffff;
            --ink: #0f172a;
            --mist: #64748b;
            --faint: #94a3b8;
            --line: rgba(15, 23, 42, 0.07);
            --line-strong: rgba(15, 23, 42, 0.1);
            --radius-lg: 28px;
            --radius-md: 20px;
            --radius-sm: 14px;
            --shadow-card: 0 6px 20px rgba(15, 23, 42, 0.05);
            --shadow-hover: 0 14px 34px rgba(15, 23, 42, 0.1);
            --grad-primary: linear-gradient(135deg, #4f46e5 0%, #6366f1 55%, #2563eb 100%);
            --grad-hero: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            margin: 0; background: var(--surface); color: var(--navy);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding-bottom: {{ (isset($hideNav) && $hideNav) ? '20px' : 'calc(110px + env(safe-area-inset-bottom))' }};
            user-select: none; -webkit-user-select: none;
            touch-action: manipulation; overscroll-behavior-y: contain;
            -webkit-font-smoothing: antialiased;
        }

        input, textarea, select { user-select: text; -webkit-user-select: text; }

        .mobile-shell { max-width: 640px; margin: auto; }

        .mobile-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: #fff; border-radius: 0 0 32px 32px;
            padding: 32px 24px 40px; position: relative; overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
        }
        .mobile-hero::after {
            content: ''; position: absolute; top: -50px; right: -30px;
            width: 150px; height: 150px; border-radius: 50%;
            background: radial-gradient(circle, rgba(36, 107, 254, 0.2) 0%, transparent 70%);
        }

        .eyebrow { font-size: 11px; letter-spacing: 0.12em; opacity: 0.6; font-weight: 800; text-transform: uppercase; }
        .hero-title { font-size: 28px; font-weight: 800; letter-spacing: -0.02em; margin-top: 4px; }

        .mobile-card {
            border: 0; border-radius: var(--radius-md);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
            background: #fff; margin-bottom: 16px;
        }

        .btn:active, .tap-card:active { transform: scale(0.96); }

        /* Premium Floating Nav */
        .bottom-nav {
            position: fixed; bottom: calc(24px + env(safe-area-inset-bottom)); left: 20px; right: 20px;
            max-width: 500px; margin: 0 auto;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px; padding: 10px 8px;
            display: flex; justify-content: space-around;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
            z-index: 2000;
        }

        .bottom-nav a {
            color: #94a3b8; text-align: center; text-decoration: none;
            font-size: 10px; font-weight: 700; flex: 1;
            transition: all 0.3s; padding: 6px 0;
        }
        .bottom-nav a.active { color: var(--blue); }
        .nav-icon { display: block; font-size: 20px; margin-bottom: 2px; transition: all 0.3s; }
        .bottom-nav a.active .nav-icon { transform: translateY(-4px); }

        /* Loader */
        #page-loader {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px);
            display: none; align-items: center; justify-content: center;
        }
        .loader-logo { width: 60px; animation: pulse 2s infinite ease-in-out; }
        @keyframes pulse { 0% { transform: scale(1); opacity: 0.8; } 50% { transform: scale(1.1); opacity: 1; } 100% { transform: scale(1); opacity: 0.8; } }

        .stagger > * { animation: fadeUp 0.5s both; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

        #portal-toast {
            position: fixed; top: 20px; left: 16px; right: 16px; z-index: 10001;
            background: #fff; border-radius: 20px; padding: 16px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
            border: 1px solid rgba(15, 23, 42, 0.05); border-left: 6px solid var(--blue);
            transition: all 0.3s;
        }

        /* ============================================================
           UI KIT — komponen premium terpusat (dipakai seluruh halaman)
           ============================================================ */
        .pui-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            border: 0; cursor: pointer; text-decoration: none;
            font-weight: 700; border-radius: var(--radius-sm);
            padding: 13px 20px; font-size: 14px; line-height: 1;
            transition: transform .16s, box-shadow .18s, filter .18s, background .18s;
            box-shadow: 0 6px 16px rgba(15,23,42,.08);
        }
        .pui-btn:active { transform: scale(.96); }
        .pui-btn:disabled { opacity: .5; pointer-events: none; }
        .pui-btn-primary {
            color: #fff; background: var(--grad-primary);
            box-shadow: 0 10px 24px rgba(79,70,229,.32);
        }
        .pui-btn-primary:active { box-shadow: 0 6px 14px rgba(79,70,229,.28); }
        .pui-btn-ghost { color: var(--ink); background: #fff; border: 1px solid var(--line-strong); }
        .pui-btn-soft { color: var(--indigo); background: #eef2ff; }
        .pui-btn-danger { color: #fff; background: linear-gradient(135deg,#dc2626,#ef4444); box-shadow: 0 10px 24px rgba(220,38,38,.28); }
        .pui-btn-block { width: 100%; }
        .pui-btn-sm { padding: 9px 14px; font-size: 12.5px; border-radius: 12px; }
        .pui-btn-round { border-radius: 999px; }

        .pui-card {
            background: var(--surface-card); border: 1px solid var(--line);
            border-radius: var(--radius-md); box-shadow: var(--shadow-card);
        }

        .pui-chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 13px; border-radius: 999px;
            font-size: 11.5px; font-weight: 700; letter-spacing: .01em;
            background: #f1f5f9; color: var(--mist); border: 1px solid transparent;
        }
        .pui-chip i { font-size: 12px; }
        .pui-chip-primary { background: #eef2ff; color: #4f46e5; }
        .pui-chip-green    { background: #ecfdf5; color: #059669; }
        .pui-chip-amber    { background: #fffbeb; color: #d97706; }
        .pui-chip-red      { background: #fef2f2; color: #dc2626; }
        .pui-chip-sky      { background: #f0f9ff; color: #0284c7; }
        .pui-chip-violet   { background: #f5f3ff; color: #7c3aed; }
        .pui-chip-ink      { background: rgba(15,23,42,.6); color: #fff; }

        .pui-avatar {
            width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
            display: grid; place-items: center; color: #fff; font-weight: 800; font-size: 15px;
            background: var(--grad-primary);
            box-shadow: 0 4px 12px rgba(79,70,229,.25);
        }
        .pui-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

        .pui-section {
            display: flex; align-items: baseline; justify-content: space-between;
            gap: 10px; margin: 22px 2px 12px;
        }
        .pui-section h3 { font-size: 16px; font-weight: 800; color: var(--ink); margin: 0; letter-spacing: -.01em; }
        .pui-section p  { margin: 2px 0 0; font-size: 12px; color: var(--faint); }
        .pui-section a.link { margin-left: auto; font-size: 12.5px; font-weight: 700; color: var(--indigo); text-decoration: none; }

        .pui-input, .pui-select, .pui-textarea {
            width: 100%; padding: 13px 15px; border-radius: var(--radius-sm);
            border: 1.5px solid var(--line-strong); background: #fff; color: var(--ink);
            font-size: 14px; font-weight: 500; outline: none;
            transition: border-color .18s, box-shadow .18s;
        }
        .pui-input:focus, .pui-select:focus, .pui-textarea:focus {
            border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(99,102,241,.14);
        }
        .pui-label { display: block; font-size: 12.5px; font-weight: 700; color: var(--ink); margin-bottom: 6px; }
        .pui-field { margin-bottom: 16px; }
        .pui-stat {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; padding: 14px 8px; border-radius: var(--radius-md);
            background: #fff; border: 1px solid var(--line); box-shadow: var(--shadow-card);
        }
        .pui-stat .num { font-size: 22px; font-weight: 800; letter-spacing: -.02em; color: var(--ink); }
        .pui-stat .lb { font-size: 10px; font-weight: 700; letter-spacing: .04em; color: var(--faint); text-transform: uppercase; margin-top: 5px; }

        .pui-row {
            display: flex; align-items: center; gap: 14px; padding: 13px 0;
            text-decoration: none; color: var(--ink);
        }
        .pui-row + .pui-row { border-top: 1px solid var(--line); }
        .pui-row .grow { flex: 1; min-width: 0; }
        .pui-row .t { font-size: 14px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .pui-row .s { font-size: 11.5px; color: var(--faint); }

        .pui-empty {
            text-align: center; padding: 48px 24px; color: var(--faint);
        }
        .pui-empty .ico { font-size: 42px; opacity: .45; }
        .pui-empty h4 { color: var(--ink); font-weight: 800; margin: 12px 0 4px; font-size: 15.5px; }
        .pui-empty p { font-size: 13px; margin: 0; }

        .pui-topbar {
            display: flex; align-items: center; gap: 12px;
            padding: 16px 18px 0; max-width: 640px; margin: 0 auto;
        }
        .pui-topbar .back {
            display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
            color: var(--mist); font-weight: 800; font-size: 13px; text-decoration: none;
            padding: 9px 12px; border-radius: 12px; background: rgba(255,255,255,.8);
            border: 1px solid var(--line-strong); box-shadow: 0 2px 8px rgba(15,23,42,.04);
            backdrop-filter: blur(8px); transition: all .18s;
        }
        .pui-topbar .back:active { transform: scale(.97); background: #fff; }
        .pui-topbar h1 { font-size: 22px; font-weight: 800; letter-spacing: -.02em; margin: 0; color: var(--ink); }
        .pui-topbar .spacer { flex: 1; }
    </style>
</head>
<body>
    <div id="page-loader">
        <img src="{{ asset('logo_sekolah.png') }}" class="loader-logo" alt="Logo" onerror="this.style.display='none'">
    </div>

    <div class="mobile-shell">
        @yield('content')
    </div>

    <!-- Global App Lock Overlay -->
    <div id="app-lock-overlay" style="position:fixed;inset:0;background:var(--navy);z-index:30000;display:none;flex-direction:column;align-items:center;justify-content:center;color:#fff;padding:20px;">
        <div class="mb-5 text-center">
            <div style="width:80px;height:80px;background:rgba(255,255,255,0.1);border-radius:24px;margin:0 auto 20px;display:grid;place-items:center;">
                <i class="bi bi-shield-lock-fill" style="font-size:32px;color:var(--blue-bright);"></i>
            </div>
            <h4 class="fw-bold mb-1">Akses Terkunci</h4>
            <p id="lock-subtitle" class="text-muted small">Masukkan PIN atau gunakan Biometrik</p>
            <p id="lock-error" class="small" style="color:#f87171;display:none;margin-top:8px;"></p>
            <p id="lock-countdown" class="small" style="color:#fbbf24;display:none;margin-top:8px;"></p>
        </div>

        <div id="lock-pin-container" class="w-100" style="max-width:280px;">
            <div class="d-flex justify-content-center gap-3 mb-4" id="pin-dots"></div>

            <div id="pin-keypad" style="display:grid;grid-template-columns:repeat(3,1fr);gap:15px;text-align:center;">
                @foreach([1,2,3,4,5,6,7,8,9] as $n)
                    <button class="pui-btn pui-btn-ghost pui-btn-round pin-key" style="height:60px;width:60px;background:rgba(255,255,255,0.05);border-color:transparent;color:#fff;font-size:20px;" onclick="inputPin({{ $n }})">{{ $n }}</button>
                @endforeach
                <button class="pui-btn pui-btn-ghost pui-btn-round pin-key" id="btn-biometric" style="height:60px;width:60px;background:transparent;border-color:transparent;color:#fff;display:none;" onclick="useBiometric()"><i class="bi bi-fingerprint" style="font-size:24px;"></i></button>
                <button class="pui-btn pui-btn-ghost pui-btn-round pin-key" style="height:60px;width:60px;background:rgba(255,255,255,0.05);border-color:transparent;color:#fff;font-size:20px;" onclick="inputPin(0)">0</button>
                <button class="pui-btn pui-btn-ghost pui-btn-round pin-key" style="height:60px;width:60px;background:transparent;border-color:transparent;color:#fff;" onclick="clearPin()"><i class="bi bi-backspace" style="font-size:20px;"></i></button>
            </div>
        </div>

        <div class="mt-5">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); window.Capacitor.Plugins.NativeBridge.clearToken().then(function() { localStorage.removeItem('pin_set'); localStorage.removeItem('biometric_enabled'); sessionStorage.clear(); document.getElementById('logout-form').submit(); }).catch(function() { localStorage.removeItem('pin_set'); localStorage.removeItem('biometric_enabled'); sessionStorage.clear(); document.getElementById('logout-form').submit(); })" class="text-white-50 text-decoration-none small">Logout Akun</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </div>
    </div>

    @if(!isset($hideNav) || !$hideNav)
    <nav class="bottom-nav" style="gap:2px">
        <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-house-door nav-icon"></i>Beranda
        </a>
        <a class="{{ request()->routeIs('global.portal') ? 'active' : '' }}" href="{{ route('global.portal') }}">
            <i class="bi bi-globe2 nav-icon"></i>Global
        </a>
        @if(session('user_role') !== 'admin')
        {{-- Tab berikut 403 untuk admin (route guru/siswa) — disembunyikan otomatis --}}
        <a class="{{ request()->routeIs('absensi.*') ? 'active' : '' }}" href="{{ route('absensi.index') }}">
            <i class="bi bi-calendar-check nav-icon"></i>Absen
        </a>
        <a class="{{ request()->routeIs('chat.*') ? 'active' : '' }}" href="{{ route('chat.index') }}">
            <i class="bi bi-chat-dots nav-icon"></i>Chat
        </a>
        <a class="{{ request()->routeIs('tugas.*') ? 'active' : '' }}" href="{{ route('tugas.index') }}">
            <i class="bi bi-journal-text nav-icon"></i>Tugas
        </a>
        <a class="{{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.show') }}">
            <i class="bi bi-person nav-icon"></i>Profil
        </a>
        @endif
    </nav>
    @endif

    <div id="portal-toast" class="animate__animated animate__fadeInDown" style="display:none;">
        <a id="toast-link" href="#" style="display:block; text-decoration:none; color:inherit;">
            <div class="d-flex align-items-center gap-3">
                <div id="toast-icon-box" style="width: 42px; height: 42px; background: #e8f0fe; border-radius: 12px; display: grid; place-items: center; color: var(--blue); flex-shrink:0;">
                    <i id="toast-icon" class="bi bi-bell-fill" style="font-size:18px;"></i>
                </div>
                <div style="flex: 1; min-width:0;">
                    <div id="toast-title" class="fw-bold text-dark" style="font-size: 14px; line-height:1.3;">Notifikasi</div>
                    <div id="toast-msg" class="text-muted" style="font-size: 13px; line-height:1.4; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">Ada pesan baru.</div>
                </div>
                <button type="button" class="btn-close btn-close-sm" onclick="event.preventDefault();event.stopPropagation();document.getElementById('portal-toast').style.display='none';" style="flex-shrink:0;"></button>
            </div>
        </a>
    </div>

    <audio id="notif-sound" src="{{ asset('sounds/doorbell.mp3') }}" preload="auto"></audio>

    <script>
        // ===== Sinkronisasi Izin & Download Mobile (APK/WebView) =====
        (function() {
            const isWebView = /wv|Android.*Version\/[\d\.]+/.test(navigator.userAgent);

            if (isWebView) {
                // Beri jeda sedikit agar UI stabil baru minta izin
                setTimeout(() => {
                    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
                        window.Capacitor.Plugins.NativeBridge.checkPermissionsStatus().then(function(res) {
                            if (!res.isComplete && !localStorage.getItem('perm_hint_shown')) {
                                showNotification('Izin Aplikasi', 'Pastikan memberikan izin Penyimpanan agar bisa mengunduh laporan PDF/Excel.', '#');
                                localStorage.setItem('perm_hint_shown', 'true');
                            }
                        });
                    } else if (!localStorage.getItem('perm_hint_shown')) {
                        showNotification('Izin Aplikasi', 'Pastikan memberikan izin Penyimpanan agar bisa mengunduh laporan PDF/Excel.', '#');
                        localStorage.setItem('perm_hint_shown', 'true');
                    }
                }, 2000);

                // Perbaikan Download di WebView agar tidak reload
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    // Lewati link yang sudah memakai puiExportFile (ditangani sendiri).
                    if (link && link.getAttribute('onclick') && link.getAttribute('onclick').indexOf('puiExportFile') !== -1) return;
                    if (link && (link.href.includes('pdf') || link.href.includes('xls'))) {
                        e.preventDefault();
                        // Coba via Capacitor plugin
                        if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
                            var filename = link.href.split('/').pop() || 'download';
                            window.Capacitor.Plugins.NativeBridge.downloadFile({ url: link.href, filename: filename })
                                .then(function() { showNotification('Unduhan', 'File tersimpan di folder Downloads.'); })
                                .catch(function() { window.open(link.href, '_blank'); });
                        } else if (window.Android && window.Android.downloadFile) {
                            window.Android.downloadFile(link.href);
                        } else {
                            window.open(link.href, '_blank');
                        }
                    }
                });
            }
        })();

        window.addEventListener('load', () => { var l=document.getElementById('page-loader'); if(l) l.style.display='none'; });
        setTimeout(() => { var l=document.getElementById('page-loader'); if(l) l.style.display='none'; }, 8000);
        // Offline banner
        (function(){
            var bar=document.createElement('div');
            bar.id='offline-bar';
            bar.style.cssText='position:fixed;top:0;left:0;right:0;z-index:10003;background:#dc2626;color:#fff;text-align:center;padding:8px 12px;font-size:12px;font-weight:700;display:none;';
            bar.innerHTML='<i class="bi bi-wifi-off me-1"></i> Anda sedang offline — beberapa fitur tidak tersedia';
            document.body.prepend(bar);
            function upd(){ bar.style.display=navigator.onLine?'none':'block'; }
            window.addEventListener('online',upd); window.addEventListener('offline',upd); upd();
        })();

        var portalToastEl = document.getElementById('portal-toast');
        var toastTimer = null;
        var lastSoundAt = 0;

        var NOTIF_ICONS = {
            chat: { icon: 'bi-chat-left-text-fill', bg: '#eff6ff', color: '#2563eb' },
            tugas: { icon: 'bi-journal-text', bg: '#f0fdf4', color: '#16a34a' },
            pengumuman: { icon: 'bi-megaphone-fill', bg: '#fefce8', color: '#ca8a04' },
            announcement: { icon: 'bi-megaphone-fill', bg: '#fefce8', color: '#ca8a04' },
            spp: { icon: 'bi-wallet2', bg: '#fff7ed', color: '#ea580c' },
            absensi: { icon: 'bi-calendar-check-fill', bg: '#faf5ff', color: '#9333ea' },
            eskul: { icon: 'bi-people-fill', bg: '#f0fdf4', color: '#15803d' },
            general: { icon: 'bi-bell-fill', bg: '#e8f0fe', color: '#3b82f6' }
        };
        var notifQueue = []; var isShowingNotif = false;

        function playNotifSound() {
            var now = Date.now();
            var snd = document.getElementById('notif-sound');
            if (snd && (now - lastSoundAt) > 1500) {
                lastSoundAt = now;
                snd.currentTime = 0;
                snd.play().catch(function () {});
            }
        }

        function showNotification(title, message, url, type, actorName, actorPhoto) {
            notifQueue.push([title, message, url, type, actorName, actorPhoto]);
            processNotifQueue();
        }
        function processNotifQueue(){
            if(isShowingNotif || notifQueue.length===0) return;
            isShowingNotif=true;
            var args=notifQueue.shift();
            var title=args[0], message=args[1], url=args[2], type=args[3], actorName=args[4], actorPhoto=args[5];
            type = type || 'general';
            if(type==='announcement') type='pengumuman';
            var cfg = NOTIF_ICONS[type] || NOTIF_ICONS.general;
            var iconBox = document.getElementById('toast-icon-box');
            var iconEl = document.getElementById('toast-icon');
            // WA-like: if chat with photo, show avatar img
            if(type==='chat' && actorPhoto){
                iconBox.style.background='transparent';
                iconBox.style.padding='0';
                iconBox.innerHTML='<img src="'+actorPhoto+'" style="width:42px;height:42px;border-radius:12px;object-fit:cover;border:1px solid rgba(15,23,42,.06)">'; 
            } else {
                iconBox.style.background = cfg.bg;
                iconBox.style.color = cfg.color;
                iconBox.style.padding='';
                iconBox.innerHTML='<i id="toast-icon" class="bi '+cfg.icon+'" style="font-size:18px;"></i>';
                iconEl = document.getElementById('toast-icon');
                if(iconEl) iconEl.className = 'bi ' + cfg.icon;
            }
            if (type === 'chat' && actorName) {
                document.getElementById('toast-title').innerText = actorName;
            } else {
                document.getElementById('toast-title').innerText = title || 'Notifikasi';
            }
            document.getElementById('toast-msg').innerText = message || '';
            document.getElementById('toast-link').setAttribute('href', url || '#');
            playNotifSound();
            try{ if(navigator.vibrate) navigator.vibrate(120); }catch(e){}
            portalToastEl.style.display = 'block';
            portalToastEl.classList.remove('animate__fadeOutUp');
            portalToastEl.classList.add('animate__fadeInDown');
            if (toastTimer) clearTimeout(toastTimer);
            toastTimer = setTimeout(() => {
                portalToastEl.classList.remove('animate__fadeInDown');
                portalToastEl.classList.add('animate__fadeOutUp');
                setTimeout(() => { portalToastEl.style.display = 'none'; isShowingNotif=false; processNotifQueue(); }, 400);
            }, 5500);
        }

        document.querySelectorAll('a[href]').forEach(el => {
            el.addEventListener('click', function() {
                const href = this.getAttribute('href');
                if (!href || href==='#' || href.startsWith('javascript:') || href.startsWith('tel:') || href.startsWith('mailto:')) return;
                if (href.length > 1 && href.startsWith('/') && !href.startsWith('//')) {
                    var loader=document.getElementById('page-loader'); if(loader) loader.style.display='flex';
                    setTimeout(function(){ if(loader) loader.style.display='none'; }, 7000);
                }
            });
        });

        @if(session('success') || session('error'))
            showNotification('Informasi', '{{ session('success') ?: session('error') }}');
        @endif

        // Sync token to NativeBridge if available
        @if(session('api_token'))
        (function() {
            if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
                window.Capacitor.Plugins.NativeBridge.saveToken({
                    token: '{{ session('api_token') }}',
                    baseUrl: window.location.origin
                });
                window.Capacitor.Plugins.NativeBridge.saveUserId({ userId: {{ (int) session('user_id') }} });
            }
        })();
        @endif
    </script>

    <script>
        // ===== Sesi realtime + Notifikasi langsung (seperti aplikasi native) =====
        (function () {
            var SESSION_URL = "{{ route('session.status') }}";
            var POLL_URL = "{{ route('notifications.poll') }}";
            var LOGIN_URL = "{{ route('login') }}";
            var MAX_RETRY = 3;

            // Update badge notifikasi bila ada elemen ber-peringatan unread live.
            function updateUnreadBadges(count) {
                document.querySelectorAll('[data-live-unread]').forEach(function (el) {
                    var num = document.getElementById(el.getAttribute('data-live-unread'));
                    if (!num) return;
                    num.innerText = count > 99 ? '99+' : String(count);
                    num.style.display = (count > 0) ? 'grid' : 'none';
                });
                // Elemen bertanda data-live-dot menampilkan angka + toggle tampil.
                document.querySelectorAll('[data-live-dot]').forEach(function (h) {
                    h.innerText = count > 99 ? '99+' : String(count);
                    h.style.display = (count > 0) ? 'grid' : 'none';
                });
            }

            var lastId = 0;
            var bootstrapped = false;   // sudah sinkron `lastId` dengan server
            var offlineRetry = 0;
            var stdout = null;
            var pollTimer = null;

            // Polling notifikasi baru (fallback saat Echo tak tersedia)
            function pollNotifications() {
                fetch(POLL_URL + '?last_id=' + lastId + '&t=' + Date.now(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { if (!r.ok) throw new Error('poll'); return r.json(); })
                    .then(function (d) {
                        offlineRetry = 0;
                        updateUnreadBadges(d.unread);

                        // Pertama kali: jadikan titik awal resume = notif terbaru.
                        // Dengan ini notifikasi histori lama TIDAK pernah di-popup/bunyi ulang.
                        if (!bootstrapped) {
                            bootstrapped = true;
                            lastId = (d.latest_id || 0);
                            d.items = [];   // jangan tampilkan histori yang sudah ada
                            return;
                        }

                        if (d.new_last_id && d.new_last_id > lastId) {
                            // Reverse agar popup berurutan kronologis (WhatsApp-like)
                            var list=(d.items||[]).slice().reverse();
                            list.forEach(function (it) {
                                if (!document.hidden) {
                                    showNotification(it.judul, it.pesan, it.url || '#', it.type, it.actor_name, it.actor_photo);
                                }
                            });
                            lastId = d.new_last_id;
                        }
                    })
                    .catch(function () {
                        offlineRetry++;
                        if (offlineRetry > MAX_RETRY) { stopHeartbeat(); }
                    });
            }

            // Heartbeat sesi: jaga last_activity + deteksi sesi mati secara realtime
            function heartbeat() {
                fetch(SESSION_URL + '?t=' + Date.now(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        if (!d.authenticated) {
                            // Sesi habis/expired -> redirect ke login langsung
                            if (d.redirect) window.location.href = d.redirect;
                            else window.location.href = LOGIN_URL;
                            return;
                        }
                        updateUnreadBadges(d.unread);
                    })
                    .catch(function () { /* offline sementara, abaikan */ });
            }

            function startHeartbeat() {
                if (stdout) return;
                heartbeat();
                stdout = setInterval(heartbeat, 60000);   // setiap 60 detik
                // Notifikasi di-poll lebih rapat untuk nuansa "langsung"
                pollNotifications();
                pollTimer = setInterval(pollNotifications, 15000);
            }
            function stopHeartbeat() {
                if (stdout) { clearInterval(stdout); stdout = null; }
                if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
            }

            // Echo/Reverb: terima notifikasi push secara instan
            if (window.Echo) {
                var myId = @json((int) session('user_id'));
                try {
                    window.Echo.private('portal-notifications.' + myId)
                        .listen('.new-notification', function (e) {
                            showNotification(e.title || 'Notifikasi', e.message || '', e.url || '#', e.type, e.actor_name, e.actor_photo);
                            var dots = document.querySelectorAll('[data-live-dot]');
                            dots.forEach(function (h) { h.style.display = 'block'; });
                        });
                } catch (e) { /* Echo gagal -> pakai polling */ }
            }

            startHeartbeat();
        })();
    </script>

    <!-- ===== Panel Log / Status Unduhan (PDF & Excel) ===== -->
    <div id="pui-file-overlay">
        <div class="pui-file-box">
            <div class="pui-file-head">
                <div class="pui-file-ico"><i class="bi bi-file-earmark-arrow-down-fill"></i></div>
                <div class="grow">
                    <div class="pui-file-title" id="pui-file-title">Unduh Laporan</div>
                    <div class="pui-file-sub" id="pui-file-sub">Status</div>
                </div>
                <button type="button" class="pui-file-close" onclick="puiCloseFilePanel()">&times;</button>
            </div>
            <div class="pui-file-body">
                <div class="pui-file-status" id="pui-file-status"></div>
                <div class="pui-file-log" id="pui-file-log"></div>
            </div>
            <div class="pui-file-actions">
                <button type="button" class="pui-btn pui-btn-ghost pui-btn-sm" onclick="puiCopyFileLog()">Salin Detail</button>
                <button type="button" class="pui-btn pui-btn-primary pui-btn-sm" onclick="puiCloseFilePanel()">Tutup</button>
            </div>
        </div>
    </div>

    <style>
        #pui-file-overlay { position: fixed; inset: 0; z-index: 10002; display: none; align-items: flex-end; justify-content: center; padding: 16px; background: rgba(15,23,42,.45); backdrop-filter: blur(4px); }
        #pui-file-overlay.on { display: flex; }
        .pui-file-box { width: 100%; max-width: 440px; background: #fff; border-radius: var(--radius-lg); box-shadow: 0 24px 60px rgba(15,23,42,.25); overflow: hidden; animation: puiSlideUp .25s ease; }
        @keyframes puiSlideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .pui-file-head { display: flex; align-items: center; gap: 12px; padding: 18px 18px 14px; background: linear-gradient(135deg,#0f172a,#1e293b); color: #fff; }
        .pui-file-ico { width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0; background: linear-gradient(135deg,var(--indigo),var(--blue)); display: grid; place-items: center; color: #fff; font-size: 18px; }
        .pui-file-title { font-weight: 800; font-size: 15px; }
        .pui-file-sub { font-size: 12px; color: rgba(255,255,255,.6); }
        .pui-file-close { background: rgba(255,255,255,.12); color: #fff; width: 30px; height: 30px; border-radius: 9px; font-size: 18px; }
        .pui-file-body { padding: 14px 18px; }
        .pui-file-status { font-size: 13.5px; color: var(--ink); line-height: 1.5; }
        .pui-file-log { margin-top: 12px; background: #f8fafc; border: 1px solid var(--line); border-radius: var(--radius-sm); padding: 10px 12px; font-family: ui-monospace,Menlo,Consolas,monospace; font-size: 11.5px; color: var(--mist); white-space: pre-wrap; max-height: 120px; overflow: auto; }
        .pui-file-actions { display: flex; justify-content: flex-end; gap: 8px; padding: 0 18px 16px; }
    </style>

    <script>
        // Deteksi perangkat: apakah sedang di aplikasi WebView Android (APK).
        (function () {
            var ua = navigator.userAgent;
            var isAndroidApp = /Android/i.test(ua) && (/wv|Version\/[\d\.]+/i.test(ua) || typeof window.Android !== 'undefined');
            window.PUI_IS_ANDROID_APP = isAndroidApp;
            window.puiDeviceReport = function () {
                if (isAndroidApp) return 'android-app';
                if (/Android/i.test(ua)) return 'android-browser';
                if (/iPhone|iPad|iPod/i.test(ua)) return 'ios';
                return 'desktop';
            };
        })();
    </script>

    <script>
        var puiFileOverlay = document.getElementById('pui-file-overlay');

        // ===== puiExportFile: universal download handler untuk PDF/Excel =====
        window.puiExportFile = function(url, label, kind) {
            if (!url) return;

            var isAndroidApp = window.PUI_IS_ANDROID_APP || false;
            var ext = (kind || 'pdf').toLowerCase();
            var icon = ext === 'excel' ? 'bi-file-earmark-excel' : 'bi-file-earmark-pdf';
            var color = ext === 'excel' ? '#16a34a' : '#dc2626';

            // Tampilkan overlay UI
            var overlay = document.getElementById('pui-file-overlay');
            if (overlay) {
                overlay.classList.add('on');
                overlay.querySelector('.pui-file-ico').innerHTML = '<i class="bi ' + icon + '"></i>';
                overlay.querySelector('.pui-file-ico').style.background = 'linear-gradient(135deg,' + color + ',' + color + 'cc)';
                overlay.querySelector('.pui-file-title').textContent = label || 'Download File';
                overlay.querySelector('.pui-file-sub').textContent = 'Menyiapkan unduhan...';
                overlay.querySelector('.pui-file-status').textContent = 'File ' + ext.toUpperCase() + ' sedang diproses.';
            }

            // Coba download native via Capacitor plugin
            if (isAndroidApp && window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
                window.Capacitor.Plugins.NativeBridge.downloadFile({
                    url: url,
                    filename: (label || 'download') + '.' + (ext === 'excel' ? 'xls' : 'pdf')
                }).then(function(res) {
                    if (overlay) {
                        overlay.querySelector('.pui-file-sub').textContent = 'Berhasil!';
                        overlay.querySelector('.pui-file-status').textContent = 'File tersimpan di folder Downloads.';
                        setTimeout(function() { overlay.classList.remove('on'); }, 2000);
                    }
                }).catch(function(e) {
                    if (overlay) {
                        overlay.querySelector('.pui-file-sub').textContent = 'Gagal mengunduh';
                        overlay.querySelector('.pui-file-status').textContent = (e.message || e) + '. Mencoba cara lain...';
                    }
                    // Fallback: window.open
                    window.open(url, '_blank');
                    setTimeout(function() { if (overlay) overlay.classList.remove('on'); }, 2500);
                });
            } else if (isAndroidApp && window.Android && window.Android.downloadFile) {
                // Legacy bridge fallback
                window.Android.downloadFile(url);
                setTimeout(function() { if (overlay) overlay.classList.remove('on'); }, 2000);
            } else {
                // Browser: buka di tab baru
                window.open(url, '_blank');
                setTimeout(function() { if (overlay) overlay.classList.remove('on'); }, 1500);
            }
        };
    </script>

    <script>
        // ===== PREMIUM SECURITY SYSTEM =====
        (function() {
            var PIN_MIN = 4;
            var PIN_MAX = 6;
            var MAX_ATTEMPTS = 5;
            var LOCKOUT_SECONDS = 30;
            var storedPinLength = 4;

            var currentPin = '';
            var attemptCount = parseInt(sessionStorage.getItem('pin_attempts') || '0', 10);
            var lockoutUntil = parseInt(sessionStorage.getItem('lockout_until') || '0', 10);
            var autoLockTimer = null;
            var biometricPrompting = false;

            function getAutoLockSeconds() {
                return parseInt(localStorage.getItem('auto_lock_seconds') || '60', 10);
            }

            function getPinLength() {
                return new Promise(function(resolve) {
                    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
                        window.Capacitor.Plugins.NativeBridge.getPinLength().then(function(res) {
                            storedPinLength = res.length || 4;
                            resolve(storedPinLength);
                        }).catch(function() { resolve(4); });
                    } else { resolve(4); }
                });
            }

            function renderPinDots(length) {
                var container = document.getElementById('pin-dots');
                container.innerHTML = '';
                for (var i = 0; i < length; i++) {
                    var dot = document.createElement('div');
                    dot.style.cssText = 'width:16px;height:16px;border-radius:50%;border:2px solid #fff;transition:all 0.2s;';
                    container.appendChild(dot);
                }
            }

            function updatePinDots() {
                var dots = document.getElementById('pin-dots').children;
                for (var i = 0; i < dots.length; i++) {
                    if (i < currentPin.length) {
                        dots[i].style.background = '#fff';
                        dots[i].style.transform = 'scale(1.1)';
                        setTimeout(function(el) { el.style.transform = 'scale(1)'; }, 100, dots[i]);
                    } else {
                        dots[i].style.background = 'transparent';
                        dots[i].style.transform = 'scale(1)';
                    }
                }
            }

            function showError(msg) {
                var el = document.getElementById('lock-error');
                el.textContent = msg;
                el.style.display = 'block';
                setTimeout(function() { el.style.display = 'none'; }, 3000);
            }

            function showCountdown(seconds) {
                var el = document.getElementById('lock-countdown');
                el.style.display = 'block';
                var remaining = seconds;
                el.textContent = 'Terlalu banyak percobaan. Coba lagi dalam ' + remaining + ' detik.';
                var interval = setInterval(function() {
                    remaining--;
                    if (remaining <= 0) {
                        clearInterval(interval);
                        el.style.display = 'none';
                        attemptCount = 0;
                        sessionStorage.removeItem('pin_attempts');
                        sessionStorage.removeItem('lockout_until');
                        enableKeypad(true);
                    } else {
                        el.textContent = 'Terlalu banyak percobaan. Coba lagi dalam ' + remaining + ' detik.';
                    }
                }, 1000);
            }

            function enableKeypad(enabled) {
                var keys = document.querySelectorAll('.pin-key');
                keys.forEach(function(k) { k.disabled = !enabled; k.style.opacity = enabled ? '1' : '0.3'; });
            }

            window.inputPin = function(n) {
                if (Date.now() < lockoutUntil) return;
                if (currentPin.length < PIN_MAX) {
                    currentPin += n;
                    updatePinDots();
                    if (currentPin.length === storedPinLength) {
                        verifyAppPin();
                    }
                }
            };

            window.clearPin = function() {
                currentPin = '';
                updatePinDots();
            };

            function verifyAppPin() {
                if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
                    window.Capacitor.Plugins.NativeBridge.verifyPin({ pin: currentPin }).then(function(res) {
                        if (res.isValid) {
                            unlockApp();
                        } else {
                            attemptCount++;
                            sessionStorage.setItem('pin_attempts', attemptCount);
                            currentPin = '';
                            updatePinDots();
                            if (attemptCount >= MAX_ATTEMPTS) {
                                lockoutUntil = Date.now() + (LOCKOUT_SECONDS * 1000);
                                sessionStorage.setItem('lockout_until', lockoutUntil);
                                enableKeypad(false);
                                showCountdown(LOCKOUT_SECONDS);
                            } else {
                                showError('PIN Salah. Sisa percobaan: ' + (MAX_ATTEMPTS - attemptCount));
                            }
                        }
                    });
                }
            }

            window.useBiometric = function() {
                if (biometricPrompting) return;
                if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
                    biometricPrompting = true;
                    window.Capacitor.Plugins.NativeBridge.performBiometricAuth().then(function(res) {
                        biometricPrompting = false;
                        if (res && res.cancelled) {
                            // User chose "Use PIN" — not an error, just stay on PIN input
                            return;
                        }
                        unlockApp();
                    }).catch(function(e) {
                        biometricPrompting = false;
                        // Real error — show message only if not cancelled
                        if (e && e.message && e.message.indexOf('cancel') === -1) {
                            showError('Biometrik gagal. Gunakan PIN.');
                        }
                    });
                }
            };

            function unlockApp() {
                sessionStorage.setItem('unlocked', 'true');
                sessionStorage.removeItem('pin_attempts');
                sessionStorage.removeItem('lockout_until');
                attemptCount = 0;
                document.getElementById('app-lock-overlay').style.display = 'none';
                resetAutoLock();
            }

            function lockApp() {
                if (localStorage.getItem('pin_set') !== 'true') return;
                sessionStorage.removeItem('unlocked');
                currentPin = '';
                document.getElementById('app-lock-overlay').style.display = 'flex';
                renderPinDots(storedPinLength);
                updatePinDots();
                if (localStorage.getItem('biometric_enabled') === 'true') {
                    setTimeout(window.useBiometric, 500);
                }
            }

            function resetAutoLock() {
                if (autoLockTimer) clearTimeout(autoLockTimer);
                var sec = getAutoLockSeconds();
                if (sec > 0 && localStorage.getItem('pin_set') === 'true') {
                    autoLockTimer = setTimeout(function() { lockApp(); }, sec * 1000);
                }
            }

            ['click', 'touchstart', 'keydown', 'scroll'].forEach(function(evt) {
                document.addEventListener(evt, function() {
                    if (sessionStorage.getItem('unlocked') === 'true') {
                        resetAutoLock();
                    }
                }, { passive: true });
            });

            document.addEventListener('visibilitychange', function() {
                if (localStorage.getItem('pin_set') !== 'true') return;
                var sec = getAutoLockSeconds();
                if (document.hidden) {
                    sessionStorage.setItem('bg_time', Date.now());
                } else {
                    var bgTime = parseInt(sessionStorage.getItem('bg_time') || '0', 10);
                    var bgDuration = bgTime ? (Date.now() - bgTime) / 1000 : 0;
                    if (bgDuration > 10 || sec <= 10) {
                        if (sessionStorage.getItem('unlocked') === 'true') {
                            lockApp();
                        }
                    } else if (sec > 0) {
                        resetAutoLock();
                    }
                }
            });

            window.addEventListener('pageshow', function(e) {
                if (e.persisted && localStorage.getItem('pin_set') === 'true') {
                    lockApp();
                }
            });

            window.addEventListener('load', function() {
                getPinLength().then(function(len) {
                    if (localStorage.getItem('pin_set') === 'true' && !sessionStorage.getItem('unlocked')) {
                        document.getElementById('app-lock-overlay').style.display = 'flex';
                        renderPinDots(len);
                        if (Date.now() < lockoutUntil) {
                            enableKeypad(false);
                            showCountdown(Math.ceil((lockoutUntil - Date.now()) / 1000));
                        }
                        if (localStorage.getItem('biometric_enabled') === 'true') {
                            setTimeout(window.useBiometric, 500);
                        }
                    } else {
                        renderPinDots(len);
                        resetAutoLock();
                    }
                    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
                        window.Capacitor.Plugins.NativeBridge.checkBiometricSupport().then(function(res) {
                            if (res.isAvailable) {
                                document.getElementById('btn-biometric').style.display = 'grid';
                            }
                        });
                    }
                });
            });

            window.lockApp = lockApp;
        })();
    </script>

    <!-- ===== GLOBAL ERROR CATCHER — premium, no reload loop ===== -->
    <div id="error-screen" style="position:fixed;inset:0;background:#0f172a;z-index:99999;display:none;flex-direction:column;align-items:center;justify-content:center;color:#fff;padding:24px;text-align:center;overflow:auto;">
        <div style="width:80px;height:80px;background:rgba(255,255,255,0.08);border-radius:24px;display:grid;place-items:center;margin:0 auto 16px;">
            <i id="error-icon" class="bi bi-exclamation-triangle" style="font-size:36px;color:#fbbf24;"></i>
        </div>
        <h4 id="error-title" style="font-size:18px;font-weight:800;margin-bottom:6px;">Terjadi Kesalahan</h4>
        <p id="error-detail" style="font-size:13px;color:#94a3b8;line-height:1.6;max-width:340px;margin:0 auto 8px;">Terjadi gangguan pada sistem. Silakan coba beberapa saat lagi.</p>
        <div id="error-meta" style="font-size:11px;color:#475569;margin-bottom:18px;max-width:340px;word-break:break-word;"></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;">
            <button id="error-retry" style="padding:12px 22px;background:#3b82f6;color:#fff;border:none;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;"><i class="bi bi-arrow-clockwise"></i> Coba Lagi</button>
            <button onclick="document.getElementById('error-screen').style.display='none'" style="padding:12px 22px;background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.15);border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">Tutup</button>
            <button id="error-copy" style="padding:12px 18px;background:transparent;color:#94a3b8;border:1px solid rgba(255,255,255,.15);border-radius:12px;font-size:12px;font-weight:700;cursor:pointer;"><i class="bi bi-clipboard"></i> Salin</button>
        </div>
        <div id="error-countdown" style="margin-top:12px;font-size:11px;color:#64748b;"></div>
        <div style="margin-top:24px;font-size:11px;color:#475569;">{{ config('app.name', 'Sekolah') }} &middot; <span id="error-trace"></span></div>
    </div>

    <script>
    (function() {
        var shown = false;
        var errorCount = 0;
        var lastShownAt = 0;
        var retryCount = 0;
        var MAX_RETRY = 3;
        var RETRY_COOLDOWN = 4000;
        var ERROR_THRESHOLD = 3;
        var CriticalErrors = ['Script error','Unexpected token','SyntaxError','ReferenceError','TypeError','ChunkLoadError','Failed to fetch'];
        var bgEndpoints = ['/session/status','/notifikasi/poll','/chat/poll'];
        var detailEl=document.getElementById('error-detail');
        var metaEl=document.getElementById('error-meta');
        var screenEl=document.getElementById('error-screen');
        var titleEl=document.getElementById('error-title');
        var iconEl=document.getElementById('error-icon');
        var traceEl=document.getElementById('error-trace');
        var countdownEl=document.getElementById('error-countdown');

        function isCritical(msg){ return CriticalErrors.some(function(e){ return String(msg||'').indexOf(e)!==-1; }); }
        function isBg(url){ return bgEndpoints.some(function(ep){ return url.indexOf(ep)!==-1; }); }

        function showError(msg, opts){
            opts=opts||{};
            var now=Date.now();
            if(now - lastShownAt < 1500) return; // debounce
            errorCount++;
            var critical = isCritical(msg) || opts.critical;
            if(!critical && errorCount < ERROR_THRESHOLD) return;
            if(shown && !opts.force) return;
            shown=true; lastShownAt=now;
            var friendly = opts.title || 'Terjadi Kesalahan';
            var color = opts.iconColor || '#fbbf24';
            var icon = opts.icon || 'bi-exclamation-triangle';
            titleEl.textContent=friendly;
            iconEl.className='bi '+icon;
            iconEl.style.color=color;
            if(detailEl) detailEl.textContent=opts.detail || ('Error: '+(msg||'unknown'));
            if(metaEl) metaEl.textContent='Waktu: '+new Date().toLocaleString('id-ID')+' | URL: '+location.pathname + (navigator.onLine?' | Online':' | Offline');
            if(traceEl) traceEl.textContent='Trace '+Math.random().toString(36).slice(2,8).toUpperCase()+' | '+location.href.slice(0,80);
            screenEl.style.display='flex';
            if(!navigator.onLine){
                if(detailEl) detailEl.textContent='Anda sedang offline. Periksa koneksi internet lalu coba lagi.';
                titleEl.textContent='Tidak Ada Koneksi';
                iconEl.className='bi bi-wifi-off';
                iconEl.style.color='#f87171';
            }
            if(opts.autoRetry && retryCount < MAX_RETRY){
                var wait = RETRY_COOLDOWN * Math.pow(1.5, retryCount);
                countdownEl.textContent='Mencoba otomatis dalam '+(wait/1000)+' dtk... ('+(retryCount+1)+'/'+MAX_RETRY+')';
                setTimeout(function(){ retryCount++; shown=false; screenEl.style.display='none'; countdownEl.textContent=''; }, wait);
            }
        }
        function resetAndReload(){
            if(retryCount >= MAX_RETRY){ showError('Batas percobaan tercapai. Muat ulang manual.',{force:true,title:'Gagal Memuat',icon:'bi-x-circle',iconColor:'#f87171',detail:'Sudah mencoba '+MAX_RETRY+'x. Periksa koneksi atau hubungi admin.'}); return; }
            retryCount++; shown=false; screenEl.style.display='none'; location.reload();
        }
        document.getElementById('error-retry').addEventListener('click', resetAndReload);
        document.getElementById('error-copy').addEventListener('click', function(){
            var t=(detailEl?detailEl.textContent:'')+'\n'+(metaEl?metaEl.textContent:'')+'\n'+location.href;
            if(navigator.clipboard) navigator.clipboard.writeText(t).then(function(){ countdownEl.textContent='Disalin!'; setTimeout(function(){countdownEl.textContent='';},1500);});
        });

        window.onerror = function(msg, src, line, col){
            var m = msg + (src ? ' @ '+(src.split('/').pop())+':'+line : '');
            if(isCritical(m)) showError(m,{critical:true, detail:m});
            return true;
        };
        window.addEventListener('unhandledrejection', function(e){
            var r = (e.reason && (e.reason.message||e.reason)) || e.detail || '';
            var s = String(r);
            if(s.indexOf('ChunkLoadError')!==-1 || s.indexOf('Loading chunk')!==-1){
                showError(s,{critical:true,title:'Gagal Memuat Modul',icon:'bi-cloud-download',detail:'File aplikasi gagal dimuat. Coba muat ulang. Jika di APK, tutup dan buka kembali.'});
            } else if(isCritical(s)) showError(s,{critical:true});
        });
        var origFetch = window.fetch;
        window.fetch = function(){
            var url = arguments[0]; var urlStr = typeof url==='string'?url:(url&&url.url||'');
            var bg = isBg(urlStr);
            return origFetch.apply(this, arguments).then(function(res){
                if(!bg){
                    if(res.status===419) showError('419 Sesi kedaluwarsa',{critical:true,title:'Sesi Kedaluwarsa',icon:'bi-clock-history',iconColor:'#fbbf24',detail:'Sesi berakhir. Muat ulang halaman dan login kembali.'});
                    else if(res.status===429) showError('429 Terlalu banyak permintaan',{critical:true,title:'Terlalu Sering',icon:'bi-hourglass-split',detail:'Tunggu beberapa detik lalu coba lagi.'});
                    else if(res.status===403) showError('403 Akses ditolak',{critical:true,title:'Akses Ditolak',icon:'bi-shield-x',detail:'Anda tidak memiliki izin untuk aksi ini.'});
                    else if(res.status>=500) showError('server_'+res.status,{critical:true,title:'Gangguan Server ('+res.status+')',icon:'bi-cpu',iconColor:'#f87171',detail:'Server mengembalikan '+res.status+'. Coba lagi atau hubungi admin jika berlanjut.'});
                }
                return res;
            }).catch(function(err){
                if(!bg){
                    if(!navigator.onLine) showError('offline',{critical:true,title:'Offline',icon:'bi-wifi-off',iconColor:'#f87171',detail:'Tidak ada koneksi. Periksa internet Anda.'});
                    else showError('network',{detail:'Gagal terhubung ke server. Periksa koneksi.'});
                }
                throw err;
            });
        };
        window.addEventListener('error', function(e){
            if(e.target && (e.target.tagName==='IMG'||e.target.tagName==='SCRIPT'||e.target.tagName==='LINK')){
                if(e.target.tagName==='SCRIPT') showError('ChunkLoadError '+ (e.target.src||''),{critical:true,title:'Gagal Memuat Script',icon:'bi-file-earmark-code'});
                return;
            }
        }, true);
        window.addEventListener('online', function(){ if(screenEl.style.display==='flex' && titleEl.textContent==='Tidak Ada Koneksi'){ screenEl.style.display='none'; shown=false; } });
    })();
    </script>

    @yield('scripts')
</body>
</html>
