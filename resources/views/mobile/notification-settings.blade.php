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
        <div class="p-3 d-flex align-items-center justify-content-between border-bottom" onclick="requestOptimizationExemption()">
            <div>
                <div class="fw-bold" style="font-size:14px;">Optimasi Baterai</div>
                <div id="batteryStatus" class="text-muted" style="font-size:11px;">Mengecek status...</div>
            </div>
            <i class="bi bi-shield-check text-muted"></i>
        </div>
        <div class="p-3 d-flex align-items-center justify-content-between border-bottom" onclick="requestExactAlarm()">
            <div>
                <div class="fw-bold" style="font-size:14px;">Penjadwalan Tepat Waktu</div>
                <div id="alarmStatus" class="text-muted" style="font-size:11px;">Mengecek status...</div>
            </div>
            <i class="bi bi-alarm text-muted"></i>
        </div>
        <div class="p-3 d-flex align-items-center justify-content-between border-bottom">
            <div>
                <div class="fw-bold" style="font-size:14px;">Status Push (FCM)</div>
                <div id="fcmStatus" class="text-muted" style="font-size:11px;">Mengecek...</div>
            </div>
            <span id="fcmBadge" class="pui-chip pui-chip-secondary" style="font-size:10px;">--</span>
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

        window.Capacitor.Plugins.NativeBridge.checkBatteryExemption().then(function(res) {
            var statusEl = document.getElementById('batteryStatus');
            if (res.isExempted) {
                statusEl.textContent = 'Sudah diizinkan (Latar belakang stabil)';
                statusEl.className = 'text-success small';
            } else {
                statusEl.textContent = 'Dibatasi sistem (Klik untuk izinkan)';
                statusEl.className = 'text-warning small';
            }
        });

        window.Capacitor.Plugins.NativeBridge.checkExactAlarmSupport().then(function(res) {
            var statusEl = document.getElementById('alarmStatus');
            if (res.isGranted) {
                statusEl.textContent = 'Aktif (Notifikasi instan)';
                statusEl.className = 'text-success small';
            } else {
                statusEl.textContent = 'Mungkin tertunda (Klik untuk aktifkan)';
                statusEl.className = 'text-warning small';
            }
        });

        checkFcmTokenStatus();
    }
}

function checkFcmTokenStatus() {
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        window.Capacitor.Plugins.NativeBridge.getFcmToken().then(function(res) {
            var statusEl = document.getElementById('fcmStatus');
            var badge = document.getElementById('fcmBadge');
            if (res.available) {
                statusEl.textContent = 'Token terdaftar';
                statusEl.className = 'text-success small';
                badge.textContent = 'Aktif';
                badge.className = 'pui-chip pui-chip-success';
            } else {
                statusEl.textContent = 'Token belum diambil';
                statusEl.className = 'text-warning small';
                badge.textContent = 'Menunggu';
                badge.className = 'pui-chip pui-chip-warning';
                // Auto-request token
                requestFcmToken();
            }
        }).catch(function() {
            document.getElementById('fcmStatus').textContent = 'Gagal cek status';
            document.getElementById('fcmStatus').className = 'text-danger small';
        });
    }
}

function requestFcmToken() {
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        window.Capacitor.Plugins.NativeBridge.requestFcmToken().then(function(res) {
            if (res.available) {
                document.getElementById('fcmStatus').textContent = 'Token terdaftar';
                document.getElementById('fcmStatus').className = 'text-success small';
                document.getElementById('fcmBadge').textContent = 'Aktif';
                document.getElementById('fcmBadge').className = 'pui-chip pui-chip-success';
            }
        }).catch(function(err) {
            document.getElementById('fcmStatus').textContent = 'Gagal: ' + (err.message || 'unknown');
            document.getElementById('fcmStatus').className = 'text-danger small';
        });
        setTimeout(checkFcmTokenStatus, 3000);
    }
}

function requestExactAlarm() {
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        window.Capacitor.Plugins.NativeBridge.requestExactAlarmPermission().then(function() {
            setTimeout(checkServiceStatus, 2000);
        });
    }
}

function requestOptimizationExemption() {
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        window.Capacitor.Plugins.NativeBridge.requestBatteryExemption().then(function() {
            // Re-check after returning
            setTimeout(checkServiceStatus, 2000);
        });
    }
}

function togglePolling(el) {
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        if (el.checked) {
            window.Capacitor.Plugins.NativeBridge.requestFcmToken().then(function() {
                window.Capacitor.Plugins.NativeBridge.startService();
                showNotification('Notifikasi', 'Layanan latar belakang & push aktif.');
            }).catch(function() {
                window.Capacitor.Plugins.NativeBridge.startService();
                showNotification('Notifikasi', 'Layanan latar belakang diaktifkan.');
            });
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
