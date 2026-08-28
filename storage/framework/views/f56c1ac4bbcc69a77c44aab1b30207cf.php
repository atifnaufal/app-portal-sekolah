<?php $hideNav = true; ?>


<?php $__env->startSection('content'); ?>
<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.88); backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 70px; padding-bottom: 48px; }
    .day-header {
        position: sticky; top: 65px; z-index: 900;
        background: #f8fafc; padding: 12px 4px;
        font-weight: 800; font-size: 13px; text-transform: uppercase;
        letter-spacing: 0.1em; color: #64748b;
        display: flex; align-items: center; gap: 8px;
    }
    .day-header::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

    .schedule-card {
        background: #fff; border: none; border-radius: 22px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        margin-bottom: 14px; display: flex; overflow: hidden;
        border: 1px solid rgba(15, 23, 42, 0.04);
    }
    .time-strip {
        width: 75px; background: #f8fafc; padding: 20px 8px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        border-right: 1px dashed #e2e8f0;
    }
    .time-val { font-weight: 800; color: #0f172a; font-size: 14px; letter-spacing: -0.5px; }
    .time-end { font-size: 10px; color: #94a3b8; font-weight: 700; margin-top: 2px; }

    .content-area { padding: 18px 20px; flex: 1; position: relative; }
    .subject-name { font-weight: 800; color: #1e293b; font-size: 16px; margin-bottom: 4px; letter-spacing: -0.2px; }
    .meta-info { font-size: 11px; color: #64748b; display: flex; align-items: center; gap: 8px; font-weight: 600; }
    .meta-info i { color: var(--blue); }

    .status-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: #cbd5e1; position: absolute; top: 20px; right: 20px;
    }
    .status-dot.active { background: #10b981; box-shadow: 0 0 8px rgba(16,185,129,0.4); }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="page-header">
    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px; letter-spacing: -0.4px;">Jadwal Pelajaran</div>
</div>

<div class="page-container">
    <header class="mobile-hero" style="border-radius: 0 0 28px 28px; margin-bottom: 10px; background: linear-gradient(135deg, #0f172a, #1e293b); padding: 32px 24px 28px;">
        <div class="eyebrow" style="color: #94a3b8; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;">
            <?php echo e($user->kelas?->nama ?? ($isGuru ? 'Guru Pengampu' : 'Akademik')); ?>

        </div>
        <div class="hero-title mt-2 text-white" style="font-size: 24px; font-weight: 800; letter-spacing: -0.5px;"><?php echo e($isGuru ? 'Agenda Mengajar' : 'Agenda Belajar'); ?></div>
        <p class="mb-0 mt-1" style="font-size: 12px; color: rgba(255,255,255,.6); line-height: 1.5;">
            <?php echo e($isGuru ? 'Pantau jadwal mengajar Anda setiap harinya.' : 'Lihat jadwal mata pelajaran Anda minggu ini.'); ?>

        </p>

        <?php if(isset($stat) && $stat['total'] > 0): ?>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <div style="flex:1;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:16px;padding:12px;text-align:center;">
                    <div style="font-size:22px;font-weight:800;"><?php echo e($stat['total']); ?></div>
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;opacity:0.6;">Total Sesi</div>
                </div>
                <div style="flex:1;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:16px;padding:12px;text-align:center;">
                    <div style="font-size:22px;font-weight:800;"><?php echo e($stat['mapel']); ?></div>
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;opacity:0.6;"><?php echo e($isGuru ? 'Mapel Diampu' : 'Mata Pelajaran'); ?></div>
                </div>
                <div style="flex:1;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:16px;padding:12px;text-align:center;">
                    <div style="font-size:22px;font-weight:800;color:#4ade80;"><?php echo e($stat['hariIni']->count()); ?></div>
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;opacity:0.6;">Hari Ini</div>
                </div>
            </div>
        <?php endif; ?>
    </header>

    <main class="mobile-content px-3">
        <?php ($currentDay = \Carbon\Carbon::now()->translatedFormat('l')); ?>
        <?php ($currentTime = \Carbon\Carbon::now()->format('H:i')); ?>

        <?php $__empty_1 = true; $__currentLoopData = $jadwals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hari => $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="day-header mt-3 mb-2"><?php echo e($hari); ?></div>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php ($isActive = ($hari === $currentDay && $currentTime >= $j->jam_mulai && $currentTime <= $j->jam_selesai)); ?>
                <div class="schedule-card" style="animation: fadeIn 0.4s ease both;">
                    <div class="time-strip">
                        <div class="time-val"><?php echo e(\Carbon\Carbon::parse($j->jam_mulai)->format('H:i')); ?></div>
                        <div class="time-end"><?php echo e(\Carbon\Carbon::parse($j->jam_selesai)->format('H:i')); ?></div>
                    </div>
                    <div class="content-area">
                        <?php if($isActive): ?>
                            <div class="status-dot active"></div>
                        <?php endif; ?>
                        <div class="subject-name"><?php echo e($j->mataPelajaran->nama); ?></div>
                        <div class="meta-info mb-1">
                            <i class="bi bi-geo-alt"></i> <?php echo e($j->ruangan ?: 'Ruang Kelas'); ?>

                        </div>
                        <div class="meta-info">
                            <i class="bi <?php echo e($isGuru ? 'bi-people' : 'bi-person-badge'); ?>"></i>
                            <?php echo e($isGuru ? $j->kelas?->nama : $j->guru?->name); ?>

                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-x h1 text-muted"></i>
                <div class="fw-bold mt-2">Belum ada jadwal</div>
                <p class="small text-muted">Hubungi bagian kurikulum untuk informasi jadwal.</p>
            </div>
        <?php endif; ?>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\jadwal.blade.php ENDPATH**/ ?>