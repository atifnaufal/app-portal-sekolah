<?php $__env->startSection('content'); ?>
<?php
    $stats = $stats ?? ['total' => 0, 'lunas' => 0, 'belum' => 0, 'total_nominal' => 0, 'total_terbayar' => 0, 'total_kekurangan' => 0];
    $namaBulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $namaBulanFull = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $pctLunas = $stats['total'] > 0 ? round(($stats['lunas'] / $stats['total']) * 100) : 0;
?>

<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
    <div>
        <div class="text-primary small fw-semibold">KEUANGAN SEKOLAH</div>
        <h1 class="h3 fw-bold mb-1">Data SPP & Pembayaran</h1>
        <p class="text-secondary mb-0">Kelola tagihan berdasarkan kelas, nama, dan NIK siswa.</p>
    </div>
    <a href="<?php echo e(route('spp.create')); ?>" class="btn btn-primary rounded-pill px-4">
        <i class="bi bi-plus-lg me-1"></i> Buat Tagihan
    </a>
</div>


<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card" style="border-top-color: #246bfe;">
            <div class="card-body">
                <div class="metric-label">TOTAL TAGIHAN</div>
                <div class="metric-value"><?php echo e($stats['total']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card" style="border-top-color: #16a34a;">
            <div class="card-body">
                <div class="metric-label">LUNAS</div>
                <div class="metric-value" style="color:#16a34a;"><?php echo e($stats['lunas']); ?></div>
                <div class="small text-muted"><?php echo e($pctLunas); ?>% dari total</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card" style="border-top-color: #f59e0b;">
            <div class="card-body">
                <div class="metric-label">BELUM LUNAS</div>
                <div class="metric-value" style="color:#f59e0b;"><?php echo e($stats['belum']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card" style="border-top-color: #dc2626;">
            <div class="card-body">
                <div class="metric-label">TOTAL KEKURANGAN</div>
                <div class="metric-value" style="font-size:20px;color:#dc2626;">Rp <?php echo e(number_format($stats['total_kekurangan'], 0, ',', '.')); ?></div>
            </div>
        </div>
    </div>
</div>


<div class="card table-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Siswa</th>
                        <th>Kelas</th>
                        <th>Periode</th>
                        <th>Tagihan</th>
                        <th>Dibayar</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $spps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $spp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $pct = $spp->nominal > 0 ? min(100, round(((float)$spp->dibayar / (float)$spp->nominal) * 100)) : 0;
                            $isLunas = $spp->status === 'lunas';
                            $isOverdue = $spp->jatuh_tempo && $spp->jatuh_tempo->isPast() && !$isLunas;
                        ?>
                        <tr>
                            <td class="ps-4">
                                <strong><?php echo e($spp->siswa->name ?? '-'); ?></strong>
                                <div class="small text-secondary">NIK: <?php echo e($spp->siswa->nik ?? '-'); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?php echo e($spp->siswa->kelas?->nama ?? '-'); ?></span>
                            </td>
                            <td>
                                <strong><?php echo e($namaBulanFull[$spp->bulan] ?? ''); ?></strong>
                                <div class="small text-muted"><?php echo e($spp->tahun); ?></div>
                            </td>
                            <td class="fw-bold">Rp <?php echo e(number_format($spp->nominal, 0, ',', '.')); ?></td>
                            <td>Rp <?php echo e(number_format($spp->dibayar, 0, ',', '.')); ?></td>
                            <td style="min-width:120px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;border-radius:99px;">
                                        <div class="progress-bar <?php echo e($isLunas ? 'bg-success' : 'bg-warning'); ?>" style="width:<?php echo e($pct); ?>%;border-radius:99px;"></div>
                                    </div>
                                    <span class="small fw-bold text-muted"><?php echo e($pct); ?>%</span>
                                </div>
                            </td>
                            <td>
                                <?php if($isLunas): ?>
                                    <span class="badge rounded-pill" style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:700;">Lunas</span>
                                <?php elseif($isOverdue): ?>
                                    <span class="badge rounded-pill" style="background:#fee2e2;color:#dc2626;font-size:11px;font-weight:700;">Jatuh Tempo</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill" style="background:#fef3c7;color:#b45309;font-size:11px;font-weight:700;">Belum Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="<?php echo e(route('spp.edit', $spp)); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size:12px;">Edit</a>
                                    <form class="d-inline" method="POST" action="<?php echo e(route('spp.destroy', $spp)); ?>">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3" style="font-size:12px;" onclick="return confirm('Hapus tagihan ini?')">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-secondary py-5">
                                <i class="bi bi-receipt" style="font-size:32px;display:block;margin-bottom:8px;opacity:0.4;"></i>
                                Belum ada tagihan SPP. Buat tagihan pertama dari tombol di atas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\admin\spp.blade.php ENDPATH**/ ?>