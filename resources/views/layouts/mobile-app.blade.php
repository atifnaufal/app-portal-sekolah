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
            padding-bottom: {{ (isset($hideNav) && $hideNav) ? '20px' : '110px' }};
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
            position: fixed; bottom: 24px; left: 20px; right: 20px;
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

    @if(!isset($hideNav) || !$hideNav)
    <nav class="bottom-nav">
        <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-house-door nav-icon"></i>Beranda
        </a>
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
    </nav>
    @endif

    <div id="portal-toast" class="animate__animated animate__fadeInDown" style="display:none;">
        <a id="toast-link" href="#" style="display:block; text-decoration:none; color:inherit;">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 42px; height: 42px; background: #e8f0fe; border-radius: 12px; display: grid; place-items: center; color: var(--blue); flex-shrink:0;">
                    <i class="bi bi-bell-fill" style="font-size:18px;"></i>
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
                    if (window.Android && window.Android.requestPermissions) {
                        window.Android.requestPermissions();
                    } else {
                        // Jika tidak ada bridge, setidaknya tampilkan info satu kali via toast
                        if (!localStorage.getItem('perm_hint_shown')) {
                            showNotification('Izin Aplikasi', 'Pastikan memberikan izin Penyimpanan agar bisa mengunduh laporan PDF/Excel.', '#');
                            localStorage.setItem('perm_hint_shown', 'true');
                        }
                    }
                }, 2000);

                // Perbaikan Download di WebView agar tidak reload
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    // Lewati link yang sudah memakai puiExportFile (ditangani sendiri).
                    if (link && link.getAttribute('onclick') && link.getAttribute('onclick').indexOf('puiExportFile') !== -1) return;
                    if (link && (link.href.includes('pdf') || link.href.includes('xls'))) {
                        // Jangan biarkan reload, gunakan method download APK jika tersedia
                        if (window.Android && window.Android.downloadFile) {
                            e.preventDefault();
                            window.Android.downloadFile(link.href);
                        } else {
                            // Fallback: gunakan window.location daripada window.open untuk WebView
                            e.preventDefault();
                            window.location.href = link.href;
                        }
                    }
                });
            }
        })();

        window.addEventListener('load', () => { document.getElementById('page-loader').style.display = 'none'; });

        var portalToastEl = document.getElementById('portal-toast');
        var toastTimer = null;
        var lastSoundAt = 0;

        function playNotifSound() {
            // Debounce: jangan bunyi berulang/beruntun dalam 1,5 detik
            var now = Date.now();
            var snd = document.getElementById('notif-sound');
            if (snd && (now - lastSoundAt) > 1500) {
                lastSoundAt = now;
                snd.currentTime = 0;
                snd.play().catch(function () {});
            }
        }

        function showNotification(title, message, url) {
            document.getElementById('toast-title').innerText = title || 'Notifikasi';
            document.getElementById('toast-msg').innerText = message || '';
            document.getElementById('toast-link').setAttribute('href', url || '#');
            playNotifSound();
            portalToastEl.style.display = 'block';
            portalToastEl.classList.remove('animate__fadeOutUp');
            portalToastEl.classList.add('animate__fadeInDown');
            if (toastTimer) clearTimeout(toastTimer);
            toastTimer = setTimeout(() => {
                portalToastEl.classList.remove('animate__fadeInDown');
                portalToastEl.classList.add('animate__fadeOutUp');
                setTimeout(() => { portalToastEl.style.display = 'none'; }, 400);
            }, 6000);
        }

        document.querySelectorAll('a, button').forEach(el => {
            el.addEventListener('click', function() {
                const href = this.getAttribute('href');
                if (href && href.length > 1 && !href.startsWith('#') && !href.startsWith('javascript')) {
                    document.getElementById('page-loader').style.display = 'flex';
                }
            });
        });

        @if(session('success') || session('error'))
            showNotification('Informasi', '{{ session('success') ?: session('error') }}');
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
                            (d.items || []).forEach(function (it) {
                                showNotification(it.judul, it.pesan, it.url || '#');
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
                setInterval(pollNotifications, 15000);
            }
            function stopHeartbeat() { if (stdout) { clearInterval(stdout); stdout = null; } }

            // Echo/Reverb: terima notifikasi push secara instan
            if (window.Echo) {
                var myId = @json((int) session('user_id'));
                try {
                    window.Echo.private('portal-notifications.' + myId)
                        .listen('.new-notification', function (e) {
                            showNotification(e.title || 'Notifikasi', e.message || '', '#');
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
        var puiFileStatus = document.getElementById('pui-file-status');
        var puiFileLog = document.getElementById('pui-file-log');
        var puiFileTitle = document.getElementById('pui-file-title');
        var puiFileSub = document.getElementById('pui-file-sub');
        var puiLogs = [];

        function puiPrintLog(msg) { puiLogs.push(msg); puiFileLog.innerText = puiLogs.join('\n'); }

        window.puiShowFilePanel = function (title, sub) {
            puiFileTitle.innerText = title || 'Unduh Laporan';
            puiFileSub.innerText = sub || '';
            puiFileLog.innerText = ''; puiLogs = [];
            puiFileOverlay.classList.add('on');
        };

        window.puiCloseFilePanel = function () { puiFileOverlay.classList.remove('on'); };

        window.puiCopyFileLog = function () {
            var t = puiFileSub.innerText + '\n' + puiLogs.join('\n');
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(t).then(function () { puiPrintLog('Detail disalin ke clipboard.'); });
            } else { puiPrintLog('Clipboard tidak tersedia di perangkat ini.'); }
        };

        // Fungsi tunggal export PDF/Excel di semua perangkat.
        window.puiExportFile = function (url, label, kind) {
            var humanKind = (kind === 'excel') ? 'Excel' : 'PDF';
            if (!window.PUI_IS_ANDROID_APP) {
                window.puiShowFilePanel(label, 'Membuka di peramban…');
                puiPrintLog('Perangkat: ' + window.puiDeviceReport());
                puiPrintLog('Metode: buka tab/pratinjau browser.');
                window.open(url, '_blank');
                puiPrintLog('Status: dibuka di tab baru. Anda dapat mengunduh dari sana.');
                return;
            }
            // Android (APK/WebView)
            window.puiShowFilePanel(label, 'Status: mencoba mengunduh di aplikasi…');
            puiPrintLog('Perangkat: Android (aplikasi/WebView).');
            puiPrintLog('File: ' + humanKind + ' · ' + label);
            if (window.Android && typeof window.Android.downloadFile === 'function') {
                try { window.Android.downloadFile(url); puiPrintLog('Bridge Android ditemukan: unduhan dikirim ke sistem.'); return; }
                catch (err) { puiPrintLog('Bridge error: ' + err.message); }
            }
            puiPrintLog('Kesalahan: modul unduhan Android (downloadFile) tidak tersedia.');
            puiPrintLog('Penyebab: versi aplikasi ini tidak punya akses simpan file otomatis.');
            puiPrintLog('Solusi: buka versi stabil di Desktop/komputer, lalu unduh ' + humanKind + ' di sana.');
            puiFileSub.innerText = 'Tidak dapat mengunduh di aplikasi ini.';
            puiFileStatus.innerText = 'PDF/Excel tidak dapat diunduh langsung di versi ini. Perangkat ini memakai aplikasi Android tanpa modul unduhan (downloadFile). Buka versi stabil Desktop untuk mengunduh ' + humanKind + ' dengan normal.';
            try { window.open(url, '_blank'); } catch (e) { /* abaikan */ }
        };
    </script>
</body>
</html>
