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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root{--ink:#14213d;--muted:#738097;--blue:#246bfe;--surface:#f5f7fb;--danger:#d94b61}
        *{box-sizing:border-box; -webkit-tap-highlight-color: transparent;}

        body {
            margin:0;
            background:var(--surface);
            color:var(--ink);
            font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            padding-bottom:92px;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
            overscroll-behavior-y: contain;
        }

        input, textarea { user-select: text; -webkit-user-select: text; }
        .mobile-shell{max-width:680px;margin:auto}.mobile-hero{background:linear-gradient(140deg,#14213d,#246bfe);color:#fff;border-radius:0 0 30px 30px;padding:24px 20px 30px}.eyebrow{font-size:11px;letter-spacing:.13em;opacity:.75;font-weight:800}.hero-title{font-size:26px;font-weight:800;letter-spacing:-.02em}.avatar{width:48px;height:48px;border-radius:17px;background:#ffffff2b;display:grid;place-items:center;font-weight:800;font-size:18px}.class-pill{display:inline-block;background:#ffffff20;border:1px solid #ffffff42;border-radius:99px;padding:6px 12px;font-size:12px}.mobile-content{padding:20px}.section-title{font-size:17px;font-weight:800}.mobile-card{border:0;border-radius:20px;box-shadow:0 8px 24px #14213d12;animation:rise .45s both; position: relative; overflow: hidden; }.mobile-card:nth-child(2){animation-delay:.08s}.mobile-card:nth-child(3){animation-delay:.16s}

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
        }.tap-card{transition:transform .2s,box-shadow .2s}.tap-card:hover,.tap-card:active{transform:translateY(-3px);box-shadow:0 13px 28px #14213d1c}.icon-box{width:43px;height:43px;border-radius:14px;display:grid;place-items:center;font-size:20px;background:#e8efff;color:var(--blue)}.bottom-nav{position:fixed;z-index:5;bottom:0;left:0;right:0;background:#fffffff2;border-top:1px solid #e8ecf3;backdrop-filter:blur(14px);padding:10px max(18px,calc((100% - 680px)/2 + 18px)) calc(10px + env(safe-area-inset-bottom));display:flex;justify-content:space-around}.bottom-nav a{color:var(--muted);text-align:center;text-decoration:none;font-size:11px;font-weight:700;min-width:58px;transition:color .2s,transform .2s}.bottom-nav a:hover,.bottom-nav a.active{color:var(--blue)}.bottom-nav a:active{transform:scale(.92)}.nav-icon{display:block;font-size:21px;line-height:23px;margin-bottom:2px}.bottom-nav a.active .nav-icon{transform:translateY(-2px)}.profile-action{border-radius:14px}.logout-panel{display:flex;align-items:center;justify-content:space-between;gap:14px;border:1px solid #f1d8dd;background:#fff5f6;border-radius:20px;padding:16px}.logout-panel .btn{border-radius:12px;white-space:nowrap}.stagger>*{animation:rise .45s both}.stagger>*:nth-child(2){animation-delay:.08s}.stagger>*:nth-child(3){animation-delay:.16s}@keyframes rise{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}}@media(min-width:681px){body{padding-top:20px}.mobile-hero{border-radius:30px 30px 0 0}}
        .avatar{width:48px!important;min-width:48px;max-width:48px;height:48px!important;min-height:48px;max-height:48px;flex:0 0 48px;aspect-ratio:1/1;overflow:hidden;display:grid;place-items:center;flex-shrink:0}.avatar img{display:block;width:100%!important;height:100%!important;max-width:none;max-height:none;object-fit:cover}

        #page-loader {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: white;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }
        .loader-logo { width: 80px; height: auto; animation: pulse 2s infinite; }

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
        <img src="https://png.pngtree.com/png-clipart/20230124/original/pngtree-high-school-kids-holding-big-red-and-white-flags-png-image_8927815.png" class="loader-logo" alt="Logo">
    </div>

    <div id="app-lock-screen">
        <div class="lock-icon">&#128274;</div>
        <h3>Portal Terkunci</h3>
        <p class="px-4 opacity-75">Gunakan sidik jari atau wajah untuk membuka aplikasi.</p>
        <button onclick="startBiometricAuth()" class="btn btn-light mt-3 px-4 rounded-pill">Buka Sekarang</button>
    </div>

    <div class="mobile-shell">@yield('content')</div>
    <nav class="bottom-nav" aria-label="Navigasi utama">
        <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-icon">&#8962;</span>Beranda</a>
        <a class="{{ request()->routeIs('absensi.*') ? 'active' : '' }}" href="{{ route('absensi.index') }}"><span class="nav-icon">&#10003;</span>Absen</a>
        <a class="{{ request()->routeIs('spp.*') ? 'active' : '' }}" href="{{ route('spp.index') }}"><span class="nav-icon">&#8364;</span>SPP</a>
        <a class="{{ request()->routeIs('chat.*') ? 'active' : '' }}" href="{{ route('chat.index') }}"><span class="nav-icon">&#9993;</span>Chat</a>
        <a class="{{ request()->routeIs('tugas.*') ? 'active' : '' }}" href="{{ route('tugas.index') }}"><span class="nav-icon">&#9745;</span>Tugas</a>
        <a class="{{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.show') }}"><span class="nav-icon">&#9786;</span>Profil</a>
    </nav>

    <script>
        // Safety check for Capacitor
        const isNative = typeof Capacitor !== 'undefined';
        const { App, SplashScreen } = isNative ? Capacitor.Plugins : {};

        if (isNative && SplashScreen) {
            SplashScreen.hide().catch(() => {});
        }

        const isLoggedIn = {{ session()->has('user_id') ? 'true' : 'false' }};

        let isAuthenticating = false;

        function showLockScreen() {
            const biometricEnabled = localStorage.getItem('biometric_enabled') === 'true';
            const lockScreen = document.getElementById('app-lock-screen');
            if (isNative && isLoggedIn && biometricEnabled && !isAuthenticating && lockScreen.style.display !== 'flex') {
                lockScreen.style.display = 'flex';
                startBiometricAuth();
            }
        }

        async function startBiometricAuth() {
            if (!isNative || isAuthenticating) return;
            isAuthenticating = true;
            try {
                const NativeBiometric = Capacitor.Plugins.NativeBiometric;
                if (!NativeBiometric) {
                    isAuthenticating = false;
                    return;
                }
                const result = await NativeBiometric.isAvailable();
                if (result.isAvailable) {
                    await NativeBiometric.verifyIdentity({
                        reason: "Buka portal sekolah dengan sidik jari/wajah",
                        title: "Verifikasi Keamanan",
                        subtitle: "Masuk Kembali",
                        description: "Pastikan ini adalah Anda."
                    });
                    document.getElementById('app-lock-screen').style.display = 'none';
                }
            } catch (error) {
                console.error(error);
            } finally {
                isAuthenticating = false;
            }
        }

        if (isNative && App) {
            App.addListener('appStateChange', ({ isActive }) => { if (isActive) showLockScreen(); });
        }

        window.addEventListener('load', () => { document.getElementById('page-loader').style.display = 'none'; });

        window.addEventListener('online', () => document.getElementById('offline-indicator').style.display = 'none');
        window.addEventListener('offline', () => document.getElementById('offline-indicator').style.display = 'block');

        document.querySelectorAll('.bottom-nav a, .tap-card, .btn-primary').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href && !href.startsWith('#') && !href.startsWith('javascript')) {
                    document.getElementById('page-loader').style.display = 'flex';
                }
            });
        });

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
    </script>
</body>
</html>
