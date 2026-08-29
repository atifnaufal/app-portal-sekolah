<?php $__env->startSection('content'); ?>
<div class="p-3 pb-0">
    <a href="<?php echo e(route('dashboard')); ?>" class="text-decoration-none text-muted d-inline-flex align-items-center gap-2 small fw-bold">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
        Kembali
    </a>
</div>

<header class="mobile-hero mt-3" style="border-radius: 25px;">
    <div class="eyebrow">PEMBERITAHUAN</div>
    <div class="hero-title mt-2">Notifikasi</div>
    <div class="mt-3">
        <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-2 fw-normal" style="font-size: 11px;">
            Info penting untukmu
        </span>
    </div>
</header>

<main class="mobile-content">
    <div class="stagger">
        <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $isSpp = str_contains(strtolower($notification->judul), 'spp');
                $isUnread = is_null($notification->dibaca_pada);
            ?>
            <a href="<?php echo e($notification->url ?: route('dashboard')); ?>" class="card mobile-card shadow-sm border-0 mb-3 <?php echo e($isUnread ? 'bg-primary-subtle bg-opacity-10' : ''); ?>" style="border-radius: 20px;">
                <div class="card-body p-3">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="icon-box flex-shrink-0" style="background: <?php echo e($isSpp ? '#fff9ed' : '#f0f7ff'); ?>; color: <?php echo e($isSpp ? '#a66b00' : '#246bfe'); ?>; width: 44px; height: 44px; border-radius: 12px;">
                            <?php if($isSpp): ?>
                                <i class="bi bi-wallet2 h5 mb-0"></i>
                            <?php else: ?>
                                <i class="bi bi-chat-left-text h5 mb-0"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h3 class="h6 fw-bold mb-0 text-truncate pe-2"><?php echo e($notification->judul); ?></h3>
                                <?php if($isUnread): ?>
                                    <span class="badge bg-primary p-1 rounded-circle" style="width: 8px; height: 8px;"></span>
                                <?php endif; ?>
                            </div>
                            <p class="small text-secondary mb-2 line-clamp-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?php echo e($notification->pesan); ?>

                            </p>
                            <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 10px;">
                                <i class="bi bi-clock"></i>
                                <span><?php echo e($notification->created_at->diffForHumans()); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-5 opacity-50">
                <i class="bi bi-bell-slash h1"></i>
                <div class="small mt-2">Belum ada notifikasi baru.</div>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\notifications.blade.php ENDPATH**/ ?>