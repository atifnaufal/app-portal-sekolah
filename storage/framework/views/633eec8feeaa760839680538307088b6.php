<?php $__env->startSection('content'); ?>
<div class="mb-4">
    <a href="<?php echo e(route('mahasiswa.index')); ?>" class="text-decoration-none">&larr; Kembali</a>
    <h1 class="h3 fw-bold mt-3"><?php echo e($mahasiswa->exists ? 'Edit Siswa' : 'Tambah Siswa'); ?></h1>
</div>

<div class="card form-card">
    <div class="card-body p-4">
        <form method="POST" action="<?php echo e($mahasiswa->exists ? route('mahasiswa.update', $mahasiswa) : route('mahasiswa.store')); ?>">
            <?php echo csrf_field(); ?>
            <?php if($mahasiswa->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">NIS / NIM</label>
                    <input name="nik" value="<?php echo e(old('nik', $mahasiswa->nik)); ?>" class="form-control" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Nama lengkap</label>
                    <input name="name" value="<?php echo e(old('name', $mahasiswa->name)); ?>" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input name="email" type="email" value="<?php echo e(old('email', $mahasiswa->email)); ?>" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. HP</label>
                    <input name="no_hp" value="<?php echo e(old('no_hp', $mahasiswa->no_hp)); ?>" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-select" required>
                        <option value="">Pilih kelas</option>
                        <?php $__currentLoopData = $kelases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kelas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($kelas->id); ?>" <?php if(old('kelas_id', $mahasiswa->kelas_id) == $kelas->id): echo 'selected'; endif; ?>><?php echo e($kelas->nama); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php if(!$mahasiswa->exists): ?>
                    <div class="col-md-6">
                        <label class="form-label">Password awal</label>
                        <input name="password" type="text" value="password" class="form-control">
                        <div class="form-text text-secondary">Default: <code>password</code>. Boleh diubah.</div>
                    </div>
                <?php endif; ?>
            </div>

            <button class="btn btn-primary mt-4">Simpan</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mahasiswa\form.blade.php ENDPATH**/ ?>