<?php $__env->startSection('content'); ?>
<style>
    .announcement-tag {
        font-size: 10px; font-weight: 800; padding: 3px 9px; border-radius: 6px;
        text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 4px;
    }
    .tag-general { background: #fee2e2; color: #ef4444; }
    .tag-class   { background: #dcfce7; color: #16a34a; }
    .tag-eskul   { background: #fef3c7; color: #d97706; }
    .tag-private { background: linear-gradient(135deg,#e0e7ff,#ede9fe); color: #4f46e5; }
    .announcement-img { width: 100%; height: 180px; object-fit: cover; }
    .announcement-img-placeholder {
        width: 100%; height: 120px; background:
            linear-gradient(135deg,#eef2ff,#f5f3ff);
        display: flex; align-items: center; justify-content: center;
        color: #c7d2fe; font-size: 40px;
    }
    .unread-dot {
        width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0;
        background: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.15);
    }
    .private-card { border-left: 4px solid #6366f1; }
    .chip-mini {
        font-size: 10.5px; font-weight: 700; color: #64748b;
        background: #f1f5f9; border-radius: 99px; padding: 3px 9px;
        display: inline-flex; align-items: center; gap: 5px;
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
    <div class="class-pill mt-3">Agenda, berita, dan pesan pribadi</div>
</header>

<main class="mobile-content">
    <div class="stagger">
        <?php $__empty_1 = true; $__currentLoopData = $pengumumans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php ($isPrivate = method_exists($item,'isPrivate') && $item->isPrivate()); ?>
            <article class="card mobile-card mb-3 overflow-hidden border-0 shadow-sm <?php echo e($isPrivate ? 'private-card' : ''); ?>">
                <?php if($isPrivate): ?>
                    <div class="announcement-img-placeholder">
                        <i class="bi bi-person-lock"></i>
                    </div>
                <?php elseif($item->gambar): ?>
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
                    <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                        <div class="small text-muted fw-bold">
                            <?php echo e($item->created_at->format('d M Y')); ?>

                            <?php if($item->tanggal_acara): ?>
                                <span class="text-primary ms-1"><i class="bi bi-calendar-event"></i> <?php echo e($item->tanggal_acara->format('d M Y')); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <?php if($isPrivate): ?>
                                <?php if(empty($item->user_read_at) && $item->user_id != ($user->id ?? null)): ?>
                                    <span title="Belum dibaca"><span class="unread-dot"></span></span>
                                <?php endif; ?>
                                <span class="announcement-tag tag-private"><i class="bi bi-lock-fill"></i> Pribadi</span>
                            <?php elseif($item->kelas_id): ?>
                                <span class="announcement-tag tag-class">KELAS <?php echo e($item->kelas->nama); ?></span>
                            <?php elseif($item->eskul_id): ?>
                                <span class="announcement-tag tag-eskul"><?php echo e($item->eskul->nama); ?></span>
                            <?php else: ?>
                                <span class="announcement-tag tag-general">UMUM</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h2 class="h5 fw-bold mb-1"><?php echo e($item->judul); ?></h2>

                    <p class="small text-secondary mb-0 mt-2" style="white-space:pre-line"><?php echo e($item->isi); ?></p>

                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top">
                        <span class="chip-mini">
                            <i class="bi bi-person-circle"></i>
                            <?php echo e($item->user->name ?? 'Admin'); ?>

                        </span>
                        <?php if(!$isPrivate): ?>
                            <span class="chip-mini">
                                <i class="bi bi-people-fill"></i>
                                <?php echo e($item->kelas_id ? 'Kelas' : ($item->eskul_id ? 'Eskul' : 'Seluruh Sekolah')); ?>

                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if(isset($canManage) && $canManage && $item->user_id == ($user->id ?? null)): ?>
                        <div class="d-flex gap-2 mt-3">
                            <a href="<?php echo e(route('pengumuman.edit', $item)); ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                        </div>
                    <?php endif; ?>
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