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
    <div class="p-4 border-bottom"><h6 class="fw-bold mb-0"><i class="bi bi-grid me-2 text-primary"></i>Postingan {{ $profileUser->name }}</h6></div>
    @forelse($posts as $p)
    @php $liked = $p->likes->contains('user_id', $myId); @endphp
    <div class="gp-post">
        <div class="text-muted small mb-1">{{ $p->created_at->diffForHumans() }} &bull; {{ $p->school->name ?? '' }}</div>
        <div style="font-size:14px;white-space:pre-wrap;">{{ $p->content }}</div>
        @if($p->image)
            <img src="{{ \App\Services\FirebaseStorageService::url($p->image) }}" class="img-fluid rounded-4 mt-3" style="max-height:380px;width:100%;object-fit:cover;" alt="">
        @endif
        <div class="d-flex gap-3 mt-2 small fw-bold text-muted">
            <span><i class="bi bi-heart{{ $liked ? '-fill text-danger' : '' }}"></i> {{ $p->likes_count }}</span>
            <span><i class="bi bi-chat"></i> {{ $p->comments_count }}</span>
        </div>
    </div>
    @empty
    <div class="text-center py-5 text-muted small">Belum ada postingan.</div>
    @endforelse
    @if($posts->hasPages())
    <div class="p-3 border-top">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
