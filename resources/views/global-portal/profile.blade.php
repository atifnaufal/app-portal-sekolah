{{-- Profil pengguna Global Portal versi WEB DESKTOP. --}}
@extends('layouts.app', ['title' => $profileUser->name . ' | Global Portal'])
@section('content')
@php $isAdmin = session('user_role') === 'admin'; $myId = (int) session('user_id'); $isSelf = $myId === (int) $profileUser->id; @endphp
<style>
    .gp-card { border-radius: 20px; border: 1px solid var(--border); background: #fff; box-shadow: var(--shadow); overflow: hidden; }
    .gp-cover { height: 140px; background: linear-gradient(135deg, var(--navy) 0%, #1e293b 60%, #1d4ed8 100%); position: relative; }
    .gp-avatar-lg { width: 88px; height: 88px; border-radius: 28px; object-fit: cover; border: 4px solid #fff; box-shadow: var(--shadow); margin-top: -44px; }
    .gp-stat { text-align: center; }
    .gp-stat .num { font-size: 20px; font-weight: 800; color: var(--navy); }
    .gp-stat .lb { font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
    .gp-post { padding: 20px 24px; }
    .gp-post + .gp-post { border-top: 1px solid #f1f5f9; }
</style>

<a href="{{ route('global.portal') }}" class="btn btn-sm btn-outline-secondary mb-3" style="border-radius:10px;"><i class="bi bi-arrow-left me-1"></i> Kembali ke Portal</a>

<div class="gp-card mb-4">
    <div class="gp-cover"></div>
    <div class="p-4">
        <div class="d-flex gap-3 align-items-end flex-wrap">
            <img src="{{ $profileUser->avatar_url }}" class="gp-avatar-lg" alt="">
            <div class="flex-fill" style="min-width:200px;">
                <h3 class="fw-bold mb-0" style="letter-spacing:-.02em;">{{ $profileUser->name }}
                    @if($profileUser->isOnline())<span class="badge rounded-pill bg-success" style="font-size:10px;">online</span>@endif
                </h3>
                <div class="text-muted small">{{ $profileUser->school->name ?? 'Umum' }} &bull; {{ ucfirst($profileUser->role) }}</div>
            </div>
            @if(!$isSelf && !$isAdmin)
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('global.portal.follow', $profileUser) }}">@csrf
                    <button class="btn btn-sm {{ $isFollowing ? 'btn-outline-secondary' : 'btn-primary' }}" style="border-radius:10px;">{{ $isFollowing ? 'Mengikuti' : 'Ikuti' }}</button>
                </form>
                <a href="{{ route('chat.startPrivate', $profileUser) }}" class="btn btn-sm btn-light border" style="border-radius:10px;"><i class="bi bi-send me-1"></i> Pesan</a>
            </div>
            @endif
        </div>
        <div class="d-flex gap-4 mt-3 pt-3 border-top">
            <div class="gp-stat"><div class="num">{{ $profileUser->followers->count() }}</div><div class="lb">Followers</div></div>
            <div class="gp-stat"><div class="num">{{ $profileUser->following->count() }}</div><div class="lb">Following</div></div>
            <div class="gp-stat"><div class="num">{{ $posts->total() }}</div><div class="lb">Postingan</div></div>
        </div>
    </div>
</div>

<div class="gp-card">
    <div class="p-4 border-bottom d-flex justify-content-between align-items-center"><h6 class="fw-bold mb-0"><i class="bi bi-grid me-2 text-primary"></i>Galeri Postingan</h6><span class="badge rounded-pill bg-primary-subtle text-primary">{{ $posts->total() }} post</span></div>
    @if($posts->count())
    <div class="p-3">
        <div class="row g-2">
            @foreach($posts as $p)
            <div class="col-4">
                <div class="position-relative rounded-4 overflow-hidden" style="aspect-ratio:1/1;background:linear-gradient(135deg,#f1f5f9,#e2e8f0);">
                    @if($p->image)
                        <img src="{{ \App\Services\FirebaseStorageService::url($p->image) }}" style="width:100%;height:100%;object-fit:cover;" alt="">
                    @else
                        <div class="w-100 h-100 d-grid p-3" style="place-items:center;">
                            <div class="small fw-semibold text-dark" style="line-height:1.5;display:-webkit-box;-webkit-line-clamp:5;-webkit-box-orient:vertical;overflow:hidden;">{{ \Illuminate\Support\Str::limit($p->content, 110) }}</div>
                        </div>
                    @endif
                    <div class="position-absolute bottom-0 start-0 end-0 d-flex gap-3 px-3 py-2 text-white small fw-bold" style="background:linear-gradient(transparent,rgba(0,0,0,.6));">
                        <span><i class="bi bi-heart-fill me-1"></i>{{ $p->likes_count }}</span>
                        <span><i class="bi bi-chat-fill me-1"></i>{{ $p->comments_count }}</span>
                        <span class="ms-auto" style="font-weight:400;opacity:.8;">{{ $p->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="text-center py-5 text-muted small">Belum ada postingan.</div>
    @endif
    @if($posts->hasPages())
    <div class="p-3 border-top">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
