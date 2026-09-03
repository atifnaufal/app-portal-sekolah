{{-- Aktivitas Global Portal versi WEB DESKTOP. --}}
@extends('layouts.app', ['title' => 'Aktivitas Portal'])
@section('content')
<style>
    .gp-card { border-radius: 20px; border: 1px solid var(--border); background: #fff; box-shadow: var(--shadow); overflow: hidden; }
    .gp-card-head { padding: 18px 24px; border-bottom: 1px solid var(--border); }
    .gp-card-title { font-size: 15px; font-weight: 800; color: var(--navy); margin: 0; }
    .act-row { display: flex; gap: 14px; align-items: center; padding: 14px 24px; }
    .act-row + .act-row { border-top: 1px solid #f8fafc; }
    .act-ava { width: 44px; height: 44px; border-radius: 14px; object-fit: cover; flex-shrink: 0; }
    .act-ico { width: 44px; height: 44px; border-radius: 14px; display: grid; place-items: center; color: #fff; font-size: 18px; flex-shrink: 0; }
    .act-sec { padding: 16px 24px 4px; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
</style>

<a href="{{ route('global.portal') }}" class="btn btn-sm btn-outline-secondary mb-3" style="border-radius:10px;"><i class="bi bi-arrow-left me-1"></i> Kembali ke Portal</a>

<div class="gp-card">
    <div class="gp-card-head"><h2 class="gp-card-title"><i class="bi bi-heart-fill me-2" style="color:#e11d48;"></i>Aktivitas di Postinganmu</h2></div>

    <div class="act-sec">Pengikut Baru ({{ $followers->count() }})</div>
    @forelse($followers as $f)
    <div class="act-row">
        <div class="act-ico" style="background:linear-gradient(135deg,#4f46e5,#2563eb);"><i class="bi bi-person-plus-fill"></i></div>
        <div class="flex-fill"><b style="font-size:14px;">{{ $f->follower->name }}</b> <span class="text-muted small">mulai mengikutimu.</span></div>
        <span class="text-muted small">{{ $f->created_at->diffForHumans() }}</span>
    </div>
    @empty
    <div class="px-4 py-2 text-muted small">Belum ada pengikut baru.</div>
    @endforelse

    <div class="act-sec">Suka ({{ $likes->count() }})</div>
    @forelse($likes as $l)
    <div class="act-row">
        <img src="{{ $l->user->avatar_url }}" class="act-ava" alt="">
        <div class="flex-fill" style="font-size:13.5px;"><b>{{ $l->user->name }}</b> menyukai: "{{ \Illuminate\Support\Str::limit($l->post->content ?? '', 80) }}"</div>
        <span class="text-muted small">{{ $l->created_at->diffForHumans() }}</span>
    </div>
    @empty
    <div class="px-4 py-2 text-muted small">Belum ada suka baru.</div>
    @endforelse

    <div class="act-sec">Komentar Terbaru ({{ $comments->count() }})</div>
    @forelse($comments as $c)
    <div class="act-row">
        <img src="{{ $c->user->avatar_url }}" class="act-ava" alt="">
        <div class="flex-fill" style="font-size:13.5px;"><b>{{ $c->user->name }}</b>: {{ $c->body }}<br><span class="text-muted small">di "{{ \Illuminate\Support\Str::limit($c->post->content ?? '', 70) }}"</span></div>
        <span class="text-muted small">{{ $c->created_at->diffForHumans() }}</span>
    </div>
    @empty
    <div class="px-4 py-2 text-muted small">Belum ada komentar baru.</div>
    @endforelse
</div>
@endsection
