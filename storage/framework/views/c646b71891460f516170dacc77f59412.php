<?php $__env->startSection('content'); ?>
<style>
    .announcement-tag {
        font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 6px;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    .tag-general { background: #fee2e2; color: #ef4444; }
    .tag-class { background: #dcfce7; color: #22c55e; }
    .announcement-img { width: 100%; height: 180px; object-fit: cover; }
    .announcement-img-placeholder {
        width: 100%; height: 120px; background: #f8fafc;
        display: flex; align-items: center; justify-content: center;
        color: #e2e8f0; font-size: 40px;
    }
</style>

<div class="p-3 pb-0">
    <a href="<?php echo e(route('dashboard')); ?>" class="text-decoration-none text-muted d-inline-flex align-items-center gap-2 small fw-bold">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<header class="mobile-hero">
    <div class="eyebrow">INFORMASI RESMI</div>
    <div class="hero-title mt-2">Pengumuman</div>
    <div class="class-pill mt-3">Agenda dan berita terkini</div>
</header>

<main class="mobile-content">
    <div class="stagger">
        <?php $__empty_1 = true; $__currentLoopData = $pengumumans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="card mobile-card mb-3 overflow-hidden border-0 shadow-sm">
                <?php if($item->gambar): ?>
                    <img src="<?php echo e(asset('storage/'.$item->gambar)); ?>"
                         alt="<?php echo e($item->judul); ?>"
                         class="announcement-img"
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Info&background=f1f5f9&color=94a3b8';">
                <?php else: ?>
                    <div class="announcement-img-placeholder">
                        <i class="bi bi-megaphone"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="small text-muted fw-bold"><?php echo e($item->created_at->format('d M Y')); ?></div>
                        <?php if($item->kelas_id): ?>
                            <span class="announcement-tag tag-class">KELAS <?php echo e($item->kelas->nama); ?></span>
                        <?php elseif($item->eskul_id): ?>
                            <span class="announcement-tag tag-eskul"><?php echo e($item->eskul->nama); ?></span>
                        <?php else: ?>
                            <span class="announcement-tag tag-general">UMUM</span>
                        <?php endif; ?>
                    </div>
                    <h2 class="h5 fw-bold"><?php echo e($item->judul); ?></h2>
                    <p class="small text-secondary mb-0 mt-2" style="white-space:pre-line"><?php echo e($item->isi); ?></p>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-5">
                <i class="bi bi-megaphone h1 opacity-25 d-block mb-3"></i>
                <div class="text-secondary">Belum ada pengumuman untuk Anda.</div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php if(isset($canCreate) && $canCreate): ?>
    <a href="<?php echo e(route('pengumuman.create')); ?>" class="btn btn-primary shadow-lg d-flex align-items-center justify-content-center"
       style="position:fixed; bottom:80px; right:20px; width:56px; height:56px; border-radius:18px; z-index:1000;">
        <i class="bi bi-plus-lg h3 mb-0"></i>
    </a>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\pengumuman.blade.php ENDPATH**/ ?>