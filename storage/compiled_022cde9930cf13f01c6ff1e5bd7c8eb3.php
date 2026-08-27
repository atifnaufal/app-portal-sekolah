<?php $__env->startSection('content'); ?>
<header class="mobile-hero" style="border-radius: 0 0 35px 35px;">
    <div class="eyebrow">PENGATURAN AKUN</div>
    <div class="d-flex align-items-center gap-3 mt-3">
        <div class="avatar shadow-sm border border-3 border-white border-opacity-30" style="width: 65px; height: 65px; border-radius: 22px;">
            <?php if($user->foto): ?>
                <img src="<?php echo e(asset('storage/'.$user->foto)); ?>" alt="Foto" style="width:100%;height:100%;object-fit:cover;object-position:center;">
            <?php else: ?>
                <span class="h4 mb-0 fw-bold"><?php echo e(strtoupper(substr($user->name,0,1))); ?></span>
            <?php endif; ?>
        </div>
        <div>
            <div class="hero-title" style="font-size: 24px;"><?php echo e(explode(' ', $user->name)[0]); ?></div>
            <div class="class-pill mt-1" style="font-size: 10px;">AKUN <?php echo e(strtoupper($user->role)); ?></div>
        </div>
    </div>
</header>

<main class="mobile-content">
    
    <?php if($user->role === 'siswa' && !$user->hasVerifiedEmail()): ?>
        <div class="alert alert-warning border-0 rounded-4 p-3 mb-4 d-flex align-items-start gap-3">
            <i class="bi bi-exclamation-octagon-fill h4 mb-0"></i>
            <div>
                <div class="fw-bold small">Email Belum Diverifikasi</div>
                <p class="x-small mb-2 opacity-75">Beberapa fitur mungkin dibatasi hingga Anda melakukan konfirmasi email.</p>
                <form method="POST" action="<?php echo e(route('verification.send')); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-warning btn-sm fw-bold rounded-pill px-3" style="font-size: 10px;">Kirim Ulang Link</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="card mobile-card mb-4 shadow-sm border-0" style="border-radius: 25px;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <i class="bi bi-person-badge text-primary h5 mb-0"></i>
                <div class="flex-grow-1">
                    <div class="text-secondary" style="font-size: 10px; font-weight: 800;">NAMA LENGKAP</div>
                    <div class="fw-bold text-dark"><?php echo e($user->name); ?></div>
                </div>
            </div>
            <hr class="opacity-5 my-3">
            <div class="d-flex align-items-center gap-3 mb-3">
                <i class="bi bi-envelope-at text-primary h5 mb-0"></i>
                <div class="flex-grow-1">
                    <div class="text-secondary" style="font-size: 10px; font-weight: 800;">ALAMAT EMAIL</div>
                    <div class="fw-bold text-dark d-flex align-items-center gap-2">
                        <?php echo e($user->email); ?>

                        <?php if($user->hasVerifiedEmail()): ?>
                            <i class="bi bi-patch-check-fill text-success" title="Terverifikasi"></i>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <hr class="opacity-5 my-3">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-mortarboard text-primary h5 mb-0"></i>
                <div class="flex-grow-1">
                    <div class="text-secondary" style="font-size: 10px; font-weight: 800;">KELAS / JABATAN</div>
                    <div class="fw-bold text-dark"><?php echo e($user->kelas?->nama ?? 'Staf Sekolah'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <a href="<?php echo e(route('profile.edit')); ?>" class="btn btn-primary w-100 py-3 shadow-sm mb-3" style="border-radius: 18px; font-weight: 800;">
        <i class="bi bi-pencil-square me-2"></i> EDIT PROFIL SAYA
    </a>

    <?php if($user->role === 'admin'): ?>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-dark w-100 py-3 mb-3" style="border-radius: 18px; font-weight: 800; background: #1e293b;">
            <i class="bi bi-cpu me-2"></i> PANEL KONTROL ADMIN
        </a>
    <?php endif; ?>

    <div class="logout-panel mt-5 shadow-sm" style="border: 1px solid #fee2e2; background: #fff5f5; border-radius: 20px;">
        <div>
            <div class="fw-bold text-danger">Keluar Akun?</div>
            <div class="x-small text-secondary mt-1">Hentikan sesi aktif perangkat.</div>
        </div>
        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" style="font-size: 12px;">KELUAR</button>
        </form>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>