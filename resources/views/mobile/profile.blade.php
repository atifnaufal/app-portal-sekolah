@extends('layouts.mobile-page')
@section('content')
<header class="mobile-hero"><div class="eyebrow">AKUN SAYA</div><div class="d-flex align-items-center gap-3 mt-3"><div class="avatar">@if($user->foto)<img src="{{ asset('storage/'.$user->foto) }}" alt="Foto profil" style="width:100%;height:100%;object-fit:cover;object-position:{{ $user->foto_posisi_x }}% {{ $user->foto_posisi_y }}%;border-radius:inherit">@else{{ strtoupper(substr($user->name,0,1)) }}@endif</div><div><div class="hero-title">Profil</div><div class="class-pill mt-2">{{ ucfirst($user->role) }}</div></div></div></header>
<main class="mobile-content">
    <div id="biometric-card" class="card mobile-card mb-3" style="display: none;">
        <div class="card-body p-4 d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-bold">Login Sidik Jari</div>
                <div class="small text-secondary mt-1">Gunakan biometrik untuk masuk aplikasi.</div>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="biometricToggle" style="width: 45px; height: 24px;">
            </div>
        </div>
    </div>

    <div class="card mobile-card mb-3">
        <div class="card-body p-4">
            <div class="text-secondary small">Nama lengkap</div>
            <div class="fw-bold mt-1">{{ $user->name }}</div>
            <hr>
            <div class="text-secondary small">Email</div>
            <div class="fw-bold mt-1">{{ $user->email }}</div>
            <hr>
            <div class="text-secondary small">Kelas</div>
            <div class="fw-bold mt-1">{{ $user->kelas?->nama ?? 'Belum ditentukan' }}</div>
        </div>
    </div>

    <a href="{{ route('profile.edit') }}" class="btn btn-primary w-100 py-3 profile-action">Edit profil</a>

    @if($user->role === 'admin')
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary w-100 py-3 mt-3 profile-action">
            &#9881; Buka Panel Admin Web
        </a>
    @endif

    <div class="logout-panel mt-4">
        <div>
            <div class="fw-bold">Keluar dari akun?</div>
            <div class="small text-secondary mt-1">Sesi akan diakhiri di perangkat ini.</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-danger">Keluar</button>
        </form>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const isNative = typeof Capacitor !== 'undefined';
        if (isNative) {
            const NativeBiometric = Capacitor.Plugins.NativeBiometric;
            if (NativeBiometric) {
                const result = await NativeBiometric.isAvailable();
                if (result.isAvailable) {
                    const card = document.getElementById('biometric-card');
                    const toggle = document.getElementById('biometricToggle');

                    card.style.display = 'block';
                    toggle.checked = localStorage.getItem('biometric_enabled') === 'true';

                    toggle.addEventListener('change', (e) => {
                        localStorage.setItem('biometric_enabled', e.target.checked);
                    });
                }
            }
        }
    });
</script>
@endsection
