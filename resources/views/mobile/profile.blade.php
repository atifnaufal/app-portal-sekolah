@extends('layouts.mobile-app')

@section('content')
<style>
    .pf-page { padding: 0 16px 120px; max-width: 640px; margin: 0 auto; }

    .pf-hero {
        background: linear-gradient(135deg, var(--navy) 0%, #1e1b4b 55%, #3730a3 100%);
        border-radius: var(--radius-lg); padding: 30px 20px 26px; margin-bottom: 16px;
        color: #fff; text-align: center; position: relative; overflow: hidden;
        box-shadow: 0 14px 40px rgba(30,27,75,0.3);
    }
    .pf-hero::before {
        content:''; position:absolute; top:-50px; right:-40px;
        width:170px; height:170px; border-radius:50%;
        background:radial-gradient(circle, rgba(139,92,246,0.35) 0%, transparent 70%);
    }
    .pf-hero::after {
        content:''; position:absolute; bottom:-60px; left:-40px;
        width:200px; height:200px; border-radius:50%;
        background:radial-gradient(circle, rgba(59,130,246,0.28) 0%, transparent 70%);
    }
    .pf-hero > * { position: relative; z-index: 1; }

    .pf-avatar-badge {
        width: 104px; height: 104px; margin: 0 auto 14px; position: relative;
    }
    .pf-avatar {
        width: 100%; height: 100%; border-radius: var(--radius-lg); overflow: hidden;
        background: transparent; display: flex; align-items: center; justify-content: center;
        position: relative; cursor: pointer;
        border: 3px solid rgba(255,255,255,0.22);
        box-shadow: 0 12px 30px rgba(0,0,0,0.3);
    }
    .pf-avatar img { width: 100%; height: 100%; object-fit: cover; display:block; }
    .pf-avatar .initial { font-size: 38px; font-weight: 800; color: rgba(255,255,255,0.85); }

    .pf-name { font-size:24px; font-weight:800; letter-spacing:-0.02em; }
    .pf-badges { margin-top:6px; }
    .pf-role-pill {
        background:rgba(255,255,255,0.14); border:1px solid rgba(255,255,255,0.18);
        padding:4px 12px; border-radius:8px; font-weight:700; font-size:11px;
        text-transform:uppercase; letter-spacing:0.05em; backdrop-filter:blur(6px);
    }

    .pf-info-card {
        background: var(--surface-card); border-radius: var(--radius-md); padding: 18px;
        margin-bottom: 12px; box-shadow: var(--shadow-card);
        border: 1px solid var(--line);
    }
    .pf-info-row { display: flex; align-items: center; gap: 12px; padding: 12px 0; }
    .pf-info-row + .pf-info-row { border-top: 1px solid var(--line); }
    .pf-info-icon {
        width: 38px; height: 38px; border-radius: var(--radius-sm); flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 16px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.6), 0 4px 10px rgba(15,23,42,0.05);
    }
    .pf-info-label { font-size: 10px; font-weight: 700; color: var(--faint); text-transform: uppercase; letter-spacing: 0.04em; }
    .pf-info-value { font-size: 14px; font-weight: 700; color: var(--ink); }

    /* New Premium Styles */
    .pf-menu-card {
        background: var(--surface-card); border-radius: var(--radius-md); padding: 8px 0;
        margin-bottom: 12px; box-shadow: var(--shadow-card); border: 1px solid var(--line);
    }
    .pf-menu-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 18px; cursor: pointer; transition: background 0.2s;
    }
    .pf-menu-item:active { background: #f8fafc; }
    .pf-menu-item + .pf-menu-item { border-top: 1px solid var(--line); }
    .pf-menu-content { display: flex; align-items: center; gap: 14px; }
    .pf-menu-icon {
        width: 32px; height: 32px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; font-size: 14px;
    }
    .pf-menu-text { font-size: 14px; font-weight: 600; color: var(--ink); }
    .pf-menu-arrow { color: var(--faint); font-size: 12px; }

    .pf-permission-card {
        background: #fffbeb; border: 1px solid #fef3c7; border-radius: var(--radius-md);
        padding: 16px; margin-bottom: 16px; display: none;
    }
    .pf-permission-card.active { display: block; }
    .pf-permission-title { font-size: 13px; font-weight: 800; color: #92400e; display: flex; align-items: center; gap: 8px; }
    .pf-permission-desc { font-size: 11px; color: #b45309; margin-top: 4px; line-height: 1.4; }
    .pf-permission-btn {
        margin-top: 12px; background: #92400e; color: #fff; border: none;
        padding: 8px 16px; border-radius: 8px; font-size: 11px; font-weight: 700;
    }

    .pf-toast {
        position: fixed; top: 16px; left: 16px; right: 16px; z-index: 9999;
        background: var(--surface-card); border-radius: var(--radius-sm); padding: 12px 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12); display: none;
        max-width: 640px; margin: 0 auto;
    }
    .pf-toast.show { display: flex; animation: fadeDown 0.3s ease; }
    @keyframes fadeDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
</style>

<div id="pfToast" class="pf-toast">
    <i id="pfToastIcon" class="bi bi-check-circle-fill" style="color:#16a34a;font-size:18px;"></i>
    <span id="pfToastMsg" style="flex:1;font-size:13px;font-weight:600;margin-left:8px;"></span>
</div>

<div class="pf-page" style="padding-top:16px;">
    {{-- Hero --}}
    <div class="pf-hero">
        <div class="pf-avatar-badge">
            <div class="pf-avatar" id="avatarDisplay" onclick="document.getElementById('avatarInput').click()">
                <img src="{{ $user->avatar_url }}" id="avatarImg" style="object-position: {{ $user->foto_posisi_x ?? 50 }}% {{ $user->foto_posisi_y ?? 50 }}%;">
            </div>
            <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="pfUploadFoto(this)">
        </div>
        <div class="pf-name" id="nameDisplay">{{ $user->name }}</div>
        <div class="pf-badges">
            <span class="pf-role-pill">{{ $user->role }}</span>
            @if($user->kelas)
                <span style="background:rgba(255,255,255,0.12);padding:4px 12px;border-radius:8px;font-size:11px;font-weight:600;margin-left:6px;">{{ $user->kelas->nama }}</span>
            @endif
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="pf-info-card">
        <div class="pf-info-row">
            <div class="pf-info-icon" style="background:#eef4ff;color:var(--blue);"><i class="bi bi-person-badge"></i></div>
            <div>
                <div class="pf-info-label">Nama Lengkap</div>
                <div class="pf-info-value" id="infoName">{{ $user->name }}</div>
            </div>
        </div>
        <div class="pf-info-row">
            <div class="pf-info-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-envelope-at"></i></div>
            <div>
                <div class="pf-info-label">Email</div>
                <div class="pf-info-value">
                    {{ $user->email }}
                </div>
            </div>
        </div>
        <div class="pf-info-row">
            <div class="pf-info-icon" style="background:#ede9fe;color:#7c3aed;"><i class="bi bi-mortarboard"></i></div>
            <div>
                <div class="pf-info-label">Kelas / Jabatan</div>
                <div class="pf-info-value">{{ $user->kelas?->nama ?? 'Staf Sekolah' }}</div>
            </div>
        </div>
    </div>

    {{-- Premium Menu Section --}}
    <div class="pf-menu-card">
        <div class="pf-menu-item" onclick="window.location.href='/help/faq'">
            <div class="pf-menu-content">
                <div class="pf-menu-icon" style="background:#fff7ed;color:#ea580c;"><i class="bi bi-question-circle"></i></div>
                <div class="pf-menu-text">Pusat Bantuan & FAQ</div>
            </div>
            <i class="bi bi-chevron-right pf-menu-arrow"></i>
        </div>
        <div class="pf-menu-item" onclick="window.location.href='/security/settings'">
            <div class="pf-menu-content">
                <div class="pf-menu-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-shield-lock"></i></div>
                <div class="pf-menu-text">Keamanan & Privasi</div>
            </div>
            <i class="bi bi-chevron-right pf-menu-arrow"></i>
        </div>
        <div class="pf-menu-item" onclick="window.location.href='/settings/notifications'">
            <div class="pf-menu-content">
                <div class="pf-menu-icon" style="background:#f0f9ff;color:#0284c7;"><i class="bi bi-bell"></i></div>
                <div class="pf-menu-text">Pengaturan Notifikasi</div>
            </div>
            <i class="bi bi-chevron-right pf-menu-arrow"></i>
        </div>
        <div class="pf-menu-item" onclick="window.location.href='/about'">
            <div class="pf-menu-content">
                <div class="pf-menu-icon" style="background:#f8fafc;color:#64748b;"><i class="bi bi-info-circle"></i></div>
                <div class="pf-menu-text">Tentang Aplikasi</div>
            </div>
            <i class="bi bi-chevron-right pf-menu-arrow"></i>
        </div>
    </div>

    {{-- Permission Warning Card --}}
    <div id="permissionWarning" class="pf-permission-card">
        <div class="pf-permission-title">
            <i class="bi bi-exclamation-triangle-fill"></i> Izin Belum Lengkap
        </div>
        <div class="pf-permission-desc">
            Fitur notifikasi dan presensi memerlukan izin tambahan (<span id="missingNames"></span>). Aktifkan sekarang untuk pengalaman terbaik.
        </div>
        <button class="pf-permission-btn" onclick="openNativeSettings()">Lengkapi Sekarang</button>
    </div>

    {{-- Action Buttons --}}
    <div style="background:#fff5f5;border:1px solid #fee2e2;border-radius:var(--radius-md);padding:14px;display:flex;align-items:center;justify-content:space-between;margin-top:20px;">
    <div style="background:#fff5f5;border:1px solid #fee2e2;border-radius:var(--radius-md);padding:14px;display:flex;align-items:center;justify-content:space-between;margin-top:20px;">
        <div>
            <div style="font-size:13px;font-weight:700;color:#dc2626;">Keluar Akun?</div>
            <div style="font-size:11px;color:var(--faint);">Hentikan sesi aktif.</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button style="padding:8px 20px;border-radius:10px;background:#dc2626;color:#fff;font-weight:700;font-size:12px;border:none;cursor:pointer;">KELUAR</button>
        </form>
    </div>
</div>

<script>
function showToast(msg, type) {
    var t = document.getElementById('pfToast');
    var icon = document.getElementById('pfToastIcon');
    var isError = type === 'error';

    document.getElementById('pfToastMsg').textContent = msg;
    icon.className = 'bi ' + (isError ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill');
    icon.style.color = isError ? '#dc2626' : '#16a34a';
    t.style.borderLeft = '4px solid ' + (isError ? '#dc2626' : '#16a34a');

    t.classList.add('show');
    setTimeout(function() { t.classList.remove('show'); }, 5000);
}

// --- Fungsi upload foto dari profil ---
var pfCsrf = '{{ csrf_token() }}';
var avatarImgUrl = @json($user->foto ? asset('storage/'.$user->foto) : null);
var pfPosX = {{ $user->foto_posisi_x ?? 50 }};
var pfPosY = {{ $user->foto_posisi_y ?? 50 }};

function pfUploadFoto(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    if (file.size > 2 * 1024 * 1024) { showToast('Ukuran maks 2MB', 'error'); return; }

    var fd = new FormData();
    fd.append('foto', file);
    fd.append('_token', pfCsrf);

    fetch('{{ route("profile.foto.upload") }}', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            avatarImgUrl = data.url;
            var img = document.getElementById('avatarImg');
            img.src = data.url;
            img.style.display = 'block';
            img.style.objectPosition = '50% 50%';
            pfPosX = 50; pfPosY = 50;
            showToast(data.message || 'Foto berhasil diupload!');
        } else {
            showToast(data.message || 'Gagal upload', 'error');
        }
    })
    .catch(function() { showToast('Gagal upload foto', 'error'); });
}

// Seret untuk atur posisi foto
var pfWrap = document.getElementById('avatarDisplay');
var pfDragging = false;
var pfDragStart = {x:0,y:0};
var pfPosStart = {x:50,y:50};

pfWrap.addEventListener('mousedown', function(e) { pfStartDrag(e.clientX, e.clientY); e.preventDefault(); });
pfWrap.addEventListener('touchstart', function(e) { pfStartDrag(e.touches[0].clientX, e.touches[0].clientY); }, {passive:true});

function pfStartDrag(x, y) {
    if (!avatarImgUrl) return;
    pfDragging = true;
    pfDragStart = {x:x, y:y};
    pfPosStart = {x:pfPosX, y:pfPosY};
}

document.addEventListener('mousemove', function(e) { if(pfDragging) pfMoveDrag(e.clientX, e.clientY); });
document.addEventListener('touchmove', function(e) { if(pfDragging) pfMoveDrag(e.touches[0].clientX, e.touches[0].clientY); }, {passive:true});
document.addEventListener('mouseup', pfEndDrag);
document.addEventListener('touchend', pfEndDrag);

function pfMoveDrag(x, y) {
    var rect = pfWrap.getBoundingClientRect();
    var dx = ((x - pfDragStart.x) / rect.width) * 100;
    var dy = ((y - pfDragStart.y) / rect.height) * 100;
    pfPosX = Math.max(0, Math.min(100, pfPosStart.x - dx));
    pfPosY = Math.max(0, Math.min(100, pfPosStart.y - dy));
    document.getElementById('avatarImg').style.objectPosition = pfPosX + '% ' + pfPosY + '%';
}
function pfEndDrag() {
    if (!pfDragging) return;
    pfDragging = false;
    var fd = new FormData();
    fd.append('foto_posisi_x', Math.round(pfPosX));
    fd.append('foto_posisi_y', Math.round(pfPosY));
    fd.append('_token', pfCsrf);
    fetch('{{ route("profile.foto.posisi") }}', {
        method:'PATCH',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':pfCsrf,'Accept':'application/json'},
        body: JSON.stringify({foto_posisi_x: Math.round(pfPosX), foto_posisi_y: Math.round(pfPosY)})
    }).catch(function(){});
}

@if(session('success'))
showToast(@json(session('success')), 'success');
@endif
@if(session('error'))
showToast(@json(session('error')), 'error');
@endif

// --- Native Integration for Premium Features ---
document.addEventListener('DOMContentLoaded', function() {
    checkNativePermissions();
});

function checkNativePermissions() {
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        window.Capacitor.Plugins.NativeBridge.checkPermissionsStatus()
            .then(function(res) {
                if (!res.isComplete) {
                    var card = document.getElementById('permissionWarning');
                    document.getElementById('missingNames').textContent = res.missingPermissions;
                    card.classList.add('active');
                }
            })
            .catch(function(e) { console.error('Bridge Error:', e); });
    }
}

function openNativeSettings() {
    if (window.Capacitor && window.Capacitor.Plugins.NativeBridge) {
        window.Capacitor.Plugins.NativeBridge.openAppSettings();
    }
}
</script>
@endsection
