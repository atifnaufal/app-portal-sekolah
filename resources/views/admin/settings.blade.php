@extends('layouts.app', ['title' => 'Pengaturan Portal'])
@section('content')
<div class="cp-shell">
@include('admin.partials.sidebar')
<div class="cp-main">

@if(empty($isSuperAdmin) && !empty($school))
{{-- Admin sekolah: hanya pendaftaran sekolahnya sendiri --}}
<div class="row">
    <div class="col-md-8">
        <div class="form-card card p-4 mb-4">
            <h5 class="fw-bold mb-1">Pendaftaran {{ $school->name }}</h5>
            <p class="small text-secondary mb-4">Buka/tutup pendaftaran akun baru untuk sekolah Anda. Pendaftar baru masuk sebagai <b>menunggu persetujuan</b> dan harus disetujui admin.</p>
            @foreach([['role' => 'guru', 'label' => 'Guru', 'open' => $schoolRegGuru], ['role' => 'siswa', 'label' => 'Siswa', 'open' => $schoolRegSiswa]] as $reg)
            <form method="POST" action="{{ route('admin.registration.toggle') }}" class="mb-3 p-3 rounded-3 border d-flex justify-content-between align-items-center" style="background:#f8fafc;">
                @csrf @method('PATCH') <input type="hidden" name="role" value="{{ $reg['role'] }}">
                <div>
                    <div class="fw-bold">Pendaftaran {{ $reg['label'] }}</div>
                    <span class="badge rounded-pill {{ $reg['open'] ? 'bg-success' : 'bg-secondary' }}">{{ $reg['open'] ? 'DIBUKA' : 'DITUTUP' }}</span>
                </div>
                <button class="btn {{ $reg['open'] ? 'btn-outline-danger' : 'btn-success' }}" style="border-radius:12px;">{{ $reg['open'] ? 'Tutup' : 'Buka' }}</button>
            </form>
            @endforeach
            <a href="{{ route('admin.users') }}" class="btn btn-primary px-4 mt-2"><i class="bi bi-people me-2"></i>Kelola Persetujuan Akun</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-4">
            <h6 class="fw-bold">Info</h6>
            <p class="small text-secondary mb-0">Pengaturan absensi & sistem global dikelola Admin Pusat. Akun pendaftar baru berstatus nonaktif sampai Anda setujui di menu Akun.</p>
        </div>
    </div>
</div>
@else
{{-- Admin pusat: Pendaftaran + FAQ ala platform besar (tanpa Absensi Vermuk) --}}
<style>
    .cp-page-header {
        background: linear-gradient(135deg, var(--navy) 0%, #1e293b 100%);
        border-radius: 24px; padding: 32px 36px; color: #fff;
        position: relative; overflow: hidden; margin-bottom: 24px;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
    }
    .cp-page-header::after {
        content: ''; position: absolute; top: -70px; right: -70px;
        width: 220px; height: 220px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36,107,254,0.18) 0%, transparent 70%);
    }
    .cp-page-title { font-size: 26px; font-weight: 800; letter-spacing: -0.02em; position: relative; z-index: 1; }
    .cp-page-sub { font-size: 13px; color: #94a3b8; position: relative; z-index: 1; }
    .set-card { border-radius: 20px; border: 1px solid var(--border); background: #fff; box-shadow: var(--shadow); overflow: hidden; }
    .set-card-head { padding: 20px 24px; border-bottom: 1px solid var(--border); }
    .set-card-title { font-size: 16px; font-weight: 800; color: var(--navy); margin: 0; }
    .quick-link { display: flex; align-items: center; gap: 12px; padding: 12px 0; text-decoration: none; color: var(--navy); font-weight: 700; font-size: 13.5px; }
    .quick-link + .quick-link { border-top: 1px solid #f1f5f9; }
    .quick-link:hover { color: var(--blue); }
    .quick-ico { width: 38px; height: 38px; border-radius: 12px; display: grid; place-items: center; font-size: 16px; flex-shrink: 0; }
    .accordion-item { border: 1px solid var(--border) !important; border-radius: 14px !important; overflow: hidden; margin-bottom: 10px; }
    .accordion-button { font-size: 13.5px; font-weight: 700; }
    .accordion-button:not(.collapsed) { background: #eef2ff; color: #4f46e5; box-shadow: none; }
    .accordion-button:focus { box-shadow: none; border-color: var(--border); }
    .accordion-body { font-size: 13px; color: var(--muted); line-height: 1.7; }
    @media (max-width: 768px) {
        .cp-page-header { padding: 24px; border-radius: 20px; }
        .cp-page-title { font-size: 22px; }
    }
</style>

<div class="cp-page-header">
    <div class="position-relative" style="z-index:1;">
        <div class="small fw-bold" style="letter-spacing:.1em;color:#94a3b8;">SUPER ADMIN ONLY</div>
        <h1 class="cp-page-title">Pengaturan Platform</h1>
        <p class="cp-page-sub mb-0">Kontrol pendaftaran semua sekolah, pintasan manajemen, dan panduan penggunaan.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="set-card">
            <div class="set-card-head d-flex justify-content-between align-items-center">
                <h2 class="set-card-title"><i class="bi bi-person-plus me-2 text-primary"></i>Pendaftaran per Sekolah</h2>
                <span class="badge rounded-pill bg-primary-subtle text-primary">{{ $schoolsReg->count() }} sekolah</span>
            </div>
            <div class="p-3">
                <p class="small text-secondary px-2 mb-3">Pendaftaran diatur <b>per sekolah</b>. Atur buka/tutup dari halaman Detail tiap sekolah — admin sekolah yang aktif juga bisa mengaturnya sendiri.</p>
                @forelse($schoolsReg as $sr)
                <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-2" style="background:#f8fafc;">
                    <span class="small fw-bold flex-fill text-truncate">[{{ $sr->id }}] {{ $sr->name }}</span>
                    <span class="badge rounded-pill {{ $sr->reg_guru_open ? 'bg-success' : 'bg-secondary' }}" style="font-size:10px;">Guru: {{ $sr->reg_guru_open ? 'Buka' : 'Tutup' }}</span>
                    <span class="badge rounded-pill {{ $sr->reg_siswa_open ? 'bg-success' : 'bg-secondary' }}" style="font-size:10px;">Siswa: {{ $sr->reg_siswa_open ? 'Buka' : 'Tutup' }}</span>
                    <a href="{{ route('admin.schools.detail', $sr->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:11px;">Atur</a>
                </div>
                @empty
                <div class="text-center py-4 text-muted small">Belum ada sekolah. <a href="{{ route('admin.schools.index') }}">Tambah sekolah dulu</a>.</div>
                @endforelse
            </div>
        </div>

        @if(!empty($insightSummary))
        <div class="set-card mt-4">
            <div class="set-card-head d-flex justify-content-between align-items-center">
                <h2 class="set-card-title"><i class="bi bi-cpu-fill me-2 text-primary"></i>AI Analyst & Terminal</h2>
                <a href="{{ route('admin.insights') }}" class="btn btn-sm btn-primary" style="border-radius:10px;">Buka <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
            <div class="p-4">
                <p class="small text-secondary mb-3">Insight kesehatan sistem, terminal diagnostik allowlist, dan status GitHub — khusus Admin Pusat, tiap aksi diaudit.</p>
                <div class="d-flex gap-2 flex-wrap">
                    <div class="mini-stat flex-fill" style="background:#f8fafc;border-radius:12px;padding:12px;text-align:center;min-width:120px;">
                        <div class="h5 fw-extrabold mb-0 {{ $insightSummary['pending'] ? 'text-danger' : '' }}">{{ number_format($insightSummary['pending']) }}</div>
                        <div class="small text-muted fw-bold text-uppercase" style="font-size:10px;">Pending</div>
                    </div>
                    <div class="mini-stat flex-fill" style="background:#f8fafc;border-radius:12px;padding:12px;text-align:center;min-width:120px;">
                        <div class="h5 fw-extrabold mb-0">{{ number_format($insightSummary['sekolah']) }}</div>
                        <div class="small text-muted fw-bold text-uppercase" style="font-size:10px;">Sekolah</div>
                    </div>
                    <div class="mini-stat flex-fill" style="background:#f8fafc;border-radius:12px;padding:12px;text-align:center;min-width:120px;">
                        <div class="h5 fw-extrabold mb-0">{{ number_format($insightSummary['login_hari_ini']) }}</div>
                        <div class="small text-muted fw-bold text-uppercase" style="font-size:10px;">Login Hari Ini</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    <div class="col-lg-4">
        <div class="set-card mb-4">
            <div class="set-card-head"><h2 class="set-card-title"><i class="bi bi-lightning-charge me-2 text-warning"></i>Aksi Cepat</h2></div>
            <div class="px-4 py-2">
                <a href="{{ route('admin.features') }}" class="quick-link"><span class="quick-ico" style="background:#eef2ff;color:#4f46e5;"><i class="bi bi-sliders"></i></span>Kelola Fitur per Sekolah<i class="bi bi-chevron-right ms-auto text-muted"></i></a>
                <a href="{{ route('admin.school-admins.index') }}" class="quick-link"><span class="quick-ico" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-shield-check"></i></span>CRUD Admin Sekolah<i class="bi bi-chevron-right ms-auto text-muted"></i></a>
                <a href="{{ route('admin.schools.index') }}" class="quick-link"><span class="quick-ico" style="background:#fffbeb;color:#d97706;"><i class="bi bi-buildings-fill"></i></span>Kelola Sekolah<i class="bi bi-chevron-right ms-auto text-muted"></i></a>
                <a href="{{ route('admin.users') }}" class="quick-link"><span class="quick-ico" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-people-fill"></i></span>Persetujuan Akun<i class="bi bi-chevron-right ms-auto text-muted"></i></a>
                <a href="{{ route('global.portal') }}" class="quick-link"><span class="quick-ico" style="background:#fdf4ff;color:#9333ea;"><i class="bi bi-globe"></i></span>Global Portal<i class="bi bi-chevron-right ms-auto text-muted"></i></a>
            </div>
        </div>
        <div class="set-card">
            <div class="set-card-head"><h2 class="set-card-title"><i class="bi bi-info-circle me-2 text-primary"></i>Info Sistem</h2></div>
            <div class="p-4">
                <div class="small text-secondary">Zona Waktu Server</div>
                <div class="fw-bold mb-2">{{ config('app.timezone') }}</div>
                <p class="small text-secondary mb-0">Jam absensi & sistem mengikuti default platform. Pendaftar baru berstatus nonaktif sampai disetujui admin.</p>
            </div>
        </div>
    </div>
</div>

<div class="set-card mb-2">
    <div class="set-card-head d-flex justify-content-between align-items-center">
        <h2 class="set-card-title"><i class="bi bi-patch-question me-2 text-primary"></i>FAQ Admin Pusat</h2>
        <span class="badge rounded-pill bg-primary-subtle text-primary">Panduan</span>
    </div>
    <div class="p-4">
        <div class="accordion accordion-flush" id="pusatFaq">
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#fq1">Bagaimana membuka pendaftaran untuk sebuah sekolah?</button></h2>
                <div id="fq1" class="accordion-collapse collapse" data-bs-parent="#pusatFaq"><div class="accordion-body">Buka <b>Sekolah → Detail → panel Pendaftaran</b>, lalu tekan <b>Buka</b> untuk Guru/Siswa. Bisa juga dari tabel di atas lewat tombol <b>Atur</b>. Setelah dibuka, admin sekolah tersebut ikut bisa membuka/menutupnya sendiri.</div></div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#fq2">Siapa yang menyetujui akun pendaftar baru?</button></h2>
                <div id="fq2" class="accordion-collapse collapse" data-bs-parent="#pusatFaq"><div class="accordion-body">Pendaftar masuk sebagai <b>Menunggu Persetujuan</b>. Admin sekolah menyetujui via <b>Akun Pengguna</b>, Admin Pusat bisa menyetujui semua sekolah via <b>Akun Pengguna → pilih sekolah → Detail</b>.</div></div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#fq3">Sekolah belum punya admin, bagaimana?</button></h2>
                <div id="fq3" class="accordion-collapse collapse" data-bs-parent="#pusatFaq"><div class="accordion-body">Buatkan akunnya di <b>Admin Sekolah → Tambah Admin</b>. Selama belum ada admin, hanya Admin Pusat yang bisa mengatur pendaftaran & menyetujui akun sekolah itu.</div></div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#fq4">Bagaimana cara kerja override fitur per sekolah?</button></h2>
                <div id="fq4" class="accordion-collapse collapse" data-bs-parent="#pusatFaq"><div class="accordion-body">Buka <b>Fitur → pilih sekolah</b>, toggle fitur yang diinginkan. Kartu bertanda biru berarti khusus sekolah itu. Tombol <b>Reset</b> menghapus override sehingga kembali ikut default global.</div></div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#fq5">Mengapa menu admin sekolah hilang (SPP/Jadwal/Perpus/Eskul)?</button></h2>
                <div id="fq5" class="accordion-collapse collapse" data-bs-parent="#pusatFaq"><div class="accordion-body">Fitur tersebut <b>dinonaktifkan untuk sekolah itu</b>. Aktifkan lagi di <b>Fitur → pilih sekolah → toggle On</b>, menu web sekolah akan muncul otomatis.</div></div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#fq6">Di mana pengaturan Absensi Vermuk?</button></h2>
                <div id="fq6" class="accordion-collapse collapse" data-bs-parent="#pusatFaq"><div class="accordion-body">Absensi mengikuti <b>default platform</b> (zona {{ config('app.timezone') }}) dan tidak lagi diatur per halaman. Jika ada sekolah butuh jadwal khusus, hubungi tim teknis pengembang.</div></div>
            </div>
        </div>
    </div>
</div>
@endif

</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}
@endsection
