@extends('layouts.mobile-app')

@section('content')
<div class="pui-topbar">
    <a href="{{ route('profile.show') }}" class="back"><i class="bi bi-chevron-left"></i> Profil</a>
    <h1>Notifikasi</h1>
</div>

<div class="p-3 stagger">
    <div class="pui-section">
        <h3>Layanan Latar Belakang</h3>
    </div>

    <div class="pui-card mb-4">
        <div class="p-3 d-flex align-items-center justify-content-between border-bottom">
            <div>
                <div class="fw-bold" style="font-size:14px;">Polling Otomatis</div>
                <div class="text-muted" style="font-size:11px;">Cek notifikasi saat aplikasi tertutup</div>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="pollingSwitch" checked onchange="togglePolling(this)">
            </div>
        </div>
        <div class="p-3 d-flex align-items-center justify-content-between" onclick="openNativeSettings()">
            <div>
                <div class="fw-bold" style="font-size:14px;">Izin Sistem Android</div>
                <div class="text-muted" style="font-size:11px;">Kelola suara dan pop-up notifikasi</div>
            </div>
            <i class="bi bi-box-arrow-up-right text-muted"></i>
        </div>
    </div>

    <div class="pui-section">
        <h3>Kategori Notifikasi</h3>
    </div>
    <div class="pui-card mb-4">
        <div class="p-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="fw-bold" style="font-size:14px;">Pengumuman Sekolah</div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" checked>
            </div>
        </div>
        <div class="p-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="fw-bold" style="font-size:14px;">Pesan Chat</div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" checked>
            </div>
        </div>
        <div class="p-3 d-flex align-items-center justify-content-between">
            <div class="fw-bold" style="font-size:14px;">Tugas & Ujian</div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" checked>
            </div>
        </div>
    </div>

    <div class="pui-card p-3" style="background:#f0f9ff;border:1px solid #e0f2fe;">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-info-circle-fill text-primary" style="font-size:20px;"></i>
            <div>
                <div class="fw-bold text-primary" style="font-size:13px;">Catatan</div>
                <div class="text-muted" style="font-size:11px;line-height:1.5;">Menonaktifkan polling otomatis akan menghentikan notifikasi saat aplikasi tidak dibuka.</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    checkServiceStatus();
});

function checkServiceStatus() {
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        window.Capacitor.Plugins.NativeBridge.getAppInfo().then(function(res) {
            document.getElementById('pollingSwitch').checked = res.serviceRunning;
        });
    }
}

function togglePolling(el) {
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        if (el.checked) {
            window.Capacitor.Plugins.NativeBridge.startService();
            showNotification('Notifikasi', 'Layanan latar belakang diaktifkan.');
        } else {
            window.Capacitor.Plugins.NativeBridge.stopService();
            showNotification('Notifikasi', 'Layanan latar belakang dinonaktifkan.');
        }
    }
}

function openNativeSettings() {
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        window.Capacitor.Plugins.NativeBridge.openAppSettings();
    }
}
</script>
@endsection
