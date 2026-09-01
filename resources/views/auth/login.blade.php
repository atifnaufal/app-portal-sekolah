<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Masuk | Portal Sekolah Digital</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1e3a5f">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body {
            min-height: 100vh; margin: 0;
            background: linear-gradient(160deg, #0f172a 0%, #1e3a5f 40%, #1d4ed8 100%);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            display: flex; align-items: center; justify-content: center;
            padding: 20px; overflow-x: hidden;
        }

        .auth-container {
            max-width: 440px; width: 100%; position: relative;
        }

        /* Screen transitions */
        .auth-screen {
            display: none;
            animation: screenIn 0.4s ease both;
        }
        .auth-screen.active { display: block; }
        @keyframes screenIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Welcome Screen */
        .welcome-card {
            background: rgba(255,255,255,0.07);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 32px;
            padding: 52px 32px 40px;
            text-align: center;
            color: #fff;
        }
        .welcome-logo {
            width: 100px; height: 100px; border-radius: 30px;
            margin: 0 auto 24px;
            background: transparent;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            filter: drop-shadow(0 6px 16px rgba(0,0,0,0.25));
        }
        .welcome-logo img { width: 100%; height: 100%; object-fit: contain; }
        .welcome-title {
            font-size: 28px; font-weight: 800; letter-spacing: -0.02em;
            margin-bottom: 8px;
        }
        .welcome-sub {
            font-size: 14px; opacity: 0.6; line-height: 1.5;
            margin-bottom: 36px;
        }
        .welcome-features {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
            margin-bottom: 32px; text-align: center;
        }
        .welcome-feature {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px; padding: 12px 6px;
            transition: all 0.2s;
        }
        .welcome-feature:active { background: rgba(255,255,255,0.1); transform: scale(0.96); }
        .welcome-feature-icon {
            width: 28px; height: 28px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; margin: 0 auto 8px;
        }
        .welcome-feature-title { font-size: 9.5px; font-weight: 800; letter-spacing: 0.01em; color: #fff; line-height: 1.2; }
        .welcome-feature-desc { font-size: 8px; opacity: 0.4; margin-top: 3px; line-height: 1.1; }

        .btn-welcome {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 14px 28px; border-radius: 16px; font-weight: 700;
            font-size: 14px; border: none; cursor: pointer;
            transition: all 0.2s; text-decoration: none; width: 100%;
        }
        .btn-welcome:active { transform: scale(0.97); }
        .btn-welcome-primary {
            background: #fff; color: #1e3a5f;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        .btn-welcome-secondary {
            background: rgba(255,255,255,0.1); color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* Login Screen */
        .login-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #f0f4ff, #e8f0fe);
            padding: 36px 32px 28px;
            text-align: center;
            position: relative;
        }
        .login-header-logo {
            width: 64px; height: 64px; border-radius: 20px;
            margin: 0 auto 14px; overflow: hidden;
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 14px rgba(15,23,42,0.10);
        }
        .login-header-logo img { width: 100%; height: 100%; object-fit: contain; }
        .login-header h1 { font-size: 18px; font-weight: 800; color: #1e293b; margin: 0; }
        .login-header p { font-size: 12px; color: #64748b; margin: 4px 0 0; }

        .login-body { padding: 28px 32px 32px; }

        .form-control {
            border-radius: 14px; padding: 12px 16px;
            border: 1.5px solid #e2e8f0; background: #f8fafc;
            font-size: 14px; transition: all 0.2s;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(36,107,254,0.1);
            border-color: #246bfe; background: #fff;
        }
        .form-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px; }

        .btn-login-submit {
            width: 100%; padding: 14px; border-radius: 14px;
            background: linear-gradient(135deg, #246bfe, #1d59d4);
            color: #fff; font-weight: 700; font-size: 14px;
            border: none; cursor: pointer; transition: all 0.2s;
            box-shadow: 0 6px 20px rgba(36,107,254,0.25);
        }
        .btn-login-submit:active { transform: scale(0.97); }
        .btn-login-submit:disabled { opacity: 0.7; }

        .back-btn {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.7);
            text-decoration: none; margin-bottom: 16px; cursor: pointer;
            background: none; border: none; padding: 0;
        }
        .back-btn:hover { color: #fff; }

        .password-toggle { cursor: pointer; color: #94a3b8; }

        .info-box {
            background: #f8fafc; border: 1px dashed #e2e8f0;
            border-radius: 12px; padding: 10px 12px;
            font-size: 11px; color: #64748b; margin-top: 12px;
        }

        /* Loading overlay */
        #loading-overlay {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(15,23,42,0.9); backdrop-filter: blur(8px);
            display: none; flex-direction: column;
            align-items: center; justify-content: center; color: #fff;
        }
        #loading-overlay.show { display: flex; }
        .loader-ring {
            width: 44px; height: 44px; border: 3px solid rgba(255,255,255,0.1);
            border-top-color: #60a5fa; border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 480px) {
            body { padding: 12px; align-items: flex-start; padding-top: 40px; }
            .welcome-card { padding: 32px 16px 24px; border-radius: 28px; }
            .welcome-features { grid-template-columns: repeat(3, 1fr); gap: 6px; }
            .login-body { padding: 20px; }
        }
    </style>
</head>
<body>

<div id="loading-overlay">
    <div class="loader-ring"></div>
    <div style="font-weight:700;margin-top:16px;font-size:14px;">Menghubungkan...</div>
    <div style="font-size:12px;opacity:0.5;margin-top:4px;">Menyiapkan App portal akademik</div>
</div>

<div class="auth-container">
    {{-- ========== SCREEN 1: WELCOME ========== --}}
    <div class="auth-screen active" id="welcomeScreen">
        <div class="welcome-card">
            <div class="welcome-logo">
                <img src="{{ asset('logo_sekolah.png') }}" alt="Logo" onerror="this.style.display='none'">
            </div>
            <div class="welcome-title">Portal Sekolah Digital</div>
            <div class="welcome-sub">Portal Akademik Mahasiswa & Guru</div>

            <div class="welcome-features">
                <div class="welcome-feature">
                    <div class="welcome-feature-icon" style="background:rgba(59,130,246,0.2);color:#60a5fa;">
                        <i class="bi bi-journal-check"></i>
                    </div>
                    <div class="welcome-feature-title">Tugas</div>
                    <div class="welcome-feature-desc">Kumpul Tugas</div>
                </div>
                <div class="welcome-feature">
                    <div class="welcome-feature-icon" style="background:rgba(16,185,129,0.2);color:#34d399;">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="welcome-feature-title">Absensi</div>
                    <div class="welcome-feature-desc">Catat Hadir</div>
                </div>
                <div class="welcome-feature">
                    <div class="welcome-feature-icon" style="background:rgba(251,191,36,0.2);color:#fbbf24;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="welcome-feature-title">SPP Online</div>
                    <div class="welcome-feature-desc">Cek Tagihan Online</div>
                </div>
                <div class="welcome-feature">
                    <div class="welcome-feature-icon" style="background:rgba(139,92,246,0.2);color:#a78bfa;">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="welcome-feature-title">Chat</div>
                    <div class="welcome-feature-desc">Online Chat</div>
                </div>
                 <div class="welcome-feature">
                    <div class="welcome-feature-icon" style="background:rgba(14,165,233,0.2);color:#38bdf8;">
                      <i class="bi bi-file-earmark-richtext"></i>
                    </div>
                 <div class="welcome-feature-title">Perpus Digital</div>
                    <div class="welcome-feature-desc">Baca Online Digital</div>
                </div>
                <div class="welcome-feature">
                    <div class="welcome-feature-icon" style="background:rgba(244,114,182,0.2);color:#f472b6;">
                        <i class="bi bi-flag"></i>
                    </div>
                    <div class="welcome-feature-title">Eskul</div>
                    <div class="welcome-feature-desc">Minat Bakat</div>
                </div>
                <div class="welcome-feature">
                    <div class="welcome-feature-icon" style="background:rgba(124,58,237,0.2);color:#c084fc;">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div class="welcome-feature-title">E-Learning</div>
                    <div class="welcome-feature-desc">Materi & Tugas</div>
                </div>

                <div class="welcome-feature">
                    <div class="welcome-feature-icon" style="background:rgba(16,185,129,0.2);color:#34d399;">
                        <i class="bi bi-bar-chart-line"></i>
                    </div>
                    <div class="welcome-feature-title">Nilai</div>
                    <div class="welcome-feature-desc">Rapor Online</div>
                </div>
                <div class="welcome-feature" style="background:linear-gradient(135deg,rgba(99,102,241,.18),rgba(79,70,229,.18));border-color:rgba(99,102,241,.3)">
                    <div class="welcome-feature-icon" style="background:rgba(99,102,241,.25);color:#a5b4fc;">
                        <i class="bi bi-globe2"></i>
                    </div>
                    <div class="welcome-feature-title" style="background:linear-gradient(135deg,rgba(99,102,241,.18),rgba(79,70,229,.18));border-color:rgba(99,102,241,.3)">Global Portal</div>
                    <div class="welcome-feature-desc" style="color:rgba(199,210,254,.7)">Sosial media Sekolah</div>
                </div>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="button" class="btn-welcome btn-welcome-primary" onclick="showScreen('loginScreen')" style="flex:1;">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
                @if(\App\Models\Setting::getValue('registration_guru_enabled', false) || \App\Models\Setting::getValue('registration_siswa_enabled', false))
                    <a href="{{ route('register') }}" class="btn-welcome btn-welcome-secondary" style="flex:1;">
                        <i class="bi bi-person-plus"></i>Pendaftaran
                    </a>
                @endif
            </div>

            <div style="margin-top:20px;">
                <a href="{{ route('download.apk') }}" style="font-size:11px;color:rgba(255,255,255,0.4);text-decoration:none;">
                    <i class="bi bi-cloud-arrow-down"></i> Unduh Aplikasi Android
                </a>
            </div>
        </div>
    </div>

    {{-- ========== SCREEN 2: LOGIN FORM ========== --}}
    <div class="auth-screen" id="loginScreen">
        <button type="button" class="back-btn" onclick="showScreen('welcomeScreen')">
            <i class="bi bi-chevron-left"></i> Kembali
        </button>

        <div class="login-card">
            <div class="login-header">
                <div class="login-header-logo">
                    <img src="{{ asset('logo_sekolah.png') }}" alt="Logo" onerror="this.style.display='none'">
                </div>
                <h1>Portal Sekolah Digital</h1>
                <p>Portal Akademik Mahasiswa & Guru</p>
            </div>

            <div class="login-body">
                @if(session('success'))<div class="alert alert-success small py-2 mb-3 border-0 rounded-3">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger small py-2 mb-3 border-0 rounded-3">{{ session('error') }}</div>@endif

                <form method="POST" action="{{ route('login.store') }}" id="loginForm">
                    @csrf
                    <div style="margin-bottom:14px;">
                        <label class="form-label">Alamat Email</label>
                        <div style="position:relative;">
                            <i class="bi bi-person" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:15px;"></i>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" style="padding-left:40px;" placeholder="nama@gmail.com" required autofocus>
                        </div>
                    </div>

                    <div style="margin-bottom:8px;">
                        <label class="form-label">Kata Sandi</label>
                        <div style="position:relative;">
                            <i class="bi bi-shield-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:15px;"></i>
                            <input type="password" name="password" id="passwordInput" class="form-control" style="padding-left:40px;padding-right:40px;" placeholder="Masukkan password" required>
                            <i class="bi bi-eye password-toggle" id="togglePassword" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:15px;"></i>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:18px;">
                        <label style="display:flex;align-items:center;gap:7px;font-size:12px;color:#64748b;cursor:pointer;user-select:none;">
                            <input type="checkbox" name="remember" value="1" checked
                                   style="width:16px;height:16px;accent-color:#246bfe;cursor:pointer;">
                            <span><strong>Tetap masuk</strong> di perangkat ini</span>
                        </label>
                        <a href="{{ route('password.request') }}" style="font-size:11px;font-weight:600;color:#246bfe;text-decoration:none;">Lupa Password?</a>
                    </div>
                    <div style="text-align:right;margin:-8px 0 16px;">
                        <a href="{{ route('email.request') }}" style="font-size:11px;font-weight:600;color:#94a3b8;text-decoration:none;">Lupa Email?</a>
                    </div>

                    <button type="submit" class="btn-login-submit" id="submitBtn">
                        Masuk ke Portal
                    </button>
                </form>

                <div class="info-box" style="background:#fffbeb;border-color:#fde68a">
                    <div style="display:flex;gap:8px;align-items:start;">
                        <i class="bi bi-building" style="color:#d97706;flex-shrink:0;margin-top:1px;"></i>
                        <div>
                            <div style="font-weight:700;color:#92400e">Pendaftaran butuh ID Sekolah aktif</div>
                            <div style="margin-top:2px;color:#92400e">Publik bisa daftar <b>hanya jika sekolah sudah diberi ID & diaktifkan Admin Pusat</b>. Jika ID dinonaktifkan/dihapus, pendaftaran tertutup. Hubungi Admin Pusat.</div>
                        </div>
                    </div>
                </div>
                <div class="info-box">
                    <div style="display:flex;gap:8px;align-items:start;">
                        <i class="bi bi-info-circle" style="color:#3b82f6;flex-shrink:0;margin-top:1px;"></i>
                        <div>
                            <div style="margin-top:2px;color:#94a3b8;">Butuh bantuan akses? Hubungi Admin IT sekolah / adminpusat@pusat.com</div>
                        </div>
                    </div>
                </div>

                @if(\App\Models\Setting::getValue('registration_guru_enabled', false) || \App\Models\Setting::getValue('registration_siswa_enabled', false))
                    <div style="text-align:center;margin-top:16px;padding-top:16px;border-top:1px solid #f1f5f9;">
                        <span style="font-size:12px;color:#94a3b8;">Belum punya akun?</span>
                        <a href="{{ route('register') }}" style="font-size:12px;font-weight:700;color:#246bfe;text-decoration:none;margin-left:4px;">Daftar Sekarang</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function showScreen(id) {
    document.querySelectorAll('.auth-screen').forEach(function(s) { s.classList.remove('active'); });
    document.getElementById(id).classList.add('active');
}

// Password toggle
document.getElementById('togglePassword').addEventListener('click', function() {
    var input = document.getElementById('passwordInput');
    if (input.type === 'password') {
        input.type = 'text';
        this.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        this.classList.replace('bi-eye-slash', 'bi-eye');
    }
});

// Loading on submit
document.getElementById('loginForm').addEventListener('submit', function() {
    document.getElementById('loading-overlay').classList.add('show');
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menghubungkan...';
});

// Auto-show login if there are errors or specific session messages
@if((isset($errors) && $errors->any()) || session('error') || session('success'))
    showScreen('loginScreen');
@endif

// Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js');
    });
}
</script>
</body>
</html>
