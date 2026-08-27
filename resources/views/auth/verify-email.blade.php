@extends('layouts.mobile-app')
@section('content')
<div class="mobile-content d-flex flex-column align-items-center justify-content-center min-vh-100 text-center px-4">
    <div class="icon-box bg-primary bg-opacity-10 text-primary mb-4" style="width: 80px; height: 80px; border-radius: 25px;">
        <i class="bi bi-envelope-check display-5"></i>
    </div>

    <h1 class="h3 fw-bold mb-3">Verifikasi Email Anda</h1>
    <p class="text-secondary mb-4">
        Kami telah mengirimkan link verifikasi ke
        <strong>{{ auth()->user()->email }}</strong>.<br>
        Silakan klik link tersebut untuk mengaktifkan akun Anda.
    </p>

    @if (session('error'))
        <div class="alert alert-danger border-0 rounded-4 small mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4 small mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="w-100">
        @csrf
        <button type="submit" class="btn btn-primary w-100 py-3 shadow" style="border-radius: 15px; font-weight: 800;">
            Kirim Ulang Link Verifikasi
        </button>
    </form>

    <div class="x-small text-muted mt-3" style="line-height:1.6;">
        Maksimal 6 kali pengiriman per menit.<br>
        Tidak menerima email? Cek folder spam, lalu tunggu 1 menit dan coba lagi.
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="btn btn-link text-decoration-none text-muted fw-bold">
            Keluar (Logout)
        </button>
    </form>
</div>
@endsection
