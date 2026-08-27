@extends('layouts.mobile-app')
@section('content')
<header class="mobile-hero" style="padding-top: 45px; border-radius: 0 0 45px 45px; box-shadow: 0 10px 30px rgba(20, 33, 61, 0.2);">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <div class="eyebrow" style="opacity: 0.9; letter-spacing: 2.5px; font-weight: 900;">PORTAL AKADEMIK</div>
            <div class="hero-title mt-1" style="font-size: 28px;">Halo, {{ $user->name }}!</div>
            <div class="mt-2">
                <span class="badge bg-white bg-opacity-20 rounded-pill px-3 py-2 fw-medium" style="font-size: 11px; backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.2);">
                    <i class="bi bi-mortarboard me-1"></i> {{ $user->kelas?->nama ?? 'Staf Sekolah' }}
                </span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('notifications.index') }}" class="btn bg-white bg-opacity-20 rounded-circle p-0 d-flex align-items-center justify-content-center position-relative" style="width: 48px; height: 48px; color: #fff; border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(5px);">
                <i class="bi bi-bell-fill h5 mb-0"></i>
                @if($unreadNotificationsCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-2 border-white rounded-circle" style="box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);"></span>
                @endif
            </a>
            <a href="{{ route('profile.show') }}" class="avatar border border-3 border-white border-opacity-30 shadow-sm" style="width: 52px; height: 52px; border-radius: 20px;">
                @if($user->foto)
                    <img src="{{ asset('storage/'.$user->foto) }}" alt="P" style="object-position:{{ $user->foto_posisi_x }}% {{ $user->foto_posisi_y }}%;">
                @else
                    <span class="h5 mb-0 fw-bold">{{ strtoupper(substr($user->name,0,1)) }}</span>
                @endif
            </a>
        </div>
    </div>
</header>

<main class="mobile-content" style="margin-top: -30px;">
    <!-- Widgets Grid -->
    <div class="row g-3 stagger mb-4">
        <div class="col-6">
            <a href="{{ route('tugas.index') }}" class="card mobile-card tap-card border-0 text-decoration-none text-dark h-100 shadow-sm" style="border-radius: 28px; background: #fff;">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px; border-radius: 18px; background: linear-gradient(135deg, #e0f2fe, #bae6fd); color: #0284c7;">
                        <i class="bi bi-journal-check h4 mb-0"></i>
                    </div>
                    <div class="h3 fw-bold mb-0" style="color: #0c4a6e;">{{ $tugas->count() }}</div>
                    <div class="fw-bold text-secondary" style="font-size: 10px; letter-spacing: 1px;">TUGAS AKTIF</div>
                </div>
            </a>
        </div>
        <div class="col-6">
            <a href="{{ route('spp.index') }}" class="card mobile-card tap-card border-0 text-decoration-none text-dark h-100 shadow-sm" style="border-radius: 28px; background: #fff;">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px; border-radius: 18px; background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706;">
                        <i class="bi bi-wallet2 h4 mb-0"></i>
                    </div>
                    <div class="h3 fw-bold mb-0" style="color: #78350f;">SPP</div>
                    <div class="fw-bold text-secondary" style="font-size: 10px; letter-spacing: 1px;">PEMBAYARAN</div>
                </div>
            </a>
        </div>
    </div>

    @if($user->role === 'guru')
        <div class="px-2">
            <a href="{{ route('tugas.create') }}" class="btn btn-primary w-100 mb-4 shadow py-3" style="border-radius: 22px; font-weight: 800; background: linear-gradient(135deg, #246bfe, #1d59d4); border: none;">
                <i class="bi bi-plus-circle-fill me-2"></i> BUAT TUGAS BARU
            </a>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
        <h2 class="section-title mb-0" style="font-size: 18px; color: #14213d;">Pengumuman Terkini</h2>
        <a href="{{ route('pengumuman.index') }}" class="btn btn-link text-decoration-none fw-bold p-0" style="font-size: 13px; color: #246bfe;">Lihat Semua</a>
    </div>

    <div class="stagger px-1">
        @forelse($publicPengumumans as $item)
            <a href="{{ route('pengumuman.index') }}" class="card mobile-card tap-card text-decoration-none text-dark mb-3 border-0 shadow-sm" style="border-radius: 25px; overflow: hidden;">
                @if($item->gambar)
                    <img src="{{ asset('storage/'.$item->gambar) }}" alt="{{ $item->judul }}" style="width:100%; height:180px; object-fit:cover;">
                @endif
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge rounded-pill px-3 py-1" style="background: #e0f2fe; color: #0284c7; font-size: 10px; font-weight: 700;">INFO</span>
                        <div class="fw-bold text-muted" style="font-size: 10px;">{{ $item->tanggal_acara?->format('d M Y') ?? $item->created_at->format('d M Y') }}</div>
                    </div>
                    <h3 class="h6 fw-bold mb-2 ls-tight" style="color: #14213d; line-height: 1.4;">{{ $item->judul }}</h3>
                    <p class="small text-secondary mb-0 opacity-80" style="line-height: 1.6;">{{ \Illuminate\Support\Str::limit($item->isi, 85) }}</p>
                </div>
            </a>
        @empty
            <div class="card mobile-card border-0 shadow-sm py-5 text-center" style="border-radius: 25px; background: rgba(255,255,255,0.5);">
                <div class="opacity-30">
                    <i class="bi bi-megaphone-fill display-4"></i>
                    <div class="small mt-2 fw-bold">Belum ada pengumuman hari ini</div>
                </div>
            </div>
        @endforelse
    </div>
</main>
@endsection
