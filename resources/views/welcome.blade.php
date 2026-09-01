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
.hero-main{max-width:1120px;margin:0 auto;padding:36px 20px 40px;display:grid;grid-template-columns:1.1fr .9fr;gap:28px;align-items:center;position:relative;z-index:1}
@media(max-width:900px){.hero-main{grid-template-columns:1fr}.nav-links{display:none}}
.eyebrow{font-size:11px;letter-spacing:.14em;opacity:.7;font-weight:800;text-transform:uppercase}
.h1{font-size:36px;font-weight:900;letter-spacing:-.03em;line-height:1.05;margin-top:8px}
@media(max-width:640px){.h1{font-size:30px}}
.lead{font-size:14px;opacity:.85;line-height:1.6;margin-top:12px}
.hero-card{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);border-radius:22px;padding:14px;backdrop-filter:blur(12px)}
.mock{width:100%;max-width:320px;margin:0 auto;background:#fff;border-radius:28px;overflow:hidden;box-shadow:0 20px 60px rgba(15,23,42,.25);border:6px solid rgba(255,255,255,.9)}
.mock img{width:100%;display:block}
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
.badge{display:inline-flex;gap:6px;align-items:center;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);font-size:11px;font-weight:800}
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
      <div class="badge"><i class="bi bi-globe2"></i> Global Portal • Antar Sekolah Se-Nusantara</div>
      <div class="h1">Satu Aplikasi,<br>Semua Kebutuhan Sekolah Digital</div>
      <div class="lead">Absensi, Tugas LMS, Nilai, SPP, Eskul, Chat WA-like & <b>Global Portal sosmed</b> antar sekolah — premium, cepat, ringan.</div>
      <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap">
        <a href="{{ route('register') }}" class="btn btn-primary" style="padding:13px 22px;border-radius:14px">Mulai Sekarang — Gratis <i class="bi bi-arrow-right"></i></a>
        <a href="{{ route('download.apk') }}" class="btn btn-ghost"><i class="bi bi-download"></i> Download APK</a>
      </div>
      <div style="display:flex;gap:12px;margin-top:16px;font-size:11px;opacity:.8">
        <span><i class="bi bi-shield-check"></i> Aman & Terverifikasi</span>
        <span><i class="bi bi-lightning"></i> Ringan 11MB</span>
        <span><i class="bi bi-phone"></i> Android & Web</span>
      </div>
    </div>
    <div class="hero-card">
      <div class="mock">
        <img src="{{ asset('logo_sekolah.png') }}" alt="Preview" style="padding:24px;max-height:220px;object-fit:contain;background:linear-gradient(135deg,#eef2ff,#f5f3ff)">
        <div style="padding:14px">
          <div style="height:10px;background:#e2e8f0;border-radius:999px;width:60%;margin-bottom:8px"></div>
          <div style="height:8px;background:#f1f5f9;border-radius:999px;margin-bottom:6px"></div>
          <div style="height:8px;background:#f1f5f9;border-radius:999px;width:80%"></div>
          <div style="display:flex;gap:8px;margin-top:12px"><span style="flex:1;height:32px;background:#4f46e5;border-radius:10px;display:grid;place-items:center;color:#fff;font-weight:800;font-size:11px">Masuk</span><span style="flex:1;height:32px;background:#f1f5f9;border-radius:10px"></span></div>
        </div>
      </div>
      <div style="margin-top:12px;display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
        <div style="background:rgba(255,255,255,.12);border-radius:12px;padding:10px;text-align:center"><div style="font-weight:900">4.9★</div><div style="font-size:10px;opacity:.8">Rating</div></div>
        <div style="background:rgba(255,255,255,.12);border-radius:12px;padding:10px;text-align:center"><div style="font-weight:900">10K+</div><div style="font-size:10px;opacity:.8">Pengguna</div></div>
        <div style="background:rgba(255,255,255,.12);border-radius:12px;padding:10px;text-align:center"><div style="font-weight:900">3 Sekolah</div><div style="font-size:10px;opacity:.8">Tergabung</div></div>
      </div>
    </div>
  </div>
</div>

<div class="section">
  <div class="eyebrow" style="color:#6366f1">Fitur Unggulan</div>
  <div class="h2">Kenapa Portal Sekolah Berbeda?</div>
  <div class="grid">
    <div class="card"><div class="ico" style="background:linear-gradient(135deg,#22c55e,#16a34a)"><i class="bi bi-geo-alt-fill"></i></div><div style="font-weight:800">Absensi Pintar</div><div style="font-size:12px;color:#64748b;margin-top:4px">GPS + foto, rekap otomatis PDF/Excel, status hadir/terlambat/izin.</div></div>
    <div class="card"><div class="ico" style="background:linear-gradient(135deg,#6366f1,#4f46e5)"><i class="bi bi-globe2"></i></div><div style="font-weight:800">Global Portal</div><div style="font-size:12px;color:#64748b;margin-top:4px">Sosmed antar sekolah — posting karya, like & komentar lintas Nusantara.</div></div>
    <div class="card"><div class="ico" style="background:linear-gradient(135deg,#06b6d4,#2563eb)"><i class="bi bi-chat-dots-fill"></i></div><div style="font-weight:800">Chat WA-like</div><div style="font-size:12px;color:#64748b;margin-top:4px">Pribadi & grup kelas/eskul, online/last seen, file & notif heads-up.</div></div>
    <div class="card"><div class="ico" style="background:linear-gradient(135deg,#f59e0b,#ef4444)"><i class="bi bi-journal-text"></i></div><div style="font-weight:800">LMS Tugas</div><div style="font-size:12px;color:#64748b;margin-top:4px">Materi, tugas form/file, nilai & rekap.</div></div>
    <div class="card"><div class="ico" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)"><i class="bi bi-wallet2"></i></div><div style="font-weight:800">SPP Digital</div><div style="font-size:12px;color:#64748b;margin-top:4px">Tagihan, pembayaran, progress realtime.</div></div>
    <div class="card"><div class="ico" style="background:linear-gradient(135deg,#0f172a,#334155)"><i class="bi bi-shield-lock-fill"></i></div><div style="font-weight:800">Aman Premium</div><div style="font-size:12px;color:#64748b;margin-top:4px">PIN + Biometrik, offline mode, notif luar aplikasi.</div></div>
  </div>
</div>

<div class="section" style="background:#fff;border:1px solid rgba(15,23,42,.07);border-radius:24px;box-shadow:0 12px 30px rgba(15,23,42,.06);margin-top:8px">
  <div class="eyebrow" style="color:#6366f1">Cara Memulai</div>
  <div class="h2">4 Langkah Jadi Warga Digital</div>
  <div class="steps">
    <div class="step"><div class="num">1</div><div style="font-weight:800">Install APK</div><div style="font-size:12px;color:#64748b;margin-top:4px">Download APK 11MB, izinkan notifikasi & akses.</div><div style="margin-top:8px;font-size:11px;color:#4f46e5;font-weight:700">Download → Izinkan</div></div>
    <div class="step"><div class="num">2</div><div style="font-weight:800">Daftar Akun</div><div style="font-size:12px;color:#64748b;margin-top:4px">Pilih Guru/Siswa, isi NIK & data.</div><div style="margin-top:8px;font-size:11px;color:#4f46e5;font-weight:700">Daftar → Tunggu</div></div>
    <div class="step"><div class="num">3</div><div style="font-weight:800">Verifikasi Admin</div><div style="font-size:12px;color:#64748b;margin-top:4px">Admin setujui + atur Kelas & Sekolah.</div><div style="margin-top:8px;font-size:11px;color:#4f46e5;font-weight:700">Disetujui → Login</div></div>
    <div class="step"><div class="num">4</div><div style="font-weight:800">Jelajahi</div><div style="font-size:12px;color:#64748b;margin-top:4px">Absen, Global Portal, Chat, Tugas.</div><div style="margin-top:8px;font-size:11px;color:#4f46e5;font-weight:700">Explore → Produktif</div></div>
  </div>
  <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap">
    <a href="{{ route('login') }}" class="btn" style="background:#0f172a;color:#fff;border-radius:12px">Saya Sudah Punya Akun</a>
    <a href="{{ route('help.faq') }}" class="btn" style="background:#f1f5f9;color:#0f172a;border:1px solid #e2e8f0;border-radius:12px">Lihat FAQ</a>
  </div>
</div>

<div class="section" style="background:#fff;border:1px solid rgba(15,23,42,.07);border-radius:24px;box-shadow:0 12px 30px rgba(15,23,42,.06);margin-top:16px">
  <div class="eyebrow" style="color:#059669">Panduan Lengkap</div>
  <div class="h2">Cara Daftar — Publik Se-Dunia Bisa, Jika Sekolah Aktif</div>
  <div style="font-size:13px;color:#64748b;line-height:1.6;margin-top:6px">1. <b>Cari ID Sekolah</b> dari admin sekolahmu (contoh: [ID:1] Sekolah Pusat Semarang). Tanpa ID tidak bisa lanjut.<br>2. Pilih <b>Daftar</b> → isi NIK, Nama, WA, Email, Kelas, <b>Sekolah (ID)</b> → buat password.<br>3. Akun <b>menunggu persetujuan Admin Sekolah</b> (jika DB ada data murid/guru, admin tinggal klik Setujui).<br>4. Jika sekolah <b>dinonaktifkan/di hapus Admin Pusat</b>, tombol daftar hilang & NIK tertolak — hubungi adminpusat@pusat.com.<br>5. Setelah disetujui, login → atur PIN → jelajahi Global Portal & Chat.</div>
  <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap"><span style="padding:6px 10px;background:#dcfce7;color:#166534;border-radius:999px;font-size:11px;font-weight:800"><i class="bi bi-check-circle"></i> ID Aktif = Bisa Daftar</span><span style="padding:6px 10px;background:#fee2e2;color:#991b1b;border-radius:999px;font-size:11px;font-weight:800"><i class="bi bi-x-circle"></i> ID Nonaktif = Tertutup</span></div>
</div>
<div class="footer">© {{ date('Y') }} {{ config('app.name') }} — Platform Digital Antar Sekolah • <a href="{{ route('offline') }}" style="color:#6366f1;text-decoration:none">Offline Mode</a> • Admin Pusat: adminpusat@pusat.com</div>
</body>
</html>
