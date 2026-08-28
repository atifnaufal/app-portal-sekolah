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
            --blue: #246bfe;
            --surface: #f8fafc;
            --border: rgba(15, 23, 42, 0.05);
            --radius-lg: 28px;
            --radius-md: 20px;
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
        <div class="d-flex align-items-center gap-3">
            <div style="width: 40px; height: 40px; background: #e8f0fe; border-radius: 12px; display: grid; place-items: center; color: var(--blue);">
                <i class="bi bi-bell-fill"></i>
            </div>
            <div style="flex: 1;">
                <div id="toast-title" class="fw-bold text-dark" style="font-size: 14px;">Notifikasi</div>
                <div id="toast-msg" class="text-muted" style="font-size: 13px;">Ada pesan baru.</div>
            </div>
            <button onclick="document.getElementById('portal-toast').style.display='none'" class="btn-close btn-close-sm"></button>
        </div>
    </div>

    <audio id="notif-sound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

    <script>
        window.addEventListener('load', () => { document.getElementById('page-loader').style.display = 'none'; });

        function showNotification(title, message) {
            const toast = document.getElementById('portal-toast');
            document.getElementById('toast-title').innerText = title;
            document.getElementById('toast-msg').innerText = message;
            document.getElementById('notif-sound').play().catch(() => {});
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 5000);
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
</body>
</html>
