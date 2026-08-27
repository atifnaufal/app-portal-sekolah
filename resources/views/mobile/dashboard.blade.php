@extends('layouts.mobile-app')
@section('content')
<header class="mobile-hero" style="padding-top: 45px; border-radius: 0 0 45px 45px; box-shadow: 0 10px 30px rgba(20, 33, 61, 0.2);">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <div class="eyebrow" style="opacity: 0.9; letter-spacing: 2.5px; font-weight: 900; color: rgba(255,255,255,0.85);">PORTAL AKADEMIK</div>
            <div class="hero-title mt-1" style="font-size: 28px; color: #fff;">Halo, {{ $user->name }}!</div>
            <div class="mt-2">
                <span class="badge bg-white bg-opacity-20 rounded-pill px-3 py-2 fw-medium text-white" style="font-size: 11px; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.25); display: inline-flex; align-items: center; gap: 5px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M8.21 13.89L7 14.95 5.79 13.89C2.46 10.87 0 8.64 0 6A6 6 0 0 1 6 0a6 6 0 0 1 6 6c0 2.64-2.46 4.87-5.79 7.89zM11 6A5 5 0 0 0 1 6c0 2.11 2.21 4.09 5 6.63 2.79-2.54 5-4.52 5-6.63z"/><path d="M4.847 1.444c.125-.301.454-.533.89-.533.437 0 .766.232.89.533a.5.5 0 0 1-.923.385.18.18 0 0 0-.153-.1c-.066 0-.12.036-.153.1a.5.5 0 1 1-.923-.385zM7.5 7a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1 0-1h1a.5.5 0 0 1 .5.5z"/></svg>
                    {{ $user->kelas?->nama ?? 'Staf Sekolah' }}
                </span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('notifications.index') }}" class="btn bg-white bg-opacity-20 rounded-circle p-0 d-flex align-items-center justify-content-center position-relative" style="width: 48px; height: 48px; color: #fff; border: 1px solid rgba(255,255,255,0.25); backdrop-filter: blur(8px);">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917z"/></svg>
                @if($unreadNotificationsCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-2 border-white rounded-circle" style="box-shadow: 0 0 15px rgba(220, 53, 69, 0.6);"></span>
                @endif
            </a>
            <a href="{{ route('profile.show') }}" class="avatar border border-3 border-white border-opacity-30 shadow-sm" style="width: 52px; height: 52px; border-radius: 18px; background: rgba(255,255,255,0.2); backdrop-filter: blur(5px);">
                @if($user->foto)
                    <img src="{{ asset('storage/'.$user->foto) }}" alt="P" onerror="this.parentElement.innerHTML='<span class=\"h5 mb-0 fw-bold text-white\">{{ strtoupper(substr($user->name,0,1)) }}</span>'" style="object-position:{{ $user->foto_posisi_x }}% {{ $user->foto_posisi_y }}%;">
                @else
                    <span class="h5 mb-0 fw-bold text-white">{{ strtoupper(substr($user->name,0,1)) }}</span>
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
                    <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 54px; height: 54px; border-radius: 18px; background: linear-gradient(135deg, #e0f2fe, #bae6fd); color: #0284c7;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/><path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.147-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.147-1.147a.5.5 0 0 1 .708 0z"/></svg>
                    </div>
                    <div class="h3 fw-bold mb-0" style="color: #0c4a6e; font-size: 24px;">{{ $tugas->count() }}</div>
                    <div class="fw-bold text-muted mt-1" style="font-size: 10px; letter-spacing: 1.2px;">TUGAS AKTIF</div>
                </div>
            </a>
        </div>
        <div class="col-6">
            <a href="{{ route('spp.index') }}" class="card mobile-card tap-card border-0 text-decoration-none text-dark h-100 shadow-sm" style="border-radius: 28px; background: #fff;">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 54px; height: 54px; border-radius: 18px; background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M1.5 2A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13zM1 3.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-1zm0 4a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm7 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-7 3a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13a.5.5 0 0 1-.5-.5z"/></svg>
                    </div>
                    <div class="h3 fw-bold mb-0" style="color: #78350f; font-size: 24px;">SPP</div>
                    <div class="fw-bold text-muted mt-1" style="font-size: 10px; letter-spacing: 1.2px;">PEMBAYARAN</div>
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
        <h2 class="section-title mb-0" style="font-size: 19px; color: #14213d; font-weight: 800;">Pengumuman Terkini</h2>
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
                        <span class="badge rounded-pill px-3 py-1" style="background: #e0f2fe; color: #0284c7; font-size: 10px; font-weight: 800; letter-spacing: 0.5px;">INFO</span>
                        <div class="fw-bold text-muted" style="font-size: 10px;">{{ $item->tanggal_acara?->format('d M Y') ?? $item->created_at->format('d M Y') }}</div>
                    </div>
                    <h3 class="h6 fw-bold mb-2 ls-tight" style="color: #14213d; line-height: 1.5; font-size: 15px;">{{ $item->judul }}</h3>
                    <p class="small text-secondary mb-0 opacity-80" style="line-height: 1.7; font-size: 13px;">{{ \Illuminate\Support\Str::limit($item->isi, 95) }}</p>
                </div>
            </a>
        @empty
            <div class="card mobile-card border-0 shadow-sm py-5 text-center" style="border-radius: 28px; background: #fff;">
                <div class="opacity-40 py-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="#cbd5e1" viewBox="0 0 16 16"><path d="M13 2.5a1.5 1.5 0 0 1 3 0v11a1.5 1.5 0 0 1-3 0v-11zm-1 .5H3.5A1.5 1.5 0 0 0 2 4.5v7A1.5 1.5 0 0 0 3.5 13H12V3zm1 0v11h2v-11h-2zM5.5 6a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5z"/></svg>
                    <div class="small mt-3 fw-bold text-secondary">Belum ada pengumuman hari ini</div>
                </div>
            </div>
        @endforelse
    </div>
</main>
@endsection
