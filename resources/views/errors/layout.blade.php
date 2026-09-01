@php($exception = $exception ?? null)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <title>@yield('code','Error') - @yield('title','Terjadi Kesalahan') | {{ config('app.name','Portal Sekolah') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',system-ui,-apple-system,sans-serif;background:#f6f7fb;color:#0f172a;min-height:100vh;display:flex;flex-direction:column}
        .topbar{max-width:640px;margin:0 auto;width:100%;padding:18px 16px;display:flex;align-items:center;gap:12px}
        .topbar .logo{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#4f46e5,#2563eb);display:grid;place-items:center;color:#fff;font-weight:800;font-size:14px}
        .topbar .brand{font-weight:800;letter-spacing:-.02em}
        .wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:16px}
        .card{width:100%;max-width:560px;background:#fff;border:1px solid rgba(15,23,42,.07);border-radius:28px;box-shadow:0 20px 60px rgba(15,23,42,.08);overflow:hidden}
        .hero{padding:28px 24px 20px;text-align:center;background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 55%,#3730a3 100%);color:#fff;position:relative;overflow:hidden}
        .hero::after{content:'';position:absolute;top:-40px;right:-40px;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,rgba(99,102,241,.35),transparent 70%)}
        .hero > *{position:relative;z-index:1}
        .code{font-size:56px;font-weight:900;letter-spacing:-.04em;line-height:1}
        .code small{font-size:11px;letter-spacing:.14em;opacity:.6;display:block;margin-top:4px;font-weight:700;text-transform:uppercase}
        .badge{display:inline-flex;align-items:center;gap:6px;margin-top:14px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);padding:6px 12px;border-radius:999px;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
        .body{padding:22px 20px}
        .title{font-size:17px;font-weight:800;letter-spacing:-.02em}
        .msg{font-size:13.5px;color:#64748b;line-height:1.6;margin-top:6px}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px}
        .mini{background:#f8fafc;border:1px solid #eef2f7;border-radius:14px;padding:12px}
        .mini .k{font-size:10px;font-weight:800;color:#94a3b8;letter-spacing:.06em;text-transform:uppercase}
        .mini .v{font-size:13px;font-weight:800;margin-top:2px}
        .actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
        .btn{appearance:none;border:0;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;padding:12px 18px;border-radius:14px;font-size:13px;font-weight:800;transition:transform .16s,box-shadow .18s}
        .btn:active{transform:scale(.97)}
        .btn-primary{color:#fff;background:linear-gradient(135deg,#4f46e5,#2563eb);box-shadow:0 10px 24px rgba(79,70,229,.32)}
        .btn-ghost{background:#fff;border:1px solid rgba(15,23,42,.1);color:#0f172a}
        .details{margin-top:16px;background:#0f172a;color:#cbd5e1;border-radius:14px;padding:14px;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:11.5px;line-height:1.6;white-space:pre-wrap;word-break:break-word;display:none;max-height:220px;overflow:auto}
        .details.on{display:block}
        .hint{margin-top:14px;background:#fffbeb;border:1px solid #fef3c7;border-radius:14px;padding:12px 14px;color:#92400e;font-size:12px;line-height:1.5}
        .footer{max-width:640px;margin:0 auto;width:100%;padding:16px;text-align:center;font-size:11px;color:#94a3b8}
        @media(min-width:640px){.wrap{padding:24px}}
    </style>
</head>
<body>
    <div class="topbar">
        <div class="logo">PS</div>
        <div class="brand">{{ config('app.name','Portal Sekolah') }}</div>
        <div style="margin-left:auto;font-size:11px;font-weight:700;color:#94a3b8">{{ now()->translatedFormat('d M Y, H:i') }} WIB</div>
    </div>
    <div class="wrap">
        <div class="card">
            <div class="hero">
                <div class="code">@yield('code','500')<small>@yield('code_label','Kesalahan Server')</small></div>
                <div class="badge"><i class="bi @yield('icon','bi-exclamation-triangle-fill')"></i> @yield('badge','Error')</div>
            </div>
            <div class="body">
                <div class="title">@yield('title','Terjadi Kesalahan')</div>
                <div class="msg">@yield('message','Terjadi gangguan pada sistem. Silakan coba beberapa saat lagi.')</div>

                <div class="grid">
                    <div class="mini">
                        <div class="k">Waktu</div>
                        <div class="v">{{ now()->format('H:i:s') }}</div>
                    </div>
                    <div class="mini">
                        <div class="k">Kode</div>
                        <div class="v">@yield('code','-')</div>
                    </div>
                </div>

                @hasSection('hint')
                <div class="hint"><i class="bi bi-lightbulb me-1"></i> @yield('hint')</div>
                @endif

                <div class="actions">
                    @yield('action')
                    <button type="button" class="btn btn-ghost" onclick="copyErr()"><i class="bi bi-clipboard"></i> Salin Detail</button>
                    <button type="button" class="btn btn-ghost" onclick="toggleDetails()" id="btn-detail"><i class="bi bi-code-slash"></i> Detail</button>
                </div>

                <div class="details" id="err-details">@yield('debug_detail')URL: {{ request()->fullUrl() }}
Method: {{ request()->method() }}
IP: {{ request()->ip() }}
UA: {{ request()->userAgent() }}
@isset($exception)
Error: {{ $exception->getMessage() }}
File: {{ $exception->getFile() }}:{{ $exception->getLine() }}
@endisset
Trace ID: {{ substr(md5(request()->fullUrl().microtime()),0,8) }}</div>

                @if(config('app.debug') && isset($exception))
                <div class="details on" style="margin-top:10px;background:#fef2f2;color:#7f1d1d;border:1px solid #fecaca">{{ $exception->getMessage() }}</div>
                @endif
            </div>
        </div>
    </div>
    <div class="footer">Butuh bantuan? Hubungi Admin IT Sekolah &middot; {{ config('app.name') }} &copy; {{ date('Y') }}</div>
<script>
function toggleDetails(){var e=document.getElementById('err-details');e.classList.toggle('on');document.getElementById('btn-detail').innerHTML=e.classList.contains('on')?'<i class="bi bi-x-lg"></i> Tutup':'<i class="bi bi-code-slash"></i> Detail'}
function copyErr(){var t=document.getElementById('err-details').innerText;if(navigator.clipboard){navigator.clipboard.writeText(t).then(function(){alert('Detail disalin')})}else{var e=document.createElement('textarea');e.value=t;document.body.appendChild(e);e.select();document.execCommand('copy');e.remove();alert('Detail disalin')}}
</script>
</body>
</html>
