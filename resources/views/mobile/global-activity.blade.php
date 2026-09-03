@php $hideNav = false; $title = 'Aktivitas'; @endphp
@extends('layouts.mobile-app')
@section('content')
<style>
.act-page{max-width:640px;margin:0 auto;padding-bottom:110px;background:#f6f7fb;min-height:100vh}
.act-header{position:sticky;top:0;z-index:100;background:rgba(15,23,42,.94);backdrop-filter:blur(12px);display:flex;align-items:center;gap:10px;padding:12px 14px;padding-top:calc(12px + env(safe-area-inset-top));border-radius:0 0 24px 24px;box-shadow:0 12px 30px rgba(15,23,42,.25);color:#fff}
.act-back{width:36px;height:36px;border-radius:12px;display:grid;place-items:center;color:#fff;text-decoration:none;font-size:18px;background:rgba(255,255,255,.12);flex-shrink:0}
.act-title{flex:1;text-align:center;font-size:17px;font-weight:800;letter-spacing:-.01em}
.act-sec{display:flex;align-items:center;gap:8px;font-size:12px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.06em;padding:20px 16px 10px}
.act-sec .pill{margin-left:auto;font-size:10px;background:#fff;border:1px solid rgba(15,23,42,.08);padding:3px 10px;border-radius:99px;color:#4f46e5;box-shadow:0 2px 8px rgba(79,70,229,.12)}
.act-card{display:flex;gap:12px;align-items:center;margin:0 14px 10px;padding:14px;background:#fff;border:1px solid rgba(15,23,42,.06);border-radius:20px;text-decoration:none;color:inherit;box-shadow:0 8px 24px rgba(15,23,42,.07);animation:rise .45s both}
.act-card:nth-child(odd){animation-delay:.05s}
@keyframes rise{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.act-card:active{transform:scale(.98)}
.act-orb{width:48px;height:48px;border-radius:16px;display:grid;place-items:center;flex-shrink:0;font-size:20px;color:#fff}
.orb-follow{background:linear-gradient(135deg,#4f46e5,#2563eb);box-shadow:0 6px 16px rgba(79,70,229,.45)}
.orb-like{background:linear-gradient(135deg,#f43f5e,#fb7185);box-shadow:0 6px 16px rgba(244,63,94,.45)}
.orb-comment{background:linear-gradient(135deg,#0ea5e9,#22d3ee);box-shadow:0 6px 16px rgba(14,165,233,.45)}
.act-ava{width:48px;height:48px;border-radius:16px;object-fit:cover;flex-shrink:0;box-shadow:0 6px 16px rgba(15,23,42,.2)}
.act-txt{flex:1;font-size:13px;line-height:1.5;min-width:0;color:#0f172a}
.act-time{font-size:10px;color:#94a3b8;font-weight:700;flex-shrink:0;background:#f1f5f9;padding:4px 8px;border-radius:99px}
.act-empty{margin:0 14px 10px;padding:26px;text-align:center;color:#94a3b8;font-size:13px;background:#fff;border:1px dashed #e2e8f0;border-radius:20px}
.act-empty i{font-size:28px;display:block;margin-bottom:6px;opacity:.5}
</style>
<div class="act-page">
  <div class="act-header">
    <a href="{{ route('global.portal') }}" class="act-back"><i class="bi bi-chevron-left"></i></a>
    <div class="act-title">Aktivitas</div>
    <div style="width:36px;"></div>
  </div>

  <div class="act-sec"><i class="bi bi-person-plus-fill" style="color:#4f46e5;"></i>Pengikut Baru<span class="pill">{{ $followers->count() }}</span></div>
  @forelse($followers as $f)
  <div class="act-card">
    <div class="act-orb orb-follow"><i class="bi bi-person-plus-fill"></i></div>
    <div class="act-txt"><b>{{ $f->follower->name }}</b> mulai mengikutimu.</div>
    <div class="act-time">{{ $f->created_at->diffForHumans() }}</div>
  </div>
  @empty
  <div class="act-empty"><i class="bi bi-people"></i>Belum ada pengikut baru.</div>
  @endforelse

  <div class="act-sec"><i class="bi bi-heart-fill" style="color:#f43f5e;"></i>Disukai<span class="pill">{{ $likes->count() }}</span></div>
  @forelse($likes as $l)
  <a class="act-card" href="{{ route('global.portal') }}#cmt-{{ $l->global_post_id }}">
    <img src="{{ $l->user->avatar_url }}" class="act-ava" alt="">
    <div class="act-txt"><b>{{ $l->user->name }}</b> menyukai postinganmu<span style="color:#64748b;"> “{{ \Illuminate\Support\Str::limit($l->post->content ?? '', 60) }}”</span></div>
    <div class="act-time">{{ $l->created_at->diffForHumans() }}</div>
  </a>
  @empty
  <div class="act-empty"><i class="bi bi-heart"></i>Belum ada suka baru.</div>
  @endforelse

  <div class="act-sec"><i class="bi bi-chat-fill" style="color:#0ea5e9;"></i>Komentar Terbaru<span class="pill">{{ $comments->count() }}</span></div>
  @forelse($comments as $c)
  <a class="act-card" href="{{ route('global.portal') }}#cmt-{{ $c->global_post_id }}">
    <img src="{{ $c->user->avatar_url }}" class="act-ava" alt="">
    <div class="act-txt"><b>{{ $c->user->name }}</b>: {{ \Illuminate\Support\Str::limit($c->body, 80) }}<br><span style="color:#8e8e8e;font-size:11px;">di “{{ \Illuminate\Support\Str::limit($c->post->content ?? '', 50) }}”</span></div>
    <div class="act-time">{{ $c->created_at->diffForHumans() }}</div>
  </a>
  @empty
  <div class="act-empty"><i class="bi bi-chat"></i>Belum ada komentar baru.</div>
  @endforelse
</div>
@endsection
