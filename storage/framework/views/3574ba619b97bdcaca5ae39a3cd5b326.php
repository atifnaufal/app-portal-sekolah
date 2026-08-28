<div class="modal fade" id="editModal<?php echo e($j->id); ?>" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?php echo e(route('admin.jadwal.update', $j)); ?>" class="modal-content">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php echo $__env->make('admin.jadwal.fields', ['j' => $j, 'materi' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\admin\jadwal\modal-edit.blade.php ENDPATH**/ ?>