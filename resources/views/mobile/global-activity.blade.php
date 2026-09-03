@php $hideNav = false; $title = 'Aktivitas'; @endphp
@extends('layouts.mobile-app')
@section('content')
<style>
.act-page{max-width:640px;margin:0 auto;padding-bottom:110px;background:#fff;min-height:100vh}
.act-header{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.94);backdrop-filter:blur(12px);border-bottom:1px solid #efefef;display:flex;align-items:center;gap:10px;padding:10px 14px;padding-top:calc(10px + env(safe-area-inset-top))}
.act-back{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;color:#0f172a;text-decoration:none;font-size:18px;flex-shrink:0}
.act-title{flex:1;text-align:center;font-size:17px;font-weight:800}
.act-sec{font-size:12px;font-weight:800;color:#8e8e8e;text-transform:uppercase;letter-spacing:.05em;padding:16px 14px 6px}
.act-row{display:flex;gap:12px;align-items:center;padding:10px 14px;text-decoration:none;color:inherit}
.act-row + .act-row{border-top:1px solid #f8fafc}
.act-ava{width:42px;height:42px;border-radius:50%;object-fit:cover;flex-shrink:0}
.act-ico{width:42px;height:42px;border-radius:50%;display:grid;place-items:center;flex-shrink:0;font-size:18px;color:#fff}
.act-txt{flex:1;font-size:13px;line-height:1.45;min-width:0}
.act-time{font-size:11px;color:#8e8e8e;flex-shrink:0}
.act-thumb{width:44px;height:44px;border-radius:10px;object-fit:cover;background:#fafafa;flex-shrink:0}
</style>
<div class="act-page">
  <div class="act-header">
    <a href="{{ route('global.portal') }}" class="act-back"><i class="bi bi-chevron-left"></i></a>
    <div class="act-title">Aktivitas</div>
    <div style="width:34px;"></div>
  </div>

  <div class="act-sec"><i class="bi bi-person-plus me-1"></i>Pengikut Baru ({{ $followers->count() }})</div>
  @forelse($followers as $f)
  <div class="act-row">
    <div class="act-ico" style="background:linear-gradient(135deg,#4f46e5,#2563eb);"><i class="bi bi-person-plus-fill"></i></div>
    <div class="act-txt"><b>{{ $f->follower->name }}</b> mulai mengikutimu.</div>
    <div class="act-time">{{ $f->created_at->diffForHumans() }}</div>
  </div>
  @empty
  <div style="padding:6px 14px;font-size:13px;color:#8e8e8e;">Belum ada pengikut baru.</div>
  @endforelse

  <div class="act-sec"><i class="bi bi-heart-fill me-1" style="color:#ed4956;"></i>Suka di Postinganmu ({{ $likes->count() }})</div>
  @forelse($likes as $l)
  <a class="act-row" href="{{ route('global.portal') }}#cmt-{{ $l->global_post_id }}">
    <img src="{{ $l->user->avatar_url }}" class="act-ava" alt="">
    <div class="act-txt"><b>{{ $l->user->name }}</b> menyukai postinganmu: "{{ \Illuminate\Support\Str::limit($l->post->content ?? '', 60) }}"</div>
    <div class="act-time">{{ $l->created_at->diffForHumans() }}</div>
  </a>
  @empty
  <div style="padding:6px 14px;font-size:13px;color:#8e8e8e;">Belum ada suka baru.</div>
  @endforelse

  <div class="act-sec"><i class="bi bi-chat-fill me-1" style="color:#0095f6;"></i>Komentar Terbaru ({{ $comments->count() }})</div>
  @forelse($comments as $c)
  <a class="act-row" href="{{ route('global.portal') }}#cmt-{{ $c->global_post_id }}">
    <img src="{{ $c->user->avatar_url }}" class="act-ava" alt="">
    <div class="act-txt"><b>{{ $c->user->name }}</b>: {{ \Illuminate\Support\Str::limit($c->body, 80) }}<br><span style="color:#8e8e8e;font-size:11px;">di "{{ \Illuminate\Support\Str::limit($c->post->content ?? '', 50) }}"</span></div>
    <div class="act-time">{{ $c->created_at->diffForHumans() }}</div>
  </a>
  @empty
  <div style="padding:6px 14px;font-size:13px;color:#8e8e8e;">Belum ada komentar baru.</div>
  @endforelse
</div>
@endsection
