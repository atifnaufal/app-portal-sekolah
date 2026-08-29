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
                <div id="biometricDesc" class="text-muted" style="font-size:11px;">Gunakan Sidik Jari / Wajah</div>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="biometricSwitch" onchange="toggleBiometric(this)">
            </div>
        </div>
        <div class="p-3 d-flex align-items-center justify-content-between" onclick="showPinModal()">
            <div>
                <div class="fw-bold" style="font-size:14px;">PIN Keamanan</div>
                <div id="pinStatus" class="text-muted" style="font-size:11px;">Belum diatur</div>
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
        </div>
    </div>

    <!-- PIN Modal -->
    <div id="pinModal" style="position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:10005;display:none;align-items:center;justify-content:center;padding:20px;">
        <div class="pui-card p-4 w-100" style="max-width:320px;">
            <h5 class="fw-bold mb-3">Atur PIN Baru</h5>
            <input type="number" id="pinInput" class="pui-input mb-3" placeholder="4-6 digit" maxlength="6">
            <div class="d-flex gap-2">
                <button class="pui-btn pui-btn-ghost flex-1" onclick="closePinModal()">Batal</button>
                <button class="pui-btn pui-btn-primary flex-1" onclick="saveNewPin()">Simpan</button>
            </div>
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

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    checkInitialState();
});

function checkInitialState() {
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        // Check biometric support
        window.Capacitor.Plugins.NativeBridge.checkBiometricSupport().then(function(res) {
            if (!res.isAvailable) {
                document.getElementById('biometricSwitch').disabled = true;
                document.getElementById('biometricDesc').textContent = 'Perangkat tidak mendukung biometrik';
            }
        });

        // Load saved state for switch (can be from localStorage)
        var bioEnabled = localStorage.getItem('biometric_enabled') === 'true';
        document.getElementById('biometricSwitch').checked = bioEnabled;

        // Check PIN status
        // PIN saved natively, we just check if it exists in local storage sync
        var pinSet = localStorage.getItem('pin_set') === 'true';
        document.getElementById('pinStatus').textContent = pinSet ? 'Aktif' : 'Belum diatur';
    }
}

function toggleBiometric(el) {
    if (el.checked) {
        // Here we could prompt for fingerprint verification before enabling
        localStorage.setItem('biometric_enabled', 'true');
        showNotification('Keamanan', 'Biometrik diaktifkan.');
    } else {
        localStorage.setItem('biometric_enabled', 'false');
        showNotification('Keamanan', 'Biometrik dinonaktifkan.');
    }
}

function showPinModal() {
    document.getElementById('pinModal').style.display = 'flex';
}
function closePinModal() {
    document.getElementById('pinModal').style.display = 'none';
}
function saveNewPin() {
    var pin = document.getElementById('pinInput').value;
    if (pin.length < 4) {
        alert('PIN minimal 4 digit');
        return;
    }
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        window.Capacitor.Plugins.NativeBridge.savePin({ pin: pin }).then(function() {
            localStorage.setItem('pin_set', 'true');
            document.getElementById('pinStatus').textContent = 'Aktif';
            closePinModal();
            showNotification('Keamanan', 'PIN berhasil disimpan.');
        }).catch(function(e) {
            alert('Gagal simpan PIN: ' + e.message);
        });
    }
}
</script>
@endsection
