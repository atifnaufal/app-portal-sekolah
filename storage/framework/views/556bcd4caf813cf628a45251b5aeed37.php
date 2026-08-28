<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Nilai Periode</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica','Arial',sans-serif; font-size: 11px; color: #1e293b; margin: 24px; }
        .kop { text-align: center; border-bottom: 3px double #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
        .kop h1 { margin: 0; font-size: 18px; text-transform: uppercase; letter-spacing: 1px; color: #0f172a; }
        .kop .sub { font-size: 11px; color: #475569; margin-top: 4px; }
        .kop .line { font-size: 9px; color: #64748b; }
        .judul { text-align: center; margin: 16px 0 12px; }
        .judul h2 { margin: 0; font-size: 15px; text-transform: uppercase; letter-spacing: 1px; }
        .judul .meta { font-size: 11px; color: #475569; margin-top: 4px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 6px 5px; text-align: center; }
        table.data thead th { background: #0f172a !important; color: #fff !important; font-size: 10px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        table.data tbody tr:nth-child(even) { background: #f8fafc; }
        .tleft { text-align: left !important; }
        .footer { margin-top: 34px; display: flex; justify-content: space-between; font-size: 11px; }
        .sig { width: 220px; text-align: center; }
        .sig .nama { font-weight: bold; margin-top: 70px; border-top: 1px dotted #94a3b8; padding-top: 6px; }
        .keterangan { font-size: 9px; color: #64748b; margin-top: 8px; }
        .predikat { font-weight: bold; }
    </style>
</head>
<body>
    <div class="kop">
        <h1><?php echo e(config('app.name', 'Portal Sekolah')); ?></h1>
        <div class="sub">Jl. Pendidikan No. 1, Indonesia</div>
        <div class="line">Email: info@sekolah.sch.id &bull; Telp: (021) 1234567</div>
    </div>

    <div class="judul">
        <h2>Rekapitulasi Nilai Siswa
            <?php if($periode === 'bulanan'): ?>
                (Bulanan)
            <?php else: ?>
                (Tahunan)
            <?php endif; ?>
        </h2>
        <div class="meta">
            <?php if($periode === 'bulanan'): ?>
                <?php echo e(\Carbon\Carbon::create()->month($bulan)->translatedFormat('F')); ?> <?php echo e($tahun); ?>

            <?php else: ?>
                Tahun Ajaran <?php echo e($tahunAjaran); ?>

            <?php endif; ?>
        </div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Nama Siswa</th>
                <th colspan="<?php echo e($mapels->count()); ?>">Mata Pelajaran</th>
                <th rowspan="2">Rata-rata</th>
                <th rowspan="2">Predikat</th>
            </tr>
            <tr>
                <?php $__currentLoopData = $mapels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th><?php echo e($mp->nama); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        </thead>
        <tbody>
            <?php $nomor = 1; ?>
            <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $total = 0; $count = 0; ?>
                <tr>
                    <td><?php echo e($nomor); ?></td>
                    <td class="tleft"><?php echo e($s->name); ?></td>
                    <?php $__currentLoopData = $mapels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $n = $nilais[$s->id][$mp->id] ?? null;
                            $val = $n && ($n->tugas !== null || $n->uts !== null || $n->uas !== null)
                                ? round((($n->tugas ?? 0) + ($n->uts ?? 0) + ($n->uas ?? 0)) / 3, 2)
                                : null;
                            if ($val !== null) { $total += $val; $count++; }
                        ?>
                        <td><?php echo e($val ?? '-'); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $avg = $count > 0 ? round($total / $count, 2) : null;
                        $pred = $avg !== null
                            ? match(true){ $avg >= 90 => 'A', $avg >= 80 => 'B', $avg >= 70 => 'C', $avg >= 60 => 'D', default => 'E' }
                            : '-';
                    ?>
                    <td class="predikat"><?php echo e($avg ?? '-'); ?></td>
                    <td><?php echo e($pred); ?></td>
                </tr>
                <?php $nomor++; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="<?php echo e($mapels->count() + 4); ?>">Belum ada data nilai pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="keterangan">
        Predikat: A = Sangat Baik (&ge;90), B = Baik (&ge;80), C = Cukup (&ge;70), D = Kurang (&ge;60), E = Sangat Kurang (&lt;60)
    </div>

    <div class="footer">
        <div>Dicetak pada: <?php echo e($today); ?></div>
        <div class="sig">
            <div>Mengetahui,<br>Kepala Sekolah,</div>
            <div class="nama">..........................</div>
            <div>NIP. ..........................</div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views/pdf/rekap-nilai-periode.blade.php ENDPATH**/ ?>