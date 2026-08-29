<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', 'Noto Sans', sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 16pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 11pt;
            color: #64748b;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 5px;
            text-align: left;
            font-size: 9pt;
        }
        th {
            background-color: #e2e8f0;
            font-weight: bold;
            color: #1e293b;
        }
        .status-terkirim { color: #059669; }
        .status-dinilai { color: #0891b2; }
        .status-perlu-revisi { color: #d97706; }
        .status-tidak-mengumpulkan { color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">REKAPITULASI PENUMPULAN TUGAS</div>
        <div class="subtitle"><?php echo e($tugas->judul); ?> | <?php echo e($tugas->mataPelajaran?->nama ?? 'Umum'); ?> | Kelas <?php echo e($tugas->kelas?->nama ?? '-'); ?></div>
    </div>

    <table>
        <thead>
        <tr>
            <th>No</th>
            <th>Nama Siswa</th>
            <th>Status</th>
            <th>Nilai</th>
            <th>Feedback Guru</th>
            <th>Dikumpulkan Pada</th>
        </tr>
        </thead>
        <tbody>
        <?php $rowNum = 1; ?>
        <?php foreach ($submissions as $sub): ?>
            <tr>
                <td><?php echo $rowNum; ?></td>
                <td><?php echo e($sub->siswa->name ?? '-'); ?></td>
                <td>
                    <?php
                    $status = match($sub->status) {
                        'terkirim' => 'Terkirim',
                        'dinilai' => 'Dinilai',
                        'perlu_revisi' => 'Perlu Revisi',
                        'tidak_mengumpulkan' => 'Tidak Mengumpulkan',
                        default => ucfirst(str_replace('_', ' ', $sub->status)),
                    };
                    $class = match($sub->status) {
                        'terkirim' => 'status-terkirim',
                        'dinilai' => 'status-dinilai',
                        'perlu_revisi' => 'status-perlu-revisi',
                        'tidak_mengumpulkan' => 'status-tidak-mengumpulkan',
                        default => '',
                    };
                    ?>
                    <span class="<?php echo $class; ?>"><?php echo $status; ?></span>
                </td>
                <td><?php echo $sub->nilai !== null ? $sub->nilai : '-'; ?></td>
                <td><?php echo $sub->feedback_guru ?: '-'; ?></td>
                <td><?php echo $sub->dikumpulkan_pada ? $sub->dikumpulkan_pada->format('d/m/Y H:i') : '-'; ?></td>
            </tr>
            <?php $rowNum++; ?>
        <?php endforeach; ?>

        <?php foreach ($belum as $siswa): ?>
            <tr>
                <td><?php echo $rowNum; ?></td>
                <td><?php echo e($siswa->name); ?></td>
                <td><span class="status-tidak-mengumpulkan">Belum Mengumpulkan</span></td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <?php $rowNum++; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right; font-size: 9pt; color: #64748b;">
        Dicetak pada <?php echo e($today); ?>

    </div>
</body>
</html><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\pdf\rekap-tugas.blade.php ENDPATH**/ ?>