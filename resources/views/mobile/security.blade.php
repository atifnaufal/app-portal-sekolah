@extends('layouts.mobile-app')

@section('content')
<style>
    .sec-option { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border-bottom:1px solid var(--line); cursor:pointer; }
    .sec-option:last-child { border-bottom:none; }
    .sec-option:active { background:var(--surface); }
    .sec-label { font-size:14px; font-weight:700; color:var(--ink); }
    .sec-desc { font-size:11px; color:var(--mist); margin-top:2px; }
    .sec-value { font-size:13px; font-weight:700; color:var(--blue); }
    .sec-badge { padding:4px 10px; border-radius:8px; font-size:11px; font-weight:700; }
    .sec-badge.active { background:#dcfce7; color:#16a34a; }
    .sec-badge.inactive { background:#f1f5f9; color:#94a3b8; }
    .sec-timeout-modal { position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:10005; display:none; align-items:center; justify-content:center; padding:20px; }
    .sec-timeout-modal.show { display:flex; }
    .timeout-option { padding:14px 16px; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; cursor:pointer; }
    .timeout-option:active { background:var(--surface); }
    .timeout-option.selected { background:#eff6ff; }
</style>

<div class="pui-topbar">
    <a href="{{ route('profile.show') }}" class="back"><i class="bi bi-chevron-left"></i> Profil</a>
    <h1>Keamanan</h1>
</div>

<div class="p-3 stagger">
    <div class="pui-section">
        <h3>Akses Aplikasi</h3>
    </div>

    <div class="pui-card mb-4" style="border-radius:var(--radius-md);overflow:hidden;">
        <div class="sec-option" onclick="toggleBiometricSwitch()">
            <div>
                <div class="sec-label">Kunci Biometrik</div>
                <div id="biometricDesc" class="sec-desc">Gunakan Sidik Jari / Wajah</div>
            </div>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="biometricSwitch">
            </div>
        </div>

        <div class="sec-option" onclick="showPinModal()">
            <div>
                <div class="sec-label">PIN Keamanan</div>
                <div id="pinStatus" class="sec-desc">Belum diatur</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span id="pinBadge" class="sec-badge inactive">OFF</span>
                <i class="bi bi-chevron-right text-muted"></i>
            </div>
        </div>

        <div id="removePinRow" class="sec-option" style="display:none;" onclick="removePin()">
            <div>
                <div class="sec-label" style="color:#ef4444;">Nonaktifkan PIN</div>
                <div class="sec-desc">Hapus PIN keamanan dari perangkat</div>
            </div>
            <i class="bi bi-trash" style="color:#ef4444;"></i>
        </div>

        <div class="sec-option" onclick="showTimeoutModal()">
            <div>
                <div class="sec-label">Auto-Lock Timeout</div>
                <div id="timeoutDesc" class="sec-desc">Kunci otomatis setelah tidak aktif</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span id="timeoutValue" class="sec-value">60d</span>
                <i class="bi bi-chevron-right text-muted"></i>
            </div>
        </div>
    </div>

    <div class="pui-section">
        <h3>Akun</h3>
    </div>
    <div class="pui-card mb-4" style="border-radius:var(--radius-md);overflow:hidden;">
        <a href="{{ route('profile.edit') }}" class="sec-option text-decoration-none text-dark">
            <div>
                <div class="sec-label">Ubah Kata Sandi</div>
                <div class="sec-desc">Perbarui password secara berkala</div>
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
        </a>
    </div>

    <div class="pui-card p-3" style="background:#fff5f5;border:1px solid #fee2e2;border-radius:var(--radius-md);">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-shield-fill-exclamation text-danger" style="font-size:20px;"></i>
            <div>
                <div class="fw-bold text-danger" style="font-size:13px;">Tips Keamanan</div>
                <div class="text-muted" style="font-size:11px;line-height:1.5;">Jangan pernah memberikan kode OTP atau password Anda kepada siapapun, termasuk staf sekolah.</div>
            </div>
        </div>
    </div>
</div>

<!-- PIN Modal -->
<div id="pinModal" class="sec-timeout-modal">
    <div class="pui-card p-4 w-100" style="max-width:320px;border-radius:var(--radius-lg);">
        <h5 class="fw-bold mb-3" id="pinModalTitle">Atur PIN Baru</h5>
        <div id="pinCurrentWrap" style="display:none;">
            <input type="password" id="pinCurrent" class="pui-input mb-2" placeholder="PIN saat ini" maxlength="6" inputmode="numeric">
        </div>
        <input type="password" id="pinInput" class="pui-input mb-2" placeholder="4-6 digit" maxlength="6" inputmode="numeric">
        <input type="password" id="pinConfirm" class="pui-input mb-3" placeholder="Konfirmasi PIN" maxlength="6" inputmode="numeric">
        <div id="pinError" class="text-danger small mb-2" style="display:none;"></div>
        <div class="d-flex gap-2">
            <button class="pui-btn pui-btn-ghost flex-1" onclick="closePinModal()">Batal</button>
            <button class="pui-btn pui-btn-primary flex-1" onclick="saveNewPin()">Simpan</button>
        </div>
    </div>
</div>

<!-- Timeout Modal -->
<div id="timeoutModal" class="sec-timeout-modal">
    <div class="pui-card p-4 w-100" style="max-width:320px;border-radius:var(--radius-lg);">
        <h5 class="fw-bold mb-3">Auto-Lock Timeout</h5>
        <p class="text-muted small mb-3">Kunci otomatis setelah durasi tidak aktif</p>
        <div id="timeoutOptions"></div>
        <button class="pui-btn pui-btn-ghost w-100 mt-2" onclick="closeTimeoutModal()">Batal</button>
    </div>
</div>

<!-- Remove PIN Modal -->
<div id="removePinModal" class="sec-timeout-modal">
    <div class="pui-card p-4 w-100" style="max-width:320px;border-radius:var(--radius-lg);">
        <h5 class="fw-bold mb-3">Nonaktifkan PIN</h5>
        <input type="password" id="removePinCurrent" class="pui-input mb-2" placeholder="Masukkan PIN saat ini" maxlength="6" inputmode="numeric">
        <div id="removePinError" class="text-danger small mb-2" style="display:none;"></div>
        <div class="d-flex gap-2">
            <button class="pui-btn pui-btn-ghost flex-1" onclick="closeRemovePinModal()">Batal</button>
            <button class="pui-btn pui-btn-primary flex-1" onclick="confirmRemovePin()" style="background:#ef4444;">Nonaktifkan</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
var PIN_MODE = 'set'; // 'set' atau 'change'
var TIMEOUT_OPTIONS = [
    { label: '30 Detik', value: 30 },
    { label: '1 Menit', value: 60 },
    { label: '2 Menit', value: 120 },
    { label: '5 Menit', value: 300 },
    { label: '10 Menit', value: 600 },
    { label: 'Tidak Pernah', value: 0 }
];

document.addEventListener('DOMContentLoaded', function() {
    loadState();
    renderTimeoutOptions();
});

function loadState() {
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        // Biometric support
        window.Capacitor.Plugins.NativeBridge.checkBiometricSupport().then(function(res) {
            if (!res.isAvailable) {
                document.getElementById('biometricSwitch').disabled = true;
                document.getElementById('biometricDesc').textContent = 'Perangkat tidak mendukung';
            }
        });
        document.getElementById('biometricSwitch').checked = localStorage.getItem('biometric_enabled') === 'true';
    }

    // PIN status
    var pinSet = localStorage.getItem('pin_set') === 'true';
    document.getElementById('pinStatus').textContent = pinSet ? 'Aktif (terlindungi)' : 'Belum diatur';
    var badge = document.getElementById('pinBadge');
    badge.textContent = pinSet ? 'ON' : 'OFF';
    badge.className = 'sec-badge ' + (pinSet ? 'active' : 'inactive');
    document.getElementById('removePinRow').style.display = pinSet ? 'flex' : 'none';

    // Timeout
    var timeout = parseInt(localStorage.getItem('auto_lock_seconds') || '60', 10);
    updateTimeoutDisplay(timeout);
}

function updateTimeoutDisplay(val) {
    var el = document.getElementById('timeoutValue');
    var desc = document.getElementById('timeoutDesc');
    if (val === 0) {
        el.textContent = 'OFF';
        desc.textContent = 'Tidak mengunci otomatis';
    } else if (val < 60) {
        el.textContent = val + 'd';
        desc.textContent = 'Kunci setelah ' + val + ' detik tidak aktif';
    } else {
        el.textContent = (val / 60) + 'm';
        desc.textContent = 'Kunci setelah ' + (val / 60) + ' menit tidak aktif';
    }
}

// ===== BIOMETRIC =====
function toggleBiometricSwitch() {
    var sw = document.getElementById('biometricSwitch');
    sw.checked = !sw.checked;
    toggleBiometric(sw);
}

function toggleBiometric(el) {
    if (el.checked) {
        // Biometric requires PIN to be set first
        if (localStorage.getItem('pin_set') !== 'true') {
            el.checked = false;
            showNotif('Atur PIN keamanan terlebih dahulu.', 'error');
            return;
        }
        if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
            window.Capacitor.Plugins.NativeBridge.checkBiometricSupport().then(function(res) {
                if (res.isAvailable) {
                    window.Capacitor.Plugins.NativeBridge.performBiometricAuth().then(function(r) {
                        if (r && r.cancelled) { el.checked = false; return; }
                        localStorage.setItem('biometric_enabled', 'true');
                        showNotif('Biometrik diaktifkan.');
                    }).catch(function() {
                        el.checked = false;
                        showNotif('Gagal aktivasi biometrik.', 'error');
                    });
                } else {
                    el.checked = false;
                    showNotif('Biometrik tidak tersedia.', 'error');
                }
            });
        }
    } else {
        localStorage.setItem('biometric_enabled', 'false');
        showNotif('Biometrik dinonaktifkan.');
    }
}

// ===== PIN =====
function showPinModal() {
    var pinSet = localStorage.getItem('pin_set') === 'true';
    PIN_MODE = pinSet ? 'change' : 'set';
    document.getElementById('pinModalTitle').textContent = pinSet ? 'Ubah PIN' : 'Atur PIN Baru';
    document.getElementById('pinCurrentWrap').style.display = pinSet ? 'block' : 'none';
    document.getElementById('pinInput').value = '';
    document.getElementById('pinConfirm').value = '';
    document.getElementById('pinCurrent').value = '';
    document.getElementById('pinError').style.display = 'none';
    document.getElementById('pinModal').classList.add('show');
}

function closePinModal() {
    document.getElementById('pinModal').classList.remove('show');
}

function saveNewPin() {
    var current = document.getElementById('pinCurrent').value;
    var pin = document.getElementById('pinInput').value;
    var confirm = document.getElementById('pinConfirm').value;
    var errEl = document.getElementById('pinError');

    if (PIN_MODE === 'change' && current.length < 4) {
        errEl.textContent = 'Masukkan PIN saat ini';
        errEl.style.display = 'block';
        return;
    }

    if (pin.length < 4 || pin.length > 6) {
        errEl.textContent = 'PIN harus 4-6 digit';
        errEl.style.display = 'block';
        return;
    }

    if (pin !== confirm) {
        errEl.textContent = 'Konfirmasi PIN tidak cocok';
        errEl.style.display = 'block';
        return;
    }

    if (PIN_MODE === 'change') {
        // Verify old PIN first
        if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
            window.Capacitor.Plugins.NativeBridge.verifyPin({ pin: current }).then(function(res) {
                if (!res.isValid) {
                    errEl.textContent = 'PIN saat ini salah';
                    errEl.style.display = 'block';
                    return;
                }
                doSavePin(pin);
            });
        }
    } else {
        doSavePin(pin);
    }
}

function doSavePin(pin) {
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        window.Capacitor.Plugins.NativeBridge.savePin({ pin: pin }).then(function() {
            localStorage.setItem('pin_set', 'true');
            closePinModal();
            loadState();
            showNotif('PIN berhasil disimpan.');
        }).catch(function(e) {
            document.getElementById('pinError').textContent = e.message || 'Gagal simpan';
            document.getElementById('pinError').style.display = 'block';
        });
    }
}

function removePin() {
    document.getElementById('removePinCurrent').value = '';
    document.getElementById('removePinError').style.display = 'none';
    document.getElementById('removePinModal').classList.add('show');
}

function closeRemovePinModal() {
    document.getElementById('removePinModal').classList.remove('show');
}

function confirmRemovePin() {
    var current = document.getElementById('removePinCurrent').value;
    var errEl = document.getElementById('removePinError');
    if (!current || current.length < 4) {
        errEl.textContent = 'Masukkan PIN saat ini';
        errEl.style.display = 'block';
        return;
    }
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        window.Capacitor.Plugins.NativeBridge.verifyPin({ pin: current }).then(function(res) {
            if (!res.isValid) {
                errEl.textContent = 'PIN salah';
                errEl.style.display = 'block';
                return;
            }
            window.Capacitor.Plugins.NativeBridge.savePin({ pin: '' }).then(function() {
                localStorage.setItem('pin_set', 'false');
                localStorage.setItem('biometric_enabled', 'false');
                sessionStorage.removeItem('unlocked');
                closeRemovePinModal();
                loadState();
                showNotif('PIN dinonaktifkan.');
            });
        });
    }
}

// ===== TIMEOUT =====
function renderTimeoutOptions() {
    var current = parseInt(localStorage.getItem('auto_lock_seconds') || '60', 10);
    var container = document.getElementById('timeoutOptions');
    container.innerHTML = '';
    TIMEOUT_OPTIONS.forEach(function(opt) {
        var div = document.createElement('div');
        div.className = 'timeout-option' + (opt.value === current ? ' selected' : '');
        div.innerHTML = '<span style="font-size:14px;font-weight:600;">' + opt.label + '</span>' +
            (opt.value === current ? '<i class="bi bi-check-lg" style="color:var(--blue);font-size:18px;"></i>' : '');
        div.onclick = function() { selectTimeout(opt.value); };
        container.appendChild(div);
    });
}

function showTimeoutModal() {
    renderTimeoutOptions();
    document.getElementById('timeoutModal').classList.add('show');
}

function closeTimeoutModal() {
    document.getElementById('timeoutModal').classList.remove('show');
}

function selectTimeout(val) {
    localStorage.setItem('auto_lock_seconds', val);
    updateTimeoutDisplay(val);
    closeTimeoutModal();
    showNotif('Auto-lock diatur ke ' + (val === 0 ? 'Tidak Pernah' : val < 60 ? val + ' detik' : (val/60) + ' menit'));
}

function showNotif(msg, type) {
    // Simple notification
    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:20px;left:20px;right:20px;z-index:99999;background:' + (type === 'error' ? '#fee2e2' : '#dcfce7') + ';border:1px solid ' + (type === 'error' ? '#fecaca' : '#bbf7d0') + ';border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);max-width:400px;margin:0 auto;';
    toast.innerHTML = '<i class="bi bi-' + (type === 'error' ? 'exclamation-circle-fill' : 'check-circle-fill') + '" style="color:' + (type === 'error' ? '#dc2626' : '#16a34a') + ';font-size:18px;"></i>' +
        '<span style="flex:1;font-size:13px;font-weight:600;">' + msg + '</span>';
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3000);
}
</script>
@endsection
