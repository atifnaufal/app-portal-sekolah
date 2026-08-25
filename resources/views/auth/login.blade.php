<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk | App Mahasiswa</title>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0d6efd">
<meta name="apple-mobile-web-app-capable" content="yes">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>
    body{min-height:100vh;background:linear-gradient(135deg,#0d6efd,#14213d)}
    .login-card{max-width:420px;border:0;border-radius:16px}
    .desktop-label{display:none}
    .desktop-welcome{display:none}
    @media(min-width:768px){.mobile-label,.mobile-welcome{display:none}.desktop-label,.desktop-welcome{display:inline}}

    #web-splash {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: white;
        display: none;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.5s;
    }
    .splash-logo {
        width: 140px;
        height: auto;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 0.8; }
        50% { transform: scale(1.1); opacity: 1; }
        100% { transform: scale(1); opacity: 0.8; }
    }
</style>
</head>
<body class="d-flex align-items-center">

<div id="web-splash">
    <img src="https://png.pngtree.com/png-clipart/20230124/original/pngtree-high-school-kids-holding-big-red-and-white-flags-png-image_8927815.png" class="splash-logo" alt="Logo Sekolah">
    <div class="mt-3 text-primary fw-bold">Memuat Portal...</div>
</div>

<div class="container">
    <div class="card login-card shadow-lg mx-auto">
        <div class="card-body p-4 p-md-5">
            <div class="text-primary fw-bold small mb-2">SISTEM AKADEMIK SEKOLAH</div>
            <h1 class="h3 fw-bold mb-1"><span class="mobile-welcome">Siap belajar hari ini?</span><span class="desktop-welcome">Selamat datang</span></h1>
            <p class="text-secondary mb-4"><span class="mobile-welcome">Masuk untuk belajar, tetap semangat, dan jangan lupa absen tepat waktu.</span><span class="desktop-welcome">Masuk ke portal sekolah.</span></p>

            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

            <form method="POST" action="{{ route('login.store') }}" id="loginForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label"><span class="mobile-label">Email</span><span class="desktop-label">ID admin</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">Masuk ke portal</button>
            </form>

            @if(\App\Models\Setting::getValue('registration_enabled', false))
                <a href="{{ route('register') }}" class="btn btn-outline-primary w-100 mt-3">Daftar akun guru / siswa</a>
            @endif

            <div id="download-app-area">
    <hr class="my-4">

    <div class="text-center mb-3">
        <div class="fw-bold">
            📱 Aplikasi Android
        </div>

        <div class="small text-muted">
            Versi 1.0.0 • Android
        </div>
    </div>

    <a
        href="https://github.com/atifnaufal/app-portal-sekolah/releases/latest/download/app-portal-sekolah.apk"
        class="btn btn-success w-100 py-2 fw-semibold"
        download
    >
        📥 Download APK
    </a>

    <p class="text-center small text-muted mt-2 mb-0">
        Download aplikasi Portal Sekolah untuk Android
    </p>
</div>


            
        </div>
    </div>
</div>

<script>
    let deferredPrompt;
    const installArea = document.getElementById('install-area');
    const btnInstall = document.getElementById('btn-install');

    window.addEventListener('beforeinstallprompt', (e) => {
        // Mencegah mini-infobar muncul di mobile
        e.preventDefault();
        // Simpan event agar bisa dipicu nanti
        deferredPrompt = e;
        // Tampilkan tombol instal
        installArea.style.display = 'block';
    });

    btnInstall.addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            console.log(`User response to the install prompt: ${outcome}`);
            deferredPrompt = null;
            installArea.style.display = 'none';
        }
    });

    window.addEventListener('appinstalled', () => {
        installArea.style.display = 'none';
        console.log('PWA was installed');
    });

    document.getElementById('loginForm').addEventListener('submit', function() {
        document.getElementById('web-splash').style.display = 'flex';
    });

    // Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js');
        });
    }
</script>
</body></html>
