<?php $__env->startSection('content'); ?>
<div class="mb-4">
    <a href="<?php echo e(route('pengumuman.index')); ?>" class="text-decoration-none small fw-bold"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h1 class="h3 fw-bold mt-3"><?php echo e($pengumuman->exists ? 'Edit Pengumuman' : 'Buat Pengumuman Baru'); ?></h1>
    <p class="text-secondary small">Siarkan informasi ke kelas, eskul, atau seluruh sekolah.</p>
</div>

<div class="card form-card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form method="POST" action="<?php echo e($pengumuman->exists ? route('pengumuman.update', $pengumuman) : route('pengumuman.store')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <?php if($pengumuman->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <div class="mb-3">
                <label class="form-label fw-bold">Target Pengumuman</label>
                <?php
                    $isPrivateEdit = $pengumuman->exists && method_exists($pengumuman,'isPrivate') && $pengumuman->isPrivate();
                    $currentTarget = old('target', $isPrivateEdit ? 'private' : ($pengumuman->eskul_id ? 'eskul:'.$pengumuman->eskul_id : ($pengumuman->kelas_id ? 'class' : (!$pengumuman->exists ? '' : 'general'))));
                ?>
                <select name="target" id="targetSelect" class="form-select" required>
                    <?php if(session('user_role') === 'admin'): ?>
                        <option value="general" <?php if($currentTarget === 'general'): echo 'selected'; endif; ?>>Umum (Seluruh Sekolah)</option>
                    <?php endif; ?>

                    <?php if(isset($isWaliKelas) && $isWaliKelas): ?>
                        <option value="class" <?php if($currentTarget === 'class'): echo 'selected'; endif; ?>>Wali Kelas (<?php echo e($isWaliKelas->nama); ?>)</option>
                    <?php endif; ?>

                    <?php if(isset($adminEskuls)): ?>
                        <?php $__currentLoopData = $adminEskuls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ae): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="eskul:<?php echo e($ae->id); ?>" <?php if($currentTarget === 'eskul:'.$ae->id): echo 'selected'; endif; ?>>Admin Eskul (<?php echo e($ae->nama); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>

                    <?php if(session('user_role') === 'admin' || (isset($isWaliKelas) && $isWaliKelas)): ?>
                        <option value="private" <?php if($currentTarget === 'private'): echo 'selected'; endif; ?>>Pribadi (Siswa Tertentu)</option>
                    <?php endif; ?>
                </select>
                <div class="small text-muted mt-1">Pilih jangkauan informasi yang akan Anda bagikan.</div>
            </div>

            <div class="mb-3" id="privateBox" style="<?php echo e($currentTarget === 'private' ? '' : 'display:none;'); ?>">
                <label class="form-label fw-bold">Pilih Siswa Penerima</label>
                <select name="siswa_ids[]" id="siswaSelect" class="form-select" multiple size="8">
                    <?php $__currentLoopData = $siswaList ?? collect(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s->id); ?>" <?php if(in_array($s->id, old('siswa_ids', $selectedSiswa ?? []), true)): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <div class="small text-muted mt-1">Tahan Ctrl (atau sentuh) untuk memilih beberapa siswa. Pengumuman privat hanya terlihat oleh siswa terpilih.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Judul</label>
                <input name="judul" value="<?php echo e(old('judul',$pengumuman->judul)); ?>" class="form-control" required placeholder="Ketik judul pengumuman...">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Isi Informasi</label>
                <textarea name="isi" rows="6" class="form-control" required placeholder="Tuliskan detail informasi di sini..."><?php echo e(old('isi',$pengumuman->isi)); ?></textarea>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tanggal Agenda (Opsional)</label>
                    <input name="tanggal_acara" type="date" value="<?php echo e(old('tanggal_acara',$pengumuman->tanggal_acara?->format('Y-m-d'))); ?>" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Gambar Pendukung</label>
                    <input name="gambar" type="file" accept="image/*" class="form-control">
                    <div class="small text-secondary mt-1">Maksimal 5 MB</div>
                </div>
            </div>

            <?php if(session('user_role') === 'admin'): ?>
            <div class="form-check mt-4">
                <input name="is_landing" type="checkbox" value="1" class="form-check-input" id="is_landing" <?php if(old('is_landing',$pengumuman->is_landing)): echo 'checked'; endif; ?>>
                <label for="is_landing" class="form-check-label small fw-bold">Tampilkan di Banner Landing Page</label>
            </div>
            <?php endif; ?>

            <button class="btn btn-primary w-100 py-3 rounded-3 mt-4 fw-bold shadow-sm">
                <?php echo e($pengumuman->exists ? 'Simpan Perubahan' : 'Terbitkan Sekarang'); ?>

            </button>
        </form>
    </div>
</div>

<script>
(function () {
    const sel = document.getElementById('targetSelect');
    const box = document.getElementById('privateBox');
    const siswa = document.getElementById('siswaSelect');
    if (!sel || !box || !siswa) return;

    function sync() {
        const isPrivate = sel.value === 'private';
        box.style.display = isPrivate ? '' : 'none';
        siswa.required = isPrivate;
    }
    sel.addEventListener('change', sync);
    sync();
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(session('user_role') === 'admin' ? 'layouts.app' : 'layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\pengumuman\form.blade.php ENDPATH**/ ?>