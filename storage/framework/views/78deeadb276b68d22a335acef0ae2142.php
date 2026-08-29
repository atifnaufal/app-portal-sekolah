<?php $hideNav = true; ?>


<?php $__env->startSection('content'); ?>
<?php
    $isGuru = $user->role === 'guru';
    $pendingTugas = $pendingTugas ?? collect();
    $completedTugas = $completedTugas ?? collect();
    $expiredTugas = $expiredTugas ?? collect();
    $siswaCounts = $siswaCounts ?? collect();

    if ($isGuru) {
        $statTotal = $tugas->count();
        $statPending = (int) $tugas->sum('pending_count');
        $statRevisi = (int) $tugas->sum('revisi_count');
        $statExpired = $tugas->filter(fn ($item) => $item->isExpired())->count();
    } else {
        $statTotal = $tugas->count();
        $statPending = $pendingTugas->count();
        $statDone = $completedTugas->count();
        $statExpired = $expiredTugas->count();
    }
?>

<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.88); backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 70px; padding-bottom: 48px; }
    .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .stat-chip {
        background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.16);
        border-radius: 16px; padding: 10px 8px; text-align: center;
    }
    .stat-chip .num { font-size: 18px; font-weight: 800; color: #fff; line-height: 1.1; }
    .stat-chip .lbl { font-size: 9px; font-weight: 700; letter-spacing: .04em; color: rgba(255,255,255,.72); margin-top: 4px; text-transform: uppercase; }
    .search-wrap {
        background: #fff; border-radius: 16px; display: flex; align-items: center; gap: 10px;
        padding: 10px 14px; box-shadow: 0 8px 24px rgba(20,33,61,.06);
    }
    .search-wrap input { border: 0; outline: none; width: 100%; font-size: 14px; background: transparent; }
    .filter-row { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none; }
    .filter-row::-webkit-scrollbar { display: none; }
    .filter-chip {
        border: 1px solid #e2e8f0; background: #fff; color: #64748b;
        border-radius: 999px; padding: 7px 14px; font-size: 12px; font-weight: 700;
        white-space: nowrap; cursor: pointer;
    }
    .filter-chip.active { background: #14213d; color: #fff; border-color: #14213d; }
    .ai-card {
        background: #fff; border: none; border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        position: relative; overflow: hidden;
    }
    .ai-card::before {
        content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%;
        background: var(--blue); opacity: 0.85;
    }
    .ai-card.urgent::before { background: var(--danger); }
    .ai-card.completed::before { background: #10b981; }
    .ai-card.pending::before { background: #f59e0b; }
    .ai-card.revise::before { background: #f59e0b; }
    .glass-pill {
        background: rgba(36, 107, 254, 0.06); color: var(--blue);
        border: 1px solid rgba(36, 107, 254, 0.12);
        padding: 4px 10px; border-radius: 100px; font-size: 10px; font-weight: 800; letter-spacing: .02em;
    }
    .revise-badge {
        background: #fef3c7; color: #b45309; border: 1px solid #fde68a;
        padding: 3px 10px; border-radius: 100px; font-size: 10px; font-weight: 800;
    }
    .progress-slim { height: 7px; border-radius: 99px; background: #eef2f7; overflow: hidden; }
    .progress-slim > span { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, #246bfe, #60a5fa); }
    .icon-action {
        width: 36px; height: 36px; border-radius: 12px; border: 1px solid #e2e8f0;
        background: #fff; display: inline-flex; align-items: center; justify-content: center;
        color: #475569; text-decoration: none;
    }
    .icon-action.danger { color: #d94b61; border-color: #f8d7de; background: #fff5f6; }
    .tugas-modal {
        position: fixed; inset: 0; z-index: 2000; display: none;
        align-items: flex-end; justify-content: center;
        background: rgba(15, 23, 42, .45);
    }
    .tugas-modal.open { display: flex; }
    .tugas-modal-card {
        width: 100%; max-width: 680px; background: #fff;
        border-radius: 28px 28px 0 0; padding: 24px 20px 32px;
    }
    .empty-box { text-align: center; padding: 48px 20px; background: #fff; border-radius: 24px; }
    @keyframes slideUp { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<div class="page-header">
    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px; letter-spacing: -0.4px;">Pusat Tugas</div>
    <?php if($isGuru): ?>
        <a href="<?php echo e(route('tugas.create')); ?>" class="btn btn-primary btn-sm rounded-pill px-3 ms-auto" style="font-weight: 700;">+ Buat</a>
        <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                <select id="rekapSemester" class="form-select form-select-sm rounded-pill text-dark fw-semibold" style="width:auto; background:#fff; border:none;">
                    <option value="1">Semester 1</option>
                    <option value="2">Semester 2</option>
                </select>
                <a href="#" id="btnRecapPdf" onclick="goRecapTugas('pdf'); return false;" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold shadow-sm">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
                </a>
                <a href="#" id="btnRecapExcel" onclick="goRecapTugas('excel'); return false;" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
                </a>
            </div>
            <script>
                function goRecapTugas(type) {
                    if (type === 'excel') {
                        var tugasId = prompt('Masukkan ID Tugas untuk export Excel:', '');
                        if (tugasId) {
                            var excelUrl = '<?php echo e(route('tugas.export.excel', ['tugas' => '__TID__'])); ?>'.replace('__TID__', tugasId);
                            window.open(excelUrl, '_blank');
                        }
                        return false;
                    }
                    if (window.tugasList && window.tugasList.length > 0) {
                        var pdfUrl = '<?php echo e(route('tugas.export.pdf', ['tugas' => '__TID__'])); ?>'.replace('__TID__', window.tugasList[0]);
                        window.open(pdfUrl, '_blank');
                    } else {
                        alert('Pilih tugas terlebih dahulu untuk export PDF.');
                    }
                }
            </script>
    <?php endif; ?>
</div>

<div class="page-container">
    <header class="mobile-hero" style="border-radius: 0 0 28px 28px; margin-bottom: 20px; background: linear-gradient(135deg, #0f172a, #1e293b); padding: 32px 24px 28px;">
        <div class="eyebrow" style="color: #94a3b8; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;">
            <?php echo e($user->kelas?->nama ?? ($isGuru ? 'Panel Pengajar' : 'Akademik')); ?>

        </div>
        <div class="hero-title mt-2 text-white" style="font-size: 24px; font-weight: 800; letter-spacing: -0.5px;"><?php echo e($isGuru ? 'Kelola Tugas Kelas' : 'Tugas Saya'); ?></div>
        <p class="mb-3 mt-1" style="font-size: 12px; color: rgba(255,255,255,.6); line-height: 1.5;">
            <?php echo e($isGuru ? 'Pantau pengumpulan, nilai, dan kelola instruksi tugas.' : 'Pantau deadline dan kirim jawaban tugas Anda tepat waktu.'); ?>

        </p>
        <div class="stat-grid" style="gap: 10px;">
            <?php if($isGuru): ?>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;"><?php echo e($statTotal); ?></div><div class="lbl">Total</div></div>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;"><?php echo e($statPending); ?></div><div class="lbl">Nilai</div></div>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;"><?php echo e($statRevisi); ?></div><div class="lbl">Revisi</div></div>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;"><?php echo e($statExpired); ?></div><div class="lbl">Lewat</div></div>
            <?php else: ?>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;"><?php echo e($statTotal); ?></div><div class="lbl">Aktif</div></div>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;"><?php echo e($statPending); ?></div><div class="lbl">Review</div></div>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;"><?php echo e($statDone); ?></div><div class="lbl">Skor</div></div>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;"><?php echo e($statExpired); ?></div><div class="lbl">Telat</div></div>
            <?php endif; ?>
        </div>
    </header>

    <main class="mobile-content px-3 pt-0">
        <div class="search-wrap mb-3">
            <i class="bi bi-search text-muted"></i>
            <input type="search" id="tugasSearch" placeholder="Cari judul atau kelas..." autocomplete="off">
        </div>

        <div class="filter-row mb-4" id="tugasFilters">
            <button type="button" class="filter-chip active" data-filter="all">Semua</button>
            <?php if($isGuru): ?>
                <button type="button" class="filter-chip" data-filter="running">Berjalan</button>
                <button type="button" class="filter-chip" data-filter="soon">Deadline dekat</button>
                <button type="button" class="filter-chip" data-filter="expired">Terlewat</button>
                <button type="button" class="filter-chip" data-filter="review">Perlu dinilai</button>
            <?php else: ?>
                <button type="button" class="filter-chip" data-filter="active">Aktif</button>
                <button type="button" class="filter-chip" data-filter="pending">Menunggu</button>
                <button type="button" class="filter-chip" data-filter="done">Selesai</button>
                <button type="button" class="filter-chip" data-filter="expired">Terlewat</button>
            <?php endif; ?>
        </div>

        <?php if($isGuru): ?>
            <div id="tugasList">
                <script>window.tugasList = <?php echo json_encode($tugas->pluck('id')->all()); ?>;</script>
                <?php $__empty_1 = true; $__currentLoopData = $tugas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $deadline = $item->deadlineStatus();
                        $totalSiswa = (int) ($siswaCounts[$item->kelas_id] ?? 0);
                        $submitted = (int) $item->pengumpulan_count;
                        $pct = $totalSiswa > 0 ? min(100, (int) round(($submitted / $totalSiswa) * 100)) : 0;
                        $filterKey = $item->isExpired() ? 'expired' : (($deadline['key'] === 'soon' || $deadline['key'] === 'today') ? 'soon' : 'running');
                        if ((int) $item->pending_count > 0) {
                            $filterKey .= ' review';
                        }
                        $toneClass = $item->isExpired() ? 'urgent' : ((int) $item->revisi_count > 0 ? 'revise' : '');
                    ?>
                    <article class="card ai-card mb-3 <?php echo e($toneClass); ?>" data-card data-filter="<?php echo e($filterKey); ?>" data-search="<?php echo e(strtolower($item->judul.' '.$item->kelas?->nama)); ?>" style="animation: slideUp 0.4s ease both;">
                        <div class="card-body p-4">
                            <a href="<?php echo e(route('tugas.show', $item)); ?>" class="text-decoration-none text-dark d-block">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="glass-pill">
                                            <i class="bi bi-journal-bookmark me-1"></i>
                                            <?php echo e($item->mataPelajaran?->nama ?? 'Umum'); ?>

                                        </div>
                                        <div class="glass-pill" style="color:#475569;background:#f8fafc;border-color:#e2e8f0;"><?php echo e($item->kelas?->nama); ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small fw-bold text-<?php echo e($deadline['tone'] === 'muted' ? 'muted' : $deadline['tone']); ?>" style="font-size: 10px;">DEADLINE</div>
                                        <div class="fw-bold" style="font-size: 12px;"><?php echo e($item->batas_pengumpulan?->format('d M Y') ?? 'Terbuka'); ?></div>
                                    </div>
                                </div>
                                <h3 class="h6 fw-bold mb-1" style="font-size: 16px; line-height: 1.4;"><?php echo e($item->judul); ?></h3>
                                <p class="small text-secondary mb-3" style="line-height: 1.55;"><?php echo e(\Illuminate\Support\Str::limit($item->deskripsi ?: 'Tidak ada deskripsi tambahan.', 90)); ?></p>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-bold text-<?php echo e($deadline['tone'] === 'muted' ? 'muted' : $deadline['tone']); ?>"><?php echo e($deadline['label']); ?></span>
                                    <span class="text-muted"><?php echo e($submitted); ?>/<?php echo e($totalSiswa); ?> mengumpulkan</span>
                                </div>
                                <div class="progress-slim mb-1"><span style="width: <?php echo e($pct); ?>%"></span></div>
                                <div class="d-flex gap-2 mt-2" style="font-size: 11px;">
                                    <span class="text-warning fw-bold"><?php echo e((int) $item->pending_count); ?> menunggu</span>
                                    <span class="text-success fw-bold"><?php echo e((int) $item->dinilai_count); ?> dinilai</span>
                                    <?php if((int) $item->revisi_count > 0): ?>
                                        <span class="text-danger fw-bold"><?php echo e((int) $item->revisi_count); ?> revisi</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="d-flex gap-2 mt-3 pt-3 border-top">
                                <a href="<?php echo e(route('tugas.show', $item)); ?>" class="btn btn-primary btn-sm rounded-pill px-3 flex-grow-1">Kelola</a>
                                <a href="<?php echo e(route('tugas.edit', $item)); ?>" class="icon-action" title="Edit tugas"><i class="bi bi-pencil-square"></i></a>
                                <button type="button" class="icon-action danger" title="Hapus tugas"
                                    onclick="openDeleteTugas(<?php echo json_encode(route('tugas.destroy', $item), 512) ?>, <?php echo json_encode($item->judul, 15, 512) ?>, <?php echo json_encode($submitted.' pengumpulan akan ikut terhapus', 15, 512) ?>)">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="empty-box">
                        <i class="bi bi-journal-plus h1 text-primary"></i>
                        <div class="fw-bold mt-2">Belum ada tugas</div>
                        <div class="small text-secondary mt-1 mb-3">Buat tugas pertama untuk kelas Anda.</div>
                        <a href="<?php echo e(route('tugas.create')); ?>" class="btn btn-primary rounded-pill px-4">+ Buat Tugas</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div id="tugasList">
                <?php $__currentLoopData = $tugas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $deadline = $item->deadlineStatus();
                        $needsRevision = $item->pengumpulan->first()?->revisi_aktif;
                    ?>
                    <a href="<?php echo e(route('tugas.show', $item)); ?>" class="card ai-card mb-3 text-decoration-none text-dark <?php echo e($needsRevision ? 'revise' : ''); ?>" data-card data-filter="active" data-search="<?php echo e(strtolower($item->judul.' '.$item->kelas?->nama)); ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="glass-pill">
                                        <i class="bi bi-journal-bookmark me-1"></i>
                                        <?php echo e($item->mataPelajaran?->nama ?? 'Umum'); ?>

                                    </div>
                                    <?php if($needsRevision): ?>
                                        <span class="revise-badge"><i class="bi bi-arrow-repeat me-1"></i>PERLU REVISI</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <div class="small fw-bold text-<?php echo e($deadline['tone'] === 'muted' ? 'muted' : $deadline['tone']); ?>" style="font-size: 10px;">DEADLINE</div>
                                    <div class="fw-bold" style="font-size: 13px;"><?php echo e($item->batas_pengumpulan?->format('d M') ?? 'Terbuka'); ?></div>
                                </div>
                            </div>
                            <h3 class="h6 fw-bold mb-2" style="font-size: 16px; line-height: 1.4;"><?php echo e($item->judul); ?></h3>
                            <p class="small text-secondary mb-3" style="line-height: 1.6;"><?php echo e(\Illuminate\Support\Str::limit($item->deskripsi ?: 'Buka modul untuk panduan lengkap.', 85)); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-bold text-<?php echo e($needsRevision ? 'warning' : 'primary'); ?>" style="font-size: 12px;">
                                    <?php echo e($needsRevision ? 'Perbaiki & Kirim Ulang' : $deadline['label']); ?>

                                </div>
                                <i class="bi bi-arrow-right-short h4 mb-0 text-muted"></i>
                            </div>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php $__currentLoopData = $pendingTugas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php ($submission = $item->pengumpulan->first()); ?>
                    <a href="<?php echo e(route('tugas.show', $item)); ?>" class="card ai-card pending mb-3 text-decoration-none" data-card data-filter="pending" data-search="<?php echo e(strtolower($item->judul)); ?>">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: #fffbeb; color: #f59e0b;">
                                    <i class="bi bi-hourglass-split h6 mb-0"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 14px;"><?php echo e($item->judul); ?></div>
                                    <div class="small text-muted" style="font-size: 11px;">Dikirim <?php echo e($submission->dikumpulkan_pada?->format('d M Y, H:i') ?? 'Baru saja'); ?></div>
                                </div>
                            </div>
                            <span class="badge rounded-pill" style="background: #fffbeb; color: #b45309; font-size: 10px; font-weight: 800;">MENUNGGU</span>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php $__currentLoopData = $completedTugas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php ($submission = $item->pengumpulan->first()); ?>
                    <?php ($gradeColor = $submission->nilai >= 85 ? '#16a34a' : ($submission->nilai >= 70 ? '#2563eb' : ($submission->nilai >= 55 ? '#d97706' : '#dc2626'))); ?>
                    <?php ($gradeBg = $submission->nilai >= 85 ? '#f0fdf4' : ($submission->nilai >= 70 ? '#eef4ff' : ($submission->nilai >= 55 ? '#fefce8' : '#fef2f2'))); ?>
                    <a href="<?php echo e(route('tugas.show', $item)); ?>" class="card ai-card completed mb-3 text-decoration-none" data-card data-filter="done" data-search="<?php echo e(strtolower($item->judul)); ?>" style="animation: slideUp 0.4s ease both;">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: <?php echo e($gradeBg); ?>; color: <?php echo e($gradeColor); ?>;">
                                    <i class="bi bi-check2-circle h5 mb-0"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 14px;"><?php echo e($item->judul); ?></div>
                                    <div class="small text-muted" style="font-size: 11px;">Dinilai <?php echo e($submission->dinilai_pada?->format('d M Y') ?? 'Baru saja'); ?></div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="h5 fw-bold mb-0" style="color: <?php echo e($gradeColor); ?>;"><?php echo e($submission->nilai); ?></div>
                                <div class="fw-bold" style="font-size: 9px; letter-spacing: 0.5px; color: <?php echo e($gradeColor); ?>; opacity: 0.7;">SKOR</div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php $__currentLoopData = $expiredTugas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('tugas.show', $item)); ?>" class="card ai-card urgent mb-3 text-decoration-none text-dark" data-card data-filter="expired" data-search="<?php echo e(strtolower($item->judul)); ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between">
                                <div class="fw-bold"><?php echo e($item->judul); ?></div>
                                <span class="badge text-bg-danger rounded-pill" style="font-size: 10px;">TERLEWAT</span>
                            </div>
                            <div class="small text-secondary mt-1">Batas <?php echo e($item->batas_pengumpulan?->format('d M Y')); ?> · Tugas tidak dapat dikumpulkan</div>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if($tugas->isEmpty() && $pendingTugas->isEmpty() && $completedTugas->isEmpty() && $expiredTugas->isEmpty()): ?>
                    <div class="empty-box">
                        <i class="bi bi-stars h1 text-primary"></i>
                        <div class="fw-bold mt-2">Semua tugas selesai!</div>
                        <div class="small mt-1 text-secondary">Belum ada tugas baru untukmu.</div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div id="emptyFilter" class="empty-box d-none">
            <div class="fw-bold">Tidak ada tugas pada filter ini</div>
            <div class="small text-secondary mt-1">Coba kata kunci atau filter lain.</div>
        </div>
    </main>
</div>

<div class="tugas-modal" id="deleteModal" onclick="if(event.target===this)closeDeleteTugas()">
    <div class="tugas-modal-card">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fff5f6;color:#d94b61;">
                <i class="bi bi-trash3 h4 mb-0"></i>
            </div>
            <div>
                <div class="fw-bold">Hapus tugas?</div>
                <div class="small text-secondary">Tindakan ini tidak dapat dibatalkan.</div>
            </div>
        </div>
        <div class="p-3 rounded-4 mb-3" style="background:#f8fafc;">
            <div class="fw-bold" id="deleteTitle">Tugas</div>
            <div class="small text-danger mt-1" id="deleteMeta"></div>
        </div>
        <form id="deleteForm" method="POST">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button class="btn btn-danger w-100 py-2 rounded-pill fw-bold mb-2">Hapus permanen</button>
            <button type="button" class="btn btn-light w-100 py-2 rounded-pill" onclick="closeDeleteTugas()">Batal</button>
        </form>
    </div>
</div>

<script>
    function openDeleteTugas(url, title, meta) {
        document.getElementById('deleteForm').action = url;
        document.getElementById('deleteTitle').innerText = title;
        document.getElementById('deleteMeta').innerText = meta;
        document.getElementById('deleteModal').classList.add('open');
    }
    function closeDeleteTugas() {
        document.getElementById('deleteModal').classList.remove('open');
    }

    (function () {
        const search = document.getElementById('tugasSearch');
        const chips = document.querySelectorAll('#tugasFilters .filter-chip');
        const cards = document.querySelectorAll('[data-card]');
        const empty = document.getElementById('emptyFilter');
        let filter = 'all';

        function apply() {
            const q = (search.value || '').toLowerCase().trim();
            let visible = 0;
            cards.forEach(card => {
                const hay = card.getAttribute('data-search') || '';
                const keys = (card.getAttribute('data-filter') || '').split(/\s+/);
                const okFilter = filter === 'all' || keys.includes(filter);
                const okSearch = !q || hay.includes(q);
                const show = okFilter && okSearch;
                card.classList.toggle('d-none', !show);
                if (show) visible++;
            });
            empty.classList.toggle('d-none', visible > 0 || cards.length === 0);
        }

        chips.forEach(chip => chip.addEventListener('click', () => {
            chips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            filter = chip.getAttribute('data-filter');
            apply();
        }));
        search.addEventListener('input', apply);
    })();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\tugas.blade.php ENDPATH**/ ?>