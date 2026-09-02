<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <title>Offline — {{ config('app.name','Portal Sekolah') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" fetchpriority="low">
    <style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Inter',system-ui,-apple-system,sans-serif;background:#f6f7fb;color:#0f172a;min-height:100vh;display:flex;flex-direction:column}.topbar{max-width:640px;margin:0 auto;width:100%;padding:18px 16px;display:flex;align-items:center;gap:12px}.logo{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#4f46e5,#2563eb);display:grid;place-items:center;color:#fff;font-weight:800;font-size:14px}.brand{font-weight:800;letter-spacing:-.02em}.time{margin-left:auto;font-size:11px;font-weight:700;color:#94a3b8}.wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:16px}.card{width:100%;max-width:520px;background:#fff;border:1px solid rgba(15,23,42,.07);border-radius:28px;box-shadow:0 20px 60px rgba(15,23,42,.08);overflow:hidden}.hero{padding:32px 24px 24px;text-align:center;background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 55%,#334155 100%);color:#fff;position:relative;overflow:hidden}.hero::after{content:'';position:absolute;top:-40px;right:-40px;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,rgba(239,68,68,.28),transparent 70%)}.hero>*{position:relative;z-index:1}.ico{width:84px;height:84px;border-radius:22px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);display:grid;place-items:center;margin:0 auto 16px}.ico i{font-size:38px;color:#f87171}h1{font-size:22px;font-weight:900;letter-spacing:-.02em}.sub{font-size:12px;letter-spacing:.12em;opacity:.6;margin-top:6px;font-weight:700;text-transform:uppercase}.badge{display:inline-flex;align-items:center;gap:6px;margin-top:14px;background:rgba(248,113,113,.18);border:1px solid rgba(248,113,113,.3);padding:6px 12px;border-radius:999px;font-size:11px;font-weight:800}.body{padding:20px}.title{font-size:16px;font-weight:800}.msg{font-size:13px;color:#64748b;line-height:1.6;margin-top:6px}.list{margin-top:14px;display:grid;gap:8px}.item{display:flex;gap:10px;align-items:flex-start;font-size:12.5px;color:#334155;background:#f8fafc;border:1px solid #eef2f7;border-radius:14px;padding:11px 12px}.item i{color:#3b82f6;margin-top:1px}.actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}.btn{appearance:none;border:0;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;padding:12px 18px;border-radius:14px;font-size:13px;font-weight:800;transition:transform .16s,box-shadow .18s}.btn:active{transform:scale(.97)}.btn-primary{color:#fff;background:linear-gradient(135deg,#4f46e5,#2563eb);box-shadow:0 10px 24px rgba(79,70,229,.32)}.btn-ghost{background:#fff;border:1px solid rgba(15,23,42,.1);color:#0f172a}.status{margin-top:14px;display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;color:#94a3b8}.dot{width:8px;height:8px;border-radius:50%;background:#ef4444}.dot.online{background:#22c55e}.footer{max-width:640px;margin:0 auto;width:100%;padding:16px;text-align:center;font-size:11px;color:#94a3b8}@media(min-width:640px){.wrap{padding:24px}}</style>
</head>
<body>
<div class="topbar"><div class="logo">PS</div><div class="brand">{{ config('app.name','Portal Sekolah') }}</div><div class="time" id="clock"></div></div>
<div class="wrap">
<div class="card">
<div class="hero">
<div class="ico"><i class="bi bi-wifi-off"></i></div>
<h1>Tidak Ada Koneksi</h1>
<div class="sub">Offline — Periksa Internet Anda</div>
<div class="badge"><i class="bi bi-exclamation-triangle"></i> net::ERR_FAILED / Tidak Tersedia</div>
</div>
<div class="body">
<div class="title">Halaman tidak dapat dimuat</div>
<div class="msg">Perangkat Anda sedang offline atau server tidak terjangkau. Data yang sudah dibuka mungkin masih tersedia dari cache.</div>
<div class="list">
<div class="item"><i class="bi bi-check-circle-fill"></i> Pastikan <b>Data seluler / Wi-Fi</b> aktif dan sinyal kuat.</div>
<div class="item"><i class="bi bi-airplane"></i> Matikan <b>Mode Pesawat</b> jika aktif.</div>
<div class="item"><i class="bi bi-arrow-clockwise"></i> Tarik untuk refresh atau tekan <b>Coba Lagi</b>.</div>
</div>
<div class="actions">
<button class="btn btn-primary" data-action="retry"><i class="bi bi-arrow-clockwise"></i> Coba Lagi</button>
<a class="btn btn-ghost" href="{{ url('/') }}"><i class="bi bi-house"></i> Beranda</a>
<button class="btn btn-ghost" data-action="diag"><i class="bi bi-clipboard"></i> Salin Diagnosa</button>
</div>
<div class="status"><span class="dot" id="dot"></span><span id="netText">Offline</span><span style="margin-left:auto" id="diag"></span></div>
</div>
</div>
</div>
<div class="footer">Jika terus offline, tutup & buka kembali aplikasi. Hubungi Admin IT bila perlu.</div>
</body>
@push('offline-scripts')
<script>document.addEventListener('DOMContentLoaded',function(){var dot=document.getElementById('dot'),netText=document.getElementById('netText'),diag=document.getElementById('diag');function updNet(){var on=navigator.onLine;if(dot)dot.className='dot'+(on?' online':'');if(netText)netText.textContent=on?'Online — siap muat ulang':'Offline'}updNet();window.addEventListener('online',updNet);window.addEventListener('offline',updNet);if(diag)diag.textContent='URL:'+location.pathname.slice(0,28);function updClock(){var e=document.getElementById('clock');if(e)e.textContent=new Date().toLocaleString('id-ID',{hour:'2-digit',minute:'2-digit'})}setInterval(updClock,60000);updClock();document.querySelector('[data-action="retry"]').addEventListener('click',function(){if(navigator.onLine)location.reload();else alert('Masih offline — periksa koneksi')});document.querySelector('[data-action="diag"]').addEventListener('click',function(){var t='Offline\nURL:'+location.href+'\nUA:'+navigator.userAgent+'\nTime:'+new Date().toISOString();if(navigator.clipboard)navigator.clipboard.writeText(t).then(function(){alert('Diagnosa disalin')}).catch(function(){fC(t)});else fC(t)});function fC(t){var e=document.createElement('textarea');e.value=t;document.body.appendChild(e);e.select();document.execCommand('copy');e.remove();alert('Diagnosa disalin')}});</script>
@endpush
</html>