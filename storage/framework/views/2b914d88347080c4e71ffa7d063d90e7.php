<?php $__env->startSection('content'); ?>
<style>
    .ajd-card { border-radius: 20px; border: 1px solid var(--border); overflow: hidden; }
    .ajd-table th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); }
    .ajd-table td { vertical-align: middle; }
    .ajd-badge { font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.03em; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Jadwal Pelajaran</h1>
        <p class="text-muted mb-0 small">Kelola agenda mengajar guru dan jadwal kelas. Jadwal yang dibuat di sini otomatis tampil untuk guru &amp; siswa.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="bi bi-plus-circle me-2"></i>Tambah Jadwal
    </button>
</div>


<div class="ajd-card card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Kelas</label>
                <select name="kelas_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    <?php $__currentLoopData = $kelases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($k->id); ?>" <?php if((string) $k->id === (string) $kelasId): echo 'selected'; endif; ?>><?php echo e($k->nama); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Hari</label>
                <select name="hari" class="form-select" onchange="this.form.submit()">
                    <option value="semua" <?php if($hari === null || $hari === 'semua'): echo 'selected'; endif; ?>>Semua Hari</option>
                    <?php $__currentLoopData = ['senin','selasa','rabu','kamis','jumat','sabtu']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($h); ?>" <?php if($hari === $h): echo 'selected'; endif; ?>><?php echo e(ucfirst($h)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-4">
                <a href="<?php echo e(route('admin.jadwal.index')); ?>" class="btn btn-outline-secondary w-100">Reset Filter</a>
            </div>
        </form>
    </div>
</div>


<div class="ajd-card card shadow-sm">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <span class="fw-bold">Daftar Jadwal (<?php echo e($jadwals->count()); ?>)</span>
    </div>
    <div class="table-responsive">
        <table class="table ajd-table mb-0">
            <thead class="table-light">
                <tr>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas</th>
                    <th>Guru</th>
                    <th>Ruangan</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $jadwals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><span class="ajd-badge" style="background:#eef2ff;color:#4338ca;"><?php echo e(ucfirst($j->hari)); ?></span></td>
                        <td class="fw-bold"><?php echo e(\Carbon\Carbon::parse($j->jam_mulai)->format('H:i')); ?> - <?php echo e(\Carbon\Carbon::parse($j->jam_selesai)->format('H:i')); ?></td>
                        <td class="fw-semibold"><?php echo e($j->mataPelajaran->nama); ?></td>
                        <td><?php echo e($j->kelas->nama); ?></td>
                        <td><?php echo e($j->guru->name); ?></td>
                        <td><?php echo e($j->ruangan); ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo e($j->id); ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="<?php echo e(route('admin.jadwal.destroy', $j)); ?>" class="d-inline" onsubmit="return confirm('Hapus jadwal ini?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada jadwal. Tambahkan jadwal baru.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?php $__currentLoopData = $jadwals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php echo $__env->make('admin.jadwal.modal-edit', ['j' => $j], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php unset($j); ?>


<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?php echo e(route('admin.jadwal.store')); ?>" class="modal-content">
            <?php echo csrf_field(); ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php echo $__env->make('admin.jadwal.fields', ['j' => null, 'materi' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\admin\jadwal\index.blade.php ENDPATH**/ ?>