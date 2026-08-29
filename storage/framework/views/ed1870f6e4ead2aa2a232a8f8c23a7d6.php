<?php $hideNav = true; ?>


<?php $__env->startSection('content'); ?>
<?php
    $isGuru = $user->role === 'guru' && (int) $materi->user_id === (int) $user->id;
    $videoId = null;
    if ($materi->video_url) {
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{6,})/', $materi->video_url, $m)) {
            $videoId = $m[1];
        }
    }
?>

<style>
    .mt-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.95); backdrop-filter: blur(16px);
        border-bottom: 1px solid #f1f5f9;
        padding: 10px 16px; display: flex; align-items: center; gap: 10px;
    }
    .mt-body { padding: 62px 14px 100px; max-width: 640px; margin: 0 auto; }

    .mt-card {
        background: #fff; border-radius: 20px; padding: 18px;
        margin-bottom: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(15,23,42,0.05);
    }

    .mt-file {
        display: flex; align-items: center; gap: 12px; padding: 14px;
        background: #f8fafc; border-radius: 14px; border: 1px solid #eef2f7;
        text-decoration: none; color: #1e293b; transition: all 0.2s;
    }
    .mt-file:active { transform: scale(0.98); background: #f1f5f9; }

    @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .fade-up { animation: fadeUp 0.35s ease both; }
</style>

<div class="mt-header">
    <a href="<?php echo e(route('mapel.show', $mapel)); ?>" style="width:36px;height:36px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#475569;">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div style="font-weight:800;font-size:16px;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e(Illuminate\Support\Str::limit($materi->judul, 26)); ?></div>
    <?php if($isGuru): ?>
        <button type="button" onclick="document.getElementById('mtDel').style.display='flex'" style="width:40px;height:40px;border-radius:14px;background:#fff5f6;border:1px solid #fecdd3;color:#d94b61;display:flex;align-items:center;justify-content:center;cursor:pointer;">
            <i class="bi bi-trash3"></i>
        </button>
        <a href="<?php echo e(route('materi.edit', [$mapel, $materi])); ?>" style="width:40px;height:40px;border-radius:14px;background:#eef4ff;border:1px solid #bfdbfe;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#246bfe;">
            <i class="bi bi-pencil-square"></i>
        </a>
    <?php endif; ?>
</div>

<div class="mt-body">
    
    <div class="mt-card fade-up" style="background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border:none;box-shadow:0 10px 25px rgba(0,0,0,0.1);padding:22px 20px;">
        <div style="display:flex;gap:6px;margin-bottom:12px;flex-wrap:wrap;">
            <span style="background:rgba(255,255,255,0.08);padding:4px 10px;border-radius:8px;font-size:9px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;">
                <i class="bi bi-journal-bookmark me-1"></i> <?php echo e($mapel->nama); ?>

            </span>
            <span style="background:rgba(255,255,255,0.08);padding:4px 10px;border-radius:8px;font-size:9px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;">
                <i class="bi bi-calendar3 me-1"></i> <?php echo e($materi->created_at->translatedFormat('d M Y')); ?>

            </span>
        </div>
        <div style="font-size:20px;font-weight:800;line-height:1.25;letter-spacing:-0.02em;"><?php echo e($materi->judul); ?></div>
        <div style="font-size:12px;opacity:0.6;margin-top:10px;display:flex;align-items:center;gap:6px;">
            <i class="bi bi-person-fill"></i> <?php echo e($materi->guru->name); ?>

        </div>
    </div>

    
    <?php if($materi->deskripsi): ?>
        <div class="mt-card fade-up" style="animation-delay:0.05s;">
            <div style="font-size:13px;font-weight:700;margin-bottom:8px;"><i class="bi bi-card-text" style="color:#246bfe;"></i> Deskripsi</div>
            <div style="font-size:13.5px;color:#475569;line-height:1.7;white-space:pre-line;"><?php echo e($materi->deskripsi); ?></div>
        </div>
    <?php endif; ?>

    
    <?php if($videoId): ?>
        <div class="mt-card fade-up" style="animation-delay:0.1s;padding:0;overflow:hidden;border:none;">
            <div style="position:relative;width:100%;padding-top:56.25%;background:#000;">
                <iframe src="https://www.youtube.com/embed/<?php echo e($videoId); ?>" style="position:absolute;inset:0;width:100%;height:100%;border:0;" allowfullscreen loading="lazy"></iframe>
            </div>
        </div>
    <?php elseif($materi->video_url): ?>
        <div class="mt-card fade-up" style="animation-delay:0.1s;">
            <a href="<?php echo e($materi->video_url); ?>" target="_blank" class="mt-file">
                <div style="width:42px;height:42px;border-radius:12px;background:#fef2f2;color:#dc2626;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-play-circle-fill" style="font-size:20px;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:13px;font-weight:700;">Tonton Video</div>
                    <div style="font-size:11px;color:#94a3b8;">Buka di browser</div>
                </div>
                <i class="bi bi-box-arrow-up-right" style="color:#94a3b8;"></i>
            </a>
        </div>
    <?php endif; ?>

    
    <?php if($materi->file_materi): ?>
        <div class="mt-card fade-up" style="animation-delay:0.15s;">
            <div style="font-size:13px;font-weight:700;margin-bottom:10px;"><i class="bi bi-paperclip" style="color:#246bfe;"></i> Lampiran</div>
            <a href="<?php echo e(asset('storage/'.$materi->file_materi)); ?>" target="_blank" class="mt-file">
                <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#eff6ff,#dbeafe);color:#2563eb;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-file-earmark-arrow-down-fill" style="font-size:20px;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($materi->file_nama ?: 'Download Materi'); ?></div>
                    <div style="font-size:11px;color:#94a3b8;">Tap untuk unduh / buka</div>
                </div>
                <i class="bi bi-download" style="color:#246bfe;"></i>
            </a>
        </div>
    <?php endif; ?>

    <?php if(!$materi->deskripsi && !$materi->file_materi && !$materi->video_url): ?>
        <div class="mt-card fade-up" style="text-align:center;padding:30px;">
            <i class="bi bi-journal-x" style="font-size:36px;color:#cbd5e1;"></i>
            <div style="font-size:13px;font-weight:600;color:#94a3b8;margin-top:8px;">Materi ini belum memiliki lampiran.</div>
        </div>
    <?php endif; ?>
</div>

<?php if($isGuru): ?>
<div id="mtDel" onclick="if(event.target===this)this.style.display='none'" style="position:fixed;inset:0;z-index:2000;display:none;align-items:flex-end;justify-content:center;background:rgba(0,0,0,0.4);">
    <div style="width:100%;max-width:640px;background:#fff;border-radius:24px 24px 0 0;padding:24px 20px;">
        <div style="font-size:16px;font-weight:800;margin-bottom:4px;">Hapus materi?</div>
        <div style="font-size:12px;color:#94a3b8;margin-bottom:16px;">"<?php echo e($materi->judul); ?>" akan dihapus permanen.</div>
        <form method="POST" action="<?php echo e(route('materi.destroy', [$mapel, $materi])); ?>">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button type="submit" style="width:100%;padding:12px;border-radius:12px;background:#dc2626;color:#fff;font-weight:700;border:none;cursor:pointer;margin-bottom:8px;">Hapus Permanen</button>
            <button type="button" onclick="document.getElementById('mtDel').style.display='none'" style="width:100%;padding:12px;border-radius:12px;background:#f1f5f9;color:#475569;font-weight:700;border:none;cursor:pointer;">Batal</button>
        </form>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\materi-detail.blade.php ENDPATH**/ ?>