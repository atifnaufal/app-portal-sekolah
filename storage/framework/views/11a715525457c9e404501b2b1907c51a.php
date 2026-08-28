

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="text-primary small fw-semibold">MANAJEMEN SEKOLAH</div>
        <h1 class="h3 fw-bold mb-1">Kegiatan Ekstrakurikuler</h1>
        <p class="text-secondary mb-0">Buat eskul, tentukan pembina, dan tunjuk admin eskul.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">+ Tambah Eskul</button>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<div class="row">
    <?php $__currentLoopData = $eskuls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eskul): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card stat-card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light rounded p-2" style="width: 50px; height: 50px;">
                            <?php if($eskul->logo): ?>
                                <img src="<?php echo e(asset('storage/'.$eskul->logo)); ?>" class="w-100 h-100 object-fit-cover rounded">
                            <?php else: ?>
                                <i class="bi bi-flag text-primary h4 mb-0"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0"><?php echo e($eskul->nama); ?></h5>
                            <span class="badge <?php echo e($eskul->aktif ? 'text-bg-success' : 'text-bg-secondary'); ?> small">
                                <?php echo e($eskul->aktif ? 'Aktif' : 'Nonaktif'); ?>

                            </span>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal<?php echo e($eskul->id); ?>"><i class="bi bi-pencil me-2"></i> Edit</button></li>
                            <li>
                                <form action="<?php echo e(route('admin.eskul.destroy', $eskul)); ?>" method="POST" onsubmit="return confirm('Hapus eskul ini?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> Hapus</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mb-3 small">
                    <div class="text-muted">Pembina:</div>
                    <div class="fw-bold text-dark"><?php echo e($eskul->pembina ? $eskul->pembina->name : 'Belum ditentukan'); ?></div>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <div class="small fw-bold text-primary"><?php echo e($eskul->members_count); ?> Anggota</div>
                    <div class="d-flex gap-2">
                        <form action="<?php echo e(route('admin.eskul.toggle', $eskul)); ?>" method="POST">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button class="btn btn-sm <?php echo e($eskul->aktif ? 'btn-outline-warning' : 'btn-outline-success'); ?> rounded-pill px-3">
                                <?php echo e($eskul->aktif ? 'Matikan' : 'Aktifkan'); ?>

                            </button>
                        </form>
                        <button class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#adminModal<?php echo e($eskul->id); ?>">Admin</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal<?php echo e($eskul->id); ?>" tabindex="-1">
        <div class="modal-dialog">
            <form action="<?php echo e(route('admin.eskul.update', $eskul)); ?>" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Edit Eskul: <?php echo e($eskul->nama); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Eskul</label>
                        <input type="text" name="nama" class="form-control rounded-3" value="<?php echo e($eskul->nama); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pembina (Guru)</label>
                        <select name="pembina_id" class="form-select rounded-3">
                            <option value="">Pilih Guru</option>
                            <?php $__currentLoopData = $gurus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $guru): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($guru->id); ?>" <?php echo e($eskul->pembina_id == $guru->id ? 'selected' : ''); ?>><?php echo e($guru->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="3"><?php echo e($eskul->deskripsi); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ganti Logo</label>
                        <input type="file" name="logo" class="form-control rounded-3" accept="image/jpeg,image/png,image/jpg,image/webp">
                        <div class="form-text small">Format: JPG, PNG, WEBP. Maks 2MB.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Admin Eskul Modal -->
    <div class="modal fade" id="adminModal<?php echo e($eskul->id); ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Manajemen Admin: <?php echo e($eskul->nama); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Anggota eskul yang ditunjuk sebagai admin dapat mengelola chat grup.</p>
                    <ul class="list-group list-group-flush">
                        <?php $__empty_1 = true; $__currentLoopData = $eskul->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-light">
                                <div>
                                    <div class="fw-bold"><?php echo e($member->name); ?></div>
                                    <div class="small text-muted"><?php echo e(ucfirst($member->role)); ?></div>
                                </div>
                                <form action="<?php echo e(route('admin.eskul.set-admin', $eskul)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo e($member->id); ?>">
                                    <button class="btn btn-sm <?php echo e($member->pivot->is_admin ? 'btn-success' : 'btn-outline-secondary'); ?> rounded-pill px-3">
                                        <?php echo e($member->pivot->is_admin ? 'Admin' : 'Jadikan Admin'); ?>

                                    </button>
                                </form>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="list-group-item text-center py-4 text-muted border-0">Belum ada anggota</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo e(route('admin.eskul.store')); ?>" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            <?php echo csrf_field(); ?>
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Tambah Eskul Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nama Eskul</label>
                    <input type="text" name="nama" class="form-control rounded-3" required placeholder="Misal: Futsal, Coding Club">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Pembina (Guru)</label>
                    <select name="pembina_id" class="form-select rounded-3">
                        <option value="">Pilih Guru</option>
                        <?php $__currentLoopData = $gurus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $guru): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($guru->id); ?>"><?php echo e($guru->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Deskripsi Singkat</label>
                    <textarea name="deskripsi" class="form-control rounded-3" rows="3" placeholder="Jelaskan visi atau kegiatan eskul..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Logo Eskul</label>
                    <input type="file" name="logo" class="form-control rounded-3" accept="image/jpeg,image/png,image/jpg,image/webp">
                    <div class="form-text small">Mendukung format JPG, PNG, dan WEBP.</div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Eskul</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\admin\eskul\index.blade.php ENDPATH**/ ?>