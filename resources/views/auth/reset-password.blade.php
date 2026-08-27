<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password | Portal Sekolah Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #0f172a, #1e3a5f); display: flex; align-items: center; justify-content: center; padding: 20px; font-family: system-ui, -apple-system, sans-serif; }
        .recovery-card { max-width: 440px; width: 100%; border: none; border-radius: 28px; background: #fff; box-shadow: 0 20px 45px rgba(0,0,0,0.25); overflow: hidden; }
        .form-control { border-radius: 14px; padding: 12px 18px; border: 1.5px solid #e2e8f0; background: #fbfcfe; }
        .form-control:focus { box-shadow: 0 0 0 3px rgba(36,107,254,0.1); border-color: #246bfe; }
        .btn-recovery { background: linear-gradient(135deg, #246bfe, #1d59d4); border: none; border-radius: 14px; padding: 14px; font-weight: 700; color: #fff; width: 100%; }
        .logo-tile { width: 64px; height: 64px; border-radius: 20px; margin: 0 auto 14px; overflow: hidden; background: linear-gradient(135deg, #0f172a, #1e3a5f); display: flex; align-items: center; justify-content: center; }
        .logo-tile img { width: 100%; height: 100%; object-fit: contain; mix-blend-mode: screen; }
    </style>
</head>
<body>
<div class="recovery-card">
    <div class="p-4 p-md-5">
        <div class="logo-tile">
            <img src="{{ asset('logo_sekolah.png') }}" alt="Logo" onerror="this.style.display='none'">
        </div>
        <h1 class="h4 fw-bold text-center mb-2">Atur Ulang Password</h1>
        <p class="text-secondary small text-center mb-4">Buat password baru untuk akun Anda.</p>

        @if($errors->any())
            <div class="alert alert-danger border-0 rounded-4 small mb-4">
                <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label class="form-label fw-bold small">ALAMAT EMAIL</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small">PASSWORD BARU</label>
                <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter" minlength="8" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold small">KONFIRMASI PASSWORD</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru" minlength="8" required>
            </div>

            <button type="submit" class="btn btn-recovery mb-3">SIMPAN PASSWORD BARU &rarr;</button>
        </form>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none small fw-bold"><i class="bi bi-arrow-left"></i> Kembali ke Login</a>
        </div>
    </div>
</div>
</body>
</html>
