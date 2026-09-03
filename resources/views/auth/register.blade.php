<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi | Portal Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding: 20px 0;
        }
        .register-card {
            max-width: 600px; border: 0; border-radius: 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .register-header { background: #fff; padding: 40px 40px 20px; text-align: center; }
        .register-body { padding: 0 40px 40px; background: #fff; }
        .form-control, .form-select {
            border-radius: 14px; padding: 12px 16px;
            border: 1.5px solid #e2e8f0; background: #f8fafc;
            font-size: 14px;
        }
        .form-control:focus { border-color: #246bfe; background: #fff; box-shadow: 0 0 0 3px rgba(36,107,254,0.1); }
        .form-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
        .btn-primary {
            border-radius: 16px; padding: 16px; font-weight: 800;
            background: linear-gradient(135deg, #246bfe, #1e40af);
            border: none; box-shadow: 0 8px 20px rgba(36, 107, 254, 0.25);
        }
        .password-toggle { cursor: pointer; color: #94a3b8; position: absolute; right: 16px; top: 50%; transform: translateY(-50%); font-size: 16px; }
        .warning-box { background: #eff6ff; border: 1px dashed #93c5fd; border-radius: 16px; padding: 16px; margin-bottom: 24px; }

        .steps { display: flex; gap: 8px; margin-bottom: 24px; }
        .step { flex: 1; text-align: center; }
        .step-dot {
            width: 34px; height: 34px; border-radius: 50%; margin: 0 auto 6px;
            display: grid; place-items: center; font-weight: 800; font-size: 14px;
            background: #f1f5f9; color: #94a3b8; transition: all .3s;
        }
        .step.active .step-dot { background: linear-gradient(135deg, #246bfe, #1e40af); color: #fff; box-shadow: 0 6px 16px rgba(36,107,254,.35); }
        .step.done .step-dot { background: #dcfce7; color: #166534; }
        .step-lbl { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; }
        .step.active .step-lbl { color: #1d4ed8; }

        .school-card {
            border-radius: 20px; overflow: hidden; border: 1.5px solid #e0e7ff;
            animation: slideUp .4s ease both;
        }
        @keyframes slideUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        .school-card-top { background: linear-gradient(135deg, #4f46e5, #2563eb); color: #fff; padding: 18px 20px; }
        .reveal { animation: slideUp .4s ease both; }
        .code-input { font-size: 20px !important; font-weight: 800; letter-spacing: .15em; text-align: center; }
    </style>
</head>
<body class="d-flex align-items-center">
<div class="container">
    <div class="card register-card mx-auto">
        <div class="register-header">
            <div class="text-primary fw-bold small mb-2" style="letter-spacing: 0.1em;">AKUN AKADEMIK</div>
            <h1 class="h3 fw-bold mb-1" style="color: #0f172a;">Daftar Akun Baru</h1>
            <p class="text-muted small mb-0">Masukkan Kode Pendaftaran sekolah, lalu lengkapi data diri.</p>
        </div>

        <div class="register-body">
            <div class="steps">
                <div class="step active" id="st1"><div class="step-dot">1</div><div class="step-lbl">Kode Sekolah</div></div>
                <div class="step" id="st2"><div class="step-dot">2</div><div class="step-lbl">Sekolah & Peran</div></div>
                <div class="step" id="st3"><div class="step-dot">3</div><div class="step-lbl">Data Diri</div></div>
            </div>

            <div class="warning-box">
                <div class="d-flex gap-3">
                    <i class="bi bi-shield-check text-primary h4 mb-0"></i>
                    <div class="small fw-bold text-dark" style="line-height: 1.5;">
                        Akun akan aktif setelah <strong>disetujui Admin</strong>. Tanyakan <strong>Kode Pendaftaran</strong> ke admin sekolahmu.
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger border-0 rounded-4 small mb-4 shadow-sm">
                    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            {{-- LANGKAH 1: Kode Pendaftaran --}}
            <div class="mb-4">
                <label class="form-label"><i class="bi bi-upc-scan me-1"></i> Kode Pendaftaran Sekolah <span class="text-danger">*</span></label>
                <div class="d-flex gap-2">
                    <input id="enrollCode" class="form-control code-input" placeholder="cth. 1851372" inputmode="numeric" autocomplete="off">
                    <button type="button" id="checkCodeBtn" class="btn btn-primary px-4" style="border-radius:14px;padding:12px 20px;white-space:nowrap;">Cek Kode</button>
                </div>
                <div id="codeMsg" class="small mt-2" style="display:none;"></div>
                <div class="small text-muted mt-2">Format: <b>ID sekolah + kode kota</b> (contoh ID 18 + 51372 → <b>1851372</b>). Tidak punya kode? <a href="#" id="manualToggle">pilih sekolah manual</a>.</div>
            </div>

            <div id="manualBox" style="display:none;" class="mb-4 reveal">
                <label class="form-label">Pilih Sekolah Manual</label>
                <select id="schoolSelect" class="form-select">
                    <option value="">Cari sekolah...</option>
                    @foreach($schools as $s)
                        <option value="{{ $s->id }}" data-guru="{{ $s->reg_guru_open ? 1 : 0 }}" data-siswa="{{ $s->reg_siswa_open ? 1 : 0 }}">[ID: {{ $s->id }}] {{ $s->name }} — {{ $s->city }}</option>
                    @endforeach
                </select>
            </div>

            <form method="POST" action="{{ route('register.store') }}" id="regForm" style="display:none;">
                @csrf
                <input type="hidden" name="school_id" id="schoolId">
                <input type="hidden" name="enroll_code" id="enrollCodeHidden">

                {{-- LANGKAH 2: kartu detail sekolah + peran --}}
                <div id="schoolCard" class="school-card mb-4"></div>

                <div class="mb-4 reveal">
                    <label class="form-label">Daftar sebagai</label>
                    <select name="role" class="form-select" required id="roleSelect">
                        <option value="">Pilih peran...</option>
                        <option value="guru" id="optGuru">Guru / Tenaga Pengajar</option>
                        <option value="siswa" id="optSiswa">Siswa / Mahasiswa</option>
                    </select>
                </div>

                {{-- LANGKAH 3: data diri --}}
                <div class="row g-3 reveal">
                    <div class="col-md-6">
                        <label class="form-label">Nomor Induk / NIK</label>
                        <input name="nik" value="{{ old('nik') }}" class="form-control" placeholder="8–30 digit" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp Aktif</label>
                        <input name="no_hp" value="{{ old('no_hp') }}" class="form-control" placeholder="08..." required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nama Lengkap</label>
                        <input name="name" value="{{ old('name') }}" class="form-control" placeholder="Nama sesuai KTP/Ijazah" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email Institusi / Pribadi</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@email.com" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Pilih Kelas</label>
                        <select name="kelas_id" class="form-select" required>
                            <option value="">Cari kelas...</option>
                            @foreach($kelases as $kelas)
                                <option value="{{ $kelas->id }}" @selected(old('kelas_id')==$kelas->id)>{{ $kelas->nama }} ({{ $kelas->tahun_ajaran }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Buat Kata Sandi</label>
                        <div class="position-relative">
                            <input type="password" name="password" id="pass1" class="form-control pe-5" placeholder="Min. 8 karakter" required>
                            <i class="bi bi-eye password-toggle" onclick="togglePass('pass1', this)"></i>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Konfirmasi Sandi</label>
                        <div class="position-relative">
                            <input type="password" name="password_confirmation" id="pass2" class="form-control pe-5" placeholder="Ulangi sandi" required>
                            <i class="bi bi-eye password-toggle" onclick="togglePass('pass2', this)"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-5 mb-4">
                    DAFTAR SEKARANG <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </form>

            <div class="text-center pt-3 border-top">
                <span class="small text-muted">Sudah punya akses?</span>
                <a href="{{ route('login') }}" class="small fw-bold text-decoration-none ms-1 text-primary">Masuk ke Portal</a>
            </div>
        </div>
    </div>
</div>

<script>
    const CHECK_URL = "{{ route('register.check') }}";
    const codeInput = document.getElementById('enrollCode');
    const checkBtn = document.getElementById('checkCodeBtn');
    const codeMsg = document.getElementById('codeMsg');
    const regForm = document.getElementById('regForm');

    function setStep(n) {
        [1, 2, 3].forEach(i => {
            const el = document.getElementById('st' + i);
            el.classList.toggle('active', i === n);
            el.classList.toggle('done', i < n);
            if (i < n) el.querySelector('.step-dot').innerHTML = '<i class="bi bi-check-lg"></i>';
            else el.querySelector('.step-dot').innerText = i;
        });
    }

    function showMsg(ok, text) {
        codeMsg.style.display = 'block';
        codeMsg.innerHTML = (ok ? '<i class="bi bi-check-circle-fill text-success"></i> ' : '<i class="bi bi-x-circle-fill text-danger"></i> ') + text;
        codeMsg.className = 'small mt-2 ' + (ok ? 'text-success' : 'text-danger');
    }

    function applySchool(s) {
        document.getElementById('schoolId').value = s.id;
        document.getElementById('enrollCodeHidden').value = s.enroll_code || '';
        document.getElementById('schoolCard').innerHTML =
            '<div class="school-card-top"><div class="d-flex gap-3 align-items-center">' +
            '<div style="width:52px;height:52px;border-radius:16px;background:rgba(255,255,255,.18);display:grid;place-items:center;font-weight:800;font-size:22px;">' + s.name.charAt(0).toUpperCase() + '</div>' +
            '<div><div class="small" style="opacity:.75;">ID: ' + s.id + ' • ' + (s.city || '-') + '</div>' +
            '<div class="fw-bold" style="font-size:17px;">' + s.name + '</div>' +
            '<div><span class="badge rounded-pill bg-light text-dark" style="font-size:10px;">' + s.slug + '</span> ' +
            (s.reg_guru_open ? '<span class="badge rounded-pill bg-success" style="font-size:10px;">Guru: Buka</span>' : '') + ' ' +
            (s.reg_siswa_open ? '<span class="badge rounded-pill bg-success" style="font-size:10px;">Siswa: Buka</span>' : '') + '</div></div>' +
            '<i class="bi bi-patch-check-fill ms-auto" style="font-size:28px;opacity:.85;"></i></div></div>' +
            '<div class="p-3 small text-muted" style="background:#fff;">Kode <b>' + (s.enroll_code || '-') + '</b> terverifikasi. Data sekolah masuk otomatis ke formulir.</div>';
        document.getElementById('optGuru').hidden = !s.reg_guru_open;
        document.getElementById('optSiswa').hidden = !s.reg_siswa_open;
        document.getElementById('roleSelect').value = '';
        regForm.style.display = 'block';
        regForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        setStep(2);
        setTimeout(() => setStep(3), 2500);
    }

    async function checkCode(code) {
        checkBtn.disabled = true;
        checkBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        try {
            const res = await fetch(CHECK_URL + '?code=' + encodeURIComponent(code), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'Kode tidak ditemukan / sekolah nonaktif.');
            showMsg(true, 'Sekolah ditemukan: <b>' + data.name + '</b>');
            applySchool(data);
        } catch (e) {
            showMsg(false, e.message);
            regForm.style.display = 'none';
            setStep(1);
        } finally {
            checkBtn.disabled = false;
            checkBtn.innerText = 'Cek Kode';
        }
    }

    checkBtn.addEventListener('click', () => {
        const code = codeInput.value.trim();
        if (!code) { showMsg(false, 'Masukkan Kode Pendaftaran dulu.'); return; }
        checkCode(code);
    });
    codeInput.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); checkBtn.click(); } });

    document.getElementById('manualToggle').addEventListener('click', e => {
        e.preventDefault();
        const box = document.getElementById('manualBox');
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
    });
    document.getElementById('schoolSelect').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (!this.value) return;
        showMsg(true, 'Sekolah dipilih manual: <b>' + opt.text + '</b>');
        applySchool({
            id: this.value, name: opt.text.replace(/^\[ID: \d+\] /, '').split(' — ')[0],
            city: '', slug: '', enroll_code: '',
            reg_guru_open: opt.dataset.guru === '1', reg_siswa_open: opt.dataset.siswa === '1'
        });
        document.getElementById('enrollCodeHidden').value = '';
    });

    function togglePass(id, el) {
        const input = document.getElementById(id);
        if (input.type === 'password') { input.type = 'text'; el.classList.replace('bi-eye', 'bi-eye-slash'); }
        else { input.type = 'password'; el.classList.replace('bi-eye-slash', 'bi-eye'); }
    }
</script>
</body>
</html>
