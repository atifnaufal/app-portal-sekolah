@extends('layouts.mobile-app')
@section('content')
<div class="pui-topbar" style="padding-top:20px;">
    <a href="javascript:history.back()" class="back"><i class="bi bi-chevron-left"></i></a>
    <h1>Daftar Akun</h1>
    <div class="spacer"></div>
</div>
<div class="mobile-content px-3">
    <form action="{{ route('admin.users') }}" method="GET" class="mb-4 pui-field">
        <div class="pui-field" style="margin-bottom:0;">
            <input type="text" name="search" value="{{ request('search') }}" class="pui-input" placeholder="Cari nama, NIK, atau email...">
        </div>
    </form>

    <div class="stagger">
        @forelse($users as $user)
            <div class="pui-card mb-3" style="padding:14px 16px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="pui-avatar">
                            @if($user->foto)
                                <img src="{{ asset('storage/'.$user->foto) }}" alt="P">
                            @else
                                {{ strtoupper(substr($user->name,0,1)) }}
                            @endif
                        </div>
                        <div style="min-width:0;">
                            <div class="fw-bold" style="color:var(--ink);">{{ $user->name }}</div>
                            <div class="small" style="color:var(--faint);">{{ ucfirst($user->role) }}</div>
                            @if($user->role === 'guru')
                                @php
                                    $guruClasses = collect([$user->kelas])
                                        ->merge($user->mataPelajarans->pluck('kelas'))
                                        ->filter()->unique('id')
                                        ->sortBy([['tingkat','asc'],['nama','asc']])->values();
                                @endphp
                                @if($guruClasses->isNotEmpty())
                                    <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                                        @foreach($guruClasses as $availClass)
                                            <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:99px;background:#eef2ff;color:#4f46e5;border:1px solid #e0e7ff;font-size:10.5px;font-weight:800;">
                                                {{ $availClass->nama }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <div class="small" style="color:var(--faint);">{{ $user->kelas?->nama ?? '-' }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="form-check form-switch me-1">
                            <form action="{{ route('admin.user.toggle', $user) }}" method="POST" id="toggle-{{ $user->id }}">
                                @csrf @method('PATCH')
                                <input class="form-check-input" type="checkbox" onchange="document.getElementById('toggle-{{ $user->id }}').submit()" {{ $user->aktif ? 'checked' : '' }}>
                            </form>
                        </div>
                        <form action="{{ route('admin.user.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                            @csrf @method('DELETE')
                            <button class="pui-btn pui-btn-ghost pui-btn-sm px-2" style="color:#dc2626; box-shadow:none;" type="submit">&#128465;</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="pui-empty">
                <div class="ico">&#128101;</div>
                <h4>Akun tidak ditemukan</h4>
                <p>Coba ubah kata kunci pencarian.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
