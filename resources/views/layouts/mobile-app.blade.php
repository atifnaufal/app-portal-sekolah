<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ?? 'Portal Sekolah' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{--ink:#14213d;--muted:#738097;--blue:#246bfe;--surface:#f5f7fb;--danger:#d94b61}
        *{box-sizing:border-box} body{margin:0;background:var(--surface);color:var(--ink);font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;padding-bottom:92px}
        .mobile-shell{max-width:680px;margin:auto}.mobile-hero{background:linear-gradient(140deg,#14213d,#246bfe);color:#fff;border-radius:0 0 30px 30px;padding:24px 20px 30px}.eyebrow{font-size:11px;letter-spacing:.13em;opacity:.75;font-weight:800}.hero-title{font-size:26px;font-weight:800;letter-spacing:-.02em}.avatar{width:48px;height:48px;border-radius:17px;background:#ffffff2b;display:grid;place-items:center;font-weight:800;font-size:18px}.class-pill{display:inline-block;background:#ffffff20;border:1px solid #ffffff42;border-radius:99px;padding:6px 12px;font-size:12px}.mobile-content{padding:20px}.section-title{font-size:17px;font-weight:800}.mobile-card{border:0;border-radius:20px;box-shadow:0 8px 24px #14213d12;animation:rise .45s both}.mobile-card:nth-child(2){animation-delay:.08s}.mobile-card:nth-child(3){animation-delay:.16s}.tap-card{transition:transform .2s,box-shadow .2s}.tap-card:hover,.tap-card:active{transform:translateY(-3px);box-shadow:0 13px 28px #14213d1c}.icon-box{width:43px;height:43px;border-radius:14px;display:grid;place-items:center;font-size:20px;background:#e8efff;color:var(--blue)}.bottom-nav{position:fixed;z-index:5;bottom:0;left:0;right:0;background:#fffffff2;border-top:1px solid #e8ecf3;backdrop-filter:blur(14px);padding:10px max(18px,calc((100% - 680px)/2 + 18px)) calc(10px + env(safe-area-inset-bottom));display:flex;justify-content:space-around}.bottom-nav a{color:var(--muted);text-align:center;text-decoration:none;font-size:11px;font-weight:700;min-width:58px;transition:color .2s,transform .2s}.bottom-nav a:hover,.bottom-nav a.active{color:var(--blue)}.bottom-nav a:active{transform:scale(.92)}.nav-icon{display:block;font-size:21px;line-height:23px;margin-bottom:2px}.bottom-nav a.active .nav-icon{transform:translateY(-2px)}.profile-action{border-radius:14px}.logout-panel{display:flex;align-items:center;justify-content:space-between;gap:14px;border:1px solid #f1d8dd;background:#fff5f6;border-radius:20px;padding:16px}.logout-panel .btn{border-radius:12px;white-space:nowrap}.stagger>*{animation:rise .45s both}.stagger>*:nth-child(2){animation-delay:.08s}.stagger>*:nth-child(3){animation-delay:.16s}@keyframes rise{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}}@media(min-width:681px){body{padding-top:20px}.mobile-hero{border-radius:30px 30px 0 0}}
        .avatar{width:48px!important;min-width:48px;max-width:48px;height:48px!important;min-height:48px;max-height:48px;flex:0 0 48px;aspect-ratio:1/1;overflow:hidden;display:grid;place-items:center;flex-shrink:0}.avatar img{display:block;width:100%!important;height:100%!important;max-width:none;max-height:none;object-fit:cover}

        #page-loader {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.9);
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

    <script src="https://unpkg.com/@capacitor/core@latest/dist/capacitor.js"></script>
    <script>
        const { App } = window.Capacitor ? window.Capacitor.Plugins : {};

        // Cek apakah user sedang login (dari session Laravel ke JS)
        const isLoggedIn = {{ session()->has('user_id') ? 'true' : 'false' }};

        function showLockScreen() {
            if (window.Capacitor && isLoggedIn) {
                document.getElementById('app-lock-screen').style.display = 'flex';
                startBiometricAuth();
            }
        }

        async function startBiometricAuth() {
            if (!window.Capacitor) return;

            try {
                const NativeBiometric = window.Capacitor.Plugins.NativeBiometric;
                if (!NativeBiometric) {
                    console.warn("Plugin Biometric tidak terdeteksi.");
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
                console.error("Biometric failed", error);
                alert("Gagal memverifikasi. Silakan coba lagi.");
            }
        }

        if (window.Capacitor) {
            // Deteksi saat aplikasi dibuka kembali dari background
            App.addListener('appStateChange', ({ isActive }) => {
                if (isActive) {
                    showLockScreen();
                }
            });

            // Simpan status login di mobile untuk session persist
            if (isLoggedIn) {
                localStorage.setItem('portal_session_active', 'true');
            } else {
                localStorage.removeItem('portal_session_active');
            }
        }

        document.querySelectorAll('.bottom-nav a, .tap-card, .btn-primary').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.tagName === 'A' && this.getAttribute('href') !== '#' && !this.getAttribute('href').startsWith('javascript')) {
                    document.getElementById('page-loader').style.display = 'flex';
                }
            });
        });

        window.addEventListener('pageshow', function() {
            document.getElementById('page-loader').style.display = 'none';
        });
    </script>
</body>
</html>
