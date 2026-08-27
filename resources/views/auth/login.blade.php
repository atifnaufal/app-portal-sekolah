<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | Portal Akademik Sekolah</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#246bfe">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #246bfe;
            --dark-blue: #14213d;
            --surface-color: #f8f9fa;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--dark-blue), var(--primary-color));
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            max-width: 440px;
            width: 100%;
            border: none;
            border-radius: 28px;
            box-shadow: 0 20px 45px rgba(0,0,0,0.25);
            background: #fff;
            position: relative;
        }

        .card-header-section {
            background: #f0f4ff;
            padding: 50px 30px 20px;
            text-align: center;
            border-radius: 28px 28px 0 0;
            position: relative;
        }

        .logo-school-wrapper {
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 100px;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-school-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: none;
        }

        .illustration-img {
            width: 100%;
            max-width: 200px;
            height: auto;
            margin: 0 auto;
            display: block;
        }

        .form-control {
            border-radius: 14px;
            padding: 12px 18px;
            border: 1px solid #e2e8f0;
            background: #fbfcfe;
            font-size: 14px;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(36, 107, 254, 0.12);
            border-color: var(--primary-color);
        }

        .btn-login {
            background: var(--primary-color);
            border: none;
            border-radius: 14px;
            padding: 14px;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 0.8px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: #1d59d4;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(36, 107, 254, 0.35);
        }

        .password-toggle {
            cursor: pointer;
            color: #94a3b8;
            padding: 0 10px;
        }

        .input-group-text {
            background: #fbfcfe;
            border-color: #e2e8f0;
            color: #64748b;
        }

        #web-splash {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: #fff;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .loader-ring {
            display: inline-block;
            width: 50px;
            height: 50px;
            border: 3px solid rgba(36, 107, 254, 0.1);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 0.8s ease-in-out infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .instruction-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 11px;
            color: #64748b;
            border: 1px dashed #cbd5e1;
            margin-top: 8px;
        }

        @media (max-width: 576px) {
            .login-card { border-radius: 24px; margin-top: 40px; }
            .card-header-section { padding: 50px 20px 20px; }
            .illustration-img { max-width: 180px; }
        }
    </style>
</head>
<body>

<div id="web-splash">
    <div class="loader-ring"></div>
    <div class="mt-3 fw-bold" style="color: var(--dark-blue)">MEMUAT PORTAL</div>
    <div class="small text-muted mt-1">Menyiapkan lingkungan akademik...</div>
</div>

<div class="container">
    <div class="card login-card mx-auto">
        <div class="logo-school-wrapper">
            <img src="{{ asset('logo_sekolah.png') }}" alt="Logo Sekolah" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2940/2940651.png'">
        </div>

        <div class="card-header-section">
            <!-- Undraw Illustration (Fallback to direct SVG URL if API fails) -->
            <img src="https://raw.githubusercontent.com/undraw-co/undraw-co.github.io/master/img/illustrations/undraw_back_to_school_re_8nrc.svg" alt="Illustration" class="illustration-img" onerror="this.src='https://illustrations.popsy.co/blue/studying.svg'">
            <div class="mt-2">
                <h1 class="h5 fw-bold mb-1" style="color: var(--dark-blue)">SELAMAT DATANG</h1>
                <p class="text-secondary small mb-0">Portal Akademik Mahasiswa & Guru</p>
            </div>
        </div>

        <div class="card-body p-4 p-md-4">
            @if(session('success'))<div class="alert alert-success small py-2 mb-3 border-0">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger small py-2 mb-3 border-0">{{ session('error') }}</div>@endif

            <form method="POST" action="{{ route('login.store') }}" id="loginForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">ALAMAT EMAIL</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0" style="border-radius: 14px 0 0 14px;">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control border-start-0" placeholder="nama@sekolah.com" required autofocus style="border-radius: 0 14px 14px 0;">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-dark">KATA SANDI</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0" style="border-radius: 14px 0 0 14px;">
                            <i class="bi bi-shield-lock"></i>
                        </span>
                        <input type="password" name="password" id="passwordInput" class="form-control border-start-0 border-end-0" placeholder="••••••••" required>
                        <span class="input-group-text border-start-0" style="border-radius: 0 14px 14px 0;">
                            <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                        </span>
                    </div>
                    <div class="instruction-box">
                        <div class="d-flex gap-2">
                            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                            <div class="fw-bold" style="color: #92400e;">Email dipastikan aktif dan harus terkonfirmasi dahulu baru bisa masuk sepenuhnya.</div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 text-white mb-3">
                    MASUK KE PORTAL &rarr;
                </button>
            </form>

            @if(\App\Models\Setting::getValue('registration_enabled', false))
                <div class="text-center mt-2">
                    <span class="small text-muted">Belum terdaftar?</span>
                    <a href="{{ route('register') }}" class="small fw-bold text-decoration-none ms-1">Minta Akses</a>
                </div>
            @endif

            <div id="download-app-area" class="mt-4 pt-3 border-top text-center">
                <a href="{{ asset('downloads/app-portal-sekolah.apk') }}" class="text-decoration-none d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-cloud-arrow-down text-primary"></i>
                    <span class="small fw-bold text-dark">Unduh Aplikasi Android</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Password Toggle Logic
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#passwordInput');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });

    // Loading Splash Logic
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        document.getElementById('web-splash').style.display = 'flex';
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>MENGHUBUNGKAN...';
    });

    // Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js');
        });
    }
</script>
</body>
</html>
