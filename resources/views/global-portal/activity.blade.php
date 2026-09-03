{{-- Aktivitas Global Portal versi WEB DESKTOP — kartu glow premium. --}}
@extends('layouts.app', ['title' => 'Aktivitas Portal'])
@section('content')
<style>
    .gp-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 55%, #4f46e5 100%);
        border-radius: 24px; padding: 32px 36px; color: #fff;
        position: relative; overflow: hidden; margin-bottom: 24px;
        box-shadow: 0 20px 40px rgba(79,70,229,.35);
    }
    .gp-hero::after {
        content: ''; position: absolute; top: -70px; right: -70px;
        width: 220px; height: 220px; border-radius: 50%;
        background: radial-gradient(circle, rgba(244,114,182,.35) 0%, transparent 70%);
    }
    .gp-hero-title { font-size: 26px; font-weight: 800; letter-spacing: -0.02em; position: relative; z-index: 1; }
    .gp-hero-sub { font-size: 13px; color: #c7d2fe; position: relative; z-index: 1; }
    .act-sec { display: flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: .06em; margin: 26px 0 12px; }
    .act-sec .pill { margin-left: auto; font-size: 11px; background: #fff; border: 1px solid var(--border); padding: 4px 12px; border-radius: 99px; color: #4f46e5; box-shadow: 0 2px 8px rgba(79,70,229,.15); }
    .act-card {
        display: flex; gap: 16px; align-items: center; background: #fff;
        border: 1px solid var(--border); border-radius: 20px; padding: 18px 22px; margin-bottom: 12px;
        box-shadow: 0 8px 24px rgba(15,23,42,.07); transition: all .25s; text-decoration: none; color: inherit;
    }
    a.act-card:hover { transform: translateY(-3px); box-shadow: 0 16px 36px rgba(79,70,229,.22); border-color: transparent; }
    .act-orb { width: 52px; height: 52px; border-radius: 16px; display: grid; place-items: center; color: #fff; font-size: 22px; flex-shrink: 0; }
    .orb-follow { background: linear-gradient(135deg,#4f46e5,#2563eb); box-shadow: 0 8px 20px rgba(79,70,229,.5); }
    .orb-like { background: linear-gradient(135deg,#f43f5e,#fb7185); box-shadow: 0 8px 20px rgba(244,63,94,.5); }
    .orb-comment { background: linear-gradient(135deg,#0ea5e9,#22d3ee); box-shadow: 0 8px 20px rgba(14,165,233,.5); }
    .act-ava { width: 52px; height: 52px; border-radius: 16px; object-fit: cover; flex-shrink: 0; box-shadow: 0 8px 20px rgba(15,23,42,.22); }
    .act-time { font-size: 11px; color: #94a3b8; font-weight: 700; background: #f1f5f9; padding: 5px 12px; border-radius: 99px; flex-shrink: 0; }
    .act-empty { background: #fff; border: 1px dashed #e2e8f0; border-radius: 20px; padding: 30px; text-align: center; color: #94a3b8; font-size: 13px; margin-bottom: 12px; }
    @media (max-width: 768px) {
        .gp-hero { padding: 24px; border-radius: 20px; }
        .gp-hero-title { font-size: 22px; }
    }
</style>

<a href="{{ route('global.portal') }}" class="btn btn-sm btn-outline-secondary mb-3" style="border-radius:10px;"><i class="bi bi-arrow-left me-1"></i> Kembali ke Portal</a>

<div class="gp-hero">
    <div class="position-relative" style="z-index:1;">
        <div class="small fw-bold" style="letter-spacing:.1em;color:#c7d2fe;">NOTIFIKASI SOSIAL</div>
        <h1 class="gp-hero-title">Aktivitas di Postinganmu</h1>
        <p class="gp-hero-sub mb-0">Pengikut baru, suka, dan komentar terbaru — semua di satu tempat.</p>
    </div>
</div>

<div class="act-sec"><i class="bi bi-person-plus-fill" style="color:#4f46e5;"></i>Pengikut Baru<span class="pill">{{ $followers->count() }}</span></div>
@forelse($followers as $f)
<div class="act-card">
    <div class="act-orb orb-follow"><i class="bi bi-person-plus-fill"></i></div>
    <div class="flex-fill" style="font-size:14px;"><b>{{ $f->follower->name }}</b> <span class="text-muted">mulai mengikutimu.</span></div>
    <span class="act-time">{{ $f->created_at->diffForHumans() }}</span>
</div>
@empty
<div class="act-empty">Belum ada pengikut baru.</div>
@endforelse

<div class="act-sec"><i class="bi bi-heart-fill" style="color:#f43f5e;"></i>Disukai<span class="pill">{{ $likes->count() }}</span></div>
@forelse($likes as $l)
<div class="act-card">
    <img src="{{ $l->user->avatar_url }}" class="act-ava" alt="">
    <div class="flex-fill" style="font-size:14px;"><b>{{ $l->user->name }}</b> menyukai <span class="text-muted">“{{ \Illuminate\Support\Str::limit($l->post->content ?? '', 90) }}”</span></div>
    <span class="act-time">{{ $l->created_at->diffForHumans() }}</span>
</div>
@empty
<div class="act-empty">Belum ada suka baru.</div>
@endforelse

<div class="act-sec"><i class="bi bi-chat-fill" style="color:#0ea5e9;"></i>Komentar Terbaru<span class="pill">{{ $comments->count() }}</span></div>
@forelse($comments as $c)
<div class="act-card">
    <img src="{{ $c->user->avatar_url }}" class="act-ava" alt="">
    <div class="flex-fill" style="font-size:14px;"><b>{{ $c->user->name }}</b>: {{ $c->body }}<br><span class="text-muted small">di “{{ \Illuminate\Support\Str::limit($c->post->content ?? '', 80) }}”</span></div>
    <span class="act-time">{{ $c->created_at->diffForHumans() }}</span>
</div>
@empty
<div class="act-empty">Belum ada komentar baru.</div>
@endforelse
@endsection
