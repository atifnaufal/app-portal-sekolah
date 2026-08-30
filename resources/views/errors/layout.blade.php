<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terjadi Kesalahan</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#0f172a;color:#fff;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;text-align:center}
        .icon{width:80px;height:80px;background:rgba(255,255,255,0.08);border-radius:24px;display:grid;place-items:center;margin:0 auto 24px}
        .icon i{font-size:36px;color:#60a5fa}
        h1{font-size:20px;font-weight:700;margin-bottom:8px}
        p{font-size:14px;color:#94a3b8;line-height:1.6;max-width:340px;margin:0 auto}
        .btn{display:inline-block;margin-top:28px;padding:12px 28px;background:#3b82f6;color:#fff;text-decoration:none;border-radius:12px;font-size:14px;font-weight:600;transition:background .2s}
        .btn:hover{background:#2563eb}
        .btn:active{transform:scale(0.97)}
        .footer{margin-top:40px;font-size:11px;color:#475569}
    </style>
</head>
<body>
    <div class="icon">
        <i class="bi bi-exclamation-triangle"></i>
    </div>
    <h1>@yield('title', 'Terjadi Kesalahan')</h1>
    <p>@yield('message', 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi beberapa saat.')</p>
    @yield('action')
    <div class="footer">{{ config('app.name', 'Sekolah') }} &copy; {{ date('Y') }}</div>
</body>
</html>
