<?php $__env->startSection('content'); ?>
<?php
    $sppPersen = $sppTagihan > 0 ? round(($sppTerbayar / $sppTagihan) * 100) : 0;
    $piutang = max(0, $sppTagihan - $sppTerbayar);
?>

<style>
    .ad-hero {
        background: linear-gradient(135deg, var(--navy) 0%, #1e293b 100%);
        border-radius: 28px; padding: 40px; color: #fff;
        position: relative; overflow: hidden; margin-bottom: 32px;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }
    .ad-hero::after {
        content: ''; position: absolute; top: -100px; right: -100px;
        width: 300px; height: 300px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36,107,254,0.15) 0%, transparent 70%);
        pointer-events: none;
    }

    .ad-hero-eyebrow { font-size: 12px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; color: #94a3b8; }
    .ad-hero-title { font-size: 32px; font-weight: 800; margin: 8px 0; letter-spacing: -0.02em; }
    .ad-hero-subtitle { font-size: 14px; color: #94a3b8; margin: 0; }

    .ad-stat {
        background: #fff; border-radius: 24px; padding: 24px;
        border: 1px solid var(--border); transition: all 0.3s ease;
    }
    .ad-stat:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
    .ad-stat-icon {
        width: 56px; height: 56px; border-radius: 18px;
        display: flex; align-items: center; justify-content: center; font-size: 24px;
    }
    .ad-stat-num { font-size: 32px; font-weight: 800; color: var(--navy); margin-top: 12px; }
    .ad-stat-lbl { font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; }

    .ad-card { border-radius: 24px; border: 1px solid var(--border); overflow: hidden; }
    .ad-card-head { padding: 24px 30px; background: #fff; border-bottom: 1px solid var(--border); }
    .ad-card-title { font-size: 18px; font-weight: 800; color: var(--navy); display: flex; align-items: center; gap: 12px; }
    .ad-card-body { padding: 30px; background: #fff; }

    .ad-toggle {
        padding: 10px 18px; border-radius: 14px; font-size: 13px; font-weight: 700;
        border: 1.5px solid; cursor: pointer; background: #fff; transition: all 0.2s;
        display: flex; align-items: center; gap: 8px;
    }
    .ad-toggle:hover { filter: brightness(0.95); transform: translateY(-1px); }

    .ad-user-row { display: flex; align-items: center; gap: 16px; padding: 14px 0; }
    .ad-user-row + .ad-user-row { border-top: 1px solid #f1f5f9; }
    .ad-user-avatar {
        width: 44px; height: 44px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 15px; color: #fff; flex-shrink: 0;
    }

    .ad-kelas-bar { height: 10px; border-radius: 99px; background: #f1f5f9; overflow: hidden; margin-top: 8px; }
    .ad-kelas-fill { height: 100%; border-radius: 99px; transition: width 1s ease-out; }

    @media (max-width: 768px) {
        .ad-hero { padding: 30px; border-radius: 24px; text-align: center; }
        .ad-hero-title { font-size: 24px; }
        .ad-hero .d-flex.gap-2 { justify-content: center; margin-top: 20px; }
        .ad-toggle, .ad-hero .btn { width: 100%; justify-content: center; }
        .ad-stat-num { font-size: 26px; }
        .ad-user-row { flex-direction: column; align-items: flex-start; gap: 8px; }
        .ad-user-row > div:last-child { width: 100%; text-align: left !important; padding-left: 0; }
    }
</style>


<div class="ad-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-4" style="position:relative; z-index:1;">
        <div>
            <div class="ad-hero-eyebrow">Control Center</div>
            <h1 class="ad-hero-title">Academic Dashboard</h1>
            <p class="ad-hero-subtitle">Monitoring and managing school data in real-time.</p>
        </div>
        <div class="d-flex gap-3 flex-wrap">
            <form method="POST" action="<?php echo e(route('admin.registration.toggle')); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?> <input type="hidden" name="role" value="guru">
                <button class="ad-toggle" style="border-color:<?php echo e($registrationGuruEnabled ? '#fecaca' : '#bbf7d0'); ?>; color:<?php echo e($registrationGuruEnabled ? '#dc2626' : '#15803d'); ?>;">
                    <i class="bi bi-<?php echo e($registrationGuruEnabled ? 'person-dash' : 'person-check'); ?>"></i>
                    Guru: <?php echo e($registrationGuruEnabled ? 'Open' : 'Closed'); ?>

                </button>
            </form>
            <form method="POST" action="<?php echo e(route('admin.registration.toggle')); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?> <input type="hidden" name="role" value="siswa">
                <button class="ad-toggle" style="border-color:<?php echo e($registrationSiswaEnabled ? '#fecaca' : '#bbf7d0'); ?>; color:<?php echo e($registrationSiswaEnabled ? '#dc2626' : '#15803d'); ?>;">
                    <i class="bi bi-<?php echo e($registrationSiswaEnabled ? 'person-dash' : 'person-check'); ?>"></i>
                    Siswa: <?php echo e($registrationSiswaEnabled ? 'Open' : 'Closed'); ?>

                </button>
            </form>
            <a href="<?php echo e(route('pengumuman.create')); ?>" class="btn btn-light fw-bold px-4" style="border-radius:14px;">
                <i class="bi bi-plus-circle-fill me-2"></i> Announcement
            </a>
        </div>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="ad-card shadow-sm">
            <div class="ad-card-head d-flex justify-content-between align-items-center">
                <h2 class="ad-card-title"><i class="bi bi-mortarboard text-primary"></i> E-Learning (LMS) Overview</h2>
                <span class="badge rounded-pill bg-primary-subtle text-primary px-3" style="font-size:11px; font-weight:800;">Learning Management System</span>
            </div>
            <div class="ad-card-body">
                <div class="row g-3 text-center">
                    <div class="col-lg-3 col-md-6">
                        <div class="border rounded-4 p-4 h-100" style="background:#f5f8ff;">
                            <div class="fw-extrabold text-primary" style="font-size:34px;"><?php echo e(number_format($totalMapel)); ?></div>
                            <div class="small fw-bold text-muted text-uppercase mt-1" style="letter-spacing:0.06em;">Mata Pelajaran</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="border rounded-4 p-4 h-100" style="background:#f0fdf4;">
                            <div class="fw-extrabold text-success" style="font-size:34px;"><?php echo e(number_format($totalMateri)); ?></div>
                            <div class="small fw-bold text-muted text-uppercase mt-1" style="letter-spacing:0.06em;">Materi Dibagikan</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="border rounded-4 p-4 h-100" style="background:#fffbeb;">
                            <div class="fw-extrabold text-warning" style="font-size:34px;"><?php echo e(number_format($totalTugas)); ?></div>
                            <div class="small fw-bold text-muted text-uppercase mt-1" style="letter-spacing:0.06em;">Total Tugas</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="border rounded-4 p-4 h-100" style="background:#fff5f6;">
                            <div class="fw-extrabold <?php echo e($tugasBelumDinilai > 0 ? 'text-danger' : 'text-muted'); ?>" style="font-size:34px;"><?php echo e(number_format($tugasBelumDinilai)); ?></div>
                            <div class="small fw-bold text-muted text-uppercase mt-1" style="letter-spacing:0.06em;">Jawaban Perlu Dinilai</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Teachers</div>
                <div class="ad-stat-icon" style="background:#eff6ff; color:var(--blue);"><i class="bi bi-person-badge"></i></div>
            </div>
            <div class="ad-stat-num"><?php echo e(number_format($totalGuru)); ?></div>
            <div class="small text-muted mt-2"><i class="bi bi-graph-up text-success"></i> Active Faculty</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Students</div>
                <div class="ad-stat-icon" style="background:#f0fdf4; color:#16a34a;"><i class="bi bi-people"></i></div>
            </div>
            <div class="ad-stat-num"><?php echo e(number_format($totalSiswa)); ?></div>
            <div class="small text-muted mt-2"><i class="bi bi-mortarboard text-primary"></i> Enrolled Pupils</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Active Classes</div>
                <div class="ad-stat-icon" style="background:#fefce8; color:#d97706;"><i class="bi bi-building"></i></div>
            </div>
            <div class="ad-stat-num"><?php echo e(number_format($totalKelas)); ?></div>
            <div class="small text-muted mt-2"><i class="bi bi-check-circle text-warning"></i> Current Rooms</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="ad-stat">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="ad-stat-lbl">Pending SPP</div>
                <div class="ad-stat-icon" style="background:#fef2f2; color:#dc2626;"><i class="bi bi-cash-stack"></i></div>
            </div>
            <div class="ad-stat-num text-danger"><?php echo e($sppKurang); ?></div>
            <div class="small text-muted mt-2"><i class="bi bi-clock-history"></i> Outstanding Debt</div>
        </div>
    </div>
</div>


<div class="row g-4 mb-5">
    <div class="col-lg-8">
        <div class="ad-card shadow-sm">
            <div class="ad-card-head d-flex justify-content-between align-items-center">
                <h2 class="ad-card-title"><i class="bi bi-bar-chart-line text-primary"></i> Payment Trends</h2>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">Last 6 Months</button>
                    <ul class="dropdown-menu shadow border-0">
                        <li><a class="dropdown-item small fw-bold" href="#">Current Year</a></li>
                    </ul>
                </div>
            </div>
            <div class="ad-card-body">
                <div style="height:320px;"><canvas id="sppChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ad-card shadow-sm">
            <div class="ad-card-head"><h2 class="ad-card-title"><i class="bi bi-pie-chart text-purple" style="color:#7c3aed;"></i> Financial Health</h2></div>
            <div class="ad-card-body text-center">
                <div style="position:relative; height:180px; margin: 0 auto 24px;">
                    <canvas id="sppDonut"></canvas>
                    <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; pointer-events:none;">
                        <div style="font-size:32px; font-weight:800; color:var(--navy);"><?php echo e($sppPersen); ?>%</div>
                        <div style="font-size:12px; color:var(--muted); font-weight:700;">COLLECTED</div>
                    </div>
                </div>
                <div class="px-2">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="small fw-bold text-muted"><i class="bi bi-circle-fill me-2" style="color:#16a34a; font-size:8px;"></i> Received</span>
                        <span class="small fw-extrabold text-success">Rp <?php echo e(number_format($sppTerbayar, 0, ',', '.')); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="small fw-bold text-muted"><i class="bi bi-circle-fill me-2" style="color:#f59e0b; font-size:8px;"></i> Receivable</span>
                        <span class="small fw-extrabold text-warning">Rp <?php echo e(number_format($piutang, 0, ',', '.')); ?></span>
                    </div>
                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-dark">Total Revenue</span>
                        <span class="h6 fw-extrabold mb-0">Rp <?php echo e(number_format($sppTagihan, 0, ',', '.')); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row g-4">
    <div class="col-lg-6">
        <div class="ad-card shadow-sm h-100">
            <div class="ad-card-head d-flex justify-content-between align-items-center">
                <h2 class="ad-card-title"><i class="bi bi-shield-check text-primary"></i> Recent Registrations</h2>
                <a href="<?php echo e(route('admin.users')); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">View All</a>
            </div>
            <div class="ad-card-body">
                <?php $__empty_1 = true; $__currentLoopData = $recentUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="ad-user-row">
                        <div class="ad-user-avatar" style="background:<?php echo e($u->role === 'guru' ? 'linear-gradient(135deg,#3b82f6,#2563eb)' : 'linear-gradient(135deg,#22c55e,#16a34a)'); ?>; shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            <?php echo e(strtoupper(substr($u->name, 0, 1))); ?>

                        </div>
                        <div style="flex:1;">
                            <div class="fw-bold text-dark" style="font-size:14px;"><?php echo e($u->name); ?></div>
                            <div class="text-muted small"><?php echo e($u->email); ?></div>
                        </div>
                        <div class="text-end">
                            <span class="badge rounded-pill px-3 <?php echo e($u->role === 'guru' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'); ?> mb-1" style="font-size:10px; font-weight:800; text-transform:uppercase;"><?php echo e($u->role); ?></span>
                            <div class="small fw-bold <?php echo e($u->aktif ? 'text-success' : 'text-muted'); ?>" style="font-size:10px;"><?php echo e($u->aktif ? 'Active' : 'Inactive'); ?></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-5 text-muted small">No recent activity detected.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="ad-card shadow-sm h-100">
            <div class="ad-card-head d-flex justify-content-between align-items-center">
                <h2 class="ad-card-title"><i class="bi bi-bar-chart-fill text-warning"></i> Classroom Density</h2>
                <a href="<?php echo e(route('kelas.index')); ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold">Manage</a>
            </div>
            <div class="ad-card-body">
                <?php $__empty_1 = true; $__currentLoopData = $kelasSummaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $maxSiswa = max(1, $kelasSummaries->max('siswa_count')); ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark" style="font-size:14px;"><?php echo e($k->nama); ?></span>
                            <span class="text-muted" style="font-size:11px;">
                                <strong class="text-primary"><?php echo e($k->siswa_count); ?></strong> Students &bull; <strong class="text-success"><?php echo e($k->guru_count); ?></strong> Faculty
                            </span>
                        </div>
                        <div class="ad-kelas-bar">
                            <div class="ad-kelas-fill" style="width:<?php echo e(min(100, ($k->siswa_count / $maxSiswa) * 100)); ?>%; background: linear-gradient(90deg, var(--blue), #60a5fa);"></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-5 text-muted small">No classroom data available.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';

        var lineCtx = document.getElementById('sppChart');
        if (lineCtx) {
            var g1 = lineCtx.getContext('2d').createLinearGradient(0,0,0,300);
            g1.addColorStop(0, 'rgba(36,107,254,0.15)'); g1.addColorStop(1, 'rgba(36,107,254,0)');
            var g2 = lineCtx.getContext('2d').createLinearGradient(0,0,0,300);
            g2.addColorStop(0, 'rgba(22,163,74,0.15)'); g2.addColorStop(1, 'rgba(22,163,74,0)');

            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($chartLabels); ?>,
                    datasets: [{
                        label: 'Invoiced', data: <?php echo json_encode($chartTagihan); ?>,
                        borderColor: '#246bfe', backgroundColor: g1, borderWidth: 3, fill: true, tension: 0.4,
                        pointBackgroundColor: '#fff', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6
                    }, {
                        label: 'Received', data: <?php echo json_encode($chartTerbayar); ?>,
                        borderColor: '#16a34a', backgroundColor: g2, borderWidth: 3, fill: true, tension: 0.4,
                        pointBackgroundColor: '#fff', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', align: 'end', labels: { usePointStyle: true, padding: 25, font: { weight: '700', size: 12 } } } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9', drawBorder: false }, ticks: { callback: v => 'Rp ' + (v/1000) + 'k', padding: 10 } },
                        x: { grid: { display: false, drawBorder: false }, ticks: { padding: 10 } }
                    }
                }
            });
        }

        var donutCtx = document.getElementById('sppDonut');
        if (donutCtx) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Received', 'Receivable'],
                    datasets: [{ data: [<?php echo e((float) $sppTerbayar); ?>, <?php echo e($piutang); ?>], backgroundColor: ['#16a34a', '#f59e0b'], borderWidth: 0, cutout: '82%', hoverOffset: 8 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\admin\dashboard.blade.php ENDPATH**/ ?>