<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ?? 'Portal Sekolah' }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#246bfe">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    @vite(['resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root{--ink:#14213d;--muted:#64748b;--blue:#246bfe;--surface:#f8fafc;--danger:#d94b61}
        *{box-sizing:border-box; -webkit-tap-highlight-color: transparent;}

        body {
            margin:0;
            background:var(--surface);
            color:var(--ink);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding-bottom: {{ (isset($hideNav) && $hideNav) ? '0' : '100px' }}; /* Dynamic space */
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
            overscroll-behavior-y: contain;
        }

        input, textarea, select { user-select: text; -webkit-user-select: text; }
        .mobile-shell{max-width:680px;margin:auto}.mobile-hero{background:linear-gradient(140deg,#14213d,#246bfe);color:#fff;border-radius:0 0 30px 30px;padding:24px 20px 30px}.eyebrow{font-size:11px;letter-spacing:.13em;opacity:.75;font-weight:800}.hero-title{font-size:26px;font-weight:800;letter-spacing:-.02em}.avatar{width:48px;height:48px;border-radius:17px;background:#ffffff2b;display:grid;place-items:center;font-weight:800;font-size:18px}.class-pill{display:inline-block;background:#ffffff20;border:1px solid #ffffff42;border-radius:99px;padding:6px 12px;font-size:12px}.mobile-content{padding:20px}.section-title{font-size:17px;font-weight:800}.mobile-card{border:0;border-radius:20px;box-shadow:0 8px 24px #14213d08;animation:rise .45s both; position: relative; overflow: hidden; }.mobile-card:nth-child(2){animation-delay:.08s}.mobile-card:nth-child(3){animation-delay:.16s}

        .mobile-card > * { position: relative; z-index: 1; }

        .btn, .bottom-nav a, .tap-card {
            -webkit-tap-highlight-color: transparent;
            transition: transform 0.1s;
        }
        .btn:active, .bottom-nav a:active, .tap-card:active {
            transform: scale(0.96);
        }

        #offline-indicator {
            position: fixed; top: 0; left: 0; right: 0;
            background: #d94b61; color: white;
            text-align: center; font-size: 11px; font-weight: bold;
            padding: 4px; z-index: 10001; display: none;
        }

        /* Modern Floating Nav */
        .bottom-nav {
            position: fixed;
            z-index: 1000;
            bottom: 20px;
            left: 20px;
            right: 20px;
            max-width: 640px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 24px;
            padding: 12px 10px;
            display: flex;
            justify-content: space-around;
            box-shadow: 0 4px 20px rgba(20, 33, 61, 0.1);
            animation: navFadeIn 0.3s ease;
        }

        @keyframes navFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .bottom-nav a {
            color: var(--muted);
            text-align: center;
            text-decoration: none;
            font-size: 10px;
            font-weight: 700;
            min-width: 50px;
            position: relative;
            transition: all 0.3s;
        }
        .bottom-nav a.active { color: var(--blue); }
        .nav-icon {
            display: block;
            font-size: 20px;
            margin-bottom: 2px;
            transition: transform 0.3s;
        }
        .bottom-nav a.active .nav-icon {
            transform: translateY(-4px) scale(1.1);
        }

        /* Page Loading UI - Minimalist Transparent */
        #page-loader {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: transparent;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            pointer-events: none;
        }
        .loader-logo {
            width: 70px;
            height: auto;
            animation: floating 2s infinite ease-in-out;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1));
        }

        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .logout-panel{display:flex;align-items:center;justify-content:space-between;gap:14px;border:1px solid #f1d8dd;background:#fff5f6;border-radius:20px;padding:16px}.logout-panel .btn{border-radius:12px;white-space:nowrap}.stagger>*{animation:rise .45s both}.stagger>*:nth-child(2){animation-delay:.08s}.stagger>*:nth-child(3){animation-delay:.16s}@keyframes rise{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}}@media(min-width:681px){body{padding-top:20px}.mobile-hero{border-radius:30px 30px 0 0}}
        .avatar{width:48px!important;min-width:48px;max-width:48px;height:48px!important;min-height:48px;max-height:48px;flex:0 0 48px;aspect-ratio:1/1;overflow:hidden;display:grid;place-items:center;flex-shrink:0}.avatar img{display:block;width:100%!important;height:100%!important;max-width:none;max-height:none;object-fit:cover}

        #app-lock-screen {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, #14213d, #246bfe);
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 20000;
            color: white;
            text-align: center;
        }
        .lock-icon { font-size: 60px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div id="offline-indicator">Koneksi Terputus - Mode Offline</div>
    <div id="page-loader">
        <img src="{{ asset('logo_sekolah.png') }}" class="loader-logo" alt="Logo" onerror="this.src='https://png.pngtree.com/png-clipart/20230124/original/pngtree-high-school-kids-holding-big-red-and-white-flags-png-image_8927815.png'">
    </div>

    <div class="mobile-shell animate__animated animate__fadeIn">
        @yield('content')
    </div>

    @if(!isset($hideNav) || !$hideNav)
    <nav class="bottom-nav" aria-label="Navigasi utama">
        <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <span class="nav-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16"><path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z"/></svg>
            </span>
            Beranda
        </a>
        <a class="{{ request()->routeIs('absensi.*') ? 'active' : '' }}" href="{{ route('absensi.index') }}">
            <span class="nav-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16"><path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z"/></svg>
            </span>
            Absen
        </a>
        <a class="{{ request()->routeIs('chat.*') ? 'active' : '' }}" href="{{ route('chat.index') }}">
            <span class="nav-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/></svg>
            </span>
            Chat
        </a>
        <a class="{{ request()->routeIs('tugas.*') ? 'active' : '' }}" href="{{ route('tugas.index') }}">
            <span class="nav-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16"><path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/><path d="M11.354 4.854a.5.5 0 0 0-.708-.708L7.5 7.293 6.354 6.146a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3.5-3.5z"/></svg>
            </span>
            Tugas
        </a>
        <a class="{{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.show') }}">
            <span class="nav-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/></svg>
            </span>
            Profil
        </a>
    </nav>
    @endif

    <script>
        window.addEventListener('load', () => {
            document.getElementById('page-loader').style.display = 'none';
        });

        window.addEventListener('online', () => document.getElementById('offline-indicator').style.display = 'none');
        window.addEventListener('offline', () => document.getElementById('offline-indicator').style.display = 'block');

        document.querySelectorAll('a, button, .tap-card').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href && !href.startsWith('#') && !href.startsWith('javascript') && !this.classList.contains('no-loader')) {
                    document.getElementById('page-loader').style.display = 'flex';
                }
            });
        });

        const isLoggedIn = {{ session()->has('user_id') ? 'true' : 'false' }};
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(reg => {
                    if (Notification.permission === 'default' && isLoggedIn) {
                         setTimeout(() => { if(confirm("Izinkan Notifikasi?")) Notification.requestPermission(); }, 3000);
                    }
                });
            });
        }
    </script>

    <div id="portal-toast" class="animate__animated animate__fadeInDown" style="display:none; position:fixed; top:20px; left:20px; right:20px; z-index:10000; background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-left: 5px solid #246bfe; padding: 15px;">
        <div class="d-flex align-items-center gap-3">
            <div id="toast-icon" style="width: 40px; height: 40px; background: #e8f0fe; border-radius: 10px; display: grid; place-items: center; color: #246bfe;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/></svg>
            </div>
            <div style="flex: 1;">
                <div id="toast-title" class="fw-bold text-dark" style="font-size: 14px;">Notifikasi</div>
                <div id="toast-msg" class="text-muted" style="font-size: 13px;">Ada pesan baru untuk Anda.</div>
            </div>
            <button onclick="document.getElementById('portal-toast').style.display='none'" class="btn-close btn-close-sm"></button>
        </div>
    </div>

    <audio id="notif-sound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

    <script>
        function showNotification(title, message) {
            const toast = document.getElementById('portal-toast');
            document.getElementById('toast-title').innerText = title;
            document.getElementById('toast-msg').innerText = message;
            document.getElementById('notif-sound').play().catch(() => {});
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 5000);
        }

        window.addEventListener('load', () => {
            if (window.Echo) {
                window.Echo.channel('portal-notifications')
                    .listen('.new-notification', (e) => {
                        showNotification(e.title, e.message);
                        if (window.location.pathname.includes('pengumuman') || window.location.pathname.includes('tugas')) {
                             const refreshBar = document.createElement('div');
                             refreshBar.style = 'position:fixed; bottom:110px; left:20px; right:20px; background:#14213d; color:white; padding:12px; border-radius:12px; z-index:9; text-align:center; font-size:13px; cursor:pointer;';
                             refreshBar.innerHTML = 'Data baru tersedia. <b>Segarkan</b>';
                             refreshBar.onclick = () => window.location.reload();
                             document.body.appendChild(refreshBar);
                        }
                    });
            }
        });

        @if(session('success') || session('error'))
        showNotification(
            @json(session('success') ? 'Berhasil' : 'Perhatian'),
            @json(session('success') ?: session('error'))
        );
        @endif
    </script>
</body>
</html>
