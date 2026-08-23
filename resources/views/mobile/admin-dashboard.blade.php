@extends('layouts.mobile-app')
@section('content')
<header class="mobile-hero">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div class="eyebrow">PORTAL ADMINISTRATOR</div>
            <div class="hero-title mt-2">Dashboard Admin</div>
            <div class="class-pill mt-3">Kendali Sistem Sekolah</div>
        </div>
        <a href="{{ route('profile.show') }}" class="avatar text-white text-decoration-none">
            {{ strtoupper(substr(session('admin_name'),0,1)) }}
        </a>
    </div>
</header>

<main class="mobile-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="section-title mb-0">Statistik Cepat</h1>
        <span class="text-secondary small">{{ now()->format('d M Y') }}</span>
    </div>

    <div class="row g-3 mb-4 stagger">
        <div class="col-6">
            <div class="card mobile-card">
                <div class="card-body">
                    <div class="small text-secondary">Total Guru</div>
                    <div class="h4 fw-bold mb-0 text-primary">{{ $totalGuru }}</div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card mobile-card">
                <div class="card-body">
                    <div class="small text-secondary">Total Siswa</div>
                    <div class="h4 fw-bold mb-0 text-success">{{ $totalSiswa }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="section-title mb-0">Menu Admin Mobile</h2>
    </div>

    <div class="stagger">
        <a href="{{ route('admin.settings') }}" class="card mobile-card tap-card text-decoration-none text-dark mb-3">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background:#fef3c7; color:#92400e">&#9881;</div>
                <div>
                    <div class="fw-bold">Pengaturan Absensi</div>
                    <div class="small text-secondary">Aktifkan/Matikan Vermuk</div>
                </div>
                <div class="ms-auto">&rarr;</div>
            </div>
        </a>

        <a href="{{ route('admin.users') }}" class="card mobile-card tap-card text-decoration-none text-dark mb-3">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background:#dcfce7; color:#166534">&#128101;</div>
                <div>
                    <div class="fw-bold">Manajemen Akun</div>
                    <div class="small text-secondary">Daftar Guru & Siswa</div>
                </div>
                <div class="ms-auto">&rarr;</div>
            </div>
        </a>

        <a href="{{ route('pengumuman.index') }}" class="card mobile-card tap-card text-decoration-none text-dark mb-3">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box" style="background:#e0f2fe; color:#075985">&#128226;</div>
                <div>
                    <div class="fw-bold">Pengumuman</div>
                    <div class="small text-secondary">Buat Pengumuman Baru</div>
                </div>
                <div class="ms-auto">&rarr;</div>
            </div>
        </a>
    </div>

    <div class="alert alert-info border-0 rounded-4 mt-4 small">
        <strong>Info:</strong> Untuk manajemen data berat seperti Kelas, Jurusan, dan Laporan SPP lengkap, disarankan menggunakan versi Desktop/Web.
    </div>
</main>
@endsection
