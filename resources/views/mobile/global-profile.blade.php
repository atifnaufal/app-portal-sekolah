@php $hideNav=false; @endphp
@extends('layouts.mobile-app')
@section('content')
<style>
.gp-card{background:#fff;border:1px solid #efefef;border-radius:22px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,.05);margin:14px}
</style>
<div style="max-width:640px;margin:0 auto;padding:0 14px 100px">
  <a href="{{ route('global.portal') }}" style="display:inline-flex;gap:6px;align-items:center;margin:14px 0 8px;padding:8px 12px;background:rgba(255,255,255,.9);border:1px solid #efefef;border-radius:12px;text-decoration:none;color:#0f172a;font-weight:700"><i class="bi bi-arrow-left"></i> Kembali Portal</a>
  <div class="gp-card" style="padding:18px;text-align:center">
    <img src="{{ $profileUser->avatar_url }}" style="width:84px;height:84px;border-radius:50%;object-fit:cover;border:3px solid #fff;box-shadow:0 8px 24px rgba(0,0,0,.12)">
    <div style="margin-top:10px;font-size:18px;font-weight:900">{{ $profileUser->name }}</div>
    <div style="font-size:11px;color:#8e8e8e">{{ $profileUser->school->name ?? 'Umum' }} • {{ $profileUser->role }}</div>
    <div style="display:flex;gap:16px;justify-content:center;margin-top:12px">
      <div><div style="font-weight:900">{{ $profileUser->followers->count() }}</div><div style="font-size:11px;color:#8e8e8e">Followers</div></div>
      <div><div style="font-weight:900">{{ $profileUser->following->count() }}</div><div style="font-size:11px;color:#8e8e8e">Following</div></div>
      <div><div style="font-weight:900">{{ $posts->total() }}</div><div style="font-size:11px;color:#8e8e8e">Post</div></div>
    </div>
    <div style="display:flex;gap:8px;justify-content:center;margin-top:14px">
      @if(session('user_id')!=$profileUser->id && session('user_role')!=='admin')
        <form method="POST" action="{{ route('global.portal.follow',$profileUser) }}">@csrf<button class="btn" style="background:{{ $isFollowing?'#fff':'#0095f6' }};color:{{ $isFollowing?'#262626':'#fff' }};border:1px solid #dbdbdb;padding:8px 16px;border-radius:10px;font-weight:700">{{ $isFollowing?'Mengikuti':'Ikuti' }}</button></form>
        <a href="{{ route('chat.startPrivate',$profileUser) }}" class="btn" style="background:#efefef;color:#262626;padding:8px 16px;border-radius:10px;font-weight:700;text-decoration:none"><i class="bi bi-send"></i> Pesan</a>
      @endif
    </div>
  </div>
  <div class="gp-card" style="padding:14px">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
      <div style="font-weight:800;font-size:13px;">Galeri Postingan</div>
      <span style="font-size:10px;font-weight:800;color:#0095f6;background:#eef6ff;padding:3px 10px;border-radius:99px;">{{ $posts->total() }} post</span>
    </div>
    @if($posts->count())
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:3px;">
      @foreach($posts as $p)
      <div style="position:relative;aspect-ratio:1/1;border-radius:10px;overflow:hidden;background:linear-gradient(135deg,#f1f5f9,#e2e8f0);">
        @if($p->image)
        <img src="{{ \App\Services\FirebaseStorageService::url($p->image) }}" style="width:100%;height:100%;object-fit:cover;" alt="">
        @else
        <div style="width:100%;height:100%;display:grid;place-items:center;padding:8px;">
          <div style="font-size:10px;font-weight:600;color:#475569;line-height:1.4;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden;">{{ \Illuminate\Support\Str::limit($p->content, 70) }}</div>
        </div>
        @endif
        <div style="position:absolute;inset:auto 0 0 0;padding:10px 6px 6px;background:linear-gradient(transparent,rgba(0,0,0,.55));display:flex;gap:8px;align-items:center;color:#fff;font-size:10px;font-weight:800;">
          <span><i class="bi bi-heart-fill"></i> {{ $p->likes_count }}</span>
          <span><i class="bi bi-chat-fill"></i> {{ $p->comments_count }}</span>
        </div>
      </div>
      @endforeach
    </div>
    @else
      <div style="text-align:center;color:#8e8e8e;padding:20px">Belum ada post</div>
    @endif
    <div style="margin-top:10px">{{ $posts->links() }}</div>
  </div>
</div>
@endsection
