<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password | Portal Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #14213d, #246bfe); display: flex; align-items: center; justify-content: center; padding: 20px; }
        .recovery-card { max-width: 440px; width: 100%; border: none; border-radius: 28px; background: #fff; box-shadow: 0 20px 45px rgba(0,0,0,0.25); overflow: hidden; }
        .form-control { border-radius: 14px; padding: 12px 18px; border: 1px solid #e2e8f0; background: #fbfcfe; }
        .btn-recovery { background: #246bfe; border: none; border-radius: 14px; padding: 14px; font-weight: 700; color: #fff; transition: all 0.3s; }
        .btn-recovery:hover { background: #1d59d4; transform: translateY(-2px); }
        .instruction-box { background: #f8fafc; border-radius: 15px; padding: 15px; border: 1px dashed #cbd5e1; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="recovery-card">
    <div class="p-4 p-md-5">
        <a href="{{ route('login') }}" class="text-decoration-none small fw-bold mb-4 d-inline-block">
            <i class="bi bi-arrow-left"></i> Kembali ke Login
        </a>
        <h1 class="h4 fw-bold mb-2">Lupa Password?</h1>
        <p class="text-secondary small mb-4">Masukkan email Anda untuk menerima link reset password.</p>

        <div class="instruction-box">
            <div class="d-flex gap-2">
                <i class="bi bi-info-circle-fill text-primary"></i>
                <div class="small">Pastikan email yang Anda masukkan adalah email yang terdaftar dan aktif.</div>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success border-0 rounded-4 small mb-4">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-4">
                <label class="form-label fw-bold small">ALAMAT EMAIL</label>
                <input type="email" name="email" class="form-control" placeholder="nama@email.com" required autofocus>
            </div>
            <button type="submit" class="btn btn-recovery w-100 mb-3">KIRIM LINK RESET &rarr;</button>
        </form>

        <div class="text-center mt-3">
            <p class="x-small text-muted mb-0">Lupa email Anda? <a href="{{ route('email.request') }}" class="fw-bold text-decoration-none">Cari Email</a></p>
        </div>
    </div>
</div>
</body>
</html>
