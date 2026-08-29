@extends('layouts.mobile-app')

@section('content')
<div class="pui-topbar">
    <a href="{{ route('profile.show') }}" class="back"><i class="bi bi-chevron-left"></i> Profil</a>
    <h1>Keamanan</h1>
</div>

<div class="p-3 stagger">
    <div class="pui-section">
        <h3>Akses Aplikasi</h3>
    </div>

    <div class="pui-card mb-4">
        <div class="p-3 d-flex align-items-center justify-content-between border-bottom">
            <div>
                <div class="fw-bold" style="font-size:14px;">Kunci Biometrik</div>
                <div class="text-muted" style="font-size:11px;">Gunakan Sidik Jari / Wajah</div>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="biometricSwitch" onchange="toggleBiometric(this)">
            </div>
        </div>
        <div class="p-3 d-flex align-items-center justify-content-between" onclick="alert('Fitur PIN segera hadir')">
            <div>
                <div class="fw-bold" style="font-size:14px;">Ganti PIN Keamanan</div>
                <div class="text-muted" style="font-size:11px;">Amankan akses fitur penting</div>
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
        </div>
    </div>

    <div class="pui-section">
        <h3>Akun</h3>
    </div>
    <div class="pui-card mb-4">
        <a href="{{ route('password.request') }}" class="p-3 d-flex align-items-center justify-content-between text-decoration-none text-dark">
            <div>
                <div class="fw-bold" style="font-size:14px;">Ubah Kata Sandi</div>
                <div class="text-muted" style="font-size:11px;">Perbarui password secara berkala</div>
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
        </a>
    </div>

    <div class="pui-card p-3" style="background:#fff5f5;border:1px solid #fee2e2;">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-shield-fill-exclamation text-danger" style="font-size:20px;"></i>
            <div>
                <div class="fw-bold text-danger" style="font-size:13px;">Tips Keamanan</div>
                <div class="text-muted" style="font-size:11px;line-height:1.5;">Jangan pernah memberikan kode OTP atau password Anda kepada siapapun, termasuk staf sekolah.</div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleBiometric(el) {
    if (el.checked) {
        // Logika aktivasi biometrik via native bridge nantinya
        showNotification('Keamanan', 'Biometrik diaktifkan untuk perangkat ini.');
    }
}
</script>
@endsection
