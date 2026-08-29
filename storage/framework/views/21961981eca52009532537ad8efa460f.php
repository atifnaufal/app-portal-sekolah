
<?php $__env->startSection('content'); ?>
<div class="mb-4">
    <a href="<?php echo e(route('admin.perpustakaan.index')); ?>" class="text-decoration-none small fw-bold"><i class="bi bi-arrow-left"></i> Kembali ke Daftar</a>
    <h1 class="h3 fw-bold mt-2"><?php echo e(isset($buku) ? 'Edit Buku' : 'Tambah Buku Baru'); ?></h1>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="<?php echo e(isset($buku) ? route('admin.perpustakaan.update', $buku) : route('admin.perpustakaan.store')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <?php if(isset($buku)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Judul Buku</label>
                        <input type="text" name="judul" class="form-control" value="<?php echo e(old('judul', $buku->judul ?? '')); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kategori</label>
                            <select name="kategori_buku_id" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <?php $__currentLoopData = $kategoris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($kat->id); ?>" <?php if(old('kategori_buku_id', $buku->kategori_buku_id ?? '') == $kat->id): echo 'selected'; endif; ?>><?php echo e($kat->nama); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Stok</label>
                            <input type="number" name="stok" class="form-control" value="<?php echo e(old('stok', $buku->stok ?? 1)); ?>" required min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Penulis</label>
                            <input type="text" name="penulis" class="form-control" value="<?php echo e(old('penulis', $buku->penulis ?? '')); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Penerbit</label>
                            <input type="text" name="penerbit" class="form-control" value="<?php echo e(old('penerbit', $buku->penerbit ?? '')); ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">Tahun</label>
                            <input type="number" name="tahun_terbit" class="form-control" value="<?php echo e(old('tahun_terbit', $buku->tahun_terbit ?? '')); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="5"><?php echo e(old('deskripsi', $buku->deskripsi ?? '')); ?></textarea>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bg-light p-3 rounded-4 mb-3 border">
                        <label class="form-label fw-bold">Sampul Buku (Image)</label>
                        <?php if(isset($buku) && $buku->cover): ?>
                            <div class="mb-2">
                                <img src="<?php echo e(asset('storage/'.$buku->cover)); ?>" class="rounded shadow-sm" style="width: 100px; aspect-ratio: 2/3; object-fit: cover;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="cover" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
                        <div class="small text-secondary mt-1">Maks. 2MB. Kosongkan jika tidak ingin mengubah.</div>
                    </div>

                    <div class="bg-light p-3 rounded-4 border">
                        <label class="form-label fw-bold">File Buku (PDF)</label>
                        <?php if(isset($buku) && $buku->file_pdf): ?>
                            <div class="mb-2">
                                <a href="<?php echo e(asset('storage/'.$buku->file_pdf)); ?>" target="_blank" class="btn btn-sm btn-info text-white"><i class="bi bi-file-pdf"></i> Lihat PDF Saat Ini</a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="file_pdf" class="form-control" accept=".pdf" <?php echo e(isset($buku) ? '' : 'required'); ?>>
                        <div class="small text-secondary mt-1">Maks. 20MB. PDF only.</div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top text-end">
                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">Simpan Buku</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\admin\perpustakaan\form.blade.php ENDPATH**/ ?>