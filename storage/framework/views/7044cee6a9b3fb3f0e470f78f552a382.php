<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cari Email | Portal Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #14213d, #246bfe); display: flex; align-items: center; justify-content: center; padding: 20px; }
        .recovery-card { max-width: 440px; width: 100%; border: none; border-radius: 28px; background: #fff; box-shadow: 0 20px 45px rgba(0,0,0,0.25); overflow: hidden; }
        .form-control { border-radius: 14px; padding: 12px 18px; border: 1px solid #e2e8f0; background: #fbfcfe; }
        .btn-recovery { background: #246bfe; border: none; border-radius: 14px; padding: 14px; font-weight: 700; color: #fff; transition: all 0.3s; }
        .btn-recovery:hover { background: #1d59d4; transform: translateY(-2px); }
        .result-box { background: #ecfdf5; border: 1px solid #10b981; border-radius: 15px; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="recovery-card">
    <div class="p-4 p-md-5">
        <a href="<?php echo e(route('login')); ?>" class="text-decoration-none small fw-bold mb-4 d-inline-block">
            <i class="bi bi-arrow-left"></i> Kembali ke Login
        </a>
        <h1 class="h4 fw-bold mb-2">Lupa Email?</h1>
        <p class="text-secondary small mb-4">Verifikasi data Anda untuk menemukan email yang terdaftar.</p>

        <?php if(session('found_email')): ?>
            <div class="result-box text-center animate__animated animate__fadeIn">
                <div class="small fw-bold text-success mb-1">Email Anda Ditemukan:</div>
                <div class="h5 fw-bold text-dark"><?php echo e(session('found_email')); ?></div>
                <div class="mt-3">
                    <a href="<?php echo e(route('login', ['email' => session('found_email')])); ?>" class="btn btn-success btn-sm rounded-pill px-3">Gunakan untuk Login</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert alert-danger border-0 rounded-4 small mb-4"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('email.find')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label fw-bold small">NIK / NO. INDUK</label>
                <input type="text" name="nik" class="form-control" placeholder="Masukkan NIK Anda" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold small">NO. HP AKTIF</label>
                <input type="text" name="no_hp" class="form-control" placeholder="0812..." required>
            </div>
            <button type="submit" class="btn btn-recovery w-100 mb-3">CARI EMAIL SAYA &rarr;</button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <div class="small text-muted mb-2">Lupa semua data login?</div>
            <div class="p-3 rounded-4" style="background: #fffbeb; border: 1px dashed #f59e0b;">
                <div class="small fw-bold" style="color: #92400e;">Berikan NIK ke Bagian Admin IT Sekolah untuk reset atau aktivasi.</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\auth\forgot-email.blade.php ENDPATH**/ ?>