{{-- Global Portal versi WEB DESKTOP (layouts.app). Tampilan mobile (bottom tab bar)
     hanya dipakai di HP/APK via mobile.global-portal. --}}
@extends('layouts.app', ['title' => 'Global Portal'])
@section('content')
@php $isAdmin = session('user_role') === 'admin'; $myId = (int) session('user_id'); @endphp
<style>
    .gp-hero {
        background: linear-gradient(135deg, var(--navy) 0%, #1e293b 100%);
        border-radius: 24px; padding: 32px 36px; color: #fff;
        position: relative; overflow: hidden; margin-bottom: 24px;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
    }
    .gp-hero::after {
        content: ''; position: absolute; top: -70px; right: -70px;
        width: 220px; height: 220px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36,107,254,0.18) 0%, transparent 70%);
    }
    .gp-hero-title { font-size: 26px; font-weight: 800; letter-spacing: -0.02em; position: relative; z-index: 1; }
    .gp-hero-sub { font-size: 13px; color: #94a3b8; position: relative; z-index: 1; }
    .gp-card { border-radius: 20px; border: 1px solid var(--border); background: #fff; box-shadow: var(--shadow); overflow: hidden; }
    .gp-card-head { padding: 18px 24px; border-bottom: 1px solid var(--border); }
    .gp-card-title { font-size: 15px; font-weight: 800; color: var(--navy); margin: 0; }
    .gp-post { padding: 22px 24px; }
    .gp-post + .gp-post { border-top: 1px solid #f1f5f9; }
    .gp-avatar { width: 44px; height: 44px; border-radius: 14px; object-fit: cover; flex-shrink: 0; }
    .gp-action { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: var(--muted); background: none; border: 0; padding: 6px 10px; border-radius: 10px; text-decoration: none; }
    .gp-action:hover { background: #f1f5f9; color: var(--navy); }
    .gp-action.liked { color: #e11d48; }
    .gp-school-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; }
    .gp-school-row + .gp-school-row { border-top: 1px solid #f1f5f9; }
    .gp-school-ico { width: 38px; height: 38px; border-radius: 12px; display: grid; place-items: center; color: #fff; font-weight: 800; flex-shrink: 0; }
</style>

<div class="gp-hero">
    <div class="position-relative" style="z-index:1;">
        <div class="small fw-bold" style="letter-spacing:.1em;color:#94a3b8;">PORTAL ANTAR SEKOLAH</div>
        <h1 class="gp-hero-title">Global Portal</h1>
        <p class="gp-hero-sub mb-0">Bagikan kabar & berinteraksi dengan seluruh sekolah terdaftar.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Composer --}}
        <div class="gp-card mb-4">
            <div class="gp-card-head"><h2 class="gp-card-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Buat Postingan</h2></div>
            <div class="p-4">
                <form method="POST" action="{{ route('global.portal.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex gap-3 align-items-center mb-3">
                        <img src="{{ $me?->avatar_url ?? asset('logo_sekolah.png') }}" class="gp-avatar" alt="">
                        <div>
                            <div class="fw-bold" style="font-size:14px;">{{ $me?->name ?? session('admin_name') }}</div>
                            <div class="text-muted small">{{ $me?->school?->name ?? 'Admin Pusat' }}</div>
                        </div>
                        <select name="school_id" class="form-select form-select-sm ms-auto" style="max-width:200px;border-radius:10px;">
                            <option value="">Sekolah Saya</option>
                            @foreach($schools as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                        </select>
                    </div>
                    <textarea name="content" class="form-control mb-3" rows="3" placeholder="Apa yang ingin kamu bagikan hari ini?" required style="border-radius:14px;"></textarea>
                    <div class="d-flex align-items-center gap-3">
                        <label class="gp-action" style="cursor:pointer;color:var(--blue);"><i class="bi bi-image"></i> Gambar<input type="file" name="image" accept="image/*" hidden></label>
                        <button class="btn btn-primary ms-auto px-4" style="border-radius:12px;">Posting</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Feed --}}
        <div class="gp-card">
            <div class="gp-card-head d-flex justify-content-between align-items-center">
                <h2 class="gp-card-title"><i class="bi bi-globe me-2 text-primary"></i>Kabar Terbaru</h2>
                <span class="badge rounded-pill bg-primary-subtle text-primary">{{ $posts->total() }} postingan</span>
            </div>
            @forelse($posts as $p)
            @php $liked = $p->likes->contains('user_id', $myId); @endphp
            <div class="gp-post">
                <div class="d-flex gap-3 align-items-center mb-2">
                    <img src="{{ $p->user->avatar_url }}" class="gp-avatar" alt="">
                    <div class="flex-fill" style="min-width:0;">
                        <a href="{{ route('global.portal.profile', $p->user) }}" class="fw-bold text-dark text-decoration-none" style="font-size:14px;">
                            {{ $p->user->name }}
                            @if($p->user->isOnline())<span class="badge rounded-pill bg-success" style="font-size:9px;">online</span>@endif
                        </a>
                        <div class="text-muted small">{{ $p->school->name ?? $p->user->school->name ?? 'Umum' }} &bull; {{ $p->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                <div style="font-size:14px;white-space:pre-wrap;">{{ $p->content }}</div>
                @if($p->image)
                    <img src="{{ \App\Services\FirebaseStorageService::url($p->image) }}" class="img-fluid rounded-4 mt-3" style="max-height:420px;width:100%;object-fit:cover;" alt="">
                @endif
                <div class="d-flex align-items-center gap-1 mt-3 pt-2 border-top">
                    <form method="POST" action="{{ route('global.portal.like', $p) }}">@csrf
                        <button class="gp-action {{ $liked ? 'liked' : '' }}"><i class="bi {{ $liked ? 'bi-heart-fill' : 'bi-heart' }}"></i> {{ $p->likes_count }}</button>
                    </form>
                    <a href="#cmt-{{ $p->id }}" class="gp-action"><i class="bi bi-chat"></i> {{ $p->comments_count }}</a>
                    @if(!$isAdmin)
                        <a href="{{ route('chat.startPrivate', $p->user) }}" class="gp-action"><i class="bi bi-send"></i> Pesan</a>
                    @endif
                    <span class="text-muted small ms-auto">{{ $p->created_at->translatedFormat('d M Y') }}</span>
                </div>
                <div id="cmt-{{ $p->id }}" class="mt-2">
                    @foreach($p->comments->take(3) as $c)
                        <div class="small mb-1"><b>{{ $c->user->name }}</b> <span class="text-dark">{{ $c->body }}</span></div>
                    @endforeach
                    <form method="POST" action="{{ route('global.portal.comment', $p) }}" class="d-flex gap-2 mt-2">@csrf
                        <input name="body" class="form-control form-control-sm" placeholder="Tulis komentar..." required style="border-radius:10px;">
                        <button class="btn btn-sm btn-primary" style="border-radius:10px;">Kirim</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted"><i class="bi bi-images" style="font-size:32px;"></i><div class="mt-2 fw-bold">Belum ada postingan</div><div class="small">Jadilah yang pertama membagikan kabar.</div></div>
            @endforelse
            @if($posts->hasPages())
            <div class="p-3 border-top">{{ $posts->links() }}</div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="gp-card mb-4">
            <div class="gp-card-head"><h2 class="gp-card-title"><i class="bi bi-buildings-fill me-2 text-primary"></i>Sekolah Terdaftar ({{ $schools->count() }})</h2></div>
            <div class="px-4 py-2">
                @foreach($schools as $s)
                <div class="gp-school-row">
                    <div class="gp-school-ico" style="background:linear-gradient(135deg,#4f46e5,#2563eb);font-size:14px;">{{ strtoupper(substr($s->name, 0, 1)) }}</div>
                    <div class="flex-fill" style="min-width:0;">
                        <div class="fw-bold text-truncate" style="font-size:13px;">{{ $s->name }}</div>
                        <div class="text-muted" style="font-size:11px;">{{ $s->city ?? '-' }} &bull; {{ $s->slug }}</div>
                    </div>
                    <span class="badge rounded-pill {{ $s->is_active ? 'bg-success' : 'bg-danger' }}" style="font-size:10px;">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="gp-card">
            <div class="gp-card-head"><h2 class="gp-card-title"><i class="bi bi-info-circle me-2 text-primary"></i>Tentang Portal</h2></div>
            <div class="p-4 small text-muted" style="line-height:1.7;">
                Global Portal menghubungkan seluruh sekolah dalam satu linimasa. Posting kabar, beri suka, dan diskusi lewat komentar.
                @if($isAdmin)
                <br><br><span class="badge bg-primary-subtle text-primary">Mode Admin: like & komentar aktif, chat privat dinonaktifkan.</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
