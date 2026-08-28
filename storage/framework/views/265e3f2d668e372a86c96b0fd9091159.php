<?php $__env->startSection('content'); ?>
<style>
    .pf-page { padding: 0 16px 120px; max-width: 640px; margin: 0 auto; }

    .pf-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 55%, #3730a3 100%);
        border-radius: 28px; padding: 30px 20px 26px; margin-bottom: 16px;
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
        width: 100%; height: 100%; border-radius: 34px; overflow: hidden;
        background: transparent; display: flex; align-items: center; justify-content: center;
        position: relative; cursor: pointer;
        border: 3px solid rgba(255,255,255,0.22);
        box-shadow: 0 12px 30px rgba(0,0,0,0.3);
    }
    .pf-avatar img { width: 100%; height: 100%; object-fit: cover; display:block; }
    .pf-avatar .initial { font-size: 38px; font-weight: 800; color: rgba(255,255,255,0.85); }

    /* Floating camera "ganti foto" badge (bawah kanan avatar) */
    .pf-cam-badge {
        position:absolute; right:-7px; bottom:-7px; width:38px; height:38px;
        border-radius:50%; background:linear-gradient(135deg,#6366f1,#4f46e5);
        border:3px solid #fff; color:#fff; display:flex; align-items:center;
        justify-content:center; font-size:16px; cursor:pointer; z-index:2;
        box-shadow:0 6px 16px rgba(79,70,229,0.45);
        transition: transform .15s;
    }
    .pf-cam-badge:active { transform:scale(0.9); }

    .pf-name { font-size:24px; font-weight:800; letter-spacing:-0.02em; }
    .pf-badges { margin-top:6px; }
    .pf-role-pill {
        background:rgba(255,255,255,0.14); border:1px solid rgba(255,255,255,0.18);
        padding:4px 12px; border-radius:8px; font-weight:700; font-size:11px;
        text-transform:uppercase; letter-spacing:0.05em; backdrop-filter:blur(6px);
    }

    .pf-info-card {
        background: #fff; border-radius: 22px; padding: 18px;
        margin-bottom: 12px; box-shadow: 0 6px 20px rgba(15,23,42,0.05);
        border: 1px solid rgba(15,23,42,0.03);
    }
    .pf-info-row { display: flex; align-items: center; gap: 12px; padding: 12px 0; }
    .pf-info-row + .pf-info-row { border-top: 1px solid #f1f5f9; }
    .pf-info-icon {
        width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 16px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.6), 0 4px 10px rgba(15,23,42,0.05);
    }
    .pf-info-label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; }
    .pf-info-value { font-size: 14px; font-weight: 700; color: #1e293b; }

    .pf-toast {
        position: fixed; top: 16px; left: 16px; right: 16px; z-index: 9999;
        background: #fff; border-radius: 14px; padding: 12px 16px;
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
    
    <div class="pf-hero">
        <div class="pf-avatar-badge">
            <div class="pf-avatar" id="avatarDisplay" onclick="document.getElementById('pfFotoInput').click()">
                <?php if($user->foto): ?>
                    <img src="<?php echo e(asset('storage/'.$user->foto)); ?>" id="avatarImg" style="object-position: <?php echo e($user->foto_posisi_x ?? 50); ?>% <?php echo e($user->foto_posisi_y ?? 50); ?>%;">
                <?php else: ?>
                    <span class="initial" id="avatarInitial"><?php echo e(strtoupper(substr($user->name, 0, 1))); ?></span>
                    <img id="avatarImg" style="display:none;">
                <?php endif; ?>
            </div>
            <div class="pf-cam-badge" onclick="document.getElementById('pfFotoInput').click()">
                <i class="bi bi-camera-fill"></i>
            </div>
            <input type="file" id="pfFotoInput" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="pfUploadFoto(this)">
        </div>
        <div class="pf-name" id="nameDisplay"><?php echo e($user->name); ?></div>
        <div class="pf-badges">
            <span class="pf-role-pill"><?php echo e($user->role); ?></span>
            <?php if($user->kelas): ?>
                <span style="background:rgba(255,255,255,0.12);padding:4px 12px;border-radius:8px;font-size:11px;font-weight:600;margin-left:6px;"><?php echo e($user->kelas->nama); ?></span>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="pf-info-card">
        <div class="pf-info-row">
            <div class="pf-info-icon" style="background:#eef4ff;color:#246bfe;"><i class="bi bi-person-badge"></i></div>
            <div>
                <div class="pf-info-label">Nama Lengkap</div>
                <div class="pf-info-value" id="infoName"><?php echo e($user->name); ?></div>
            </div>
        </div>
        <div class="pf-info-row">
            <div class="pf-info-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-envelope-at"></i></div>
            <div>
                <div class="pf-info-label">Email</div>
                <div class="pf-info-value">
                    <?php echo e($user->email); ?>

                </div>
            </div>
        </div>
        <div class="pf-info-row">
            <div class="pf-info-icon" style="background:#ede9fe;color:#7c3aed;"><i class="bi bi-mortarboard"></i></div>
            <div>
                <div class="pf-info-label">Kelas / Jabatan</div>
                <div class="pf-info-value"><?php echo e($user->kelas?->nama ?? 'Staf Sekolah'); ?></div>
            </div>
        </div>
    </div>

    

    
    <a href="<?php echo e(route('profile.edit')); ?>" style="display:block;width:100%;padding:14px;border-radius:16px;background:#246bfe;color:#fff;font-weight:700;font-size:14px;text-decoration:none;text-align:center;margin-bottom:10px;">
        <i class="bi bi-pencil-square"></i> Edit Profil
    </a>

    
    <div style="background:#fff5f5;border:1px solid #fee2e2;border-radius:16px;padding:14px;display:flex;align-items:center;justify-content:space-between;margin-top:20px;">
        <div>
            <div style="font-size:13px;font-weight:700;color:#dc2626;">Keluar Akun?</div>
            <div style="font-size:11px;color:#94a3b8;">Hentikan sesi aktif.</div>
        </div>
        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
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

// --- Ganti foto langsung dari profil (premium camera badge) ---
var pfCsrf = '<?php echo e(csrf_token()); ?>';
var pfFotoUrl = <?php echo json_encode($user->foto ? asset('storage/'.$user->foto) : null, 15, 512) ?>;
var pfPosX = <?php echo e($user->foto_posisi_x ?? 50); ?>;
var pfPosY = <?php echo e($user->foto_posisi_y ?? 50); ?>;

function pfUploadFoto(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    if (file.size > 2 * 1024 * 1024) { showToast('Ukuran maks 2MB', 'error'); return; }

    var fd = new FormData();
    fd.append('foto', file);
    fd.append('_token', pfCsrf);

    fetch('<?php echo e(route("profile.foto.upload")); ?>', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            pfFotoUrl = data.url;
            var img = document.getElementById('avatarImg');
            img.src = data.url;
            img.style.display = 'block';
            img.style.objectPosition = '50% 50%';
            var init = document.getElementById('avatarInitial');
            if (init) init.style.display = 'none';
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
document.addEventListener('mousemove', function(e) { if(pfDragging) pfMoveDrag(e.clientX, e.clientY); });
document.addEventListener('touchmove', function(e) { if(pfDragging) pfMoveDrag(e.touches[0].clientX, e.touches[0].clientY); }, {passive:true});
document.addEventListener('mouseup', pfEndDrag);
document.addEventListener('touchend', pfEndDrag);

function pfStartDrag(x, y) {
    if (!pfFotoUrl) return;
    pfDragging = true;
    pfDragStart = {x:x, y:y};
    pfPosStart = {x:pfPosX, y:pfPosY};
}
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
    fetch('<?php echo e(route("profile.foto.posisi")); ?>', {
        method:'PATCH',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':pfCsrf,'Accept':'application/json'},
        body: JSON.stringify({foto_posisi_x: Math.round(pfPosX), foto_posisi_y: Math.round(pfPosY)})
    }).catch(function(){});
}

<?php if(session('success')): ?>
showToast(<?php echo json_encode(session('success'), 15, 512) ?>, 'success');
<?php endif; ?>
<?php if(session('error')): ?>
showToast(<?php echo json_encode(session('error'), 15, 512) ?>, 'error');
<?php endif; ?>
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\profile.blade.php ENDPATH**/ ?>