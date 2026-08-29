<?php $hideNav = true; ?>


<?php $__env->startSection('content'); ?>
<?php $isEdit = $materi->exists; ?>

<style>
    .mf-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.95); backdrop-filter: blur(16px);
        border-bottom: 1px solid #f1f5f9;
        padding: 10px 16px; display: flex; align-items: center; gap: 10px;
    }
    .mf-body { padding: 62px 14px 40px; max-width: 640px; margin: 0 auto; }

    .mf-card {
        background: #fff; border-radius: 20px; padding: 18px;
        margin-bottom: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(15,23,42,0.05);
    }

    .mf-label {
        font-size: 11px; font-weight: 700; color: #64748b;
        margin-bottom: 6px; display: flex; align-items: center; gap: 5px;
        text-transform: uppercase; letter-spacing: 0.04em;
    }

    .mf-input {
        width: 100%; border: 1.5px solid #e2e8f0; border-radius: 12px;
        padding: 12px 14px; font-size: 14px; color: #1e293b;
        background: #f8fafc; transition: all 0.2s;
        -webkit-appearance: none;
    }
    .mf-input:focus { outline: none; border-color: #246bfe; background: #fff; box-shadow: 0 0 0 4px rgba(36,107,254,0.1); }

    .mf-file {
        border: 2px dashed #c7d2fe; border-radius: 16px;
        padding: 24px 16px; text-align: center; cursor: pointer;
        background: #f8faff; transition: all 0.2s;
        display: flex; flex-direction: column; align-items: center; gap: 8px;
    }
    .mf-file.dragover { border-color: #246bfe; background: #eff4ff; }
    .mf-file.has-file { border-style: solid; border-color: #16a34a; background: #f0fdf4; }

    @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .fade-up { animation: fadeUp 0.35s ease both; }
</style>

<div class="mf-header">
    <a href="<?php echo e(route('mapel.show', $mapel)); ?>" style="width:36px;height:36px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#475569;">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div style="font-weight:800;font-size:16px;flex:1;"><?php echo e($isEdit ? 'Edit Materi' : 'Buat Materi'); ?></div>
</div>

<div class="mf-body">
    <div class="mf-card fade-up" style="background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border:none;box-shadow:0 10px 25px rgba(0,0,0,0.1);">
        <div style="font-size:10px;font-weight:800;letter-spacing:0.1em;text-transform:uppercase;opacity:0.5;margin-bottom:6px;"><?php echo e($mapel->kode); ?></div>
        <div style="font-size:18px;font-weight:800;"><?php echo e($mapel->nama); ?></div>
        <div style="font-size:11px;opacity:0.6;margin-top:4px;">Bagikan dokumen, video, atau catatan pembelajaran kepada siswa.</div>
    </div>

    <form method="POST" action="<?php echo e($isEdit ? route('materi.update', [$mapel, $materi]) : route('materi.store', $mapel)); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

        
        <div class="mf-card fade-up" style="animation-delay:0.05s;">
            <label class="mf-label"><i class="bi bi-type" style="color:#246bfe;"></i> Judul Materi *</label>
            <input type="text" name="judul" class="mf-input" placeholder="cth: Bab 1 - Pengenalan" value="<?php echo e(old('judul', $materi->judul)); ?>" required>
            <?php $__errorArgs = ['judul'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div style="font-size:11px;color:#dc2626;margin-top:4px;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="mf-card fade-up" style="animation-delay:0.1s;">
            <label class="mf-label"><i class="bi bi-card-text" style="color:#246bfe;"></i> Deskripsi</label>
            <textarea name="deskripsi" rows="4" class="mf-input" style="resize:none;" placeholder="Tulis ringkasan atau catatan pembelajaran..."><?php echo e(old('deskripsi', $materi->deskripsi)); ?></textarea>
        </div>

        
        <div class="mf-card fade-up" style="animation-delay:0.15s;">
            <label class="mf-label"><i class="bi bi-paperclip" style="color:#246bfe;"></i> File Materi</label>
            <label class="mf-file" id="fileZone">
                <input type="file" name="file_materi" id="fileInput" style="display:none;" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.xls,.ppt,.pptx,.csv,.txt,.zip,.mp4,.mov">
                <div style="width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#2563eb;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-cloud-arrow-up-fill" style="font-size:20px;"></i>
                </div>
                <div style="font-size:13px;font-weight:700;color:#1e293b;">Pilih File</div>
                <div style="font-size:10px;color:#94a3b8;">PDF, Dokumen, Gambar, Video (maks 50MB)</div>
                <div id="fileName" style="font-size:11px;font-weight:600;color:#16a34a;display:none;"></div>
            </label>
            <?php if($isEdit && $materi->file_materi): ?>
                <div style="display:flex;align-items:center;gap:8px;padding:10px;background:#f8fafc;border-radius:12px;margin-top:10px;">
                    <i class="bi bi-file-earmark-fill" style="color:#246bfe;"></i>
                    <div style="flex:1;font-size:12px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($materi->file_nama); ?></div>
                    <label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#dc2626;font-weight:600;cursor:pointer;">
                        <input type="checkbox" name="hapus_file" value="1"> Hapus
                    </label>
                </div>
            <?php endif; ?>
            <?php $__errorArgs = ['file_materi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div style="font-size:11px;color:#dc2626;margin-top:4px;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="mf-card fade-up" style="animation-delay:0.2s;">
            <label class="mf-label"><i class="bi bi-youtube" style="color:#dc2626;"></i> Link Video (opsional)</label>
            <input type="url" name="video_url" class="mf-input" placeholder="https://www.youtube.com/watch?v=..." value="<?php echo e(old('video_url', $materi->video_url)); ?>">
            <?php $__errorArgs = ['video_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div style="font-size:11px;color:#dc2626;margin-top:4px;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <button type="submit" style="width:100%;padding:15px;border-radius:16px;background:linear-gradient(135deg,#246bfe,#1d4ed8);color:#fff;font-weight:800;font-size:15px;border:none;cursor:pointer;box-shadow:0 10px 25px rgba(36,107,254,0.3);">
            <i class="bi bi-check-lg me-1"></i> <?php echo e($isEdit ? 'Simpan Perubahan' : 'Terbitkan Materi'); ?>

        </button>
    </form>
</div>

<script>
    var fileInput = document.getElementById('fileInput');
    var fileZone = document.getElementById('fileZone');
    var fileName = document.getElementById('fileName');

    fileInput.addEventListener('change', function() {
        if (fileInput.files && fileInput.files[0]) {
            fileZone.classList.add('has-file');
            fileName.style.display = 'block';
            fileName.textContent = fileInput.files[0].name;
        }
    });

    ['dragover','dragenter'].forEach(function(ev){
        fileZone.addEventListener(ev, function(e){ e.preventDefault(); fileZone.classList.add('dragover'); });
    });
    ['dragleave','drop'].forEach(function(ev){
        fileZone.addEventListener(ev, function(e){ e.preventDefault(); fileZone.classList.remove('dragover'); });
    });
    fileZone.addEventListener('drop', function(e){
        var files = e.dataTransfer.files;
        if (files.length) {
            fileInput.files = files;
            fileZone.classList.add('has-file');
            fileName.style.display = 'block';
            fileName.textContent = files[0].name;
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\materi-form.blade.php ENDPATH**/ ?>