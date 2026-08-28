<?php $canManageMahasiswa = session('user_role') === 'admin'; ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Data Siswa (Mahasiswa)</h1>
        <p class="text-secondary mb-0">Kelola seluruh data peserta didik (terintegrasi dengan LMS, nilai & jadwal).</p>
    </div>
    <?php if($canManageMahasiswa): ?>
        <a href="<?php echo e(route('mahasiswa.create')); ?>" class="btn btn-primary">+ Tambah siswa</a>
    <?php endif; ?>
</div>

<div class="card table-card">
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-5">
                <input name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Cari NIS/NIM, nama, atau email...">
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary">Cari</button></div>
            <?php if(request('search')): ?>
                <div class="col-auto"><a href="<?php echo e(route('mahasiswa.index')); ?>" class="btn btn-light">Reset</a></div>
            <?php endif; ?>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>NIS/NIM</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <?php if($canManageMahasiswa): ?><th class="text-end">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $mahasiswas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mahasiswa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><span class="badge text-bg-light"><?php echo e($mahasiswa->nik ?? '-'); ?></span></td>
                            <td class="fw-semibold"><?php echo e($mahasiswa->name); ?>

                                <div class="small text-secondary"><?php echo e($mahasiswa->email); ?></div>
                            </td>
                            <td><?php echo e($mahasiswa->kelas?->nama ?? '-'); ?></td>
                            <td>
                                <span class="badge rounded-pill <?php echo e($mahasiswa->aktif ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'); ?>"><?php echo e($mahasiswa->aktif ? 'Aktif' : 'Nonaktif'); ?></span>
                            </td>
                            <?php if($canManageMahasiswa): ?>
                                <td class="text-end">
                                    <a href="<?php echo e(route('mahasiswa.edit', $mahasiswa)); ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form class="d-inline" method="POST" action="<?php echo e(route('mahasiswa.destroy', $mahasiswa)); ?>">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus data siswa ini? Seluruh data terkait akan ikut terhapus.')">Hapus</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e($canManageMahasiswa ? 5 : 4); ?>" class="text-center text-secondary py-4">
                                Belum ada data siswa.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo e($mahasiswas->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mahasiswa\index.blade.php ENDPATH**/ ?>