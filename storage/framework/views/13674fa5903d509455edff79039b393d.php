<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { background: #246bfe; color: #fff; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
        .content { padding: 20px; }
        .footer { font-size: 12px; text-align: center; color: #777; margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background: #246bfe; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Tugas Baru Tersedia</h1>
        </div>
        <div class="content">
            <p>Halo,</p>
            <p>Guru Anda baru saja mengunggah tugas baru di portal akademik:</p>
            <h2 style="color: #246bfe;"><?php echo e($tugas->judul); ?></h2>
            <p><strong>Batas Pengumpulan:</strong> <?php echo e($tugas->batas_pengumpulan?->format('d F Y') ?? 'Tidak ada batas'); ?></p>
            <p><?php echo e(\Illuminate\Support\Str::limit($tugas->deskripsi, 150)); ?></p>
            <p>Silakan login ke aplikasi untuk melihat lampiran PDF dan instruksi lengkapnya.</p>
            <p style="text-align: center;">
                <a href="<?php echo e(route('tugas.show', $tugas->id)); ?>" class="btn">Lihat Detail Tugas</a>
            </p>
        </div>
        <div class="footer">
            <p>Pesan ini dikirim secara otomatis oleh sistem Portal Akademik Sekolah.</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\emails\tugas-baru.blade.php ENDPATH**/ ?>