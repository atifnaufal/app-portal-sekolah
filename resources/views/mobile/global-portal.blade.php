@php $hideNav = false; $title='Global Portal'; $myId = (int) session('user_id'); $isAdminMb = session('user_role')==='admin'; @endphp
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
  <div class="ig-header" style="margin-top:42px;justify-content:center">
    <div class="ig-logo">Global Portal</div>
    <a href="{{ route('global.portal') }}" style="position:absolute;right:14px;color:#262626"><i class="bi bi-heart" style="font-size:22px"></i><span style="position:absolute;top:-6px;right:-6px;background:#ed4956;color:#fff;font-size:9px;font-weight:800;padding:2px 5px;border-radius:999px">{{ $posts->total() >99?'99+':$posts->total() }}</span></a>
  </div>

  <div class="ig-stories">
    <div class="ig-story">
      <form method="POST" action="{{ route('global.portal.story.store') }}" enctype="multipart/form-data" id="storyForm">@csrf
        <label class="ig-ring add" style="cursor:pointer" title="Tambah cerita">
          <img src="{{ $me?->avatar_url ?? asset('logo_sekolah.png') }}" style="width:60px;height:60px;border-radius:50%;object-fit:cover">
          <span class="ig-plus"><i class="bi bi-plus-lg"></i></span>
          <input type="file" name="image" accept="image/*" hidden onchange="document.getElementById('storyForm').submit()">
        </label>
      </form>
      <div class="ig-name">Cerita Anda</div>
    </div>
    @foreach(($storiesGrouped ?? collect()) as $uid => $st)
    <div class="ig-story" onclick="openStory({{ $st->id }})">
      <div class="ig-ring"><img src="{{ \App\Services\FirebaseStorageService::url($st->image) }}" alt=""></div>
      <div class="ig-name">{{ $uid == $myId ? 'Anda' : explode(' ', strtolower($st->user->name))[0] }}</div>
    </div>
    @endforeach
  </div>

  {{-- Story viewer fullscreen --}}
  <div id="storyViewer" style="display:none;position:fixed;inset:0;z-index:5000;background:#000;">
    <div id="storyProgress" style="position:absolute;top:calc(10px + env(safe-area-inset-top));left:12px;right:12px;height:3px;background:rgba(255,255,255,.3);border-radius:99px;overflow:hidden;"><div id="storyBar" style="height:100%;width:0;background:#fff;"></div></div>
    <div style="position:absolute;top:calc(24px + env(safe-area-inset-top));left:12px;right:12px;display:flex;align-items:center;gap:10px;z-index:2;">
      <img id="storyAvatar" src="" style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
      <div style="flex:1;"><div id="storyUser" style="color:#fff;font-size:13px;font-weight:700;"></div><div id="storyTime" style="color:rgba(255,255,255,.6);font-size:11px;"></div></div>
      <button onclick="closeStory()" style="background:none;border:0;color:#fff;font-size:22px;"><i class="bi bi-x-lg"></i></button>
    </div>
    <img id="storyImg" src="" style="width:100%;height:100%;object-fit:contain;">
    <div id="storyCaption" style="position:absolute;bottom:calc(24px + env(safe-area-inset-bottom));left:16px;right:16px;color:#fff;font-size:14px;text-align:center;text-shadow:0 1px 8px rgba(0,0,0,.6);"></div>
  </div>
  <script>
    var storyData = @json(($storiesGrouped ?? collect())->map(fn($st) => ['id' => $st->id, 'img' => \App\Services\FirebaseStorageService::url($st->image), 'user' => $st->user->name, 'avatar' => $st->user->avatar_url, 'time' => $st->created_at->diffForHumans(), 'caption' => $st->caption])->values());
    var storyTimer = null, storyList = [], storyIdx = 0;
    function openStory(id) {
      storyList = storyData; storyIdx = Math.max(0, storyList.findIndex(s => s.id === id));
      document.getElementById('storyViewer').style.display = 'block';
      document.body.style.overflow = 'hidden';
      showStory();
    }
    function showStory() {
      var s = storyList[storyIdx]; if (!s) return closeStory();
      document.getElementById('storyImg').src = s.img;
      document.getElementById('storyAvatar').src = s.avatar;
      document.getElementById('storyUser').innerText = s.user;
      document.getElementById('storyTime').innerText = s.time;
      document.getElementById('storyCaption').innerText = s.caption || '';
      var bar = document.getElementById('storyBar');
      bar.style.transition = 'none'; bar.style.width = '0';
      void bar.offsetWidth;
      bar.style.transition = 'width 5s linear'; bar.style.width = '100%';
      clearTimeout(storyTimer);
      storyTimer = setTimeout(function () { storyIdx++; showStory(); }, 5000);
    }
    function closeStory() {
      clearTimeout(storyTimer);
      document.getElementById('storyViewer').style.display = 'none';
      document.body.style.overflow = '';
    }
  </script>

  <div id="composer" class="ig-composer">
    <form method="POST" action="{{ route('global.portal.store') }}" enctype="multipart/form-data">
      @csrf
      <div style="display:flex;gap:10px;align-items:center;margin-bottom:8px">
        <img src="{{ $me?->avatar_url ?? asset('logo_sekolah.png') }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
        <div style="font-size:13px;font-weight:700">{{ $me?->name ?? session('admin_name') }}</div>
        <span style="margin-left:auto;font-size:10px;font-weight:700;color:#0095f6;background:#eef6ff;padding:5px 10px;border-radius:99px;">{{ $me?->school?->name ?? 'Umum' }} • otomatis</span>
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
    <a href="{{ route('global.portal.profile',$p->user) }}" class="ig-head" style="text-decoration:none;color:inherit">
      <div class="ig-avatar"><img src="{{ $p->user->avatar_url }}" alt=""></div>
      <div class="ig-meta">
        <div class="ig-user">{{ $p->user->name }} @if($p->user->isOnline())<span style="width:6px;height:6px;background:#22c55e;border-radius:50%;display:inline-block"></span>@endif <span style="font-size:11px;color:#0095f6">• {{ $p->school->name ?? $p->user->school->name ?? 'Umum' }}</span></div>
        <div class="ig-sub">{{ $p->created_at->diffForHumans() }} • {{ $p->user->followers->count() }} followers • {{ $p->created_at->translatedFormat('d M Y') }}</div>
      </div>
      <i class="bi bi-three-dots"></i>
    </a>
    <div style="padding:0 14px 8px;font-size:13px;white-space:pre-wrap">{{ $p->content }}</div>
    @if($p->image)<img src="{{ \App\Services\FirebaseStorageService::url($p->image) }}" class="ig-img" alt="">@endif
    <div class="ig-actions" style="gap:16px">
      @php $liked = $p->likes->contains('user_id', session('user_id')); @endphp
      <form method="POST" action="{{ route('global.portal.like',$p) }}" style="display:flex;align-items:center;gap:4px">@csrf<button style="background:none;border:0;display:flex;align-items:center;gap:4px"><i class="bi {{ $liked?'bi-heart-fill ig-like':'bi-heart' }}"></i><span style="font-size:12px;font-weight:700">{{ $p->likes_count }}</span></button><span style="font-size:11px;color:#8e8e8e">pesan</span></form>
      <a href="#cmt-{{ $p->id }}" style="color:#262626;display:flex;align-items:center;gap:4px;text-decoration:none"><i class="bi bi-chat"></i><span style="font-size:12px;font-weight:700">{{ $p->comments_count }}</span></a>
      @if(session('user_role')!=='admin')
      <a href="{{ route('chat.startPrivate',$p->user) }}" style="color:#262626;display:flex;align-items:center;gap:4px;text-decoration:none"><i class="bi bi-send"></i><span style="font-size:11px;font-weight:700">pesan</span></a>
      @endif
      @if(session('user_role')!=='admin' && $p->user_id !== $myId)
      <form method="POST" action="{{ route('global.portal.report',$p) }}" onsubmit="return confirm('Laporkan postingan ini?')">@csrf<button style="background:none;border:0;color:#8e8e8e;"><i class="bi bi-flag"></i></button></form>
      @endif
      <span style="margin-left:auto" onclick="navigator.share?navigator.share({text:@json($p->content)}):alert('Link disalin')"><i class="bi bi-share"></i></span>
    </div>
    <div style="padding:0 14px;display:flex;gap:12px;font-size:11px;color:#8e8e8e"><a href="#" onclick="event.preventDefault();document.getElementById('likes-{{ $p->id }}').style.display='block'" style="color:#262626;text-decoration:none"><b>{{ $p->likes_count }} suka</b> — lihat</a> • <a href="#cmt-{{ $p->id }}" style="color:#262626;text-decoration:none">{{ $p->comments_count }} komentar — lihat</a></div>
    <div id="likes-{{ $p->id }}" style="display:none;padding:8px 14px;background:#fafafa;border-top:1px solid #efefef">
      <div style="font-size:11px;font-weight:700">Disukai oleh</div>
      @foreach($p->likes->take(5) as $l)<div style="font-size:12px">{{ $l->user_id }} • user</div>@endforeach
      <div style="font-size:11px;color:#0095f6;cursor:pointer" onclick="this.parentElement.style.display='none'">Tutup</div>
    </div>
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
