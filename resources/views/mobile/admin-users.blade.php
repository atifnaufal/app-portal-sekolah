@extends('layouts.mobile-app')
@section('content')
<div class="p-3 pb-0">
    <a href="javascript:history.back()" class="text-decoration-none text-muted d-inline-flex align-items-center gap-2 small fw-bold">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
        Kembali
    </a>
</div>
<header class="mobile-hero">
    <div class="eyebrow">ADMINISTRASI</div>
    <div class="hero-title mt-2">Daftar Akun</div>
    <div class="class-pill mt-3">Guru & Siswa</div>
</header>
<main class="mobile-content">
    <form action="{{ route('admin.users') }}" method="GET" class="mb-4">
        <div class="input-group bg-white rounded-pill shadow-sm overflow-hidden">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control border-0 px-4" placeholder="Cari nama, NIK, atau email...">
            <button class="btn btn-white border-0 px-3" type="submit">&#128269;</button>
        </div>
    </form>

    <div class="stagger">
        @forelse($users as $user)
            <div class="card mobile-card mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar shadow-sm">
                                @if($user->foto)
                                    <img src="{{ asset('storage/'.$user->foto) }}" alt="P">
                                @else
                                    {{ strtoupper(substr($user->name,0,1)) }}
                                @endif
                            </div>
                            <div>
                                <div class="fw-bold">{{ $user->name }}</div>
                                <div class="small text-secondary">{{ ucfirst($user->role) }} · {{ $user->kelas?->nama ?? '-' }}</div>
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
                                <button class="btn btn-sm btn-outline-danger border-0 px-2" type="submit">&#128465;</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 opacity-50">
                <div class="h1">&#128101;</div>
                <p>Akun tidak ditemukan.</p>
            </div>
        @endforelse
    </div>
</main>
@endsection
