@extends('layouts.mobile-app')
@section('content')
<header class="mobile-hero" style="padding-top: 40px; border-radius: 0 0 40px 40px;">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <div class="eyebrow" style="opacity: 0.8; letter-spacing: 2px;">{{ strtoupper($user->role) }} SPACE</div>
            <div class="hero-title mt-1">Halo, {{ $user->name }}!</div>
            <div class="mt-2">
                <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-2 fw-normal" style="font-size: 11px;">
                    <i class="bi bi-mortarboard-fill me-1"></i> {{ $user->kelas?->nama ?? 'Staf Sekolah' }}
                </span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('notifications.index') }}" class="btn bg-white bg-opacity-25 rounded-circle p-0 d-flex align-items-center justify-content-center position-relative" style="width: 44px; height: 44px; color: #fff;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917z"/></svg>
                @if($unreadNotificationsCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 12px; height: 12px;"></span>
                @endif
            </a>
            <a href="{{ route('profile.show') }}" class="avatar border border-2 border-white border-opacity-50">
                @if($user->foto)
                    <img src="{{ asset('storage/'.$user->foto) }}" alt="P" style="object-position:{{ $user->foto_posisi_x }}% {{ $user->foto_posisi_y }}%;">
                @else
                    {{ strtoupper(substr($user->name,0,1)) }}
                @endif
            </a>
        </div>
    </div>
</header>

<main class="mobile-content" style="margin-top: -20px;">
    <div class="card mobile-card shadow-sm border-0 mb-4" style="border-radius: 25px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Aktivitas Belajar</h2>
                <span class="badge bg-light text-secondary rounded-pill fw-normal px-2">{{ now()->format('d M') }}</span>
            </div>

            <div class="row g-3 stagger">
                <div class="col-6">
                    <a href="{{ route('tugas.index') }}" class="card border-0 text-decoration-none text-dark h-100" style="background: #f0f7ff; border-radius: 20px;">
                        <div class="card-body p-3">
                            <div class="icon-box mb-2" style="background: #fff; width: 36px; height: 36px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/><path d="M11.354 4.854a.5.5 0 0 0-.708-.708L7.5 7.293 6.354 6.146a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3.5-3.5z"/></svg>
                            </div>
                            <div class="h5 fw-bold mb-0 text-primary">{{ $tugas->count() }}</div>
                            <div class="x-small text-muted fw-bold">TUGAS AKTIF</div>
                        </div>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('spp.index') }}" class="card border-0 text-decoration-none text-dark h-100" style="background: #fff9ed; border-radius: 20px;">
                        <div class="card-body p-3">
                            <div class="icon-box mb-2" style="background: #fff; width: 36px; height: 36px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#a66b00" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/></svg>
                            </div>
                            <div class="h5 fw-bold mb-0" style="color: #a66b00;">SPP</div>
                            <div class="x-small text-muted fw-bold">PEMBAYARAN</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($user->role === 'guru')
        <a href="{{ route('tugas.create') }}" class="btn btn-primary w-100 mb-4 shadow-sm py-3" style="border-radius: 18px; font-weight: 700; background: linear-gradient(to right, #246bfe, #1d59d4);">
            <i class="bi bi-plus-circle-fill me-2"></i> BUAT TUGAS BARU
        </a>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="section-title mb-0">Pengumuman Terkini</h2>
        <a href="{{ route('pengumuman.index') }}" class="badge bg-primary bg-opacity-10 text-primary text-decoration-none px-2 py-1">Lihat Semua</a>
    </div>

    <div class="stagger">
        @forelse($publicPengumumans as $item)
            <a href="{{ route('pengumuman.index') }}" class="card mobile-card tap-card text-decoration-none text-dark mb-3">
                @if($item->gambar)
                    <img src="{{ asset('storage/'.$item->gambar) }}" alt="{{ $item->judul }}" style="width:100%; height:160px; object-fit:cover;">
                @endif
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary rounded-pill x-small px-2">INFO</span>
                        <div class="x-small text-secondary fw-bold">{{ $item->tanggal_acara?->format('d M Y') ?? $item->created_at->format('d M Y') }}</div>
                    </div>
                    <h3 class="h6 fw-bold mb-2 ls-tight">{{ $item->judul }}</h3>
                    <p class="small text-secondary mb-0 opacity-75">{{ \Illuminate\Support\Str::limit($item->isi, 80) }}</p>
                </div>
            </a>
        @empty
            <div class="text-center py-5 opacity-50">
                <i class="bi bi-megaphone h1"></i>
                <div class="small mt-2">Belum ada pengumuman hari ini.</div>
            </div>
        @endforelse
    </div>
</main>
@endsection
