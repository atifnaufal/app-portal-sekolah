@extends('layouts.mobile-app')

@section('content')
<div class="pui-topbar">
    <a href="{{ route('profile.show') }}" class="back"><i class="bi bi-chevron-left"></i> Profil</a>
    <h1>Tentang Aplikasi</h1>
</div>

<div class="p-4 text-center">
    <div class="mb-4">
        <div style="width:100px;height:100px;background:#fff;border-radius:28px;margin:0 auto;display:grid;place-items:center;box-shadow:var(--shadow-card);">
            <img src="{{ asset('logo_sekolah.png') }}" style="width:70%;" alt="Logo" onerror="this.src='https://ui-avatars.com/api/?name=PS&background=0f172a&color=fff'">
        </div>
    </div>

    <h3 class="fw-bold mb-1">Portal Sekolah</h3>
    <p class="text-muted small mb-4">Versi 1.1.0 (Premium Build)</p>

    <div class="pui-card text-start stagger">
        <div class="p-3 border-bottom d-flex justify-content-between">
            <span class="text-muted small">Update Terakhir</span>
            <span class="fw-bold small">Agustus 2026</span>
        </div>
        <div class="p-3 border-bottom d-flex justify-content-between">
            <span class="text-muted small">Pengembang</span>
            <span class="fw-bold small">IT Team Sekolah</span>
        </div>
        <div class="p-3 d-flex justify-content-between">
            <span class="text-muted small">Lisensi</span>
            <span class="fw-bold small">Enterprise Edition</span>
        </div>
    </div>

    <div class="mt-5 small text-muted px-3" style="line-height:1.6;">
        Aplikasi Portal Sekolah dirancang untuk memudahkan interaksi antara siswa, guru, dan orang tua dalam ekosistem pendidikan digital yang terintegrasi.
    </div>

    <div class="mt-5">
        <a href="{{ route('legal.terms') }}" class="text-decoration-none small fw-bold text-primary">Syarat & Ketentuan</a>
        <span class="mx-2 text-muted">|</span>
        <a href="{{ route('legal.privacy') }}" class="text-decoration-none small fw-bold text-primary">Kebijakan Privasi</a>
    </div>

    <p class="mt-4 text-muted" style="font-size:10px;">&copy; 2026 App Portal Sekolah. All rights reserved.</p>
</div>
@endsection
