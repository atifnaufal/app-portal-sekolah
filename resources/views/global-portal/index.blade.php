{{-- Global Portal WEB DESKTOP. Sekolah postingan OTOMATIS dari akun (tanpa dropdown).
     Cerita 24 jam + moderasi (filter teks selalu jalan, AI bila key ada, laporan komunitas). --}}
@extends('layouts.app', ['title' => 'Global Portal'])
@section('content')
@php
$isAdmin = session('user_role') === 'admin';
$myId = (int) session('user_id');
$mySchoolName = $me?->school?->name ?? ($isSuper ? 'Admin Pusat (Umum)' : 'Umum');
@endphp
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
    .gp-post.is-hidden-post { background: #fff7f7; }
    .gp-avatar { width: 44px; height: 44px; border-radius: 14px; object-fit: cover; flex-shrink: 0; }
    .gp-action { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: var(--muted); background: none; border: 0; padding: 6px 10px; border-radius: 10px; text-decoration: none; cursor: pointer; }
    .gp-action:hover { background: #f1f5f9; color: var(--navy); }
    .gp-action.liked { color: #e11d48; }
    .gp-school-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; }
    .gp-school-row + .gp-school-row { border-top: 1px solid #f1f5f9; }
    .gp-school-ico { width: 38px; height: 38px; border-radius: 12px; display: grid; place-items: center; color: #fff; font-weight: 800; flex-shrink: 0; }

    .story-rail { display: flex; gap: 14px; overflow-x: auto; padding: 4px 2px 10px; scrollbar-width: thin; }
    .story-item { flex-shrink: 0; width: 76px; text-align: center; cursor: pointer; background: none; border: 0; }
    .story-ring { width: 68px; height: 68px; border-radius: 50%; padding: 3px; margin: 0 auto; background: linear-gradient(45deg, #feda75, #fa7e1e, #d62976, #962fbf, #4f5bd5); }
    .story-ring img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid #fff; display: block; }
    .story-ring.mine { background: #e2e8f0; }
    .story-ring.seen { background: #d4d4d4; }
    .story-name { font-size: 11px; margin-top: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--navy); font-weight: 600; }
    .story-viewer-img { width: 100%; max-height: 60vh; object-fit: contain; background: #0f172a; border-radius: 14px; }
</style>

<div class="gp-hero">
    <div class="position-relative" style="z-index:1;">
        <div class="small fw-bold" style="letter-spacing:.1em;color:#94a3b8;">PORTAL ANTAR SEKOLAH</div>
        <h1 class="gp-hero-title">Global Portal</h1>
        <p class="gp-hero-sub mb-0">Bagikan kabar & berinteraksi dengan seluruh sekolah terdaftar. Konten dimoderasi otomatis.</p>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4"><ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Cerita --}}
        <div class="gp-card mb-4">
            <div class="gp-card-head d-flex justify-content-between align-items-center">
                <h2 class="gp-card-title"><i class="bi bi-camera-reels me-2 text-primary"></i>Cerita (24 jam)</h2>
                <form method="POST" action="{{ route('global.portal.story.store') }}" enctype="multipart/form-data" class="d-inline">
                    @csrf
                    <label class="btn btn-sm btn-primary mb-0" style="border-radius:10px;cursor:pointer;"><i class="bi bi-plus-lg me-1"></i>Tambah Cerita<input type="file" name="image" accept="image/*" hidden onchange="this.form.submit()"></label>
                </form>
            </div>
            <div class="px-4 pt-3">
                <div class="story-rail">
                    @forelse($storiesGrouped as $uid => $st)
                    <button class="story-item" data-bs-toggle="modal" data-bs-target="#storyModal{{ $st->id }}" data-story-open="{{ $st->id }}">
                        <div class="story-ring {{ $uid == $myId ? 'mine' : '' }}" data-story-ring="{{ $st->id }}"><img src="{{ \App\Services\FirebaseStorageService::url($st->image) }}" alt=""></div>
                        <div class="story-name">{{ $uid == $myId ? 'Cerita Anda' : explode(' ', $st->user->name)[0] }}</div>
                    </button>
                    @empty
                    <div class="text-muted small py-2">Belum ada cerita aktif. Jadilah yang pertama!</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Composer (sekolah otomatis) --}}
        <div class="gp-card mb-4">
            <div class="gp-card-head"><h2 class="gp-card-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Buat Postingan</h2></div>
            <div class="p-4">
                <form method="POST" action="{{ route('global.portal.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex gap-3 align-items-center mb-3">
                        <img src="{{ $me?->avatar_url ?? asset('logo_sekolah.png') }}" class="gp-avatar" alt="">
                        <div>
                            <div class="fw-bold" style="font-size:14px;">{{ $me?->name ?? session('admin_name') }}</div>
                            <div class="text-muted small">Diposting sebagai <b>{{ $mySchoolName }}</b> (otomatis)</div>
                        </div>
                    </div>
                    <textarea name="content" class="form-control mb-3" rows="3" placeholder="Apa yang ingin kamu bagikan hari ini?" required style="border-radius:14px;"></textarea>
                    <div id="composerPreviewDesk" style="display:none;margin-bottom:12px;position:relative;border-radius:14px;overflow:hidden;border:1px solid var(--border);">
                        <img id="composerPreviewDeskImg" src="" style="width:100%;max-height:240px;object-fit:cover;display:block;" alt="">
                        <div style="position:absolute;left:10px;bottom:10px;right:10px;display:flex;gap:8px;align-items:center;background:rgba(15,23,42,.65);backdrop-filter:blur(8px);border-radius:10px;padding:8px 12px;">
                            <i class="bi bi-file-earmark-image text-white"></i>
                            <span id="composerPreviewDeskName" style="flex:1;font-size:12px;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                            <button type="button" id="composerPreviewDeskRemove" style="background:rgba(255,255,255,.2);border:0;color:#fff;border-radius:8px;width:28px;height:28px;">✕</button>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <label class="gp-action" style="cursor:pointer;color:var(--blue);"><i class="bi bi-image"></i> Gambar<input type="file" name="image" id="composerFileDesk" accept="image/*" hidden></label>
                        <button class="btn btn-primary ms-auto px-4" style="border-radius:12px;">Posting</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Feed --}}
        <div class="gp-card">
            <div class="gp-card-head d-flex justify-content-between align-items-center">
                <h2 class="gp-card-title"><i class="bi bi-globe me-2 text-primary"></i>Kabar Terbaru</h2>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('global.portal.activity') }}" class="btn btn-sm btn-outline-danger position-relative" style="border-radius:10px;" title="Aktivitas"><i class="bi bi-heart"></i>@if(($activityCount ?? 0) > 0)<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $activityCount > 99 ? '99+' : $activityCount }}</span>@endif</a>
                    <span class="badge rounded-pill bg-primary-subtle text-primary">{{ $posts->total() }} postingan</span>
                </div>
            </div>
            @forelse($posts as $p)
            @php $liked = $p->likes->contains('user_id', $myId); @endphp
            <div class="gp-post {{ $p->is_hidden ? 'is-hidden-post' : '' }}" data-post-id="{{ $p->id }}">
                @if($p->is_hidden)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge rounded-pill bg-danger">Disembunyikan ({{ $p->reports_count }} laporan)</span>
                    @if($isSuper)
                    <form method="POST" action="{{ route('admin.portal.unhide', $p) }}" class="d-inline">@csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-success" style="border-radius:8px;font-size:11px;">Tampilkan Lagi</button>
                    </form>
                    @endif
                </div>
                @endif
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
                <div class="d-flex align-items-center gap-1 mt-3 pt-2 border-top flex-wrap">
                    <form method="POST" action="{{ route('global.portal.like', $p) }}" class="like-form">@csrf
                        <button class="gp-action {{ $liked ? 'liked' : '' }}"><i class="bi {{ $liked ? 'bi-heart-fill' : 'bi-heart' }}"></i> <span class="like-count">{{ $p->likes_count }}</span></button>
                    </form>
                    <a href="#cmt-{{ $p->id }}" class="gp-action"><i class="bi bi-chat"></i> {{ $p->comments_count }}</a>
                    @if(!$isAdmin && $p->user_id !== $myId)
                        <a href="{{ route('chat.startPrivate', $p->user) }}" class="gp-action"><i class="bi bi-send"></i> Pesan</a>
                        <form method="POST" action="{{ route('global.portal.report', $p) }}" class="d-inline" onsubmit="return confirm('Laporkan postingan ini sebagai tidak pantas?')">@csrf
                            <button class="gp-action" title="Laporkan"><i class="bi bi-flag"></i></button>
                        </form>
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
            <div class="p-4 text-center" id="feedEnd">
                <div id="feedLoader" style="display:none;" class="text-muted small"><span class="spinner-border spinner-border-sm me-1"></span>Memuat...</div>
                <button id="feedMoreBtn" class="btn btn-outline-primary rounded-pill px-4 fw-bold" style="display:none;">Muat Lebih Banyak</button>
                <div id="feedDone" class="text-muted small" style="display:none;">— Sudah paling bawah —</div>
            </div>
            @endif
            <button id="newPostsPill" class="btn btn-dark fw-bold shadow-lg" style="display:none;position:fixed;top:90px;left:50%;transform:translateX(-50%);z-index:1050;border-radius:99px;"><i class="bi bi-arrow-up-circle me-1"></i><span id="newPostsTxt">Postingan baru</span></button>
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
            <div class="gp-card-head"><h2 class="gp-card-title"><i class="bi bi-shield-check me-2 text-primary"></i>Moderasi Aktif</h2></div>
            <div class="p-4 small text-muted" style="line-height:1.7;">
                Setiap kiriman diperiksa otomatis: filter kata, pemeriksaan gambar AI, dan laporan komunitas (sembunyi otomatis setelah 3 laporan).
                @if($isSuper)
                <br><br><span class="badge bg-primary-subtle text-primary">Mode Admin: like & komentar aktif, chat privat dinonaktifkan.</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Story viewer modals --}}
@foreach($storiesGrouped as $uid => $st)
<div class="modal fade" id="storyModal{{ $st->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;background:#0f172a;color:#fff;border:0;">
            <div class="d-flex align-items-center gap-2 p-3">
                <img src="{{ $st->user->avatar_url }}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;" alt="">
                <div class="flex-fill"><div class="fw-bold" style="font-size:13px;">{{ $st->user->name }}</div><div style="font-size:11px;color:#94a3b8;">{{ $st->created_at->diffForHumans() }} &bull; hilang {{ $st->expires_at->diffForHumans() }}</div></div>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <img src="{{ \App\Services\FirebaseStorageService::url($st->image) }}" class="story-viewer-img" alt="">
            @if($st->caption)<div class="p-3" style="font-size:13px;">{{ $st->caption }}</div>@endif
            @if($uid == $myId || $isSuper)
            <form method="POST" action="{{ route('global.portal.story.destroy', $st) }}" class="p-3 pt-0">@csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger w-100" style="border-radius:10px;"><i class="bi bi-trash3 me-1"></i> Hapus Cerita</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endforeach
{{-- Infinite scroll + pil postingan baru + ring dilihat + like AJAX + preview upload --}}
<script>
(function () {
    /* Pratinjau gambar composer dalam kontainer. */
    var dFile = document.getElementById('composerFileDesk');
    var dBox = document.getElementById('composerPreviewDesk');
    var dImg = document.getElementById('composerPreviewDeskImg');
    var dName = document.getElementById('composerPreviewDeskName');
    var dRm = document.getElementById('composerPreviewDeskRemove');
    if (dFile && dBox) {
        dFile.addEventListener('change', function () {
            var f = dFile.files && dFile.files[0];
            if (!f) { dBox.style.display = 'none'; return; }
            if (f.size > 4 * 1024 * 1024) {
                alert('Ukuran gambar maksimal 4MB.');
                dFile.value = '';
                dBox.style.display = 'none';
                return;
            }
            var rd = new FileReader();
            rd.onload = function (ev) {
                dImg.src = ev.target.result;
                dName.innerText = f.name + ' • ' + Math.round(f.size / 1024) + ' KB';
                dBox.style.display = 'block';
            };
            rd.readAsDataURL(f);
        });
        if (dRm) dRm.addEventListener('click', function () {
            dFile.value = '';
            dBox.style.display = 'none';
        });
    }
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
                if (d.liked) { icon.classList.remove('bi-heart'); icon.classList.add('bi-heart-fill'); }
                else { icon.classList.add('bi-heart'); icon.classList.remove('bi-heart-fill'); }
            }
            if (btn) btn.classList.toggle('liked', !!d.liked);
            if (count && typeof d.likes_count !== 'undefined') count.innerText = d.likes_count;
        }).catch(function () {
            f.submit();
        }).finally(function () { if (btn) btn.disabled = false; });
    });
    function seenIds() {
        try { return JSON.parse(localStorage.getItem('seenStories') || '[]'); }
        catch (e) { return []; }
    }
    function paintSeen() {
        var seen = seenIds();
        document.querySelectorAll('[data-story-ring]').forEach(function (el) {
            if (seen.indexOf(parseInt(el.getAttribute('data-story-ring'), 10)) !== -1) el.classList.add('seen');
        });
    }
    paintSeen();
    document.addEventListener('click', function (e) {
        var b = e.target && e.target.closest ? e.target.closest('[data-story-open]') : null;
        if (!b) return;
        var id = parseInt(b.getAttribute('data-story-open'), 10);
        try {
            var seen = seenIds();
            if (seen.indexOf(id) === -1) { seen.push(id); localStorage.setItem('seenStories', JSON.stringify(seen.slice(-200))); }
        } catch (err) {}
        var ring = document.querySelector('[data-story-ring="' + id + '"]');
        if (ring) ring.classList.add('seen');
    });
    var nextUrl = @json($posts->nextPageUrl());
    var endEl = document.getElementById('feedEnd');
    if (!endEl) return;
    var loading = false;
    var loader = document.getElementById('feedLoader');
    var moreBtn = document.getElementById('feedMoreBtn');
    var doneEl = document.getElementById('feedDone');
    function refreshState() {
        if (nextUrl) { moreBtn.style.display = 'inline-block'; doneEl.style.display = 'none'; }
        else { moreBtn.style.display = 'none'; if (loader) loader.style.display = 'none'; doneEl.style.display = 'block'; }
    }
    refreshState();
    function loadMore() {
        if (loading || !nextUrl) return;
        loading = true; loader.style.display = 'block'; moreBtn.style.display = 'none';
        fetch(nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var cards = doc.querySelectorAll('.gp-post');
                var feed = endEl.parentElement;
                cards.forEach(function (c) { feed.insertBefore(c, endEl); });
                if (cards.length >= 15) {
                    try {
                        var u = new URL(nextUrl);
                        var p = parseInt(u.searchParams.get('page') || '1', 10);
                        u.searchParams.set('page', p + 1);
                        nextUrl = u.toString();
                    } catch (e) { nextUrl = null; }
                } else { nextUrl = null; }
                loading = false; loader.style.display = 'none';
                refreshState();
            })
            .catch(function () { loading = false; loader.style.display = 'none'; refreshState(); });
    }
    moreBtn.addEventListener('click', loadMore);
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (es) { if (es[0].isIntersecting) loadMore(); }, { rootMargin: '600px' }).observe(endEl);
    }
    var pill = document.getElementById('newPostsPill');
    var pillTxt = document.getElementById('newPostsTxt');
    function firstId() {
        var c = document.querySelector('.gp-post');
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
@endsection
