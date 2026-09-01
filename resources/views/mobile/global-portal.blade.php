@php $hideNav = false; $title='Global Portal'; @endphp
@extends('layouts.mobile-app')
@section('content')
<style>
.gp-page{max-width:640px;margin:0 auto;padding:0 14px 110px}
.gp-hero{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 55%,#7c3aed 100%);border-radius:28px;padding:22px 18px;color:#fff;position:relative;overflow:hidden;box-shadow:0 14px 40px rgba(15,23,42,.18)}
.gp-hero::after{content:'';position:absolute;top:-40px;right:-30px;width:160px;height:160px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.14),transparent 70%)}
.gp-hero > *{position:relative;z-index:1}
.gp-composer{background:#fff;border:1px solid rgba(15,23,42,.07);border-radius:22px;padding:14px;box-shadow:0 12px 30px rgba(15,23,42,.06);margin:14px 0}
.gp-textarea{width:100%;border:1.5px solid #e2e8f0;border-radius:14px;padding:12px 14px;font-size:13.5px;outline:none;resize:none}
.gp-textarea:focus{border-color:#6366f1;box-shadow:0 0 0 4px rgba(99,102,241,.12)}
.gp-card{background:#fff;border:1px solid rgba(15,23,42,.07);border-radius:22px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,.05);margin-bottom:14px}
.gp-head{display:flex;gap:12px;align-items:center;padding:14px 14px 0}
.gp-avatar{width:44px;height:44px;border-radius:14px;overflow:hidden;flex-shrink:0;position:relative;background:linear-gradient(135deg,#6366f1,#2563eb);display:grid;place-items:center;color:#fff;font-weight:800}
.gp-avatar img{width:100%;height:100%;object-fit:cover}
.gp-dot{position:absolute;bottom:-2px;right:-2px;width:12px;height:12px;border-radius:50%;background:#cbd5e1;border:2px solid #fff}
.gp-dot.online{background:#22c55e;box-shadow:0 0 0 4px rgba(34,197,94,.18)}
.gp-school{font-size:10px;font-weight:800;padding:4px 8px;border-radius:999px;background:#eef2ff;color:#4f46e5;border:1px solid #e0e7ff}
.gp-actions{display:flex;gap:8px;padding:10px 14px;border-top:1px solid #f1f5f9;align-items:center}
.gp-btn{flex:1;display:flex;gap:6px;align-items:center;justify-content:center;padding:9px;border-radius:12px;border:1px solid #e2e8f0;background:#f8fafc;font-size:13px;font-weight:700;color:#0f172a;text-decoration:none}
.gp-btn.liked{background:#fef2f2;color:#dc2626;border-color:#fecaca}
.gp-comments{padding:0 14px 14px}
.gp-cmt{display:flex;gap:8px;margin-top:8px}
.gp-cmt img{width:28px;height:28px;border-radius:50%;object-fit:cover}
.gp-cmt-bubble{background:#f8fafc;border:1px solid #eef2f7;border-radius:14px;padding:8px 10px;flex:1}
</style>
<div class="gp-page">
  <div class="gp-hero" style="margin-top:14px">
    <div style="display:flex;align-items:center;gap:12px">
      <div style="width:44px;height:44px;border-radius:14px;background:rgba(255,255,255,.15);display:grid;place-items:center;border:1px solid rgba(255,255,255,.2)"><i class="bi bi-globe2" style="font-size:20px"></i></div>
      <div><div style="font-size:11px;letter-spacing:.1em;opacity:.7;font-weight:700;text-transform:uppercase">Global Portal</div><div style="font-size:22px;font-weight:900;letter-spacing:-.02em">Sosmed Antar Sekolah</div></div>
      <div style="margin-left:auto;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);padding:6px 10px;border-radius:999px;font-size:11px;font-weight:800"><i class="bi bi-people-fill"></i> {{ $posts->total() }} Post</div>
    </div>
    <div style="margin-top:10px;font-size:12px;opacity:.85">Berbagi karya, prestasi & cerita — terhubung lintas sekolah se-Nusantara</div>
  </div>

  {{-- Composer --}}
  <form method="POST" action="{{ route('global.portal.store') }}" enctype="multipart/form-data" class="gp-composer">
    @csrf
    <div style="display:flex;gap:10px;align-items:center;margin-bottom:10px">
      <img src="{{ auth()->user()?->avatar_url ?? asset('logo_sekolah.png') }}" style="width:36px;height:36px;border-radius:12px;object-fit:cover">
      <div style="font-size:13px;font-weight:700">{{ auth()->user()?->name ?? session('admin_name') }}</div>
      <select name="school_id" style="margin-left:auto;padding:8px 10px;border:1px solid #e2e8f0;border-radius:10px;font-size:11px;font-weight:700">
        <option value="">Sekolah Saya</option>
        @foreach($schools as $s)<option value="{{ $s->id }}">{{ $s->name }} — {{ $s->city }}</option>@endforeach
      </select>
    </div>
    <textarea name="content" class="gp-textarea" rows="3" placeholder="Apa yang ingin kamu bagikan hari ini? (maks 2000 karakter)" required></textarea>
    <div style="display:flex;gap:8px;margin-top:10px;align-items:center">
      <label style="display:flex;gap:6px;align-items:center;padding:8px 12px;border:1px dashed #cbd5e1;border-radius:12px;font-size:12px;font-weight:700;cursor:pointer"><i class="bi bi-image"></i> Gambar <input type="file" name="image" accept="image/*" hidden onchange="this.nextElementSibling.textContent=this.files[0]?this.files[0].name:''"><span></span></label>
      <button class="pui-btn pui-btn-primary pui-btn-sm" style="margin-left:auto;border-radius:12px"><i class="bi bi-send-fill"></i> Posting</button>
    </div>
  </form>

  @forelse($posts as $p)
  <div class="gp-card">
    <div class="gp-head">
      <div class="gp-avatar">
        <img src="{{ $p->user->avatar_url }}" alt="">
        <span class="gp-dot {{ $p->user->isOnline() ? 'online' : '' }}"></span>
      </div>
      <div style="flex:1;min-width:0">
        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
          <div style="font-size:14px;font-weight:800">{{ $p->user->name }}</div>
          <span class="gp-school"><i class="bi bi-mortarboard"></i> {{ $p->school->name ?? $p->user->school->name ?? 'Umum' }}</span>
          @if($p->user->isOnline())<span style="font-size:10px;padding:3px 6px;border-radius:999px;background:#dcfce7;color:#166534;font-weight:800">ONLINE</span>@endif
        </div>
        <div style="font-size:11px;color:#64748b;font-weight:600;display:flex;gap:6px;align-items:center"><i class="bi bi-clock"></i> {{ $p->created_at->diffForHumans() }} • {{ $p->created_at->translatedFormat('d M Y H:i') }} @if($p->user->isOnline()) • <span style="color:#22c55e">online</span> @else • <span>{{ $p->user->last_seen }}</span>@endif</div>
      </div>
    </div>
    <div style="padding:12px 14px;font-size:13.5px;line-height:1.6;white-space:pre-wrap">{{ $p->content }}</div>
    @if($p->image)<img src="{{ \App\Services\FirebaseStorageService::url($p->image) }}" style="width:100%;max-height:420px;object-fit:cover;display:block">@endif
    <div style="display:flex;gap:12px;padding:8px 14px;font-size:11px;color:#64748b;font-weight:700"><span><i class="bi bi-heart-fill" style="color:#ef4444"></i> {{ $p->likes_count }} Suka</span><span><i class="bi bi-chat-fill" style="color:#6366f1"></i> {{ $p->comments_count }} Komentar</span></div>
    <div class="gp-actions">
      <form method="POST" action="{{ route('global.portal.like',$p) }}" style="flex:1">@csrf
        @php $liked = $p->likes->contains('user_id', session('user_id')); @endphp
        <button class="gp-btn {{ $liked?'liked':'' }}" style="width:100%"><i class="bi {{ $liked?'bi-heart-fill':'bi-heart' }}"></i> {{ $liked?'Disukai':'Suka' }}</button>
      </form>
      <a href="#cmt-{{ $p->id }}" class="gp-btn"><i class="bi bi-chat"></i> Komen</a>
      <button class="gp-btn" onclick="navigator.share?navigator.share({title:'Portal Global',text:@json($p->content),url:location.href}):(navigator.clipboard.writeText(location.href),alert('Link disalin'))"><i class="bi bi-share"></i> Bagikan</button>
    </div>
    <div class="gp-comments" id="cmt-{{ $p->id }}">
      @foreach($p->comments->take(3) as $c)
        <div class="gp-cmt"><img src="{{ $c->user->avatar_url }}"><div class="gp-cmt-bubble"><div style="font-size:12px;font-weight:800">{{ $c->user->name }} <span style="font-weight:600;color:#94a3b8">{{ $c->created_at->diffForHumans() }}</span></div><div style="font-size:12px">{{ $c->body }}</div></div></div>
      @endforeach
      <form method="POST" action="{{ route('global.portal.comment',$p) }}" style="display:flex;gap:8px;margin-top:10px">@csrf<input name="body" placeholder="Tulis komentar..." required style="flex:1;padding:10px 12px;border:1px solid #e2e8f0;border-radius:12px;font-size:12px"><button class="pui-btn pui-btn-primary pui-btn-sm" style="border-radius:12px">Kirim</button></form>
    </div>
  </div>
  @empty
    <div class="gp-card" style="padding:28px;text-align:center;color:#94a3b8"><i class="bi bi-globe" style="font-size:28px"></i><div style="margin-top:8px;font-weight:800">Belum ada post global</div><div style="font-size:12px">Jadilah yang pertama berbagi!</div></div>
  @endforelse

  <div style="margin-top:10px">{{ $posts->links() }}</div>
</div>
@endsection
