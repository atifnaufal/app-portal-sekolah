<?php $__env->startSection('content'); ?>
<?php
    $isGuru = $user->role === 'guru';
    $spp = $sppStats ?? null;
    $hadir = (int) ($absensiBulan['hadir'] ?? $absensiBulan['Hadir'] ?? 0);
    $izin = (int) ($absensiBulan['izin'] ?? $absensiBulan['Izin'] ?? 0);
    $sakit = (int) ($absensiBulan['sakit'] ?? $absensiBulan['Sakit'] ?? 0);
    $alpha = (int) ($absensiBulan['alpha'] ?? $absensiBulan['Alpha'] ?? 0);
    $totalAbsen = $hadir + $izin + $sakit + $alpha;
    $pctHadir = $totalAbsen > 0 ? round(($hadir / $totalAbsen) * 100) : 0;
?>

<style>
    .db-body { padding: 0 16px 120px; max-width: 640px; margin: 0 auto; }

    /* Hero Card - Dark premium design */
    .db-hero-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 30px;
        padding: 26px;
        margin-bottom: 20px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(15, 23, 42, 0.2);
    }
    .db-hero-card::before {
        content: ''; position: absolute; top: -50%; right: -30%;
        width: 200px; height: 200px; border-radius: 50%;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.3) 0%, transparent 70%);
    }
    .db-hero-card::after {
        content: ''; position: absolute; bottom: -40%; left: -20%;
        width: 180px; height: 180px; border-radius: 50%;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, transparent 70%);
    }

    .db-hero-greeting { font-size: 13px; opacity: 0.6; font-weight: 600; letter-spacing: 0.05em; }
    .db-hero-name { font-size: 24px; font-weight: 800; letter-spacing: -0.02em; margin-top: 2px; }
    .db-hero-class {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
        border-radius: 12px; padding: 6px 14px; font-size: 12px; font-weight: 600;
        margin-top: 12px; backdrop-filter: blur(8px);
    }

    .db-hero-avatar {
        width: 50px; height: 50px; border-radius: 18px; overflow: hidden;
        background: transparent;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; font-weight: 800; flex-shrink: 0;
        filter: drop-shadow(0 3px 8px rgba(0,0,0,0.2));
    }
    .db-hero-avatar img { width: 100%; height: 100%; object-fit: cover; }

    /* LMS Mapel Grid */
    .db-mapel-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .db-mapel-card {
        background: #fff; border-radius: 24px; padding: 16px;
        text-decoration: none; border: 1px solid rgba(15,23,42,0.04);
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: all 0.3s;
    }
    .db-mapel-card:active { transform: scale(0.96); }
    .db-mapel-icon {
        width: 44px; height: 44px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; margin-bottom: 12px;
    }
    .db-mapel-name { font-size: 14px; font-weight: 800; color: #1e293b; line-height: 1.3; margin-bottom: 4px; }
    .db-mapel-meta { font-size: 10px; color: #94a3b8; font-weight: 600; }

    /* Premium Stat "Glass Chip" - compact blended tinted tiles */
    .db-stat-row { display: flex; gap: 8px; margin-bottom: 16px; }
    .db-stat-card {
        flex: 1; border-radius: 18px; padding: 13px 10px 12px;
        text-align: center; position: relative; overflow: hidden;
        background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(255,255,255,0.55));
        border: 1px solid rgba(15,23,42,0.05);
        box-shadow: 0 6px 18px rgba(15,23,42,0.05);
        backdrop-filter: blur(10px);
        -webkit-tap-highlight-color: transparent;
        transition: transform 0.13s;
    }
    .db-stat-card:active { transform: scale(0.95); }
    .db-stat-card::before {
        content: ''; position: absolute; top: -16px; right: -16px;
        width: 56px; height: 56px; border-radius: 50%;
        background: var(--stat-glow, rgba(99,102,241,0.12)); filter: blur(2px);
    }
    .db-stat-icon {
        width: 34px; height: 34px; border-radius: 11px; margin: 0 auto 8px;
        display: flex; align-items: center; justify-content: center; font-size: 15px;
        box-shadow: 0 3px 8px rgba(15,23,42,0.06);
    }
    .db-stat-num { font-size: 21px; font-weight: 800; line-height: 1.05; letter-spacing: -0.02em; }
    .db-stat-lbl { font-size: 9.5px; font-weight: 700; letter-spacing: 0.03em; margin-top: 4px; opacity: 0.6; }

    /* Section Card */
    .db-section {
        background: #fff; border-radius: 22px; padding: 18px;
        margin-bottom: 14px; box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .db-section-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 14px;
    }
    .db-section-title { font-size: 15px; font-weight: 800; color: #1e293b; }
    .db-section-link { font-size: 12px; font-weight: 700; color: #6366f1; text-decoration: none; }

    /* Quick Menu Grid */
    .db-menu-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
    .db-menu-item {
        display: flex; flex-direction: column; align-items: center;
        padding: 14px 2px; border-radius: 22px; text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #fff;
        border: 1px solid rgba(15,23,42,0.03);
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .db-menu-item:active { transform: scale(0.92); background: #f8fafc; }
    .db-menu-icon {
        width: 50px; height: 50px; border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; margin-bottom: 8px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }
    .db-menu-item:hover .db-menu-icon { transform: translateY(-2px); }
    .db-menu-label { font-size: 11px; font-weight: 700; color: #475569; letter-spacing: -0.2px; }

    /* List Items */
    .db-list-item {
        display: flex; align-items: center; gap: 12px; padding: 10px 0;
        text-decoration: none; color: #1e293b;
    }
    .db-list-item + .db-list-item { border-top: 1px solid #f1f5f9; }
    .db-list-icon {
        width: 42px; height: 42px; border-radius: 14px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .db-list-text { flex: 1; min-width: 0; }
    .db-list-title { font-size: 13px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .db-list-sub { font-size: 11px; color: #94a3b8; }

    /* Attendance Bar */
    .db-absen-bar { height: 10px; border-radius: 99px; background: #f1f5f9; overflow: hidden; display: flex; margin-bottom: 10px; }
    .db-absen-legend { display: flex; gap: 12px; flex-wrap: wrap; }
    .db-absen-legend-item { display: flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; color: #64748b; }
    .db-absen-dot { width: 8px; height: 8px; border-radius: 50%; }

    /* Alert Card */
    .db-alert {
        border-radius: 18px; padding: 14px 16px; margin-bottom: 14px;
        display: flex; align-items: center; gap: 12px;
    }
    .db-alert-icon {
        width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 18px;
    }

    @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .fade-up { animation: fadeUp 0.4s ease both; }
</style>

<div class="db-body" style="padding-top: 12px;">
    
    <div class="db-hero-card fade-up">
        <div style="display:flex;align-items:center;justify-content:space-between;position:relative;z-index:1;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="db-hero-avatar">
                    <?php if($user->foto): ?>
                        <img src="<?php echo e(asset('storage/'.$user->foto)); ?>">
                    <?php else: ?>
                        <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                    <?php endif; ?>
                </div>
                <div>
                    <div class="db-hero-greeting">PORTAL AKADEMIK</div>
                    <div class="db-hero-name">Halo, <?php echo e(explode(' ', $user->name)[0]); ?>!</div>
                </div>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="<?php echo e(route('notifications.index')); ?>" style="width:42px;height:42px;border-radius:14px;background:rgba(255,255,255,0.1);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;position:relative;border:1px solid rgba(255,255,255,0.1);">
                    <i class="bi bi-bell-fill" style="font-size:17px;"></i>
                    <span data-live-dot id="notif-dot" style="display:none; position:absolute; top:8px; right:8px; min-width:18px; height:18px; padding:0 5px; border-radius:9px; background:#ef4444; border:2px solid #1e1b4b; color:#fff; font-size:10px; font-weight:800; place-items:center; line-height:1;">0</span>
                </a>
            </div>
        </div>
        <?php if($user->kelas): ?>
            <div class="db-hero-class" style="position:relative;z-index:1;">
                <i class="bi bi-mortarboard-fill" style="color:#fbbf24;font-size:14px;"></i>
                <?php echo e($user->kelas->nama); ?>

            </div>
        <?php endif; ?>
    </div>

    
    <div class="db-stat-row fade-up" style="animation-delay:0.05s;">
        <a href="<?php echo e(route('tugas.index')); ?>" class="db-stat-card text-decoration-none" style="--stat-glow:rgba(37,99,235,0.14);">
            <div class="db-stat-icon" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);color:#2563eb;"><i class="bi bi-journal-check"></i></div>
            <div class="db-stat-num" style="color:#1e40af;"><?php echo e($tugasAktif); ?></div>
            <div class="db-stat-lbl" style="color:#1e40af;">Tugas Aktif</div>
        </a>
        <?php if($spp): ?>
            <a href="<?php echo e(route('spp.index')); ?>" class="db-stat-card text-decoration-none" style="--stat-glow:rgba(16,185,129,0.14);">
                <div class="db-stat-icon" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);color:#059669;"><i class="bi bi-wallet2"></i></div>
                <div class="db-stat-num" style="color:#065f46;"><?php echo e($spp['lunas']); ?><span style="font-size:12px;opacity:0.5;">/<?php echo e($spp['total']); ?></span></div>
                <div class="db-stat-lbl" style="color:#065f46;">SPP Lunas</div>
            </a>
        <?php endif; ?>
        <div class="db-stat-card" style="--stat-glow:rgba(245,158,11,0.15);">
            <div class="db-stat-icon" style="background:linear-gradient(135deg,#fffbeb,#fef3c7);color:#d97706;"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="db-stat-num" style="color:#92400e;"><?php echo e($pctHadir); ?><span style="font-size:12px;">%</span></div>
            <div class="db-stat-lbl" style="color:#92400e;">Hadir</div>
        </div>
    </div>

    
    <div class="db-section fade-up" style="animation-delay:0.1s;">
        <div class="db-section-header">
            <div class="db-section-title">Menu Cepat</div>
        </div>
        <div class="db-menu-grid">
            <a href="<?php echo e(route('absensi.index')); ?>" class="db-menu-item">
                <div class="db-menu-icon" style="background:linear-gradient(135deg,#4f7cff,#2563eb);color:#fff;"><i class="bi bi-calendar-check-fill"></i></div>
                <div class="db-menu-label">Absensi</div>
            </a>
            <a href="<?php echo e(route('tugas.index')); ?>" class="db-menu-item">
                <div class="db-menu-icon" style="background:linear-gradient(135deg,#fbbf24,#f59e0b);color:#fff;"><i class="bi bi-journal-check"></i></div>
                <div class="db-menu-label">Tugas</div>
            </a>
            <a href="<?php echo e(route('spp.index')); ?>" class="db-menu-item">
                <div class="db-menu-icon" style="background:linear-gradient(135deg,#34d399,#10b981);color:#fff;"><i class="bi bi-wallet2"></i></div>
                <div class="db-menu-label">SPP</div>
            </a>
            <a href="<?php echo e(route('chat.index')); ?>" class="db-menu-item">
                <div class="db-menu-icon" style="background:linear-gradient(135deg,#a78bfa,#7c3aed);color:#fff;"><i class="bi bi-chat-dots-fill"></i></div>
                <div class="db-menu-label">Chat</div>
            </a>
            <a href="<?php echo e(route('perpustakaan.index')); ?>" class="db-menu-item">
                <div class="db-menu-icon" style="background:linear-gradient(135deg,#60a5fa,#2563eb);color:#fff;"><i class="bi bi-book-half"></i></div>
                <div class="db-menu-label">Perpus</div>
            </a>
            <a href="<?php echo e(route('jadwal.index')); ?>" class="db-menu-item">
                <div class="db-menu-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;"><i class="bi bi-calendar3"></i></div>
                <div class="db-menu-label">Jadwal</div>
            </a>
            <a href="<?php echo e(route('nilai.index')); ?>" class="db-menu-item">
                <div class="db-menu-icon" style="background:linear-gradient(135deg,#f472b6,#db2777);color:#fff;"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="db-menu-label">Nilai</div>
            </a>
            <a href="<?php echo e(route('eskul.index')); ?>" class="db-menu-item">
                <div class="db-menu-icon" style="background:linear-gradient(135deg,#ec4899,#be185d);color:#fff;"><i class="bi bi-flag-fill"></i></div>
                <div class="db-menu-label">Eskul</div>
            </a>
            <!-- <a href="<?php echo e(route('pengumuman.index')); ?>" class="db-menu-item">
                <div class="db-menu-icon" style="background:linear-gradient(135deg,#fee2e2,#fecaca);color:#dc2626;"><i class="bi bi-megaphone-fill"></i></div>
                <div class="db-menu-label">Info</div>
            </a>

            <?php if($isGuru): ?>
               <a href="<?php echo e(route('mahasiswa.index')); ?>" class="db-menu-item">
                <div class="db-menu-icon" style="background:linear-gradient(135deg,#cffafe,#a5f3fc);color:#0891b2;"><i class="bi bi-people-fill"></i></div>
                <div class="db-menu-label">Siswa</div>
                </a>
                <a href="<?php echo e(route('tugas.create')); ?>" class="db-menu-item">
                    <div class="db-menu-icon" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#059669;"><i class="bi bi-plus-circle-fill"></i></div>
                    <div class="db-menu-label">Buat Tugas</div>
                </a>
            <?php endif; ?>
            <a href="<?php echo e(route('profile.show')); ?>" class="db-menu-item">
                <div class="db-menu-icon" style="background:linear-gradient(135deg,#f1f5f9,#e2e8f0);color:#64748b;"><i class="bi bi-gear-fill"></i></div>
                <div class="db-menu-label">Profil</div>
            </a> -->

        </div>
    </div>

    
    <?php if($totalAbsen > 0): ?>
        <div class="db-section fade-up" style="animation-delay:0.15s;">
            <div class="db-section-header">
                <div class="db-section-title">Absensi Bulan Ini</div>
                <span style="font-size:11px;color:#94a3b8;font-weight:600;"><?php echo e($totalAbsen); ?> hari</span>
            </div>
            <div class="db-absen-bar">
                <span style="width:<?php echo e(($hadir/$totalAbsen)*100); ?>%;background:linear-gradient(90deg,#16a34a,#4ade80);"></span>
                <span style="width:<?php echo e(($sakit/$totalAbsen)*100); ?>%;background:linear-gradient(90deg,#f59e0b,#fbbf24);"></span>
                <span style="width:<?php echo e(($izin/$totalAbsen)*100); ?>%;background:linear-gradient(90deg,#3b82f6,#60a5fa);"></span>
                <span style="width:<?php echo e(($alpha/$totalAbsen)*100); ?>%;background:linear-gradient(90deg,#ef4444,#f87171);"></span>
            </div>
            <div class="db-absen-legend">
                <div class="db-absen-legend-item"><span class="db-absen-dot" style="background:#16a34a;"></span> Hadir <?php echo e($hadir); ?></div>
                <div class="db-absen-legend-item"><span class="db-absen-dot" style="background:#f59e0b;"></span> Sakit <?php echo e($sakit); ?></div>
                <div class="db-absen-legend-item"><span class="db-absen-dot" style="background:#3b82f6;"></span> Izin <?php echo e($izin); ?></div>
                <div class="db-absen-legend-item"><span class="db-absen-dot" style="background:#ef4444;"></span> Alpha <?php echo e($alpha); ?></div>
            </div>
        </div>
    <?php endif; ?>


    
    <div class="db-section fade-up" style="animation-delay:0.08s;">
        <div class="db-section-header">
            <div class="db-section-title"><?php echo e($isGuru ? 'Mata Pelajaran Diampu' : 'Mata Pelajaran Saya'); ?></div>
        </div>
        <div class="db-mapel-grid">
            <?php $__empty_1 = true; $__currentLoopData = $mapels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('mapel.show', $m->id)); ?>" class="db-mapel-card">
                    <?php
                        $colors = [
                            ['#eff6ff', '#2563eb'], ['#f0fdf4', '#16a34a'],
                            ['#fefce8', '#ca8a04'], ['#fef2f2', '#dc2626'],
                            ['#f5f3ff', '#7c3aed'], ['#fff1f2', '#db2777']
                        ];
                        $c = $colors[$loop->index % count($colors)];
                    ?>
                    <div class="db-mapel-icon" style="background:<?php echo e($c[0]); ?>; color:<?php echo e($c[1]); ?>;">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <div class="db-mapel-name"><?php echo e($m->nama); ?></div>
                    <div class="db-mapel-meta">
                        <?php if($isGuru): ?>
                            <i class="bi bi-people-fill"></i> <?php echo e($m->kelas->nama); ?>

                        <?php else: ?>
                            <i class="bi bi-person-badge-fill"></i> <?php echo e(explode(' ', $m->guru->name)[0]); ?>

                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-3 w-100" style="grid-column: span 2;">
                    <p class="small text-muted">Belum ada mata pelajaran.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="db-section fade-up" style="animation-delay:0.2s;">
        <div class="db-section-header">
            <div class="db-section-title">Tugas Terbaru</div>
            <a href="<?php echo e(route('tugas.index')); ?>" class="db-section-link">Lihat Semua</a>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $tugas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $dl = $t->deadlineStatus();
                $bgColors = ['ok' => '#eff6ff', 'soon' => '#fef3c7', 'today' => '#fee2e2', 'expired' => '#f1f5f9', 'open' => '#ecfdf5'];
                $txColors = ['ok' => '#2563eb', 'soon' => '#d97706', 'today' => '#dc2626', 'expired' => '#94a3b8', 'open' => '#059669'];
                $bg = $bgColors[$dl['key']] ?? '#f8fafc';
                $tx = $txColors[$dl['key']] ?? '#64748b';
            ?>
            <a href="<?php echo e(route('tugas.show', $t)); ?>" class="db-list-item">
                <div class="db-list-icon" style="background:<?php echo e($bg); ?>;color:<?php echo e($tx); ?>;">
                    <i class="bi <?php echo e($t->isForm() ? 'bi-ui-checks-grid' : 'bi-file-earmark-text-fill'); ?>"></i>
                </div>
                <div class="db-list-text">
                    <div class="db-list-title"><?php echo e($t->judul); ?></div>
                    <div class="db-list-sub"><?php echo e($dl['label']); ?></div>
                </div>
                <i class="bi bi-chevron-right" style="font-size:12px;color:#cbd5e1;"></i>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div style="text-align:center;padding:20px;color:#94a3b8;font-size:13px;">Belum ada tugas</div>
        <?php endif; ?>
    </div>

    
    <div class="db-section fade-up" style="animation-delay:0.25s;">
        <div class="db-section-header">
            <div class="db-section-title">Pengumuman</div>
            <a href="<?php echo e(route('pengumuman.index')); ?>" class="db-section-link">Lihat Semua</a>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $publicPengumumans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('pengumuman.index')); ?>" class="db-list-item">
                <div class="db-list-icon" style="background:linear-gradient(135deg,#fef3c7,#fde68a);color:#d97706;">
                    <i class="bi bi-megaphone-fill"></i>
                </div>
                <div class="db-list-text">
                    <div class="db-list-title"><?php echo e($p->judul); ?></div>
                    <div class="db-list-sub"><?php echo e(\Illuminate\Support\Str::limit(strip_tags($p->isi), 50)); ?></div>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div style="text-align:center;padding:20px;color:#94a3b8;font-size:13px;">Belum ada pengumuman</div>
        <?php endif; ?>
    </div>

    
    <?php if($spp && $spp['kekurangan'] > 0): ?>
        <div class="db-alert fade-up" style="animation-delay:0.3s;background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1px solid #fde68a;">
            <div class="db-alert-icon" style="background:#fef3c7;color:#d97706;">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div style="flex:1;">
                <div style="font-size:11px;font-weight:700;color:#92400e;">Tunggakan SPP</div>
                <div style="font-size:17px;font-weight:800;color:#b45309;">Rp <?php echo e(number_format($spp['kekurangan'], 0, ',', '.')); ?></div>
            </div>
            <a href="<?php echo e(route('spp.index')); ?>" style="font-size:11px;font-weight:700;color:#d97706;text-decoration:none;">Detail →</a>
        </div>
    <?php endif; ?>
</div>

<script>
    var unreadCount = <?php echo e($unreadNotificationsCount); ?>;
    var lastSpoken = localStorage.getItem('last_notif_spoken');
    if (unreadCount > 0 && unreadCount > (parseInt(lastSpoken) || 0)) {
        try { var msg = new SpeechSynthesisUtterance(); msg.text = "Ada notifikasi untukmu"; msg.lang = 'id-ID'; msg.rate = 1.0; window.speechSynthesis.speak(msg); } catch(e) {}
    }
    localStorage.setItem('last_notif_spoken', unreadCount || 0);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\dashboard.blade.php ENDPATH**/ ?>