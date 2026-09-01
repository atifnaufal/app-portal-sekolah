@php $hideNav = false; $title='Global Portal'; @endphp
@extends('layouts.mobile-app')
@section('content')
<style>
.ig-page{max-width:640px;margin:0 auto;padding-bottom:110px;background:#fff}
.ig-back{position:fixed;top:calc(12px + env(safe-area-inset-top));left:12px;z-index:3000;width:38px;height:38px;border-radius:12px;background:rgba(255,255,255,.9);backdrop-filter:blur(12px);border:1px solid rgba(15,23,42,.08);display:grid;place-items:center;color:#0f172a;box-shadow:0 8px 24px rgba(15,23,42,.12);text-decoration:none}
.ig-header{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.92);backdrop-filter:blur(12px);border-bottom:1px solid #efefef;display:flex;align-items:center;justify-content:space-between;padding:10px 14px;padding-top:calc(10px + env(safe-area-inset-top))}
.ig-logo{font-family:'Brush Script MT',cursive;font-size:26px;font-weight:800;letter-spacing:-.02em}
.ig-stories{display:flex;gap:12px;overflow-x:auto;padding:12px 14px;border-bottom:1px solid #efefef;scrollbar-width:none}
.ig-stories::-webkit-scrollbar{display:none}
.ig-story{flex-shrink:0;text-align:center;width:66px}
.ig-ring{width:66px;height:66px;border-radius:50%;padding:3px;background:linear-gradient(45deg,#feda75,#fa7e1e,#d62976,#962fbf,#4f5bd5)}
.ig-ring img{width:100%;height:100%;border-radius:50%;object-fit:cover;border:3px solid #fff;display:block}
.ig-ring.add{position:relative;background:#fff;border:2px dashed #dbdbdb;padding:0;display:grid;place-items:center}
.ig-plus{position:absolute;bottom:-2px;right:-2px;width:20px;height:20px;border-radius:50%;background:#0095f6;color:#fff;display:grid;place-items:center;border:2px solid #fff;font-size:12px}
.ig-name{font-size:11px;margin-top:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ig-card{border-bottom:1px solid #efefef;background:#fff}
.ig-head{display:flex;align-items:center;gap:10px;padding:10px 14px}
.ig-avatar{width:32px;height:32px;border-radius:50%;overflow:hidden;flex-shrink:0;background:linear-gradient(45deg,#feda75,#fa7e1e);padding:2px}
.ig-avatar img{width:100%;height:100%;border-radius:50%;object-fit:cover;border:2px solid #fff}
.ig-meta{flex:1;min-width:0}
.ig-user{font-size:13px;font-weight:700;display:flex;gap:4px;align-items:center}
.ig-sub{font-size:11px;color:#8e8e8e}
.ig-img{width:100%;aspect-ratio:1/1;object-fit:cover;background:#fafafa;display:block}
.ig-actions{display:flex;gap:14px;padding:10px 14px;align-items:center}
.ig-actions i{font-size:22px;cursor:pointer}
.ig-like{color:#ed4956}
.ig-count{padding:0 14px;font-size:13px;font-weight:700}
.ig-caption{padding:4px 14px;font-size:13px;line-height:1.4}
.ig-comments{padding:0 14px 10px}
.ig-cmt{font-size:13px}
.ig-time{font-size:10px;color:#8e8e8e;letter-spacing:.02em;text-transform:uppercase;padding:0 14px 10px}
.ig-composer{margin:12px 14px;background:#fff;border:1px solid #efefef;border-radius:16px;padding:12px}
.ig-textarea{width:100%;border:0;outline:0;resize:none;font-size:13px}
</style>
<a href="{{ route('dashboard') }}" class="ig-back" title="Beranda"><i class="bi bi-house-door-fill"></i></a>
<div class="ig-page">
  <div class="ig-header" style="margin-top:38px">
    <a href="#composer" style="width:28px;height:28px;border:1px solid #262626;border-radius:8px;display:grid;place-items:center;color:#262626;text-decoration:none"><i class="bi bi-plus-lg"></i></a>
    <div class="ig-logo">Global Portal</div>
    <a href="{{ route('global.portal') }}" style="position:relative;color:#262626"><i class="bi bi-heart" style="font-size:22px"></i><span style="position:absolute;top:-6px;right:-6px;background:#ed4956;color:#fff;font-size:9px;font-weight:800;padding:2px 5px;border-radius:999px">{{ $posts->total() >99?'99+':$posts->total() }}</span></a>
  </div>

  <div class="ig-stories">
    <div class="ig-story">
      <div class="ig-ring add" onclick="document.getElementById('composer').scrollIntoView({behavior:'smooth'})">
        <img src="{{ auth()->user()?->avatar_url ?? asset('logo_sekolah.png') }}" style="width:60px;height:60px;border-radius:50%;object-fit:cover">
        <span class="ig-plus"><i class="bi bi-plus-lg"></i></span>
      </div>
      <div class="ig-name">Cerita Anda</div>
    </div>
    @foreach($schools as $s)
    <div class="ig-story">
      <div class="ig-ring"><img src="https://ui-avatars.com/api/?name={{ urlencode($s->name) }}&background=random&color=fff&size=128" alt=""></div>
      <div class="ig-name">{{ \Illuminate\Support\Str::limit(strtolower($s->slug),10,'') }}</div>
    </div>
    @endforeach
    @foreach($posts->take(4) as $p)
    <div class="ig-story">
      <div class="ig-ring"><img src="{{ $p->user->avatar_url }}" alt=""></div>
      <div class="ig-name">{{ explode(' ', strtolower($p->user->name))[0] }}</div>
    </div>
    @endforeach
  </div>

  <div id="composer" class="ig-composer">
    <form method="POST" action="{{ route('global.portal.store') }}" enctype="multipart/form-data">
      @csrf
      <div style="display:flex;gap:10px;align-items:center;margin-bottom:8px">
        <img src="{{ auth()->user()?->avatar_url ?? asset('logo_sekolah.png') }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
        <div style="font-size:13px;font-weight:700">{{ auth()->user()?->name ?? session('admin_name') }}</div>
        <select name="school_id" style="margin-left:auto;padding:6px 8px;border:1px solid #dbdbdb;border-radius:8px;font-size:11px">
          <option value="">Sekolah Saya</option>
          @foreach($schools as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
      </div>
      <textarea name="content" class="ig-textarea" rows="2" placeholder="Apa yang ingin kamu bagikan hari ini?" required></textarea>
      <div style="display:flex;gap:8px;margin-top:8px;align-items:center">
        <label style="display:flex;gap:6px;align-items:center;font-size:12px;color:#0095f6;font-weight:700;cursor:pointer"><i class="bi bi-image"></i> Gambar <input type="file" name="image" accept="image/*" hidden></label>
        <button class="btn" style="margin-left:auto;background:#0095f6;color:#fff;padding:8px 16px;border-radius:10px;font-weight:700;border:0">Posting</button>
      </div>
    </form>
  </div>

  @forelse($posts as $p)
  <div class="ig-card">
    <div class="ig-head">
      <div class="ig-avatar"><img src="{{ $p->user->avatar_url }}" alt=""></div>
      <div class="ig-meta">
        <div class="ig-user">{{ $p->user->name }} @if($p->user->isOnline())<span style="width:6px;height:6px;background:#22c55e;border-radius:50%;display:inline-block"></span>@endif <span style="font-size:11px;color:#0095f6">• {{ $p->school->name ?? $p->user->school->name ?? 'Umum' }}</span></div>
        <div class="ig-sub">{{ $p->created_at->diffForHumans() }} • {{ $p->created_at->translatedFormat('d M Y') }}</div>
      </div>
      <i class="bi bi-three-dots"></i>
    </div>
    <div style="padding:0 14px 8px;font-size:13px;white-space:pre-wrap">{{ $p->content }}</div>
    @if($p->image)<img src="{{ \App\Services\FirebaseStorageService::url($p->image) }}" class="ig-img" alt="">@endif
    <div class="ig-actions">
      @php $liked = $p->likes->contains('user_id', session('user_id')); @endphp
      <form method="POST" action="{{ route('global.portal.like',$p) }}" style="display:inline">@csrf<button style="background:none;border:0"><i class="bi {{ $liked?'bi-heart-fill ig-like':'bi-heart' }}"></i></button></form>
      <a href="#cmt-{{ $p->id }}" style="color:#262626"><i class="bi bi-chat"></i></a>
      <i class="bi bi-send" onclick="navigator.share?navigator.share({text:@json($p->content)}):alert('Link disalin')"></i>
      <span style="margin-left:auto"><i class="bi bi-bookmark"></i></span>
    </div>
    <div class="ig-count">{{ $p->likes_count }} suka</div>
    <div class="ig-caption"><b>{{ $p->user->name }}</b> {{ \Illuminate\Support\Str::limit($p->content,80) }}</div>
    <div class="ig-comments" id="cmt-{{ $p->id }}">
      @foreach($p->comments->take(2) as $c)<div class="ig-cmt"><b>{{ $c->user->name }}</b> {{ $c->body }}</div>@endforeach
      <form method="POST" action="{{ route('global.portal.comment',$p) }}" style="display:flex;gap:8px;margin-top:8px">@csrf<input name="body" placeholder="Tulis komentar..." required style="flex:1;border:0;font-size:13px;outline:0"><button style="background:none;border:0;color:#0095f6;font-weight:700;font-size:13px">Kirim</button></form>
    </div>
    <div class="ig-time">{{ $p->created_at->translatedFormat('d F Y') }}</div>
  </div>
  @empty
  <div style="padding:40px;text-align:center;color:#8e8e8e"><i class="bi bi-images" style="font-size:32px"></i><div style="margin-top:8px;font-weight:700">Belum ada postingan</div></div>
  @endforelse
  <div style="padding:12px 14px">{{ $posts->links() }}</div>
</div>
@endsection
