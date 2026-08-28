<?php $__env->startSection('content'); ?>
<style>
    .announcement-page{max-width:1000px;margin:0 auto}.page-intro{width:100%}.announcement-card{border:0;border-radius:14px;overflow:hidden;box-shadow:0 5px 20px #14213d0d}.announcement-media{min-height:180px}.announcement-media img{display:block;width:100%;height:100%;min-height:180px;object-fit:cover}@media(max-width:767px){.announcement-card .card-body{padding:1.1rem!important}.announcement-media,.announcement-media img{min-height:160px;height:160px}.announcement-card .btn{font-size:12px}}
</style>
<div class="announcement-page">
    <div class="page-intro mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <div class="text-primary small fw-bold text-uppercase">Master Publik</div>
                <h1 class="page-heading h3 fw-bold mb-1">Pengumuman Sekolah</h1>
                <p class="text-secondary mb-0">Kelola agenda, berita, dan informasi resmi sekolah.</p>
            </div>
            <a href="<?php echo e(route('pengumuman.create')); ?>" class="btn btn-primary flex-shrink-0">+ Tambah pengumuman</a>
        </div>
    </div>

    <?php if($pengumumans->isNotEmpty()): ?>
        <div class="announcement-list">
            <?php $__currentLoopData = $pengumumans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="card announcement-card mb-3">
                    <div class="row g-0">
                        <?php if($item->gambar): ?>
                            <div class="col-12 col-md-3 announcement-media">
                                <img src="<?php echo e(asset('storage/'.$item->gambar)); ?>" alt="<?php echo e($item->judul); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="<?php echo e($item->gambar ? 'col-12 col-md-9' : 'col-12'); ?>">
                            <div class="card-body p-4">
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
                                    <div>
                                        <span class="badge rounded-pill text-bg-primary mb-2"><?php echo e($item->is_landing ? 'Banner Beranda' : 'Publik'); ?></span>
                                        <h2 class="h5 fw-bold mb-1"><?php echo e($item->judul); ?></h2>
                                        <div class="small text-secondary"><?php echo e($item->tanggal_acara?->format('d M Y') ?? $item->created_at->format('d M Y')); ?></div>
                                    </div>
                                    <div class="d-flex gap-2 flex-shrink-0">
                                        <a href="<?php echo e(route('pengumuman.edit',$item)); ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form method="POST" action="<?php echo e(route('pengumuman.destroy',$item)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus pengumuman ini?')">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                                <p class="mb-0 mt-3 text-secondary" style="white-space:pre-line"><?php echo e($item->isi); ?></p>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="card table-card">
            <div class="card-body text-center py-5">
                <div class="h5 fw-bold">Belum ada pengumuman publik</div>
                <p class="text-secondary mb-3">Tambahkan agenda atau berita resmi pertama sekolah.</p>
                <a href="<?php echo e(route('pengumuman.create')); ?>" class="btn btn-primary">Tambah pengumuman</a>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\pengumuman\index.blade.php ENDPATH**/ ?>