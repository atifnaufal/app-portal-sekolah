<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi | Portal Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #246bfe, #14213d); font-family: system-ui, -apple-system, sans-serif; }
        .register-card { max-width: 560px; border: 0; border-radius: 28px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); }
        .form-control, .form-select { border-radius: 12px; padding: 12px 15px; border: 1px solid #e2e8f0; background: #fbfcfe; }
        .btn-primary { border-radius: 12px; padding: 14px; font-weight: 700; background: #246bfe; border: none; }
        .warning-box { background: #fffbeb; border: 1px dashed #f59e0b; border-radius: 15px; padding: 15px; margin-bottom: 25px; }
    </style>
</head>
<body class="d-flex align-items-center py-5">
<div class="container">
    <div class="card register-card mx-auto">
        <div class="card-body p-4 p-md-5">
            <div class="text-primary fw-bold small mb-2">AKUN SEKOLAH</div>
            <h1 class="h3 fw-bold mb-1">Daftar Akun Baru</h1>
            <p class="text-secondary mb-4">Bergabunglah dengan ekosistem akademik digital kami.</p>

            <div class="warning-box" style="background:#eff6ff;border:1px dashed #93c5fd;">
                <div class="d-flex gap-2">
                    <i class="bi bi-shield-check" style="color:#2563eb;"></i>
                    <div class="small fw-bold" style="color:#1e3a5f; line-height: 1.5;">
                        Setelah mendaftar, akun Anda menunggu <strong>persetujuan admin</strong> sebelum bisa dipakai. Tidak ada email verifikasi yang perlu diklik.
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger border-0 rounded-4 small mb-4">
                    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold">Daftar sebagai</label>
                    <select name="role" class="form-select" required>
                        <option value="">Pilih role</option>
                        @if($guruEnabled)
                            <option value="guru" @selected(old('role')==='guru')>Guru</option>
                        @endif
                        @if($siswaEnabled)
                            <option value="siswa" @selected(old('role')==='siswa')>Siswa</option>
                        @endif
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">NIK / No. Induk</label>
                        <input name="nik" value="{{ old('nik') }}" class="form-control" placeholder="Nomor identitas..." required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">No. HP Aktif</label>
                        <input name="no_hp" value="{{ old('no_hp') }}" class="form-control" placeholder="0812..." required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Nama Lengkap</label>
                        <input name="name" value="{{ old('name') }}" class="form-control" placeholder="Sesuai ijazah..." required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@email.com" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Kelas</label>
                        <select name="kelas_id" class="form-select" required>
                            <option value="">Pilih kelas anda...</option>
                            @foreach($kelases as $kelas)
                                <option value="{{ $kelas->id }}" @selected(old('kelas_id')==$kelas->id)>{{ $kelas->nama }} · {{ $kelas->tahun_ajaran }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                    </div>
                </div>
                <button class="btn btn-primary w-100 mt-4 shadow-sm">DAFTAR SEKARANG &rarr;</button>
            </form>
            <div class="text-center mt-4 pt-3 border-top">
                <span class="small text-muted">Sudah punya akun?</span>
                <a href="{{ route('login') }}" class="small fw-bold text-decoration-none ms-1">Masuk Kembali</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
