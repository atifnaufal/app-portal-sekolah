@extends('layouts.mobile-page')
@section('content')
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
