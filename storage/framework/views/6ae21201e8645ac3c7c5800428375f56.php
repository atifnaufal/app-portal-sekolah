<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Nilai <?php echo e($mapel->nama); ?> - Semester <?php echo e($semester); ?></title>
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
        .meta-badge { display: inline-block; background: #2563eb; color: #fff; font-size: 10px; font-weight: bold; padding: 3px 10px; border-radius: 100px; }
        .info { margin-bottom: 12px; font-size: 11px; }
        .info table { width: 100%; border-collapse: collapse; }
        .info td { padding: 2px 0; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 6px 5px; text-align: center; }
        table.data thead th { background: #0f172a !important; color: #fff !important; font-size: 10px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        table.data tbody tr:nth-child(even) { background: #f8fafc; }
        .tleft { text-align: left !important; }
        .avg { background: #eff6ff !important; font-weight: bold; }
        .pred { font-weight: bold; }
        .footer { margin-top: 34px; display: flex; justify-content: space-between; font-size: 11px; }
        .sig { width: 220px; text-align: center; }
        .sig .nama { font-weight: bold; margin-top: 70px; border-top: 1px dotted #94a3b8; padding-top: 6px; }
        .keterangan { font-size: 9px; color: #64748b; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="kop">
        <h1><?php echo e(config('app.name', 'Portal Sekolah')); ?></h1>
        <div class="sub">Jl. Pendidikan No. 1, Indonesia</div>
        <div class="line">Email: info@sekolah.sch.id &bull; Telp: (021) 1234567</div>
    </div>

    <div class="judul">
        <h2>Rekapitulasi Nilai Mata Pelajaran</h2>
        <div class="meta"><span class="meta-badge"><?php echo e($mapel->nama); ?></span></div>
        <div class="meta">Kelas <?php echo e($kelas->nama ?? '-'); ?> &bull; Semester <?php echo e($semester); ?> &bull; Tahun Ajaran <?php echo e($tahunAjaran ?? '-'); ?></div>
        <div class="meta">Guru Pengampu: <?php echo e($mapel->guru->name ?? '-'); ?> &bull; KKM: <?php echo e($mapel->kkm ?? 75); ?></div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th class="tleft">Nama Siswa</th>
                <th>Tugas</th>
                <th>UTS</th>
                <th>UAS</th>
                <th>Rata-rata</th>
                <th>Predikat</th>
            </tr>
        </thead>
        <tbody>
            <?php $nomor = 1; ?>
            <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $n = $nilais[$s->id] ?? null;
                    $val = $n && ($n->tugas !== null || $n->uts !== null || $n->uas !== null)
                        ? round((($n->tugas ?? 0) + ($n->uts ?? 0) + ($n->uas ?? 0)) / 3, 2)
                        : null;
                    $predikat = $val !== null
                        ? match(true){ $val >= 90 => 'A', $val >= 80 => 'B', $val >= 70 => 'C', $val >= 60 => 'D', default => 'E' }
                        : '-';
                ?>
                <tr>
                    <td><?php echo e($nomor); ?></td>
                    <td class="tleft"><?php echo e($s->name); ?></td>
                    <td><?php echo e($n->tugas ?? '-'); ?></td>
                    <td><?php echo e($n->uts ?? '-'); ?></td>
                    <td><?php echo e($n->uas ?? '-'); ?></td>
                    <td class="avg"><?php echo e($val ?? '-'); ?></td>
                    <td class="pred"><?php echo e($predikat); ?></td>
                </tr>
                <?php $nomor++; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7">Belum ada data siswa.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="keterangan">
        Predikat: A = Sangat Baik (&ge;90), B = Baik (&ge;80), C = Cukup (&ge;70), D = Kurang (&ge;60), E = Sangat Kurang (&lt;60)
    </div>

    <div class="footer">
        <div>Dicetak pada: <?php echo e($today); ?></div>
        <div class="sig">
            <div>Mengetahui,<br>Guru Mata Pelajaran,</div>
            <div class="nama"><?php echo e($mapel->guru->name ?? '..........................'); ?></div>
            <div>NIP. ..........................</div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\pdf\rekap-nilai-mapel.blade.php ENDPATH**/ ?>