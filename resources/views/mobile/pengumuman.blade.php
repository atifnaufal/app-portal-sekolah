@extends('layouts.mobile-app')

@section('content')
<style>
    /* ===== Pengumuman Premium & Responsive ===== */
    :root {
        --p-ink: #0f172a;
        --p-soft: #64748b;
        --p-line: rgba(15, 23, 42, 0.07);
        --p-indigo: #6366f1;
        --p-blue: #2563eb;
    }

    .p-topbar {
        display: flex; align-items: center; justify-content: space-between;
        padding: 18px 20px 0;
        max-width: 860px; margin: 0 auto; width: 100%;
    }
    .p-topbar .back {
        display: inline-flex; align-items: center; gap: 6px;
        color: #64748b; font-weight: 800; font-size: 13px; text-decoration: none;
        padding: 8px 12px; border-radius: 12px; background: rgba(255,255,255,.7);
        border: 1px solid rgba(15,23,42,.06); backdrop-filter: blur(8px);
        transition: all .2s;
    }
    .p-topbar .back:active { background: rgba(255,255,255,.95); }

    .p-hero {
        max-width: 860px; margin: 0 auto; padding: 14px 20px 6px;
    }
    .p-hero .eyebrow {
        font-size: 11px; letter-spacing: .14em; font-weight: 800;
        text-transform: uppercase; color: #818cf8; display: inline-flex;
        align-items: center; gap: 6px;
    }
    .p-hero h1 {
        font-size: clamp(24px, 5vw, 32px); font-weight: 800; letter-spacing: -.02em;
        color: var(--p-ink); margin: 6px 0 4px; line-height: 1.15;
    }
    .p-hero p { color: var(--p-soft); font-weight: 500; font-size: 13.5px; margin: 0; }
    .p-hero .count-pill {
        display: inline-flex; align-items: center; gap: 6px; margin-top: 12px;
        background: #eef2ff; color: #4f46e5; font-weight: 800; font-size: 12px;
        padding: 6px 12px; border-radius: 99px;
    }
    .p-hero .count-pill i { font-size: 13px; }

    .p-filter { display: flex; gap: 8px; overflow-x: auto; padding: 16px 20px 4px;
        max-width: 860px; margin: 0 auto; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
    .p-filter::-webkit-scrollbar { display: none; }
    .p-filter .fbtn {
        flex-shrink: 0; border: 1px solid var(--p-line); background: #fff; color: var(--p-soft);
        font-weight: 700; font-size: 12.5px; padding: 8px 14px; border-radius: 99px;
        display: inline-flex; align-items: center; gap: 6px; cursor: pointer;
        transition: all .2s; box-shadow: 0 1px 3px rgba(15,23,42,.04);
    }
    .p-filter .fbtn small { font-size: 10.5px; opacity: .75; }
    .p-filter .fbtn.active {
        background: linear-gradient(135deg, #4f46e5, #2563eb); color: #fff;
        border-color: transparent; box-shadow: 0 6px 16px rgba(79,70,229,.28);
    }
    .fbtn i { font-size: 13px; }

    .p-list { max-width: 860px; margin: 0 auto; padding: 14px 20px 120px;
        display: grid; grid-template-columns: 1fr; gap: 16px; }
    @media (min-width: 640px) {
        .p-list { grid-template-columns: repeat(2, 1fr); }
    }
    @media (min-width: 900px) {
        .p-list { grid-template-columns: repeat(3, 1fr); }
    }

    .p-card {
        background: #fff; border-radius: 20px; border: 1px solid var(--p-line);
        box-shadow: 0 6px 20px rgba(15,23,42,.05); overflow: hidden;
        display: flex; flex-direction: column; position: relative;
        transition: transform .2s, box-shadow .2s;
    }
    .p-card:active { transform: scale(.985); }
    .p-card.priv::before { content:''; position:absolute; inset:0 0 auto 0; height:4px; background:linear-gradient(90deg,#6366f1,#a78bfa); z-index:2; }
    .p-card .th {
        height: 132px; display: grid; place-items: center; position: relative; overflow: hidden;
    }
    .p-card .th img { width:100%; height:100%; object-fit:cover; display:block; }
    .p-card .th.ink { background: linear-gradient(135deg,#1e1b4b,#3730a3); }
    .p-card .th.paper { background: linear-gradient(135deg,#eef2ff,#f5f3ff); }
    .p-card .th .big-ico { font-size: 40px; color: #c7d2fe; }
    .p-card .th .stage-badge { position:absolute; top:12px; left:12px; z-index:3;
        display:inline-flex; align-items:center; gap:5px; color:#fff; font-weight:800; font-size:10.5px;
        background:rgba(15,23,42,.55); backdrop-filter:blur(8px); padding:5px 11px; border-radius:99px; letter-spacing:.03em; }
    .p-card .bd { padding: 16px; display:flex; flex-direction:column; flex:1; }

    .p-meta { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:10px; }
    .p-date { font-size:11px; font-weight:700; color:#94a3b8; display:inline-flex; align-items:center; gap:5px; letter-spacing:.02em; }
    .p-date i { font-size:11.5px; }
    .p-tag { font-size:10px; font-weight:800; padding:4px 9px; border-radius:7px;
        text-transform:uppercase; letter-spacing:.05em; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; }
    .tag-umum   { background:#fee2e2; color:#dc2626; }
    .tag-kelas  { background:#dcfce7; color:#16a34a; }
    .tag-eskul  { background:#fef3c7; color:#d97706; }
    .tag-privat { background:#e0e7ff; color:#4f46e5; }
    .p-tag i { font-size:9px; }
    .unread { width:8px; height:8px; border-radius:50%; background:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,.15); flex-shrink:0; }

    .p-card h2 { font-size:16px; font-weight:800; color:var(--p-ink); line-height:1.3; margin:0 0 6px; letter-spacing:-.01em; }
    .p-card .excerpt { font-size:13px; color:var(--p-soft); line-height:1.55; margin:0; 
        display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; white-space:pre-line; }

    .p-foot { display:flex; align-items:center; justify-content:space-between; gap:10px;
        border-top:1px solid var(--p-line); margin-top:14px; padding-top:12px; }
    .p-author { display:flex; align-items:center; gap:8px; min-width:0; }
    .avatar {
        width:30px; height:30px; border-radius:50%; flex-shrink:0;
        display:grid; place-items:center; color:#fff; font-weight:800; font-size:12px;
        background:linear-gradient(135deg,#6366f1,#2563eb);
    }
    .p-author .who { min-width:0; }
    .p-author .who b { display:block; font-size:11.5px; color:var(--p-ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .p-author .who span { font-size:10px; color:#94a3b8; font-weight:600; }
    .p-actions { display:flex; gap:6px; }
    .p-actions .act { border:1px solid var(--p-line); background:#fff; color:#64748b;
        width:32px; height:32px; border-radius:10px; display:grid; place-items:center; font-size:14px;
        box-shadow:0 1px 3px rgba(15,23,42,.05); transition:all .2s; text-decoration:none; }
    .p-actions .act:hover { border-color:#c7d2fe; color:#4f46e5; }

    .p-empty { text-align:center; padding:60px 20px; color:#94a3b8; }
    .p-empty .ico { font-size:44px; opacity:.4; }
    .p-empty h4 { color:var(--p-ink); font-weight:800; margin:14px 0 4px; font-size:16px; }
    .p-empty p { font-size:13px; margin:0; }

    .p-fab { position:fixed; right:20px; z-index:1000; width:56px; height:56px; border-radius:18px;
        display:flex; align-items:center; justify-content:center; color:#fff; text-decoration:none;
        background:linear-gradient(135deg,#4f46e5,#2563eb); box-shadow:0 10px 24px rgba(79,70,229,.4);
        transition:transform .2s; }
    .p-fab:active { transform:scale(.92); }
    .p-fab i { font-size:24px; }
    @media (min-width:640px){ .p-fab{ bottom:40px; right:40px; } }
</style>

<div class="p-topbar">
    <a href="{{ route('dashboard') }}" class="back"><i class="bi bi-arrow-left"></i> Beranda</a>
</div>

<header class="p-hero">
    <div class="eyebrow"><i class="bi bi-megaphone-fill"></i> Informasi Resmi</div>
    <h1>Pengumuman</h1>
    <p>
        @if(isset($canManage) && $canManage)
            @if(isset($isWaliKelas) && $isWaliKelas)
                Untuk wali kelas <b>{{ $isWaliKelas->nama }}</b> dan pembina eskul Anda.
            @else
                Untuk kelas dan eskul yang Anda bina.
            @endif
        @else
            Agenda, berita, dan pesan pribadi untuk Anda.
        @endif
    </p>
    <span class="count-pill"><i class="bi bi-bell"></i> {{ $pengumumans->count() }} pengumuman</span>
</header>

@php
    $roleContext = session('user_role');
    $list = array_values($pengumumans->all());
@endphp

<nav class="p-filter" id="pFilter">
    <button class="fbtn active" data-f="all"><i class="bi bi-grid-1x2"></i> Semua <small>{{ count($list) }}</small></button>
    <button class="fbtn" data-f="umum"><i class="bi bi-globe2"></i> Umum</button>
    <button class="fbtn" data-f="kelas"><i class="bi bi-people-fill"></i> Kelas</button>
    <button class="fbtn" data-f="eskul"><i class="bi bi-stars"></i> Eskul</button>
    <button class="fbtn" data-f="pribadi"><i class="bi bi-lock-fill"></i> Pribadi</button>
</nav>

<main class="p-list" id="pList">
    @forelse($list as $item)
        @php
            $isPrivate = method_exists($item,'isPrivate') && $item->isPrivate();
            $cat = $isPrivate ? 'pribadi' : ($item->kelas_id ? 'kelas' : ($item->eskul_id ? 'eskul' : 'umum'));
            $unread = $isPrivate && empty($item->user_read_at) && $item->user_id != ($user->id ?? null);
            $initial = strtoupper(substr($item->user->name ?? 'A', 0, 1));
            $stageLabel = $isPrivate ? 'Pesan Pribadi' : ($item->kelas_id ? 'Kelas '.$item->kelas->nama : ($item->eskul_id ? $item->eskul->nama : 'Umum / Sekolah'));
        @endphp
        <article class="p-card {{ $isPrivate ? 'priv' : '' }}" data-cat="{{ $cat }}">
            <div class="th {{ $isPrivate ? 'ink' : ($item->gambar ? '' : 'paper') }}">
                @if($item->gambar)
                    <img src="{{ asset('storage/'.$item->gambar) }}" alt="{{ $item->judul }}"
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Info&background=f1f5f9&color=94a3b8';">
                @else
                    <i class="bi {{ $isPrivate ? 'bi-person-lock' : ($item->eskul_id ? 'bi-stars' : ($item->kelas_id ? 'bi-people-fill' : 'bi-megaphone')) }} big-ico"></i>
                @endif
                <span class="stage-badge">
                    <i class="bi {{ $isPrivate ? 'bi-lock-fill' : ($item->kelas_id ? 'bi-people-fill' : ($item->eskul_id ? 'bi-stars' : 'bi-globe2')) }}"></i>
                    {{ $stageLabel }}
                </span>
            </div>
            <div class="bd">
                <div class="p-meta">
                    <span class="p-date"><i class="bi bi-calendar3"></i> {{ $item->created_at->format('d M Y') }}</span>
                    <div style="display:flex;align-items:center;gap:6px;">
                        @if($unread)<span class="unread"></span>@endif
                        <span class="p-tag {{ $cat === 'umum' ? 'tag-umum' : ($cat === 'kelas' ? 'tag-kelas' : ($cat === 'eskul' ? 'tag-eskul' : 'tag-privat')) }}">
                            <i class="bi {{ $cat === 'umum' ? 'bi-globe2' : ($cat === 'kelas' ? 'bi-people-fill' : ($cat === 'eskul' ? 'bi-stars' : 'bi-lock-fill')) }}"></i>
                            {{ $cat === 'pribadi' ? 'Pribadi' : ($cat === 'kelas' ? 'Kelas' : ($cat === 'eskul' ? 'Eskul' : 'Umum')) }}
                        </span>
                    </div>
                </div>

                <h2>{{ $item->judul }}</h2>
                <p class="excerpt">{{ $item->isi }}</p>

                <div class="p-foot">
                    <div class="p-author">
                        <span class="avatar">{{ $initial }}</span>
                        <div class="who">
                            <b>{{ $item->user->name ?? 'Administrator' }}</b>
                            <span>
                                @if($item->tanggal_acara)
                                    <i class="bi bi-calendar-event"></i> {{ $item->tanggal_acara->format('d M Y') }}
                                @else
                                    {{ $item->created_at->diffForHumans() }}
                                @endif
                            </span>
                        </div>
                    </div>
                    @if(isset($canManage) && $canManage && $item->user_id == ($user->id ?? null))
                        <div class="p-actions">
                            <a href="{{ route('pengumuman.edit', $item) }}" class="act" title="Edit"><i class="bi bi-pencil"></i></a>
                        </div>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="p-empty" style="grid-column:1/-1;">
            <i class="bi bi-megaphone ico"></i>
            <h4>Belum ada pengumuman</h4>
            <p>Pengumuman yang ditujukan untuk Anda akan muncul di sini.</p>
        </div>
    @endforelse
</main>

@if(isset($canCreate) && $canCreate)
    <a href="{{ route('pengumuman.create') }}" class="p-fab" title="Buat pengumuman" style="bottom:90px;"><i class="bi bi-plus-lg"></i></a>
@endif

<script>
(function () {
    var btns = document.querySelectorAll('#pFilter .fbtn');
    var cards = document.querySelectorAll('#pList .p-card');
    btns.forEach(function (b) {
        b.addEventListener('click', function () {
            btns.forEach(function (x) { x.classList.remove('active'); });
            b.classList.add('active');
            var f = b.getAttribute('data-f');
            cards.forEach(function (c) {
                c.style.display = (f === 'all' || c.getAttribute('data-cat') === f) ? '' : 'none';
            });
            var empty = document.querySelector('.p-empty');
            if (empty) empty.style.display = (f === 'all' && cards.length === 0) ? 'block' : 'none';
        });
    });
})();
</script>

@endsection
