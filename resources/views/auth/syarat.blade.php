<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Syarat & Ketentuan | Portal Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding: 24px 0;
        }
        .terms-card { max-width: 640px; border: 0; border-radius: 28px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,.35); }
        .terms-hero { background: linear-gradient(135deg, #4f46e5, #2563eb); color: #fff; padding: 36px 32px; position: relative; overflow: hidden; }
        .terms-hero::after { content: ''; position: absolute; top: -50px; right: -50px; width: 180px; height: 180px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,.22), transparent 70%); }
        .terms-hero h1 { font-size: 24px; font-weight: 900; letter-spacing: -.02em; position: relative; z-index: 1; }
        .terms-hero p { font-size: 13px; opacity: .85; position: relative; z-index: 1; }
        .terms-ico { width: 56px; height: 56px; border-radius: 18px; background: rgba(255,255,255,.18); display: grid; place-items: center; font-size: 26px; margin-bottom: 14px; position: relative; z-index: 1; }
        .terms-body { background: #fff; padding: 28px 32px; }
        .terms-scroll { max-height: 320px; overflow-y: auto; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 18px 20px; background: #f8fafc; font-size: 13px; line-height: 1.75; color: #334155; }
        .terms-scroll h6 { font-weight: 800; color: #0f172a; margin: 16px 0 6px; font-size: 13.5px; }
        .terms-scroll h6:first-child { margin-top: 0; }
        .terms-scroll ul { padding-left: 18px; margin-bottom: 6px; }
        .agree-box { display: flex; gap: 12px; align-items: flex-start; background: #eff6ff; border: 1px dashed #93c5fd; border-radius: 16px; padding: 16px; margin-top: 18px; cursor: pointer; }
        .agree-box input { width: 20px; height: 20px; margin-top: 2px; accent-color: #246bfe; flex-shrink: 0; }
        .btn-cta { border-radius: 16px; padding: 15px; font-weight: 800; background: linear-gradient(135deg, #246bfe, #1e40af); border: none; box-shadow: 0 8px 20px rgba(36,107,254,.25); }
        .btn-cta:disabled { opacity: .45; box-shadow: none; }
        @media (max-width: 576px) { .terms-body { padding: 22px 20px; } .terms-hero { padding: 28px 22px; } }
    </style>
</head>
<body>
<div class="container">
    <div class="card terms-card mx-auto">
        <div class="terms-hero">
            <div class="terms-ico"><i class="bi bi-shield-check"></i></div>
            <h1>Syarat & Ketentuan Penting</h1>
            <p class="mb-0">Halo <b>{{ $name ?? 'Calon Warga Portal' }}</b> — akunmu sudah tersimpan dan menunggu persetujuan admin. Bacalah aturan main di bawah ini sebelum masuk.</p>
        </div>
        <div class="terms-body">
            <div class="terms-scroll" id="termsScroll">
                <h6>1. Akun & Identitas</h6>
                <ul>
                    <li>Satu orang satu akun. Data NIK, nama, email, dan nomor WA wajib benar dan milik sendiri.</li>
                    <li>Akun baru <b>nonaktif</b> sampai disetujui admin sekolah atau Admin Pusat.</li>
                    <li>Jaga kerahasiaan kata sandi. Segala aktivitas dari akunmu adalah tanggung jawabmu.</li>
                    <li>Dilarang memalsukan identitas guru, admin, atau sekolah lain.</li>
                </ul>
                <h6>2. Aturan Konten Portal & Cerita</h6>
                <ul>
                    <li>Dilarang mengunggah pornografi, ketelanjangan, kekerasan, darah, ujaran kebencian, SARA, judi, narkoba, dan ajakan bunuh diri — sistem moderasi otomatis akan <b>memblokir</b>.</li>
                    <li>Hormati privasi: dilarang menyebar foto, nilai, atau data pribadi orang lain tanpa izin.</li>
                    <li>Postingan yang dilaporkan 3+ pengguna akan <b>disembunyikan otomatis</b> menunggu tinjauan admin.</li>
                    <li>Gunakan bahasa yang sopan. Perundungan (bullying) dalam bentuk apa pun dilarang keras.</li>
                </ul>
                <h6>3. Integritas Akademik</h6>
                <ul>
                    <li>Tugas dikerjakan sendiri. Plagiarisme berakibat nilai dibatalkan dan dilaporkan ke wali kelas.</li>
                    <li>Absensi wajib jujur (foto & lokasi asli). Kecurangan absensi dicatat sistem.</li>
                    <li>Data nilai, SPP, dan jadwal adalah data resmi sekolah — dilarang memanipulasi.</li>
                </ul>
                <h6>4. Privasi Data</h6>
                <ul>
                    <li>Data yang dikumpulkan (identitas, akademik, lokasi absensi, aktivitas) dipakai <b>hanya untuk keperluan sekolah</b>.</li>
                    <li>Data tidak dibagikan ke pihak ketiga tanpa izin, kecuali kewajiban hukum.</li>
                    <li>Kamu dapat meminta koreksi/penghapusan data ke admin sekolah masing-masing.</li>
                </ul>
                <h6>5. Sanksi Pelanggaran</h6>
                <ul>
                    <li>Ringan: peringatan + konten dihapus.</li>
                    <li>Sedang: akun dinonaktifkan sementara oleh admin.</li>
                    <li>Berat (pornografi anak, kekerasan nyata, ujaran kebencian ekstrem): akun dihapus permanen dan dapat diteruskan ke pihak berwajib sesuai hukum Indonesia.</li>
                </ul>
                <h6>6. Bantuan</h6>
                <ul>
                    <li>Laporkan konten bermasalah lewat tombol <b>Laporkan</b> di tiap postingan.</li>
                    <li>Butuh bantuan akun? Hubungi admin sekolahmu atau Admin Pusat: <b>adminpusat@pusat.com</b>.</li>
                </ul>
            </div>
            <label class="agree-box" for="agreeCheck">
                <input type="checkbox" id="agreeCheck">
                <span class="small fw-bold">Saya telah membaca, memahami, dan <u>berjanji mematuhi</u> seluruh Syarat & Ketentuan serta Kebijakan di atas.</span>
            </label>
            <a href="{{ route('login') }}" id="ctaBtn" class="btn btn-primary btn-cta w-100 mt-3" style="pointer-events:none;opacity:.45;">Saya Mengerti & Lanjut Masuk <i class="bi bi-arrow-right ms-1"></i></a>
            <div class="text-center mt-3"><span class="small text-muted">Akun aktif setelah disetujui admin. Cek email/WA berkala.</span></div>
        </div>
    </div>
</div>
<script>
    (function () {
        var box = document.getElementById('termsScroll');
        var check = document.getElementById('agreeCheck');
        var cta = document.getElementById('ctaBtn');
        var readDone = false;
        function refresh() {
            var ok = check.checked && readDone;
            cta.style.pointerEvents = ok ? 'auto' : 'none';
            cta.style.opacity = ok ? '1' : '.45';
        }
        box.addEventListener('scroll', function () {
            if (box.scrollTop + box.clientHeight >= box.scrollHeight - 12) { readDone = true; refresh(); }
        });
        // Teks pendek di layar besar: anggap terbaca bila tidak bisa scroll.
        if (box.scrollHeight <= box.clientHeight + 12) { readDone = true; }
        check.addEventListener('change', refresh);
        refresh();
    })();
</script>
</body>
</html>
