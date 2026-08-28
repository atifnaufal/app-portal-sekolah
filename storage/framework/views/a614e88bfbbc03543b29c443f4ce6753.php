<?php
    $m = $materi ?? null;
    $val = function($key, $default = '') use ($j, $m) {
        $item = $j ?? $m;
        return old($key, $item[$key] ?? $default);
    };
?>
<div class="row g-3">
    <div class="col-12">
        <label class="form-label small fw-bold">Mata Pelajaran</label>
        <select name="mata_pelajaran_id" class="form-select" required>
            <option value="">-- Pilih Mapel --</option>
            <?php $__currentLoopData = $mapels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($mp->id); ?>" <?php if((string) $val('mata_pelajaran_id') === (string) $mp->id): echo 'selected'; endif; ?>><?php echo e($mp->nama); ?> (<?php echo e($mp->kode); ?>)</option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">Kelas</label>
        <select name="kelas_id" class="form-select" required>
            <option value="">-- Pilih Kelas --</option>
            <?php $__currentLoopData = $kelases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($k->id); ?>" <?php if((string) $val('kelas_id') === (string) $k->id): echo 'selected'; endif; ?>><?php echo e($k->nama); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">Guru</label>
        <select name="guru_id" class="form-select" required>
            <option value="">-- Pilih Guru --</option>
            <?php $__currentLoopData = $gurus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($g->id); ?>" <?php if((string) $val('guru_id') === (string) $g->id): echo 'selected'; endif; ?>><?php echo e($g->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">Hari</label>
        <select name="hari" class="form-select" required>
            <?php $__currentLoopData = ['senin','selasa','rabu','kamis','jumat','sabtu']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($h); ?>" <?php if($val('hari') === $h): echo 'selected'; endif; ?>><?php echo e(ucfirst($h)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">Ruangan</label>
        <input type="text" name="ruangan" value="<?php echo e($val('ruangan')); ?>" class="form-control" placeholder="cth: Lab Komputer 1" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">Jam Mulai</label>
        <input type="time" name="jam_mulai" value="<?php echo e($val('jam_mulai')); ?>" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">Jam Selesai</label>
        <input type="time" name="jam_selesai" value="<?php echo e($val('jam_selesai')); ?>" class="form-control" required>
    </div>
</div>
<?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\admin\jadwal\fields.blade.php ENDPATH**/ ?>