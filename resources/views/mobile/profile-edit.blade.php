@extends('layouts.mobile-app')

@section('content')
<style>
    .pe-page { padding: 0 16px 120px; max-width: 640px; margin: 0 auto; }

    .pe-header {
        position:fixed; top:0; left:0; right:0; z-index:1000;
        background:rgba(255,255,255,0.92); backdrop-filter:blur(16px);
        border-bottom:1px solid var(--line-strong); padding:10px 16px;
        display:flex; align-items:center; gap:10px;
    }
    .pe-body { padding-top: 62px; }

    .pe-card {
        background: var(--surface-card); border-radius: var(--radius-md); padding:18px;
        margin-bottom:12px; box-shadow: var(--shadow-card);
        border: 1px solid var(--line);
    }

    .pe-avatar-wrap {
        width:120px; height:120px; border-radius: var(--radius-lg); margin:0 auto;
        overflow:hidden; background:#f0f7ff; position:relative;
        border:4px solid var(--surface-card); box-shadow:0 8px 24px rgba(36,107,254,0.15);
        cursor:grab;
    }
    .pe-avatar-wrap:active { cursor:grabbing; }
    .pe-avatar-wrap img {
        width:100%; height:100%; object-fit:cover;
        transition: object-position 0.05s;
    }
    .pe-avatar-wrap .pe-initial {
        width:100%; height:100%; display:flex; align-items:center; justify-content:center;
        font-size:42px; font-weight:800; color:var(--blue);
    }

    .pe-avatar-overlay {
        position:absolute; inset:0; background:rgba(0,0,0,0.4);
        display:flex; align-items:center; justify-content:center;
        opacity:0; transition:opacity 0.2s; border-radius:var(--radius-md);
    }
    .pe-avatar-wrap:hover .pe-avatar-overlay { opacity:1; }

    .pe-toast {
        position:fixed; top:60px; left:16px; right:16px; z-index:9999;
        background: var(--surface-card); border-radius: var(--radius-sm); padding:12px 16px;
        box-shadow:0 8px 24px rgba(0,0,0,0.12); display:none;
        max-width:640px; margin:0 auto;
    }
    .pe-toast.show { display:flex; animation:fadeD 0.3s ease; }
    .pe-toast.error { border-left:4px solid #dc2626; }
    .pe-toast.success { border-left:4px solid #16a34a; }
    @keyframes fadeD { from{opacity:0;transform:translateY(-8px);} to{opacity:1;transform:translateY(0);} }

    .pe-saving { opacity:0.6; pointer-events:none; }

    .pe-pos-hint {
        font-size:11px; color:var(--faint); text-align:center; margin-top:8px;
    }
</style>

<div class="pe-header">
    <a href="{{ route('profile.show') }}" style="width:36px;height:36px;border-radius:50%;background:var(--surface);display:flex;align-items:center;justify-content:center;text-decoration:none;color:var(--mist);">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div style="font-weight:800;font-size:16px;flex:1;color:var(--ink);">Edit Profil</div>
</div>

<div id="peToast" class="pe-toast">
    <i id="peToastIcon" class="bi bi-check-circle-fill" style="font-size:18px;"></i>
    <span id="peToastMsg" style="flex:1;font-size:13px;font-weight:600;margin-left:8px;"></span>
</div>

<div class="pe-body">
    {{-- Photo Section --}}
    <div class="pe-card" style="text-align:center;">
        <div class="pe-avatar-wrap" id="avatarWrap">
            @if($user->foto)
                <img src="{{ asset('storage/'.$user->foto) }}" id="avatarImg"
                    style="object-position: {{ $user->foto_posisi_x ?? 50 }}% {{ $user->foto_posisi_y ?? 50 }}%;">
            @else
                <div class="pe-initial" id="avatarInitial">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <img id="avatarImg" style="display:none;">
            @endif
            <div class="pe-avatar-overlay" onclick="document.getElementById('fotoInput').click()">
                <i class="bi bi-camera-fill" style="color:#fff;font-size:24px;"></i>
            </div>
        </div>
        <input type="file" id="fotoInput" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="uploadFoto(this)">

        <div style="margin-top:12px;">
            <button type="button" onclick="document.getElementById('fotoInput').click()" class="pui-btn pui-btn-soft pui-btn-sm">
                <i class="bi bi-camera"></i> Ganti Foto
            </button>
        </div>
        <div class="pe-pos-hint" id="posHint" style="{{ $user->foto ? '' : 'display:none' }}">
            Seret foto untuk atur posisi
        </div>
    </div>

    {{-- Data Diri --}}
    <div class="pe-card" id="formCard">
        <form id="profileForm" onsubmit="simpanProfil(event)">
            @csrf
            <div class="pui-field">
                <label class="pui-label">Nama Lengkap</label>
                <input name="name" type="text" class="pui-input" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="pui-field">
                <label class="pui-label">Email</label>
                <input name="email" type="email" class="pui-input" value="{{ old('email', $user->email) }}" required>
            </div>

            <div style="background:var(--surface);border:1px dashed var(--line-strong);border-radius:var(--radius-sm);padding:14px;margin-bottom:14px;">
                <div style="font-size:13px;font-weight:700;margin-bottom:10px;color:var(--ink);">Ganti Password</div>
                <div style="margin-bottom:8px;">
                    <input name="password" type="password" class="pui-input" placeholder="Password baru" minlength="8">
                </div>
                <div>
                    <input name="password_confirmation" type="password" class="pui-input" placeholder="Konfirmasi password" minlength="8">
                </div>
                <div style="font-size:10px;color:var(--faint);margin-top:6px;">Kosongkan jika tidak ingin mengubah.</div>
            </div>

            <input type="hidden" name="foto_posisi_x" id="posisiX" value="{{ $user->foto_posisi_x ?? 50 }}">
            <input type="hidden" name="foto_posisi_y" id="posisiY" value="{{ $user->foto_posisi_y ?? 50 }}">

            <button type="submit" id="submitBtn" class="pui-btn pui-btn-primary pui-btn-block" style="border-radius:var(--radius-sm);">
                <i class="bi bi-check2-circle"></i> Simpan Perubahan
            </button>
        </form>
    </div>

    <a href="{{ route('profile.show') }}" style="display:block;text-align:center;padding:12px;font-size:13px;font-weight:600;color:var(--faint);text-decoration:none;">Batal</a>
</div>

<script>
var csrfToken = '{{ csrf_token() }}';
var fotoUrl = @json($user->foto ? asset('storage/'.$user->foto) : null);
var posX = {{ $user->foto_posisi_x ?? 50 }};
var posY = {{ $user->foto_posisi_y ?? 50 }};
var isDragging = false;
var dragStart = {x:0, y:0};
var posStart = {x:50, y:50};

function showToast(msg, type) {
    var t = document.getElementById('peToast');
    var icon = document.getElementById('peToastIcon');
    document.getElementById('peToastMsg').textContent = msg;
    t.className = 'pe-toast show ' + (type || 'success');
    icon.className = type === 'error' ? 'bi bi-x-circle-fill' : 'bi bi-check-circle-fill';
    icon.style.color = type === 'error' ? '#dc2626' : '#16a34a';
    setTimeout(function() { t.classList.remove('show'); }, 3000);
}

function uploadFoto(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    if (file.size > 2 * 1024 * 1024) { showToast('Ukuran maks 2MB', 'error'); return; }

    var fd = new FormData();
    fd.append('foto', file);
    fd.append('_token', csrfToken);

    fetch('{{ route("profile.foto.upload") }}', { method:'POST', body:fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            fotoUrl = data.url;
            var img = document.getElementById('avatarImg');
            img.src = data.url;
            img.style.display = 'block';
            img.style.objectPosition = '50% 50%';
            var init = document.getElementById('avatarInitial');
            if (init) init.style.display = 'none';
            document.getElementById('posHint').style.display = '';
            posX = 50; posY = 50;
            document.getElementById('posisiX').value = 50;
            document.getElementById('posisiY').value = 50;
            showToast('Foto berhasil diupload!');
        } else {
            showToast(data.message || 'Gagal upload', 'error');
        }
    })
    .catch(function() { showToast('Gagal upload foto', 'error'); });
}

// Drag to reposition
var wrap = document.getElementById('avatarWrap');

wrap.addEventListener('mousedown', function(e) { startDrag(e.clientX, e.clientY); e.preventDefault(); });
wrap.addEventListener('touchstart', function(e) { startDrag(e.touches[0].clientX, e.touches[0].clientY); }, {passive:true});

document.addEventListener('mousemove', function(e) { if(isDragging) moveDrag(e.clientX, e.clientY); });
document.addEventListener('touchmove', function(e) { if(isDragging) moveDrag(e.touches[0].clientX, e.touches[0].clientY); }, {passive:true});

document.addEventListener('mouseup', endDrag);
document.addEventListener('touchend', endDrag);

function startDrag(x, y) {
    if (!fotoUrl) return;
    isDragging = true;
    dragStart = {x:x, y:y};
    posStart = {x:posX, y:posY};
}

function moveDrag(x, y) {
    var rect = wrap.getBoundingClientRect();
    var dx = ((x - dragStart.x) / rect.width) * 100;
    var dy = ((y - dragStart.y) / rect.height) * 100;
    posX = Math.max(0, Math.min(100, posStart.x - dx));
    posY = Math.max(0, Math.min(100, posStart.y - dy));
    document.getElementById('avatarImg').style.objectPosition = posX + '% ' + posY + '%';
}

function endDrag() {
    if (!isDragging) return;
    isDragging = false;
    document.getElementById('posisiX').value = Math.round(posX);
    document.getElementById('posisiY').value = Math.round(posY);

    // Auto-save position via AJAX
    var fd = new FormData();
    fd.append('foto_posisi_x', Math.round(posX));
    fd.append('foto_posisi_y', Math.round(posY));
    fd.append('_token', csrfToken);

    fetch('{{ route("profile.foto.posisi") }}', {
        method:'PATCH',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify({foto_posisi_x: Math.round(posX), foto_posisi_y: Math.round(posY)})
    }).catch(function(){});
}

function simpanProfil(e) {
    e.preventDefault();
    var form = document.getElementById('profileForm');
    var btn = document.getElementById('submitBtn');
    var card = document.getElementById('formCard');

    btn.textContent = 'Menyimpan...';
    card.classList.add('pe-saving');

    var fd = new FormData(form);
    fd.append('_method', 'PUT');

    fetch('{{ route("profile.update") }}', {
        method: 'POST',
        headers: {'Accept':'application/json','X-CSRF-TOKEN':csrfToken},
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        card.classList.remove('pe-saving');
        if (data.ok) {
            showToast(data.message);
            btn.innerHTML = '<i class="bi bi-check2-circle"></i> Tersimpan!';
            // Update display
            if (data.user) {
                var nameEls = document.querySelectorAll('[data-display-name]');
                // Update avatar initial if no photo
                if (!fotoUrl) {
                    var init = document.getElementById('avatarInitial');
                    if (init) init.textContent = data.user.name.charAt(0).toUpperCase();
                }
            }
            setTimeout(function() {
                btn.innerHTML = '<i class="bi bi-check2-circle"></i> Simpan Perubahan';
            }, 2000);
        } else {
            var msgs = [];
            if (data.errors) {
                for (var k in data.errors) { msgs.push(data.errors[k][0]); }
            }
            showToast(msgs.join('. ') || 'Gagal menyimpan', 'error');
            btn.innerHTML = '<i class="bi bi-check2-circle"></i> Simpan Perubahan';
        }
    })
    .catch(function() {
        card.classList.remove('pe-saving');
        showToast('Koneksi gagal. Coba lagi.', 'error');
        btn.innerHTML = '<i class="bi bi-check2-circle"></i> Simpan Perubahan';
    });
}
</script>
@endsection
