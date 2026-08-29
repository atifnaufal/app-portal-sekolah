
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="text-primary small fw-semibold">MANAJEMEN PERPUSTAKAAN</div>
        <h1 class="h3 fw-bold mb-1">Koleksi Buku Digital</h1>
        <p class="text-secondary mb-0">Kelola buku-buku digital yang dapat diakses oleh siswa dan guru.</p>
    </div>
    <div>
        <a href="<?php echo e(route('admin.perpustakaan.kategori.index')); ?>" class="btn btn-outline-primary me-2">Kelola Kategori</a>
        <a href="<?php echo e(route('admin.perpustakaan.create')); ?>" class="btn btn-primary">+ Tambah Buku</a>
    </div>
</div>

<div class="card table-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">Cover</th>
                        <th>Judul Buku</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bukus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $buku): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <?php if($buku->cover): ?>
                                <img src="<?php echo e(asset('storage/'.$buku->cover)); ?>" class="rounded shadow-sm" style="width: 50px; aspect-ratio: 2/3; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; aspect-ratio: 2/3;">
                                    <i class="bi bi-book text-muted small"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo e($buku->judul); ?></strong>
                            <div class="small text-secondary"><?php echo e($buku->tahun_terbit ?? '-'); ?> · <?php echo e($buku->penerbit ?? '-'); ?></div>
                        </td>
                        <td><span class="badge text-bg-info"><?php echo e($buku->kategori->nama); ?></span></td>
                        <td><?php echo e($buku->penulis ?? '-'); ?></td>
                        <td><?php echo e($buku->stok); ?></td>
                        <td>
                            <a href="<?php echo e(route('admin.perpustakaan.edit', $buku)); ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" action="<?php echo e(route('admin.perpustakaan.destroy', $buku)); ?>" class="d-inline" onsubmit="return confirm('Hapus buku ini?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-5">
                            Belum ada koleksi buku. <a href="<?php echo e(route('admin.perpustakaan.create')); ?>">Tambah sekarang.</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\admin\perpustakaan\index.blade.php ENDPATH**/ ?>