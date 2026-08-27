@extends('layouts.mobile-app')

@section('content')
@php $hideNav = true; @endphp
<div class="mobile-content py-5">
    <div class="text-center mb-4">
        <div class="icon-box bg-dark text-white mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 20px; display: grid; place-items: center;">
            <i class="bi bi-cpu-fill h3 mb-0"></i>
        </div>
        <h1 class="h4 fw-bold">Simulator Verifikasi</h1>
        <p class="text-secondary small">Gunakan alat ini untuk memverifikasi akun tanpa menunggu email asli (Mode Developer).</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 rounded-4 small mb-4 animate__animated animate__fadeIn">
            {{ session('success') }}
        </div>
    @endif

    <div class="stagger">
        @forelse($pendingUsers as $user)
            <div class="card ai-card mb-3 border-0 shadow-sm" style="border-radius: 22px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar bg-primary bg-opacity-10 text-primary" style="width: 40px; height: 40px; border-radius: 12px;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-dark text-truncate">{{ $user->name }}</div>
                            <div class="x-small text-muted text-truncate">{{ $user->email }}</div>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-4 mb-3 border border-dashed border-secondary border-opacity-25">
                        <div class="x-small fw-bold text-secondary mb-1">PESAN SIMULASI:</div>
                        <div class="small italic text-muted">"Halo, silakan klik tombol di bawah untuk mengaktifkan akun Anda..."</div>
                    </div>

                    <form method="POST" action="{{ route('email.simulator.verify', $user->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 py-3 shadow-sm" style="border-radius: 15px; font-weight: 800;">
                            VERIFIKASI INSTAN &rarr;
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-5 opacity-50 bg-white rounded-4 border">
                <i class="bi bi-check2-all display-4 text-success"></i>
                <div class="fw-bold mt-2">Semua Beres!</div>
                <div class="small">Tidak ada user yang menunggu verifikasi.</div>
            </div>
        @endforelse
    </div>

    <div class="text-center mt-5">
        <a href="{{ route('login') }}" class="btn btn-link text-decoration-none text-muted fw-bold small">
            &larr; Kembali ke Login
        </a>
    </div>
</div>

<style>
    .ai-card { background: #fff; border: 1px solid #f1f5f9; transition: transform 0.2s; }
    .ai-card:active { transform: scale(0.98); }
</style>
@endsection
