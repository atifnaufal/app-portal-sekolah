<?php $hideNav = true; ?>


<?php $__env->startSection('content'); ?>
<?php
    $isGuru = $user->role === 'guru';
    $isSiswa = $user->role === 'siswa';
    $stats = $stats ?? ['total' => 0, 'lunas' => 0, 'belum' => 0, 'total_nominal' => 0, 'total_terbayar' => 0, 'total_kekurangan' => 0];
    $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $grouped = $spps->groupBy(function($item) { return $item->tahun . '-' . str_pad($item->bulan, 2, '0', STR_PAD_LEFT); });
?>

<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.92); backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(226,232,240,0.7);
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 70px; padding-bottom: 48px; }

    .glass-card {
        background: #fff; border: none; border-radius: 24px;
        box-shadow: 0 8px 28px rgba(0,0,0,0.04);
        overflow: hidden; margin-bottom: 16px;
    }

    .stat-card {
        border-radius: 20px; padding: 16px; text-align: center; flex: 1;
        position: relative; overflow: hidden;
    }
    .stat-card::before {
        content: ''; position: absolute; top: -10px; right: -10px;
        width: 50px; height: 50px; border-radius: 50%; opacity: 0.1;
    }
    .stat-card .stat-num { font-size: 22px; font-weight: 800; line-height: 1.1; }
    .stat-card .stat-lbl { font-size: 9px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; margin-top: 4px; opacity: 0.7; }

    .spp-row {
        background: #fff; border: 1px solid #e8ecf1; border-radius: 20px;
        padding: 16px; margin-bottom: 10px; transition: all 0.2s;
    }
    .spp-row:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }

    .progress-slim { height: 6px; border-radius: 99px; background: #eef2f7; overflow: hidden; }
    .progress-slim > span { display: block; height: 100%; border-radius: 99px; }

    .month-group-header {
        font-size: 12px; font-weight: 800; color: #64748b; letter-spacing: 0.06em;
        text-transform: uppercase; padding: 8px 0; margin-top: 8px;
        display: flex; align-items: center; gap: 8px;
    }
    .month-group-header::after {
        content: ''; flex: 1; height: 1px; background: #e2e8f0;
    }

    .status-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; border-radius: 99px; font-size: 10px; font-weight: 800;
        letter-spacing: 0.03em; text-transform: uppercase;
    }

    .currency-display { font-variant-numeric: tabular-nums; }

    @keyframes slideUp { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .slide-up { animation: slideUp 0.4s ease both; }
</style>

<div class="page-header">
    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px;">SPP & Pembayaran</div>
    <?php if($isGuru): ?>
        <a href="<?php echo e(route('spp.create')); ?>" class="btn btn-primary btn-sm rounded-pill px-3 ms-auto" style="font-weight: 700;">+ Catat</a>
    <?php endif; ?>
</div>

<div class="page-container px-3 pt-3">
    
    <div class="slide-up" style="background: linear-gradient(135deg, #1e293b, #0f766e); border-radius: 28px; padding: 24px 20px; margin-bottom: 18px; color: #fff; position: relative; overflow: hidden;">
        <div style="position:absolute;top:-20px;right:-20px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,0.06);"></div>
        <div style="position:absolute;bottom:-30px;right:40px;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.04);"></div>

        <div class="eyebrow" style="color: rgba(255,255,255,0.6); font-size: 11px; letter-spacing: 0.13em; font-weight: 800;">
            <i class="bi bi-wallet2 me-1"></i> KEUANGAN SEKOLAH
        </div>
        <div class="hero-title mt-2 text-white" style="font-size: 22px; font-weight: 800;">
            <?php echo e($isGuru ? 'Monitor SPP Kelas' : 'Tagihan SPP Saya'); ?>

        </div>
        <p class="mb-3 mt-1" style="font-size: 12px; color: rgba(255,255,255,.6);">
            <?php echo e($user->kelas?->nama ?? ($isGuru ? 'Panel Pembayaran' : '')); ?>

        </p>

        <div class="d-flex gap-2">
            <div class="stat-card" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15);">
                <div class="stat-num text-white"><?php echo e($stats['total']); ?></div>
                <div class="stat-lbl" style="color: rgba(255,255,255,0.6);">Tagihan</div>
            </div>
            <div class="stat-card" style="background: rgba(16,185,129,0.2); border: 1px solid rgba(16,185,129,0.25);">
                <div class="stat-num" style="color: #6ee7b7;"><?php echo e($stats['lunas']); ?></div>
                <div class="stat-lbl" style="color: rgba(110,231,183,0.7);">Lunas</div>
            </div>
            <div class="stat-card" style="background: rgba(251,191,36,0.2); border: 1px solid rgba(251,191,36,0.25);">
                <div class="stat-num" style="color: #fde68a;"><?php echo e($stats['belum']); ?></div>
                <div class="stat-lbl" style="color: rgba(253,230,138,0.7);">Belum</div>
            </div>
        </div>
    </div>

    
    <?php if($isSiswa): ?>
        <div class="glass-card slide-up" style="animation-delay: 0.1s;">
            <div class="p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div style="width:28px;height:28px;border-radius:10px;background:#f0fdf4;color:#16a34a;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-piggy-bank" style="font-size:14px;"></i>
                    </div>
                    <span class="fw-bold" style="font-size:14px;">Ringkasan Pembayaran</span>
                </div>
                <?php
                    $pctBayar = $stats['total_nominal'] > 0 ? round(($stats['total_terbayar'] / $stats['total_nominal']) * 100) : 0;
                ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-muted">Total terbayar</span>
                    <span class="fw-bold" style="font-size:13px;"><?php echo e($pctBayar); ?>%</span>
                </div>
                <div class="progress-slim mb-3">
                    <span style="width: <?php echo e($pctBayar); ?>%; background: linear-gradient(90deg, #16a34a, #4ade80);"></span>
                </div>
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="x-small text-muted">Terbayar</div>
                        <div class="fw-bold currency-display" style="font-size:14px; color:#16a34a;">Rp <?php echo e(number_format($stats['total_terbayar'], 0, ',', '.')); ?></div>
                    </div>
                    <div class="text-end">
                        <div class="x-small text-muted">Kekurangan</div>
                        <div class="fw-bold currency-display" style="font-size:14px; color:#dc2626;">Rp <?php echo e(number_format($stats['total_kekurangan'], 0, ',', '.')); ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="d-flex gap-2 mb-3 slide-up" style="animation-delay: 0.15s;">
        <button type="button" class="btn btn-sm rounded-pill fw-bold spp-filter active" data-filter="all" style="font-size:11px;">Semua</button>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold spp-filter" data-filter="lunas" style="font-size:11px;">Lunas</button>
        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill fw-bold spp-filter" data-filter="belum" style="font-size:11px;">Belum Lunas</button>
    </div>

    
    <div class="slide-up" style="animation-delay: 0.2s;">
        <?php $__empty_1 = true; $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $first = $items->first();
                $periodLabel = ($namaBulan[(int)$first->bulan] ?? '') . ' ' . $first->tahun;
            ?>
            <div class="month-group-header"><?php echo e($periodLabel); ?></div>

            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $spp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $pct = $spp->nominal > 0 ? min(100, round(((float)$spp->dibayar / (float)$spp->nominal) * 100)) : 0;
                    $isLunas = $spp->status === 'lunas';
                    $isOverdue = $spp->jatuh_tempo && $spp->jatuh_tempo->isPast() && !$isLunas;
                ?>
                <div class="spp-row" data-spp data-status="<?php echo e($isLunas ? 'lunas' : 'belum'); ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <?php if($isGuru): ?>
                                <div class="fw-bold text-dark" style="font-size:14px;"><?php echo e($spp->siswa->name ?? 'Siswa'); ?></div>
                                <div class="x-small text-muted"><?php echo e($spp->siswa->kelas?->nama ?? ''); ?></div>
                            <?php else: ?>
                                <div class="fw-bold text-dark" style="font-size:14px;">SPP <?php echo e($namaBulan[$spp->bulan] ?? ''); ?> <?php echo e($spp->tahun); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if($isLunas): ?>
                            <span class="status-badge" style="background:#dcfce7;color:#15803d;"><i class="bi bi-check-circle-fill"></i> Lunas</span>
                        <?php elseif($isOverdue): ?>
                            <span class="status-badge" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-exclamation-circle-fill"></i> Jatuh Tempo</span>
                        <?php else: ?>
                            <span class="status-badge" style="background:#fef3c7;color:#b45309;"><i class="bi bi-hourglass-split"></i> Belum Lunas</span>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Rp <?php echo e(number_format($spp->dibayar, 0, ',', '.')); ?> / <?php echo e(number_format($spp->nominal, 0, ',', '.')); ?></span>
                        <span class="fw-bold currency-display" style="font-size:12px; color: <?php echo e($isLunas ? '#16a34a' : '#dc2626'); ?>;">
                            <?php echo e($isLunas ? 'Lunas' : 'Sisa Rp ' . number_format($spp->kekurangan, 0, ',', '.')); ?>

                        </span>
                    </div>
                    <div class="progress-slim">
                        <span style="width: <?php echo e($pct); ?>%; background: <?php echo e($isLunas ? 'linear-gradient(90deg, #16a34a, #4ade80)' : 'linear-gradient(90deg, #f59e0b, #fbbf24)'); ?>;"></span>
                    </div>

                    <?php if($spp->jatuh_tempo && !$isLunas): ?>
                        <div class="x-small text-muted mt-2">
                            <i class="bi bi-calendar-event me-1"></i>Jatuh tempo: <?php echo e($spp->jatuh_tempo->format('d M Y')); ?>

                        </div>
                    <?php endif; ?>

                    <?php if($isGuru && !$isLunas): ?>
                        <form method="POST" action="<?php echo e(route('spp.remind', $spp)); ?>" class="mt-3">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-outline-warning btn-sm w-100 rounded-pill" style="font-size:12px;font-weight:700;">
                                <i class="bi bi-bell me-1"></i> Kirim Pengingat
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="glass-card">
                <div class="p-5 text-center">
                    <i class="bi bi-receipt" style="font-size:40px;color:#cbd5e1;"></i>
                    <div class="fw-bold mt-2 text-muted">Belum ada data SPP</div>
                    <div class="x-small text-muted mt-1">
                        <?php if($isGuru): ?>
                            Tap "+ Catat" untuk membuat tagihan pertama.
                        <?php else: ?>
                            Tagihan SPP akan muncul di sini.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.querySelectorAll('.spp-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.spp-filter').forEach(b => {
                b.classList.remove('active');
                b.className = b.className.replace(/btn-outline-\w+/g, '').replace(/btn-primary/g, '');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('active');
            const filter = this.dataset.filter;
            document.querySelectorAll('[data-spp]').forEach(el => {
                if (filter === 'all') { el.style.display = ''; }
                else { el.style.display = el.dataset.status === filter ? '' : 'none'; }
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\spp.blade.php ENDPATH**/ ?>