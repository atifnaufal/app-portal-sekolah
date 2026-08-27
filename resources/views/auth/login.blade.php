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
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            background: #fff;
        }

        .card-header-img {
            background: #f0f4ff;
            padding: 30px;
            text-align: center;
        }

        .card-header-img img {
            width: 180px;
            height: auto;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #dee2e6;
            background: #fbfcfe;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(36, 107, 254, 0.1);
            border-color: var(--primary-color);
        }

        .btn-login {
            background: var(--primary-color);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: #1d59d4;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(36, 107, 254, 0.3);
        }

        .password-toggle {
            cursor: pointer;
            color: #adb5bd;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        #web-splash {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: white;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loader {
            width: 48px;
            height: 48px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .instruction-text {
            font-size: 11px;
            color: #6c757d;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        @media (max-width: 576px) {
            .login-card { border-radius: 20px; }
            .card-header-img { padding: 20px; }
            .card-header-img img { width: 140px; }
        }
    </style>
</head>
<body>

<div id="web-splash">
    <div class="loader"></div>
    <div class="mt-3 text-primary fw-bold">Memasuki Portal Sekolah...</div>
    <div class="small text-muted mt-1">Mohon tunggu sebentar</div>
</div>

<div class="container">
    <div class="card login-card mx-auto">
        <div class="card-header-img">
            <!-- Undraw Illustration for Education -->
            <img src="https://undraw.co/api/illustrations/undraw_back_to_school_re_8nrc.svg" alt="School Illustration">
        </div>
        <div class="card-body p-4 p-md-4">
            <div class="mb-4 text-center">
                <h1 class="h4 fw-bold mb-1">Portal Akademik</h1>
                <p class="text-secondary small">Masuk untuk mengelola tugas dan absensi</p>
            </div>

            @if(session('success'))<div class="alert alert-success small py-2">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger small py-2">{{ session('error') }}</div>@endif

            <form method="POST" action="{{ route('login.store') }}" id="loginForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;">
                            <i class="bi bi-envelope text-muted"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control border-start-0" placeholder="admin@sekolah.com" required autofocus style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold small">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;">
                            <i class="bi bi-lock text-muted"></i>
                        </span>
                        <input type="password" name="password" id="passwordInput" class="form-control border-start-0 border-end-0" placeholder="Minimal 8 karakter" required>
                        <span class="input-group-text bg-white border-start-0" style="border-radius: 0 12px 12px 0;">
                            <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                        </span>
                    </div>
                    <div class="instruction-text">
                        <i class="bi bi-info-circle"></i> Gunakan password yang diberikan oleh admin sekolah.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 text-white mb-3">
                    MASUK SEKARANG
                </button>
            </form>

            @if(\App\Models\Setting::getValue('registration_enabled', false))
                <div class="text-center mt-3">
                    <span class="small text-muted">Belum punya akun?</span>
                    <a href="{{ route('register') }}" class="small fw-bold text-decoration-none ms-1">Daftar di sini</a>
                </div>
            @endif

            <div id="download-app-area" class="mt-4 pt-3 border-top">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                    <i class="bi bi-android2 text-success h5 mb-0"></i>
                    <span class="small fw-bold">Tersedia Aplikasi Android</span>
                </div>
                <a href="{{ asset('downloads/app-portal-sekolah.apk') }}" class="btn btn-light w-100 py-2 small fw-semibold border" download>
                    <i class="bi bi-download me-2"></i> Unduh APK Terbaru
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
        // Show splash screen
        document.getElementById('web-splash').style.display = 'flex';

        // Disable button to prevent double submit
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menghubungkan...';
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
