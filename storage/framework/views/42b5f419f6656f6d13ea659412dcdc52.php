<?php $hideNav = true; ?>


<?php $__env->startSection('content'); ?>
<?php
    $isEdit = isset($spp) && $spp->exists;
    $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
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

    .month-grid {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;
    }
    .month-btn {
        border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 12px;
        padding: 10px 4px; text-align: center; cursor: pointer; transition: all 0.2s;
        font-size: 11px; font-weight: 700; color: #64748b;
    }
    .month-btn:hover { border-color: #246bfe; color: #246bfe; }
    .month-btn.selected { background: #246bfe; color: #fff; border-color: #246bfe; }

    .currency-input {
        position: relative;
    }
    .currency-input::before {
        content: 'Rp'; position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        font-size: 13px; font-weight: 700; color: #64748b; z-index: 1;
    }
    .currency-input input {
        padding-left: 40px !important;
    }

    .submit-area {
        position: sticky; bottom: 0; left: 0; right: 0;
        background: rgba(255,255,255,0.92); backdrop-filter: blur(16px);
        border-top: 1px solid #edf2f7; padding: 16px 20px;
        z-index: 100;
    }

    @keyframes slideUp { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .slide-up { animation: slideUp 0.4s ease both; }
</style>

<div class="page-header">
    <a href="<?php echo e(route('spp.index')); ?>" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px;"><?php echo e($isEdit ? 'Edit SPP' : 'Catat SPP Baru'); ?></div>
</div>

<div class="page-container px-3 pt-3">
    <form method="POST" action="<?php echo e($isEdit ? route('spp.update', $spp) : route('spp.store')); ?>" id="sppForm">
        <?php echo csrf_field(); ?>
        <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

        
        <div class="glass-card slide-up">
            <div class="p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div style="width:28px;height:28px;border-radius:10px;background:#eef4ff;color:#246bfe;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-person" style="font-size:14px;"></i>
                    </div>
                    <span class="fw-bold" style="font-size:14px;">Data Siswa</span>
                </div>
                <select name="siswa_id" class="form-select border-0 shadow-sm" style="border-radius:14px;background:#f8fafc;font-size:14px;" required>
                    <option value="">Pilih siswa</option>
                    <?php $__currentLoopData = $siswas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $siswa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($siswa->id); ?>" <?php if(old('siswa_id', $spp->siswa_id ?? '') == $siswa->id): echo 'selected'; endif; ?>>
                            <?php echo e($siswa->kelas?->nama ? $siswa->kelas->nama . ' - ' : ''); ?><?php echo e($siswa->name); ?>

                            <?php if($siswa->nik): ?> (<?php echo e($siswa->nik); ?>) <?php endif; ?>
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        
        <div class="glass-card slide-up" style="animation-delay: 0.1s;">
            <div class="p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div style="width:28px;height:28px;border-radius:10px;background:#ede9fe;color:#7c3aed;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-calendar3" style="font-size:14px;"></i>
                    </div>
                    <span class="fw-bold" style="font-size:14px;">Periode Pembayaran</span>
                </div>

                <label class="x-small fw-bold text-muted mb-2 d-block">BULAN</label>
                <input type="hidden" name="bulan" id="bulanInput" value="<?php echo e(old('bulan', $spp->bulan ?? '')); ?>">
                <div class="month-grid mb-3" id="monthGrid">
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <div class="month-btn <?php echo e(old('bulan', $spp->bulan ?? '') == $m ? 'selected' : ''); ?>" data-month="<?php echo e($m); ?>" onclick="selectMonth(<?php echo e($m); ?>)">
                            <?php echo e(substr($namaBulan[$m], 0, 3)); ?>

                        </div>
                    <?php endfor; ?>
                </div>

                <label class="x-small fw-bold text-muted mb-1 d-block">TAHUN</label>
                <input name="tahun" type="number" min="2020" max="2050" value="<?php echo e(old('tahun', $spp->tahun ?? date('Y'))); ?>" class="form-control border-0 shadow-sm" style="border-radius:14px;background:#f8fafc;font-size:14px;" required>
            </div>
        </div>

        
        <div class="glass-card slide-up" style="animation-delay: 0.2s;">
            <div class="p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div style="width:28px;height:28px;border-radius:10px;background:#f0fdf4;color:#16a34a;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-cash-stack" style="font-size:14px;"></i>
                    </div>
                    <span class="fw-bold" style="font-size:14px;">Jumlah Pembayaran</span>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <label class="x-small fw-bold text-muted mb-1">TAGIHAN</label>
                        <div class="currency-input">
                            <input name="nominal" type="number" min="0" id="nominalInput" value="<?php echo e(old('nominal', $spp->nominal ?? '')); ?>" class="form-control border-0 shadow-sm" style="border-radius:14px;background:#f8fafc;font-size:14px;" required oninput="updatePreview()">
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="x-small fw-bold text-muted mb-1">SUDAH DIBAYAR</label>
                        <div class="currency-input">
                            <input name="dibayar" type="number" min="0" id="dibayarInput" value="<?php echo e(old('dibayar', $spp->dibayar ?? 0)); ?>" class="form-control border-0 shadow-sm" style="border-radius:14px;background:#f8fafc;font-size:14px;" oninput="updatePreview()">
                        </div>
                    </div>
                </div>

                <div id="paymentPreview" class="mt-3 p-3 rounded-4" style="background:#f8fafc;display:none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Status</span>
                        <span id="statusPreview" class="fw-bold" style="font-size:13px;"></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="small text-muted">Kekurangan</span>
                        <span id="kekuranganPreview" class="fw-bold" style="font-size:13px;color:#dc2626;"></span>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="x-small fw-bold text-muted mb-1">JATUH TEMPO (OPSIONAL)</label>
                    <input name="jatuh_tempo" type="date" value="<?php echo e(old('jatuh_tempo', $isEdit && $spp->jatuh_tempo ? $spp->jatuh_tempo->format('Y-m-d') : '')); ?>" class="form-control border-0 shadow-sm" style="border-radius:14px;background:#f8fafc;font-size:14px;">
                </div>
            </div>
        </div>

        <div style="height: 80px;"></div>
    </form>
</div>

<div class="submit-area">
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('spp.index')); ?>" class="btn btn-light rounded-pill px-4 fw-bold">Batal</a>
        <button type="submit" form="sppForm" class="btn btn-primary rounded-pill flex-grow-1 py-2 fw-bold" style="font-size:15px;">
            <i class="bi bi-check2-circle me-1"></i> <?php echo e($isEdit ? 'Simpan Perubahan' : 'Simpan SPP'); ?>

        </button>
    </div>
</div>

<script>
    function selectMonth(m) {
        document.getElementById('bulanInput').value = m;
        document.querySelectorAll('.month-btn').forEach(btn => {
            btn.classList.toggle('selected', parseInt(btn.dataset.month) === m);
        });
    }

    function updatePreview() {
        const nominal = parseFloat(document.getElementById('nominalInput').value) || 0;
        const dibayar = parseFloat(document.getElementById('dibayarInput').value) || 0;
        const preview = document.getElementById('paymentPreview');
        const status = document.getElementById('statusPreview');
        const kekurangan = document.getElementById('kekuranganPreview');

        if (nominal > 0) {
            preview.style.display = 'block';
            const sisa = Math.max(0, nominal - dibayar);
            if (sisa <= 0) {
                status.textContent = 'Lunas';
                status.style.color = '#16a34a';
                kekurangan.textContent = 'Rp 0';
                kekurangan.style.color = '#16a34a';
            } else {
                status.textContent = 'Belum Lunas';
                status.style.color = '#b45309';
                kekurangan.textContent = 'Rp ' + sisa.toLocaleString('id-ID');
                kekurangan.style.color = '#dc2626';
            }
        } else {
            preview.style.display = 'none';
        }
    }

    document.getElementById('sppForm').addEventListener('submit', function(e) {
        if (!document.getElementById('bulanInput').value) {
            e.preventDefault();
            alert('Pilih bulan pembayaran.');
        }
    });

    updatePreview();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\spp-form.blade.php ENDPATH**/ ?>