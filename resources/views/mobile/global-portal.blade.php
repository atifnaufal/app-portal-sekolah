@php $hideNav = false; $title='Global Portal'; $myId = (int) session('user_id'); $isAdminMb = session('user_role')==='admin'; @endphp
@extends('layouts.mobile-app')
@section('content')
<style>
.ig-page{max-width:640px;margin:0 auto;padding-bottom:110px;background:#fff}
.ig-header{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.92);backdrop-filter:blur(12px);border-bottom:1px solid #efefef;display:flex;align-items:center;gap:10px;padding:10px 14px;padding-top:calc(10px + env(safe-area-inset-top))}
.ig-backbtn{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;color:#0f172a;text-decoration:none;font-size:18px;flex-shrink:0}
.ig-logo{font-family:'Brush Script MT',cursive;font-size:24px;font-weight:800;letter-spacing:-.02em;flex:1;text-align:center}
.ig-stories{display:flex;gap:12px;overflow-x:auto;padding:12px 14px;border-bottom:1px solid #efefef;scrollbar-width:none}
.ig-stories::-webkit-scrollbar{display:none}
.ig-story{flex-shrink:0;text-align:center;width:66px}
.ig-ring{width:66px;height:66px;border-radius:50%;padding:3px;background:linear-gradient(45deg,#feda75,#fa7e1e,#d62976,#962fbf,#4f5bd5);transition:background .3s}
.ig-ring.seen{background:#d4d4d4}
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
<div class="ig-page">
  <div class="ig-header">
    <a href="{{ route('dashboard') }}" class="ig-backbtn" title="Kembali"><i class="bi bi-chevron-left"></i></a>
    <div class="ig-logo">Global Portal</div>
    <a href="{{ route('global.portal.activity') }}" style="position:relative;color:#262626;width:34px;text-align:center;"><i class="bi bi-heart" style="font-size:22px"></i>@if(($activityCount ?? 0) > 0)<span style="position:absolute;top:-6px;right:0;background:#ed4956;color:#fff;font-size:9px;font-weight:800;padding:2px 5px;border-radius:999px">{{ $activityCount > 99 ? '99+' : $activityCount }}</span>@endif</a>
  </div>

  <div class="ig-stories">
    <div class="ig-story">
      <form method="POST" action="{{ route('global.portal.story.store') }}" enctype="multipart/form-data" id="storyForm">@csrf
        <div class="ig-ring add" style="cursor:pointer" title="Tambah cerita" id="storyAddRing">
          <img src="{{ $me?->avatar_url ?? asset('logo_sekolah.png') }}" style="width:60px;height:60px;border-radius:50%;object-fit:cover;pointer-events:none;">
          <span class="ig-plus" style="pointer-events:none;"><i class="bi bi-plus-lg"></i></span>
          <input type="file" name="image" id="storyFile" accept="image/*" hidden onchange="document.getElementById('storyForm').submit()">
        </div>
      </form>
      <div class="ig-name">Cerita Anda</div>
    </div>
    @foreach(($storiesGrouped ?? collect()) as $uid => $st)
    <div class="ig-story" data-story-id="{{ $st->id }}">
      <div class="ig-ring story-ring" data-story-ring="{{ $st->id }}"><img src="{{ \App\Services\FirebaseStorageService::url($st->image) }}" alt=""></div>
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
      <button id="storyViewerClose" style="background:none;border:0;color:#fff;font-size:22px;"><i class="bi bi-x-lg"></i></button>
    </div>
    <img id="storyImg" src="" style="width:100%;height:100%;object-fit:contain;">
    <div id="storyCaption" style="position:absolute;bottom:calc(24px + env(safe-area-inset-bottom));left:16px;right:16px;color:#fff;font-size:14px;text-align:center;text-shadow:0 1px 8px rgba(0,0,0,.6);"></div>
  </div>
  <script>
    var storyData = {!! $storiesJson ?? '[]' !!};
    /* Cerita dilihat → ring abu-abu (tersimpan per perangkat). */
    var storyTimer = null, storyList = [], storyIdx = 0;
    function seenIds() {
      try { return JSON.parse(localStorage.getItem('seenStories') || '[]'); }
      catch (e) { return []; }
    }
    function paintSeenRings() {
      var seen = seenIds();
      document.querySelectorAll('[data-story-ring]').forEach(function (el) {
        if (seen.indexOf(parseInt(el.getAttribute('data-story-ring'), 10)) !== -1) el.classList.add('seen');
      });
    }
    function markStorySeen(id) {
      try {
        var seen = seenIds();
        if (seen.indexOf(id) === -1) { seen.push(id); localStorage.setItem('seenStories', JSON.stringify(seen.slice(-200))); }
      } catch (e) {}
      var el = document.querySelector('[data-story-ring="' + id + '"]');
      if (el) el.classList.add('seen');
    }
    paintSeenRings();
    function openStory(id) {
      storyList = storyData; storyIdx = Math.max(0, storyList.findIndex(s => s.id === id));
      document.getElementById('storyViewer').style.display = 'block';
      document.body.style.overflow = 'hidden';
      showStory();
    }
    function showStory() {
      var s = storyList[storyIdx]; if (!s) return closeStory();
      markStorySeen(s.id);
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
    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:12px;padding:10px 12px;font-size:12px;margin-bottom:10px;">
      <ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif    <form method="POST" action="{{ route('global.portal.store') }}" enctype="multipart/form-data">
      @csrf
      <div style="display:flex;gap:10px;align-items:center;margin-bottom:8px">
        <img src="{{ $me?->avatar_url ?? asset('logo_sekolah.png') }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
        <div style="font-size:13px;font-weight:700">{{ $me?->name ?? session('admin_name') }}</div>
        <span style="margin-left:auto;font-size:10px;font-weight:700;color:#0095f6;background:#eef6ff;padding:5px 10px;border-radius:99px;">{{ $me?->school?->name ?? 'Umum' }} • otomatis</span>
      </div>
      <textarea name="content" class="ig-textarea" rows="2" placeholder="Apa yang ingin kamu bagikan hari ini?" required></textarea>
      <div id="composerPreview" style="display:none;margin-top:8px;position:relative;border-radius:14px;overflow:hidden;border:1px solid #efefef;">
        <img id="composerPreviewImg" src="" style="width:100%;max-height:220px;object-fit:cover;display:block;" alt="">
        <div style="position:absolute;left:8px;bottom:8px;right:8px;display:flex;gap:8px;align-items:center;background:rgba(15,23,42,.65);backdrop-filter:blur(8px);border-radius:10px;padding:7px 10px;">
          <i class="bi bi-file-earmark-image" style="color:#fff;"></i>
          <span id="composerPreviewName" style="flex:1;font-size:11px;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
          <button type="button" id="composerPreviewRemove" style="background:rgba(255,255,255,.2);border:0;color:#fff;border-radius:8px;width:26px;height:26px;font-size:13px;">✕</button>
        </div>
      </div>
      <div style="display:flex;gap:8px;margin-top:8px;align-items:center">
        <label style="display:flex;gap:6px;align-items:center;font-size:12px;color:#0095f6;font-weight:700;cursor:pointer"><i class="bi bi-image"></i> Gambar <input type="file" name="image" id="composerFile" accept="image/*" hidden></label>
        <button class="btn" style="margin-left:auto;background:#0095f6;color:#fff;padding:8px 16px;border-radius:10px;font-weight:700;border:0">Posting</button>
      </div>
    </form>
  </div>

  @forelse($posts as $p)
  <div class="ig-card" data-post-id="{{ $p->id }}">
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
      <form method="POST" action="{{ route('global.portal.like',$p) }}" class="like-form" style="display:flex;align-items:center;gap:4px">@csrf<button style="background:none;border:0;display:flex;align-items:center;gap:4px"><i class="bi {{ $liked?'bi-heart-fill ig-like':'bi-heart' }}"></i><span class="like-count" style="font-size:12px;font-weight:700">{{ $p->likes_count }}</span></button><span style="font-size:11px;color:#8e8e8e">pesan</span></form>
      <a href="#cmt-{{ $p->id }}" style="color:#262626;display:flex;align-items:center;gap:4px;text-decoration:none"><i class="bi bi-chat"></i><span class="cmt-count" style="font-size:12px;font-weight:700">{{ $p->comments_count }}</span></a>
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
      <form method="POST" action="{{ route('global.portal.comment',$p) }}" class="comment-form" style="display:flex;gap:8px;margin-top:8px">@csrf<input name="body" placeholder="Tulis komentar..." required maxlength="500" style="flex:1;border:0;font-size:13px;outline:0"><button style="background:none;border:0;color:#0095f6;font-weight:700;font-size:13px">Kirim</button></form>
    </div>
    <div class="ig-time">{{ $p->created_at->translatedFormat('d F Y') }}</div>
  </div>
  @empty
  <div style="padding:40px;text-align:center;color:#8e8e8e"><i class="bi bi-images" style="font-size:32px"></i><div style="margin-top:8px;font-weight:700">Belum ada postingan</div></div>
  @endforelse
  {{-- Infinite scroll ala IG: sentinel + tombol muat (pengganti pagination mentah) --}}
  <div id="feedEnd" style="padding:20px 14px 8px;text-align:center;">
    <div id="feedLoader" style="display:none;color:#8e8e8e;font-size:13px;"><span class="spinner-border spinner-border-sm me-1"></span>Memuat...</div>
    <button id="feedMoreBtn" style="display:none;background:#f1f5f9;border:0;border-radius:99px;padding:10px 22px;font-size:13px;font-weight:800;color:#0f172a;">Muat Lebih Banyak</button>
    <div id="feedDone" style="display:none;color:#cbd5e1;font-size:12px;font-weight:700;">— Sudah paling bawah —</div>
  </div>
  <button id="newPostsPill" style="display:none;position:fixed;top:calc(64px + env(safe-area-inset-top));left:50%;transform:translateX(-50%);z-index:2500;background:#0f172a;color:#fff;border:0;border-radius:99px;padding:10px 20px;font-size:13px;font-weight:800;box-shadow:0 12px 30px rgba(15,23,42,.35);"><i class="bi bi-arrow-up-circle me-1"></i><span id="newPostsTxt">Postingan baru</span></button>
</div>
<script>
  /* Like AJAX: tetap di tempat, tanpa reload/scroll. */
  document.addEventListener('submit', function (e) {
    var f = e.target && e.target.closest ? e.target.closest('.like-form') : null;
    if (!f) return;
    e.preventDefault();
    var btn = f.querySelector('button');
    var icon = f.querySelector('i');
    var count = f.querySelector('.like-count');
    if (btn) btn.disabled = true;
    fetch(f.action, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(f)
    }).then(function (r) { return r.json(); }).then(function (d) {
      if (icon) {
        if (d.liked) { icon.classList.remove('bi-heart'); icon.classList.add('bi-heart-fill', 'ig-like'); }
        else { icon.classList.add('bi-heart'); icon.classList.remove('bi-heart-fill', 'ig-like'); }
      }
      if (count && typeof d.likes_count !== 'undefined') count.innerText = d.likes_count;
    }).catch(function () {
      f.submit(); // fallback: cara lama bila fetch gagal
    }).finally(function () { if (btn) btn.disabled = false; });
  });

  /* Komentar AJAX: muncul langsung + hitungan update, tanpa reload. */
  document.addEventListener('submit', function (e) {
    var f = e.target && e.target.closest ? e.target.closest('.comment-form') : null;
    if (!f) return;
    e.preventDefault();
    var input = f.querySelector('input[name=body]');
    var btn = f.querySelector('button');
    var text = input ? input.value.trim() : '';
    if (!text) return;
    if (btn) btn.disabled = true;
    fetch(f.action, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      body: new FormData(f)
    }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); }).then(function (res) {
      if (!res.ok || !res.d.ok) {
        alert((res.d && res.d.message) || 'Komentar ditolak sistem moderasi.');
        return;
      }
      var box = f.parentElement;
      var div = document.createElement('div');
      div.className = 'ig-cmt';
      var b = document.createElement('b');
      b.innerText = res.d.comment.user + ' ';
      var sp = document.createElement('span');
      sp.innerText = res.d.comment.body;
      div.appendChild(b); div.appendChild(sp);
      box.insertBefore(div, f);
      var card = f.closest('.ig-card');
      if (card) card.querySelectorAll('.cmt-count').forEach(function (el) { el.innerText = res.d.comments_count; });
      input.value = '';
    }).catch(function () {
      f.submit();
    }).finally(function () { if (btn) btn.disabled = false; });
  });

  /* Pratinjau gambar composer dalam kontainer + tombol hapus. */
  document.addEventListener('DOMContentLoaded', function () {
    var file = document.getElementById('composerFile');
    var box = document.getElementById('composerPreview');
    var img = document.getElementById('composerPreviewImg');
    var nameEl = document.getElementById('composerPreviewName');
    var rm = document.getElementById('composerPreviewRemove');
    if (!file || !box) return;
    file.addEventListener('change', function () {
      var f = file.files && file.files[0];
      if (!f) { box.style.display = 'none'; return; }
      if (f.size > 4 * 1024 * 1024) {
        alert('Ukuran gambar maksimal 4MB.');
        file.value = '';
        box.style.display = 'none';
        return;
      }
      var rd = new FileReader();
      rd.onload = function (ev) {
        img.src = ev.target.result;
        nameEl.innerText = f.name + ' • ' + Math.round(f.size / 1024) + ' KB';
        box.style.display = 'block';
      };
      rd.readAsDataURL(f);
    });
    if (rm) rm.addEventListener('click', function () {
      file.value = '';
      box.style.display = 'none';
    });
  });
  (function () {
    var nextUrl = @json($posts->nextPageUrl());
    var loading = false;
    var loader = document.getElementById('feedLoader');
    var moreBtn = document.getElementById('feedMoreBtn');
    var doneEl = document.getElementById('feedDone');
    var endEl = document.getElementById('feedEnd');
    function refreshState() {
      if (nextUrl) { moreBtn.style.display = 'inline-block'; doneEl.style.display = 'none'; }
      else { moreBtn.style.display = 'none'; loader.style.display = 'none'; doneEl.style.display = 'block'; }
    }
    refreshState();
    function loadMore() {
      if (loading || !nextUrl) return;
      loading = true; loader.style.display = 'block'; moreBtn.style.display = 'none';
      fetch(nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.text(); })
        .then(function (html) {
          var doc = new DOMParser().parseFromString(html, 'text/html');
          var cards = doc.querySelectorAll('.ig-card');
          var feed = endEl.parentElement;
          cards.forEach(function (c) { feed.insertBefore(c, endEl); });
          // 15 = per halaman; kurang dari itu berarti sudah halaman terakhir.
          if (cards.length >= 15) {
            try {
              var u = new URL(nextUrl);
              var p = parseInt(u.searchParams.get('page') || '1', 10);
              u.searchParams.set('page', p + 1);
              nextUrl = u.toString();
            } catch (e) { nextUrl = null; }
          } else {
            nextUrl = null;
          }
          loading = false; loader.style.display = 'none';
          refreshState();
        })
        .catch(function () { loading = false; loader.style.display = 'none'; refreshState(); });
    }
    moreBtn.addEventListener('click', loadMore);
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (es) { if (es[0].isIntersecting) loadMore(); }, { rootMargin: '600px' }).observe(endEl);
    }
    // Pil postingan baru tiap 60 detik.
    var pill = document.getElementById('newPostsPill');
    var pillTxt = document.getElementById('newPostsTxt');
    function firstId() {
      var c = document.querySelector('.ig-card');
      return c ? parseInt(c.getAttribute('data-post-id') || '0', 10) : 0;
    }
    async function checkNew() {
      try {
        var r = await fetch("{{ route('global.portal.check') }}?after_id=" + firstId(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        var d = await r.json();
        if (d.new_count > 0) {
          pillTxt.innerText = d.new_count + ' postingan baru — ketuk untuk muat';
          pill.style.display = 'block';
        }
      } catch (e) {}
    }
    pill.addEventListener('click', function () { window.location.reload(); });
    setInterval(checkNew, 60000);
  })();
</script>

{{-- Bottom sheet tambah cerita: kamera + galeri digabung --}}
<div id="sheetAdd" style="display:none;position:fixed;inset:0;z-index:6500;">
  <div style="position:absolute;left:0;right:0;bottom:0;background:#fff;border-radius:24px 24px 0 0;padding:12px 16px calc(20px + env(safe-area-inset-bottom));max-width:640px;margin:0 auto;box-shadow:0 -12px 40px rgba(0,0,0,.25);">
    <div style="width:40px;height:4px;border-radius:99px;background:#e2e8f0;margin:0 auto 12px;"></div>
    <div style="font-weight:800;font-size:15px;text-align:center;margin-bottom:12px;">Tambah Cerita</div>
    <button id="sheetGalleryBtn" style="width:100%;display:flex;align-items:center;gap:12px;padding:14px;border:1px solid #eef2f7;border-radius:16px;background:#f8fafc;font-weight:700;font-size:14px;margin-bottom:8px;"><span style="width:40px;height:40px;border-radius:12px;background:#eef2ff;color:#4f46e5;display:grid;place-items:center;font-size:18px;"><i class="bi bi-image"></i></span>Pilih dari Galeri</button>
    <button id="sheetCameraBtn" style="width:100%;display:flex;align-items:center;gap:12px;padding:14px;border:1px solid #eef2f7;border-radius:16px;background:#f8fafc;font-weight:700;font-size:14px;margin-bottom:8px;"><span style="width:40px;height:40px;border-radius:12px;background:#0f172a;color:#fff;display:grid;place-items:center;font-size:18px;"><i class="bi bi-camera-fill"></i></span>Ambil Foto <span style="margin-left:auto;font-size:11px;color:#8e8e8e;">depan/belakang + efek</span></button>
    <button id="sheetCancelBtn" style="width:100%;padding:12px;border:0;background:none;color:#8e8e8e;font-weight:700;font-size:14px;">Batal</button>
  </div>
</div>

{{-- Modal kamera premium: live stabil + pratinjau efek --}}
<div id="camModal" style="display:none;position:fixed;inset:0;z-index:6000;background:#000;">
  <div style="position:absolute;top:calc(12px + env(safe-area-inset-top));left:12px;right:12px;display:flex;align-items:center;gap:8px;z-index:3;">
    <button id="camCloseBtn" style="background:rgba(255,255,255,.15);border:0;color:#fff;width:38px;height:38px;border-radius:12px;font-size:18px;"><i class="bi bi-x-lg"></i></button>
    <div style="flex:1;text-align:center;color:#fff;font-weight:800;font-size:14px;">Kamera Cerita</div>
    <button id="camSwitchBtn" style="background:rgba(255,255,255,.15);border:0;color:#fff;width:38px;height:38px;border-radius:12px;font-size:18px;" title="Ganti kamera depan/belakang"><i class="bi bi-arrow-repeat"></i></button>
  </div>
  <video id="camVideo" autoplay playsinline muted style="width:100%;height:100%;object-fit:cover;"></video>
  <div id="camLiveUI" style="position:absolute;bottom:calc(20px + env(safe-area-inset-bottom));left:0;right:0;z-index:2;">
    <div id="camFilters" style="display:flex;gap:8px;overflow-x:auto;padding:0 16px 12px;scrollbar-width:none;">
      <button data-f="none" class="cam-filter" style="flex-shrink:0;border:2px solid #fff;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Normal</button>
      <button data-f="contrast(1.2) saturate(1.35)" class="cam-filter" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Clarendon</button>
      <button data-f="sepia(.35) contrast(1.05) brightness(1.05) saturate(1.4)" class="cam-filter" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Juno</button>
      <button data-f="saturate(.85) brightness(1.08) contrast(.95)" class="cam-filter" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Lark</button>
      <button data-f="sepia(.5) contrast(.9) brightness(1.05) saturate(.9)" class="cam-filter" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Gingham</button>
      <button data-f="grayscale(1) contrast(1.15) brightness(1.05)" class="cam-filter" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Moon</button>
      <button data-f="sepia(.4) saturate(.7) brightness(1.02)" class="cam-filter" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Slumber</button>
      <button data-f="saturate(1.1) contrast(1.05) brightness(1.08)" class="cam-filter" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Crema</button>
    </div>
    <div style="display:flex;justify-content:center;">
      <button id="camShootBtn" style="width:72px;height:72px;border-radius:50%;background:#fff;border:5px solid rgba(255,255,255,.4);font-size:26px;color:#0f172a;"><i class="bi bi-camera-fill"></i></button>
    </div>
    <div id="camMsg" style="text-align:center;color:#fff;font-size:12px;margin-top:8px;min-height:18px;"></div>
  </div>
  <div id="camPreviewUI" style="display:none;position:absolute;inset:0;z-index:2;background:#000;flex-direction:column;">
    <div style="flex:1;display:grid;place-items:center;padding:70px 12px 8px;min-height:0;"><img id="camPreview" src="" style="max-width:100%;max-height:100%;border-radius:16px;object-fit:contain;"></div>
    <div id="camFxMsg" style="text-align:center;color:#fbbf24;font-size:12px;min-height:18px;padding:0 16px;"></div>
    <div style="display:flex;gap:8px;overflow-x:auto;padding:8px 16px;scrollbar-width:none;" id="camFxRow">
      <button data-fx="none" class="cam-fx" style="flex-shrink:0;border:2px solid #fff;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Normal</button>
      <button data-fx="beauty" class="cam-fx" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Beauty</button>
      <button data-fx="glasses" class="cam-fx" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Kacamata</button>
      <button data-fx="starglasses" class="cam-fx" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Bintang</button>
      <button data-fx="cat" class="cam-fx" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Kucing</button>
      <button data-fx="dog" class="cam-fx" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Anjing</button>
      <button data-fx="crown" class="cam-fx" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Mahkota</button>
      <button data-fx="hearts" class="cam-fx" style="flex-shrink:0;border:2px solid transparent;background:rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:8px 14px;font-size:12px;font-weight:700;">Hati</button>
    </div>
    <div style="display:flex;gap:10px;padding:4px 16px calc(20px + env(safe-area-inset-bottom));">
      <button id="camRetakeBtn" style="flex:1;background:rgba(255,255,255,.15);border:0;color:#fff;border-radius:14px;padding:13px;font-weight:800;font-size:14px;">Ambil Ulang</button>
      <button id="camSendBtn" style="flex:2;background:#fff;border:0;color:#0f172a;border-radius:14px;padding:13px;font-weight:800;font-size:14px;">Kirim Cerita</button>
    </div>
  </div>
  <canvas id="camCanvas" style="display:none;"></canvas>
</div>
<script>
  function openStoryAdd(e) {
    if (e && e.preventDefault) e.preventDefault();
    document.getElementById('sheetAdd').style.display = 'block';
  }
  function storyAddTap(e) {
    try {
      openStoryAdd(e);
    } catch (err) {
      // Fallback: langsung buka galeri agar tombol tidak pernah mati.
      var f = document.getElementById('storyFile');
      if (f) f.click();
    }
  }
  function closeSheetAdd() { document.getElementById('sheetAdd').style.display = 'none'; }
  function galleryPick() { closeSheetAdd(); document.getElementById('storyFile').click(); }

  /* ===== Kamera stabil: deviceId + mirror depan konsisten ===== */
  var camStream = null, camFacing = 'environment', camFilter = 'none';
  var camPhoto = null; // dataURL foto mentah hasil jepret

  /* ===== Wiring TANPA inline-onclick (tahan WebView) + null-guard ===== */
  function bindClick(id, fn) {
    try {
      var el = document.getElementById(id);
      if (el) el.addEventListener('click', function (e) { e.preventDefault(); fn(e); });
    } catch (err) {}
  }
  document.addEventListener('DOMContentLoaded', function () {
    bindClick('storyAddRing', function () { openStoryAdd(); });
    bindClick('storyViewerClose', function () { closeStory(); });
    bindClick('sheetGalleryBtn', function () { galleryPick(); });
    bindClick('sheetCameraBtn', function () { openCamera(); });
    bindClick('sheetCancelBtn', function () { closeSheetAdd(); });
    bindClick('camCloseBtn', function () { closeCamera(); });
    bindClick('camSwitchBtn', function () { switchCamera(); });
    bindClick('camShootBtn', function () { captureStory(); });
    bindClick('camRetakeBtn', function () { retakeStory(); });
    bindClick('camSendBtn', function () { sendStory(); });
    var sh = document.getElementById('sheetAdd');
    if (sh) sh.addEventListener('click', function (e) { if (e.target === sh) closeSheetAdd(); });
    // Buka viewer cerita via delegasi (tanpa inline handler).
    document.addEventListener('click', function (e) {
      var s = (e.target && e.target.closest) ? e.target.closest('[data-story-id]') : null;
      if (s) openStory(parseInt(s.getAttribute('data-story-id'), 10));
    });
  });
  document.querySelectorAll('.cam-filter').forEach(function (b) {
    b.addEventListener('click', function () {
      document.querySelectorAll('.cam-filter').forEach(function (x) { x.style.borderColor = 'transparent'; });
      b.style.borderColor = '#fff';
      camFilter = b.dataset.f;
      document.getElementById('camVideo').style.filter = camFilter;
    });
  });
  function isFront() { return camFacing === 'user'; }
  function applyMirror() {
    // Kamera depan: preview di-mirror (natural selfie); hasil jepret dibuka mirror-nya.
    document.getElementById('camVideo').style.transform = isFront() ? 'scaleX(-1)' : 'none';
  }
  async function openCamera() {
    closeSheetAdd();
    document.getElementById('camModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    document.getElementById('camLiveUI').style.display = 'block';
    document.getElementById('camPreviewUI').style.display = 'none';
    await startCam();
  }
  async function pickDeviceId(wantFront) {
    try {
      var devs = await navigator.mediaDevices.enumerateDevices();
      var cams = devs.filter(function (d) { return d.kind === 'videoinput'; });
      if (!cams.length) return null;
      var labels = cams.map(function (c) { return (c.label || '').toLowerCase(); });
      var idx = labels.findIndex(function (l) {
        return wantFront ? /front|user|selfie|facetime/.test(l) : /back|rear|environment|utama/.test(l);
      });
      if (idx < 0) idx = wantFront ? 0 : cams.length - 1;
      return cams[idx].deviceId || null;
    } catch (e) { return null; }
  }
  async function startCam() {
    stopCam();
    var msg = document.getElementById('camMsg');
    msg.innerText = 'Menyiapkan kamera...';
    try {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) throw new Error('no-cam');
      var deviceId = await pickDeviceId(isFront());
      var constraints = deviceId
        ? { video: { deviceId: { exact: deviceId } }, audio: false }
        : { video: { facingMode: { ideal: camFacing } }, audio: false };
      camStream = await navigator.mediaDevices.getUserMedia(constraints);
      var v = document.getElementById('camVideo');
      v.srcObject = camStream;
      v.style.filter = camFilter;
      applyMirror();
      await v.play().catch(function () {});
      // Tunggu frame valid agar hasil konsisten.
      var tries = 0;
      while ((v.readyState < 2 || !v.videoWidth) && tries < 40) {
        await new Promise(function (r) { setTimeout(r, 100); });
        tries++;
      }
      if (!v.videoWidth) throw new Error('no-frame');
      msg.innerText = '';
    } catch (e) {
      msg.innerText = 'Kamera tidak tersedia — pakai galeri sebagai gantinya.';
    }
  }
  function switchCamera() {
    camFacing = isFront() ? 'environment' : 'user';
    startCam();
  }
  function closeCamera() {
    stopCam();
    document.getElementById('camModal').style.display = 'none';
    document.body.style.overflow = '';
  }
  function stopCam() {
    if (camStream) { camStream.getTracks().forEach(function (t) { try { t.stop(); } catch (e) {} }); camStream = null; }
    var v = document.getElementById('camVideo');
    if (v) v.srcObject = null;
  }
  function captureStory() {
    var v = document.getElementById('camVideo');
    var msg = document.getElementById('camMsg');
    if (!camStream || !v.videoWidth || v.readyState < 2) { msg.innerText = 'Kamera belum siap, tunggu sebentar...'; return; }
    var c = document.getElementById('camCanvas');
    c.width = v.videoWidth; c.height = v.videoHeight;
    var ctx = c.getContext('2d');
    ctx.save();
    ctx.filter = (camFilter && camFilter !== 'none') ? camFilter : 'none';
    if (isFront()) { ctx.translate(c.width, 0); ctx.scale(-1, 1); } // buka mirror agar tulisan tak terbalik
    ctx.drawImage(v, 0, 0, c.width, c.height);
    ctx.restore();
    ctx.filter = 'none';
    camPhoto = c.toDataURL('image/jpeg', 0.92);
    document.getElementById('camPreview').src = camPhoto;
    document.getElementById('camLiveUI').style.display = 'none';
    document.getElementById('camPreviewUI').style.display = 'flex';
    setFx('none');
    document.getElementById('camFxMsg').innerText = '';
    stopCam(); // hemat baterai saat pratinjau
  }
  function retakeStory() {
    document.getElementById('camPreviewUI').style.display = 'none';
    document.getElementById('camLiveUI').style.display = 'block';
    startCam();
  }

  /* ===== Efek wajah: MediaPipe landmarks + gambar prosedural ===== */
  var currentFx = 'none';
  document.querySelectorAll('.cam-fx').forEach(function (b) {
    b.addEventListener('click', function () {
      document.querySelectorAll('.cam-fx').forEach(function (x) { x.style.borderColor = 'transparent'; });
      b.style.borderColor = '#fff';
      setFx(b.dataset.fx);
    });
  });
  function setFx(name) {
    currentFx = name;
    var c = document.getElementById('camCanvas');
    var msg = document.getElementById('camFxMsg');
    // Gambar ulang dari foto mentah agar efek tidak menumpuk.
    if (camPhoto) {
      var img = new Image();
      img.onload = function () {
        var ctx = c.getContext('2d');
        ctx.filter = 'none';
        ctx.drawImage(img, 0, 0, c.width, c.height);
        applyFxOnCanvas(c, name, msg);
      };
      img.src = camPhoto;
    } else {
      applyFxOnCanvas(c, name, msg);
    }
  }
  function faceGeom(lm, w, h) {
    var L = { x: (lm[33].x + lm[133].x) / 2 * w, y: (lm[33].y + lm[133].y) / 2 * h };
    var R = { x: (lm[362].x + lm[263].x) / 2 * w, y: (lm[362].y + lm[263].y) / 2 * h };
    var dist = Math.hypot(R.x - L.x, R.y - L.y) || w * 0.2;
    return {
      L: L, R: R, dist: dist,
      ang: Math.atan2(R.y - L.y, R.x - L.x),
      cx: (L.x + R.x) / 2, cy: (L.y + R.y) / 2,
      nose: { x: lm[1].x * w, y: lm[1].y * h },
      top: { x: lm[10].x * w, y: lm[10].y * h },
      cheekL: { x: lm[234].x * w, y: lm[234].y * h },
      cheekR: { x: lm[454].x * w, y: lm[454].y * h }
    };
  }
  function drawStar(ctx, x, y, r, color) {
    ctx.save(); ctx.translate(x, y); ctx.beginPath();
    for (var i = 0; i < 10; i++) {
      var rr = (i % 2 === 0) ? r : r * 0.45;
      var a = -Math.PI / 2 + i * Math.PI / 5;
      ctx[i === 0 ? 'moveTo' : 'lineTo'](Math.cos(a) * rr, Math.sin(a) * rr);
    }
    ctx.closePath(); ctx.fillStyle = color; ctx.fill();
    ctx.lineWidth = 2; ctx.strokeStyle = 'rgba(255,255,255,.9)'; ctx.stroke(); ctx.restore();
  }
  function drawStarGlasses(ctx, lm, w, h) {
    var g = faceGeom(lm, w, h);
    ctx.save(); ctx.translate(g.cx, g.cy); ctx.rotate(g.ang);
    var r = g.dist * 0.34;
    [[-g.dist * 0.5, 0], [g.dist * 0.5, 0]].forEach(function (p) { drawStar(ctx, p[0], p[1], r, '#fbbf24'); });
    ctx.strokeStyle = '#b45309'; ctx.lineWidth = 4;
    ctx.beginPath(); ctx.moveTo(-g.dist * 0.5 + r * 0.6, 0); ctx.lineTo(g.dist * 0.5 - r * 0.6, 0); ctx.stroke();
    ctx.restore();
  }
  function drawCat(ctx, lm, w, h) {
    var g = faceGeom(lm, w, h);
    var fw = Math.hypot(g.cheekR.x - g.cheekL.x, g.cheekR.y - g.cheekL.y) || w * 0.4;
    var ear = fw * 0.30;
    ctx.save(); ctx.translate(g.top.x, g.top.y); ctx.rotate(g.ang);
    ctx.fillStyle = '#1f2937';
    [[-1, 0], [1, 0]].forEach(function (s) {
      var ex = s[0] * fw * 0.30, ey = -fw * 0.12;
      ctx.beginPath();
      ctx.moveTo(ex - ear * 0.55, ey + ear * 0.5);
      ctx.lineTo(ex - ear * 0.15, ey - ear * 0.75);
      ctx.lineTo(ex + ear * 0.45, ey + ear * 0.15);
      ctx.closePath(); ctx.fill();
      ctx.fillStyle = '#f9a8d4';
      ctx.beginPath();
      ctx.moveTo(ex - ear * 0.28, ey + ear * 0.28);
      ctx.lineTo(ex - ear * 0.12, ey - ear * 0.32);
      ctx.lineTo(ex + ear * 0.22, ey + ear * 0.10);
      ctx.closePath(); ctx.fill();
      ctx.fillStyle = '#1f2937';
    });
    // Kumis + hidung kucing.
    ctx.strokeStyle = 'rgba(31,41,55,.85)'; ctx.lineWidth = 2;
    [-1, 1].forEach(function (s) {
      for (var i = 0; i < 3; i++) {
        ctx.beginPath();
        ctx.moveTo(s * fw * 0.10, g.dist * 0.55 + i * 7 - (g.top.y - g.cy) * 0);
        ctx.lineTo(s * (fw * 0.10 + fw * 0.22), g.dist * 0.55 + i * 7 + (i - 1) * 4);
        ctx.stroke();
      }
    });
    var nx = 0, ny = (g.nose.y - g.top.y);
    ctx.fillStyle = '#f472b6';
    ctx.beginPath();
    ctx.moveTo(nx - 7, ny - 4); ctx.lineTo(nx + 7, ny - 4); ctx.lineTo(nx, ny + 5);
    ctx.closePath(); ctx.fill();
    ctx.restore();
  }
  function drawDog(ctx, lm, w, h) {
    var g = faceGeom(lm, w, h);
    var fw = Math.hypot(g.cheekR.x - g.cheekL.x, g.cheekR.y - g.cheekL.y) || w * 0.4;
    ctx.save(); ctx.translate(g.top.x, g.top.y); ctx.rotate(g.ang);
    ctx.fillStyle = '#92400e';
    [[-1, 0], [1, 0]].forEach(function (s) {
      ctx.save();
      ctx.translate(s * fw * 0.42, fw * 0.02);
      ctx.rotate(s * 0.35);
      ctx.beginPath(); ctx.ellipse(0, 0, fw * 0.13, fw * 0.26, 0, 0, 7); ctx.fill();
      ctx.fillStyle = '#78350f';
      ctx.beginPath(); ctx.ellipse(0, fw * 0.05, fw * 0.07, fw * 0.16, 0, 0, 7); ctx.fill();
      ctx.fillStyle = '#92400e';
      ctx.restore();
    });
    // Hidung + lidah anjing.
    var ny = (g.nose.y - g.top.y);
    ctx.fillStyle = '#111827';
    ctx.beginPath(); ctx.ellipse(0, ny, fw * 0.09, fw * 0.07, 0, 0, 7); ctx.fill();
    ctx.fillStyle = '#f87171';
    ctx.beginPath(); ctx.ellipse(0, ny + fw * 0.16, fw * 0.06, fw * 0.10, 0, 0, 7); ctx.fill();
    ctx.restore();
    // Bintik di pipi.
    ctx.fillStyle = 'rgba(146,64,14,.5)';
    drawHeart(ctx, g.cheekL.x, g.cheekL.y, Math.max(w, h) * 0.02, 'rgba(146,64,14,.45)');
  }
  function drawHeart(ctx, x, y, s, color) {
    ctx.save(); ctx.translate(x, y); ctx.scale(s / 30, s / 30);
    ctx.beginPath();
    ctx.moveTo(0, 10);
    ctx.bezierCurveTo(-18, -6, -8, -20, 0, -8);
    ctx.bezierCurveTo(8, -20, 18, -6, 0, 10);
    ctx.closePath(); ctx.fillStyle = color; ctx.fill(); ctx.restore();
  }
  function drawGlasses(ctx, lm, w, h) {
    var L = { x: (lm[33].x + lm[133].x) / 2 * w, y: (lm[33].y + lm[133].y) / 2 * h };
    var R = { x: (lm[362].x + lm[263].x) / 2 * w, y: (lm[362].y + lm[263].y) / 2 * h };
    var dist = Math.hypot(R.x - L.x, R.y - L.y);
    var ang = Math.atan2(R.y - L.y, R.x - L.x);
    ctx.save(); ctx.translate((L.x + R.x) / 2, (L.y + R.y) / 2); ctx.rotate(ang);
    ctx.fillStyle = 'rgba(15,23,42,.92)';
    var lw = dist * 0.62, lh = dist * 0.42;
    [[-dist * 0.5, 0], [dist * 0.5, 0]].forEach(function (p) {
      ctx.beginPath();
      if (ctx.roundRect) ctx.roundRect(p[0] - lw / 2, -lh / 2, lw, lh, lh * 0.35);
      else ctx.rect(p[0] - lw / 2, -lh / 2, lw, lh);
      ctx.fill();
    });
    ctx.fillRect(-dist * 0.5 + lw / 2 - 4, -3, dist - lw + 8, 6);
    ctx.fillStyle = 'rgba(255,255,255,.25)';
    ctx.fillRect(-dist * 0.5 - lw / 2 + 6, -lh / 2 + 5, lw * 0.5, 5);
    ctx.fillRect(dist * 0.5 - lw / 2 + 6, -lh / 2 + 5, lw * 0.5, 5);
    ctx.restore();
  }
  function drawCrown(ctx, lm, w, h) {
    var top = { x: lm[10].x * w, y: lm[10].y * h };
    var L = { x: lm[234].x * w, y: lm[234].y * h };
    var R = { x: lm[454].x * w, y: lm[454].y * h };
    var fw = Math.hypot(R.x - L.x, R.y - L.y) || w * 0.4;
    var cw = fw * 0.75, ch = fw * 0.5;
    var cx = top.x, cy = top.y - ch * 0.55;
    ctx.save();
    ctx.fillStyle = '#fbbf24'; ctx.strokeStyle = '#b45309'; ctx.lineWidth = 3;
    ctx.beginPath();
    ctx.moveTo(cx - cw / 2, cy + ch / 2);
    ctx.lineTo(cx - cw / 2, cy - ch * 0.1);
    ctx.lineTo(cx - cw * 0.25, cy + ch * 0.12);
    ctx.lineTo(cx, cy - ch / 2);
    ctx.lineTo(cx + cw * 0.25, cy + ch * 0.12);
    ctx.lineTo(cx + cw / 2, cy - ch * 0.1);
    ctx.lineTo(cx + cw / 2, cy + ch / 2);
    ctx.closePath(); ctx.fill(); ctx.stroke();
    [[-0.5, -0.1], [-0.25, 0.12], [0, -0.5], [0.25, 0.12], [0.5, -0.1]].forEach(function (p) {
      ctx.beginPath(); ctx.arc(cx + p[0] * cw, cy + p[1] * ch, fw * 0.035, 0, 7);
      ctx.fillStyle = '#ef4444'; ctx.fill();
    });
    ctx.restore();
  }
  function drawHeartsFx(ctx, lm, w, h) {
    var top = { x: lm[10].x * w, y: lm[10].y * h };
    var s = Math.max(w, h) * 0.05;
    drawHeart(ctx, top.x - s * 1.6, top.y - s * 1.2, s, '#f472b6');
    drawHeart(ctx, top.x + s * 1.4, top.y - s * 2, s * 1.3, '#ef4444');
    drawHeart(ctx, top.x + s * 0.2, top.y - s * 3.1, s * 0.9, '#fb7185');
  }
  function applyBeauty(ctx, w, h) {
    ctx.save();
    ctx.globalAlpha = 0.55;
    ctx.filter = 'brightness(1.06) saturate(1.12)';
    ctx.drawImage(ctx.canvas, 0, 0, w, h);
    ctx.restore();
    ctx.save(); ctx.globalAlpha = 0.10; ctx.fillStyle = '#fff7ed';
    ctx.fillRect(0, 0, w, h); ctx.restore();
    ctx.filter = 'none';
  }
  async function applyFxOnCanvas(c, name, msg) {
    var ctx = c.getContext('2d');
    document.getElementById('camPreview').src = c.toDataURL('image/jpeg', 0.9);
    if (name === 'none') { if (msg) msg.innerText = ''; return; }
    if (name === 'beauty') {
      applyBeauty(ctx, c.width, c.height);
      document.getElementById('camPreview').src = c.toDataURL('image/jpeg', 0.9);
      if (msg) msg.innerText = '';
      return;
    }
    if (msg) msg.innerText = 'Mendeteksi wajah...';
    try {
      if (!window.__mpDetect) throw new Error('model-belum-siap');
      var lm = await window.__mpDetect(c);
      if (!lm) throw new Error('no-face');
      if (name === 'glasses') drawGlasses(ctx, lm, c.width, c.height);
      if (name === 'starglasses') drawStarGlasses(ctx, lm, c.width, c.height);
      if (name === 'cat') drawCat(ctx, lm, c.width, c.height);
      if (name === 'dog') drawDog(ctx, lm, c.width, c.height);
      if (name === 'crown') drawCrown(ctx, lm, c.width, c.height);
      if (name === 'hearts') drawHeartsFx(ctx, lm, c.width, c.height);
      document.getElementById('camPreview').src = c.toDataURL('image/jpeg', 0.9);
      if (msg) msg.innerText = '';
    } catch (e) {
      if (msg) msg.innerText = 'Wajah tidak terdeteksi / model belum termuat — coba Beauty atau Normal.';
    }
  }
  function sendStory() {
    var c = document.getElementById('camCanvas');
    var msg = document.getElementById('camFxMsg');
    var btn = document.getElementById('camSendBtn');
    btn.disabled = true; btn.innerText = 'Mengunggah...';
    c.toBlob(function (blob) {
      var fd = new FormData();
      fd.append('image', blob, 'cerita.jpg');
      fetch("{{ route('global.portal.story.store') }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
      }).then(function () { closeCamera(); window.location.reload(); })
        .catch(function () { msg.innerText = 'Gagal mengunggah. Coba lagi.'; btn.disabled = false; btn.innerText = 'Kirim Cerita'; });
    }, 'image/jpeg', 0.85);
  }
</script>
<script type="module">
  /* MediaPipe FaceLandmarker (lazy, gagal aman → efek wajah dinonaktifkan). */
  window.__mpDetect = null;
  try {
    const vision = await import('https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14/vision_bundle.mjs');
    const fileset = await vision.FilesetResolver.forVisionTasks(
      'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14/wasm'
    );
    const landmarker = await vision.FaceLandmarker.createFromOptions(fileset, {
      baseOptions: {
        modelAssetPath: 'https://storage.googleapis.com/mediapipe-models/face_landmarker/face_landmarker/float16/1/face_landmarker.task',
        delegate: 'GPU'
      },
      runningMode: 'IMAGE',
      numFaces: 1
    });
    window.__mpDetect = async (canvasEl) => {
      const res = landmarker.detect(canvasEl);
      return (res && res.faceLandmarks && res.faceLandmarks[0]) || null;
    };
  } catch (e) { window.__mpDetect = null; }
</script>
@endsection
