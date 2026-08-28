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
            max-width: 560px; border: 0; border-radius: 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .register-header {
            background: #fff;
            padding: 40px 40px 20px;
            text-align: center;
        }
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
    </style>
</head>
<body class="d-flex align-items-center">
<div class="container">
    <div class="card register-card mx-auto">
        <div class="register-header">
            <div class="text-primary fw-bold small mb-2" style="letter-spacing: 0.1em;">AKUN AKADEMIK</div>
            <h1 class="h3 fw-bold mb-1" style="color: #0f172a;">Daftar Akun Baru</h1>
            <p class="text-muted small mb-0">Lengkapi data diri Anda untuk bergabung.</p>
        </div>

        <div class="register-body">
            <div class="warning-box">
                <div class="d-flex gap-3">
                    <i class="bi bi-shield-check text-primary h4 mb-0"></i>
                    <div class="small fw-bold text-dark" style="line-height: 1.5;">
                        Akun akan aktif setelah <strong>disetujui Admin</strong>. Pastikan email Anda aktif untuk menerima informasi.
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger border-0 rounded-4 small mb-4 shadow-sm">
                    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" id="regForm">
                @csrf
                <div class="mb-4">
                    <label class="form-label">Daftar sebagai</label>
                    <select name="role" class="form-select" required>
                        <option value="">Pilih peran...</option>
                        @if($guruEnabled) <option value="guru" @selected(old('role')==='guru')>Guru / Tenaga Pengajar</option> @endif
                        @if($siswaEnabled) <option value="siswa" @selected(old('role')==='siswa')>Siswa / Mahasiswa</option> @endif
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nomor Induk / NIK</label>
                        <input name="nik" value="{{ old('nik') }}" class="form-control" placeholder="16 digit..." required>
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
    function togglePass(id, el) {
        const input = document.getElementById(id);
        if (input.type === 'password') {
            input.type = 'text';
            el.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            el.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
</script>
</body>
</html>
