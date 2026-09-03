<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#0f172a">
<title>{{ config('app.name','Portal Sekolah') }} — Platform Digital Antar Sekolah</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',system-ui,-apple-system,sans-serif;background:#f6f7fb;color:#0f172a;overflow-x:hidden}
.hero{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 45%,#4f46e5 100%);color:#fff;position:relative;overflow:hidden}
.hero::after{content:'';position:absolute;top:-80px;right:-60px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(99,102,241,.35),transparent 70%)}
.nav{max-width:1120px;margin:0 auto;padding:16px 20px;display:flex;align-items:center;gap:12px;position:relative;z-index:1}
.logo{width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#4f46e5,#2563eb);display:grid;place-items:center;color:#fff;font-weight:900}
.nav-links{margin-left:auto;display:flex;gap:10px}
.btn{appearance:none;border:0;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;padding:11px 18px;border-radius:12px;font-weight:800;font-size:13px}
.btn-ghost{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.18);backdrop-filter:blur(8px)}
.btn-primary{background:#fff;color:#4f46e5;box-shadow:0 10px 24px rgba(15,23,42,.18)}
.hero-main{max-width:1120px;margin:0 auto;padding:36px 20px 44px;display:grid;grid-template-columns:1.1fr .9fr;gap:28px;align-items:center;position:relative;z-index:1}
@media(max-width:900px){.hero-main{grid-template-columns:1fr}.nav-links .btn-primary{display:none}}
.eyebrow{font-size:11px;letter-spacing:.14em;opacity:.7;font-weight:800;text-transform:uppercase}
.h1{font-size:36px;font-weight:900;letter-spacing:-.03em;line-height:1.05;margin-top:8px}
@media(max-width:640px){.h1{font-size:30px}}
.lead{font-size:14px;opacity:.85;line-height:1.6;margin-top:12px}
.badge{display:inline-flex;gap:6px;align-items:center;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);font-size:11px;font-weight:800}

/* ===== Onboarding carousel (mobile-first, premium) ===== */
.onboard{width:100%;max-width:400px;margin:0 auto;background:linear-gradient(180deg,rgba(255,255,255,.09),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.14);border-radius:32px;backdrop-filter:blur(16px);box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;position:relative}
.slides{display:flex;transition:transform .45s cubic-bezier(.22,.9,.3,1);touch-action:pan-y}
.slide{flex:0 0 100%;padding:30px 24px 18px;text-align:center;min-height:430px;display:flex;flex-direction:column}
.orb{width:128px;height:128px;margin:6px auto 20px;border-radius:40px;display:grid;place-items:center;position:relative;box-shadow:0 16px 40px rgba(0,0,0,.3)}
.orb i{font-size:56px;color:#fff;filter:drop-shadow(0 4px 10px rgba(0,0,0,.3))}
.orb::after{content:'';position:absolute;inset:-10px;border-radius:48px;border:1.5px dashed rgba(255,255,255,.25)}
.orb-1{background:linear-gradient(135deg,#6366f1,#2563eb)}
.orb-2{background:linear-gradient(135deg,#059669,#10b981)}
.orb-3{background:linear-gradient(135deg,#d97706,#f59e0b)}
.slide h3{font-size:23px;font-weight:900;letter-spacing:-.02em}
.slide p{font-size:13px;opacity:.72;line-height:1.65;margin-top:10px;flex:1}
.chips{display:flex;gap:6px;justify-content:center;flex-wrap:wrap;margin-top:14px}
.chip{font-size:10.5px;font-weight:800;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.16)}
.code-demo{margin:14px auto 0;background:rgba(0,0,0,.25);border:1px solid rgba(255,255,255,.14);border-radius:16px;padding:12px 16px;max-width:280px}
.code-demo .c{font-size:24px;font-weight:900;letter-spacing:.2em}
.code-demo .s{font-size:10.5px;opacity:.65;margin-top:4px}
.ob-dots{display:flex;gap:8px;justify-content:center;padding:6px 0 4px}
.ob-dot{width:8px;height:8px;border-radius:99px;background:rgba(255,255,255,.25);transition:all .3s;cursor:pointer;border:0;padding:0}
.ob-dot.on{width:28px;background:#fff}
.ob-foot{display:flex;gap:10px;padding:14px 18px 18px}
.ob-skip{flex:1;background:transparent;border:0;color:rgba(255,255,255,.6);font-weight:800;font-size:13px;cursor:pointer}
.ob-next{flex:2;background:#fff;color:#1e3a5f;border:0;border-radius:16px;padding:14px;font-weight:800;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 8px 24px rgba(0,0,0,.15)}
.hero-cta{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap}
.section{max-width:1120px;margin:0 auto;padding:28px 20px}
.h2{font-size:20px;font-weight:900;letter-spacing:-.02em}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:16px}
@media(max-width:900px){.grid{grid-template-columns:1fr 1fr}}
@media(max-width:640px){.grid{grid-template-columns:1fr}}
.card{background:#fff;border:1px solid rgba(15,23,42,.07);border-radius:20px;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.05)}
.card .ico{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;color:#fff;font-size:18px;margin-bottom:10px}
.steps{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:16px}
@media(max-width:900px){.steps{grid-template-columns:1fr 1fr}}
.step{position:relative;background:#fff;border:1px solid rgba(15,23,42,.07);border-radius:20px;padding:18px}
.step .num{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#2563eb);color:#fff;display:grid;place-items:center;font-weight:900;font-size:13px;margin-bottom:10px}
.footer{max-width:1120px;margin:0 auto;padding:24px 20px;color:#94a3b8;font-size:11px;text-align:center}
</style>
</head>
<body>
<div class="hero">
  <nav class="nav">
    <div class="logo">PS</div>
    <div style="font-weight:900;letter-spacing:-.02em">{{ config('app.name','Portal Sekolah') }}</div>
    <div style="font-size:11px;opacity:.7;margin-left:6px">v2026</div>
    <div class="nav-links">
      <a href="{{ route('login') }}" class="btn btn-ghost"><i class="bi bi-box-arrow-in-right"></i> Masuk</a>
      <a href="{{ route('register') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Daftar Akun</a>
    </div>
  </nav>
  <div class="hero-main">
    <div>
      <div class="eyebrow">Platform Digital Antar Sekolah</div>
      <div class="h1">Satu Portal untuk Seluruh Sekolah</div>
      <p class="lead">Absensi, tugas, nilai, SPP, perpustakaan, eskul, chat, hingga Global Portal antar sekolah — dalam satu aplikasi ringan. Geser kartu perkenalan <span class="badge"><i class="bi bi-phone"></i> di samping</span> untuk tur 30 detik.</p>
      <div class="hero-cta">
        <a href="{{ route('login') }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Masuk Portal</a>
        <a href="{{ route('register') }}" class="btn btn-ghost"><i class="bi bi-person-plus"></i> Daftar dengan Kode</a>
      </div>
      <div style="margin-top:12px;"><a href="{{ route('download.apk') }}" style="font-size:11px;color:rgba(255,255,255,.55);text-decoration:none"><i class="bi bi-cloud-arrow-down"></i> Unduh Aplikasi Android (APK)</a></div>
    </div>

    {{-- Onboarding carousel --}}
    <div class="onboard" id="onboard">
      <div class="slides" id="slides">
        <div class="slide">
          <div class="orb orb-1"><i class="bi bi-globe2"></i></div>
          <h3>Portal Sekolah Digital</h3>
          <p>Portal akademik untuk guru & siswa: absensi, tugas, nilai, SPP, chat, dan linimasa antar sekolah dalam satu genggaman.</p>
          <div class="chips"><span class="chip">Absensi</span><span class="chip">Tugas</span><span class="chip">Nilai</span><span class="chip">SPP</span><span class="chip">Global Portal</span></div>
        </div>
        <div class="slide">
          <div class="orb orb-2"><i class="bi bi-upc-scan"></i></div>
          <h3>Daftar Pakai Kode</h3>
          <p>Minta <b>Kode Pendaftaran</b> ke admin sekolahmu. Masukkan kode — data sekolah terisi otomatis, tinggal pilih peran & lengkapi data diri.</p>
          <div class="code-demo"><div class="c">1851372</div><div class="s">ID 18 + kode kota 51372</div></div>
        </div>
        <div class="slide">
          <div class="orb orb-3"><i class="bi bi-patch-check-fill"></i></div>
          <h3>Disetujui & Jelajahi</h3>
          <p>Akun barumu diverifikasi admin sekolah, lalu bebas absen harian, posting cerita, diskusi chat, dan pantau nilai real-time.</p>
          <div class="chips"><span class="chip">Cerita 24 Jam</span><span class="chip">Chat</span><span class="chip">Rapor Online</span></div>
        </div>
      </div>
      <div class="ob-dots" id="dots"></div>
      <div class="ob-foot">
        <button class="ob-skip" id="obSkip">Lewati</button>
        <button class="ob-next" id="obNext">Lanjut <i class="bi bi-arrow-right"></i></button>
      </div>
    </div>
  </div>
</div>

<div class="section" style="background:#fff;border:1px solid rgba(15,23,42,.07);border-radius:24px;box-shadow:0 12px 30px rgba(15,23,42,.06);margin-top:16px">
  <div class="eyebrow" style="color:#059669">Mulai Cepat</div>
  <div class="h2">3 Langkah Jadi Pelajar Sekolah Digital</div>
  <div class="steps" style="grid-template-columns:repeat(3,1fr);">
    <div class="step"><div class="num">1</div><div style="font-weight:800">Minta Kode</div><div style="font-size:12px;color:#64748b;margin-top:4px">Tanyakan <b>Kode Pendaftaran</b> (ID + kode kota) ke admin sekolahmu, lalu cek di halaman daftar.</div></div>
    <div class="step"><div class="num">2</div><div style="font-weight:800">Daftar Akun</div><div style="font-size:12px;color:#64748b;margin-top:4px">Pilih Guru/Siswa yang dibuka, isi NIM & data diri. Kartu sekolah terisi otomatis.</div></div>
    <div class="step"><div class="num">3</div><div style="font-weight:800">Disetujui & Jelajahi</div><div style="font-size:12px;color:#64748b;margin-top:4px">Admin verifikasi akunmu → login → absen, cerita, portal, chat, tugas.</div></div>
  </div>
  <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap"><span style="padding:6px 10px;background:#dcfce7;color:#166534;border-radius:999px;font-size:11px;font-weight:800"><i class="bi bi-check-circle"></i> Kode Valid = Bisa Daftar</span><span style="padding:6px 10px;background:#fee2e2;color:#991b1b;border-radius:999px;font-size:11px;font-weight:800"><i class="bi bi-x-circle"></i> Kode Salah/Tutup = Ditolak</span></div>
  <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap">
    <a href="{{ ($registrationOpen ?? true) ? route('register') : route('login') }}" class="btn" style="background:#0f172a;color:#fff;border-radius:12px">{{ ($registrationOpen ?? true) ? 'Daftar Sekarang' : 'Masuk ke Portal' }}</a>
    <a href="{{ route('help.faq') }}" class="btn" style="background:#f1f5f9;color:#0f172a;border:1px solid #e2e8f0;border-radius:12px">Lihat FAQ</a>
  </div>
</div>

<div class="footer">© {{ date('Y') }} {{ config('app.name') }} — Platform Digital Antar Sekolah • <a href="{{ route('offline') }}" style="color:#6366f1;text-decoration:none">Offline Mode</a> • Admin Pusat: adminpusat@pusat.com</div>

<script>
(function () {
    var idx = 0, total = 3;
    var slides = document.getElementById('slides');
    var dotsBox = document.getElementById('dots');
    var nextBtn = document.getElementById('obNext');
    for (var i = 0; i < total; i++) {
        var d = document.createElement('button');
        d.className = 'ob-dot' + (i === 0 ? ' on' : '');
        d.setAttribute('aria-label', 'Slide ' + (i + 1));
        (function (n) { d.addEventListener('click', function () { go(n); }); })(i);
        dotsBox.appendChild(d);
    }
    function go(n) {
        idx = (n + total) % total;
        slides.style.transform = 'translateX(-' + (idx * 100) + '%)';
        dotsBox.querySelectorAll('.ob-dot').forEach(function (el, k) { el.classList.toggle('on', k === idx); });
        nextBtn.innerHTML = idx === total - 1
            ? 'Mulai Sekarang <i class="bi bi-arrow-right"></i>'
            : 'Lanjut <i class="bi bi-arrow-right"></i>';
    }
    function finish() { window.location.href = @json(($registrationOpen ?? true) ? route('register') : route('login')); }
    nextBtn.addEventListener('click', function () { idx === total - 1 ? finish() : go(idx + 1); });
    document.getElementById('obSkip').addEventListener('click', finish);
    // Swipe
    var sx = 0;
    slides.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    slides.addEventListener('touchend', function (e) {
        var dx = e.changedTouches[0].clientX - sx;
        if (Math.abs(dx) > 40) go(idx + (dx < 0 ? 1 : -1));
    }, { passive: true });
})();
</script>
</body>
</html>
