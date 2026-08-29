<?php $hideNav = true; ?>


<?php $__env->startSection('content'); ?>
<?php
    $isGuru = $user->role === 'guru';
    $deadline = $tugas->deadlineStatus();
    $submission = $submission ?? null;
    $siswaKelas = $siswaKelas ?? collect();
    $canSubmit = $canSubmit ?? false;
    $totalSiswa = $isGuru ? $siswaKelas->count() : 0;
    $totalSubmitted = $tugas->pengumpulan->count();
    $totalGraded = $tugas->pengumpulan->whereNotNull('nilai')->where('revisi_aktif', false)->count();
    $totalPending = $tugas->pengumpulan->whereNull('nilai')->where('revisi_aktif', false)->count();
    $totalRevisi = $tugas->pengumpulan->where('revisi_aktif', true)->count();
?>

<style>
    * { box-sizing: border-box; }
    .td-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.95); backdrop-filter: blur(16px);
        border-bottom: 1px solid #e2e8f0;
        padding: 10px 16px; display: flex; align-items: center; gap: 10px;
    }
    .td-body { padding: 62px 14px 100px; max-width: 640px; margin: 0 auto; }

    .td-card {
        background: #fff; border-radius: 20px; padding: 18px;
        margin-bottom: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(15, 23, 42, 0.05);
    }

    .td-badge {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 3px 10px; border-radius: 99px; font-size: 10px; font-weight: 700;
    }

    .td-progress { height: 6px; border-radius: 99px; background: #eef2f7; overflow: hidden; }
    .td-progress > span { display: block; height: 100%; border-radius: 99px; }

    .td-submission {
        background: #fff; border: 1px solid #e8ecf1; border-radius: 16px;
        padding: 14px; margin-bottom: 10px;
    }

    .td-grade-input {
        width: 70px; border: 1.5px solid #e2e8f0; border-radius: 10px;
        padding: 8px; font-size: 16px; font-weight: 800; text-align: center;
        -webkit-appearance: none;
    }
    .td-grade-input:focus { outline: none; border-color: #246bfe; }

    .td-feedback {
        width: 100%; border: 1.5px solid #e2e8f0; border-radius: 10px;
        padding: 8px 10px; font-size: 13px; resize: none;
        -webkit-appearance: none;
    }
    .td-feedback:focus { outline: none; border-color: #246bfe; }

    .td-toggle { position: relative; width: 40px; height: 22px; flex-shrink: 0; }
    .td-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
    .td-toggle-bg { position: absolute; inset: 0; background: #e2e8f0; border-radius: 99px; transition: 0.2s; cursor: pointer; }
    .td-toggle-bg::before { content: ''; position: absolute; width: 16px; height: 16px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.15); }
    .td-toggle input:checked + .td-toggle-bg { background: #246bfe; }
    .td-toggle input:checked + .td-toggle-bg::before { transform: translateX(18px); }

    .td-grade-circle {
        width: 100px; height: 100px; border-radius: 50%; margin: 0 auto;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .td-grade-circle .num { font-size: 32px; font-weight: 800; line-height: 1; }
    .td-grade-circle .lbl { font-size: 9px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; margin-top: 2px; }

    @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .fade-up { animation: fadeUp 0.35s ease both; }
</style>

<div class="td-header">
    <a href="<?php echo e(route('tugas.index')); ?>" style="width:36px;height:36px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#475569;">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div style="font-weight:800;font-size:16px;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e(\Illuminate\Support\Str::limit($tugas->judul, 30)); ?></div>
    <?php if($isGuru): ?>
        <button type="button" onclick="document.getElementById('delModal').style.display='flex'" style="width:40px;height:40px;border-radius:14px;background:#fff5f6;border:1px solid #fecdd3;color:#d94b61;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;">
            <i class="bi bi-trash3"></i>
        </button>
        <a href="<?php echo e(route('tugas.edit', $tugas)); ?>" style="width:40px;height:40px;border-radius:14px;background:#eef4ff;border:1px solid #bfdbfe;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#246bfe;font-size:16px;">
            <i class="bi bi-pencil-square"></i>
        </a>
    <?php endif; ?>
</div>

<div class="td-body">
    
    <div class="td-card fade-up" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 24px 20px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
            <span style="background:rgba(255,255,255,0.08); padding:4px 10px; border-radius:8px; font-size:9px; font-weight:800; letter-spacing:0.05em; text-transform:uppercase;">
                <i class="bi bi-journal-bookmark me-1"></i> <?php echo e($tugas->mataPelajaran?->nama ?? 'Umum'); ?>

            </span>
            <span style="background:rgba(255,255,255,0.08); padding:4px 10px; border-radius:8px; font-size:9px; font-weight:800; letter-spacing:0.05em; text-transform:uppercase;">
                <i class="bi bi-people me-1"></i> <?php echo e($tugas->kelas->nama); ?>

            </span>
        </div>
        <div style="font-size:20px; font-weight:800; line-height:1.2; margin-bottom:6px; letter-spacing:-0.02em;"><?php echo e($tugas->judul); ?></div>
        <div style="font-size:12px; opacity:0.6; font-weight:500;">Oleh: <?php echo e($tugas->user->name); ?></div>

        <div style="margin-top:16px; padding-top:16px; border-top:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-size:9px; font-weight:800; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:0.1em; margin-bottom:2px;">Deadline</div>
                <div style="font-size:13px; font-weight:700;"><?php echo e($tugas->batas_pengumpulan?->format('d M Y, H:i') ?? 'Tidak ada batas'); ?></div>
            </div>
            <div class="text-end">
                <div style="font-size:9px; font-weight:800; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:0.1em; margin-bottom:2px;">Status</div>
                <div style="font-size:13px; font-weight:700; color:<?php echo e($deadline['tone'] === 'danger' ? '#f87171' : '#4ade80'); ?>"><?php echo e($deadline['label']); ?></div>
            </div>
        </div>
    </div>

    
    <div class="td-card fade-up" style="animation-delay:0.05s;">
        <div style="font-size:13px;font-weight:700;margin-bottom:8px;"><i class="bi bi-info-circle" style="color:#246bfe;"></i> Instruksi</div>
        <div style="font-size:13px;color:#475569;line-height:1.6;white-space:pre-line;"><?php echo e($tugas->deskripsi ?: 'Tidak ada deskripsi.'); ?></div>
        <?php if($tugas->lampiran): ?>
            <a href="<?php echo e(asset('storage/'.$tugas->lampiran)); ?>" target="_blank" style="display:flex;align-items:center;gap:10px;padding:10px;background:#f8fafc;border-radius:12px;margin-top:10px;text-decoration:none;color:#1e293b;">
                <i class="bi bi-file-earmark-fill" style="font-size:20px;color:#246bfe;"></i>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($tugas->lampiran_nama); ?></div>
                    <div style="font-size:10px;color:#94a3b8;">Tap untuk buka</div>
                </div>
                <i class="bi bi-box-arrow-up-right" style="font-size:12px;color:#94a3b8;"></i>
            </a>
        <?php endif; ?>
    </div>

    <?php if($isGuru): ?>
        
        <div style="display:flex;justify-content:space-between;align-items:center;margin:16px 0 10px;">
            <div style="font-size:14px;font-weight:800;">Pengumpulan Siswa</div>
            <div style="display:flex;gap:8px;">
            <a href="<?php echo e(route('tugas.export.pdf', $tugas)); ?>" style="font-size:11px;font-weight:700;color:#dc2626;text-decoration:none;background:#fef2f2;padding:5px 10px;border-radius:8px;"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
            <a href="<?php echo e(route('tugas.export.excel', $tugas)); ?>" style="font-size:11px;font-weight:700;color:#16a34a;text-decoration:none;background:#f0fdf4;padding:5px 10px;border-radius:8px;"><i class="bi bi-file-earmark-excel"></i> Excel</a>
        </div>
            </div>

        
        <?php if($totalSiswa > 0): ?>
            <div class="td-progress" style="margin-bottom:14px;display:flex;">
                <span style="width:<?php echo e(round(($totalGraded/$totalSiswa)*100)); ?>%;background:#16a34a;"></span>
                <span style="width:<?php echo e(round(($totalPending/$totalSiswa)*100)); ?>%;background:#f59e0b;"></span>
                <span style="width:<?php echo e(round(($totalRevisi/$totalSiswa)*100)); ?>%;background:#d94b61;"></span>
            </div>
        <?php endif; ?>

        <?php $__empty_1 = true; $__currentLoopData = $tugas->pengumpulan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="td-submission fade-up">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    <div style="width:36px;height:36px;border-radius:12px;background:#eef4ff;display:flex;align-items:center;justify-content:center;font-weight:800;color:#246bfe;font-size:13px;flex-shrink:0;">
                        <?php echo e(strtoupper(substr($item->siswa->name ?? '?', 0, 1))); ?>

                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:700;"><?php echo e($item->siswa->name ?? 'Siswa'); ?></div>
                        <div style="font-size:10px;color:#94a3b8;"><?php echo e($item->dikumpulkan_pada?->diffForHumans() ?? 'Baru saja'); ?></div>
                    </div>
                    <?php if($item->revisi_aktif): ?>
                        <span class="td-badge" style="background:#fef3c7;color:#b45309;">Revisi</span>
                    <?php elseif($item->nilai !== null): ?>
                        <span class="td-badge" style="background:#dcfce7;color:#15803d;"><?php echo e($item->nilai); ?></span>
                    <?php else: ?>
                        <span class="td-badge" style="background:#dbeafe;color:#1d4ed8;">Pending</span>
                    <?php endif; ?>
                </div>

                
                <?php if($item->jawaban_file): ?>
                    <a href="<?php echo e(asset('storage/'.$item->jawaban_file)); ?>" target="_blank" style="display:flex;align-items:center;gap:8px;padding:8px 10px;background:#f8fafc;border-radius:10px;margin-bottom:8px;text-decoration:none;color:#1e293b;">
                        <i class="bi bi-file-earmark-fill" style="color:#246bfe;"></i>
                        <span style="font-size:12px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;"><?php echo e($item->jawaban_nama ?: 'File Jawaban'); ?></span>
                        <i class="bi bi-box-arrow-up-right" style="font-size:10px;color:#94a3b8;"></i>
                    </a>
                <?php endif; ?>

                
                <?php if($item->jawaban_form): ?>
                    <?php
                        $answers = is_array($item->jawaban_form) ? $item->jawaban_form : (json_decode($item->jawaban_form ?: '[]', true) ?: []);
                        $formData = is_array($tugas->form_data) ? $tugas->form_data : (json_decode($tugas->form_data ?: '[]', true) ?: []);
                    ?>
                    <div style="font-size:10px;font-weight:800;color:#7c3aed;letter-spacing:0.05em;margin:2px 0 6px;"><i class="bi bi-ui-checks-grid"></i> JAWABAN FORMULIR SISWA</div>
                    <div style="background:#f8fafc;border-radius:10px;padding:10px;margin-bottom:8px;">
                        <?php $__currentLoopData = $formData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qi => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $ans = $answers[$qi] ?? null; ?>
                            <div style="margin-bottom:6px;">
                                <div style="font-size:11px;font-weight:700;color:#1e293b;"><?php echo e($qi+1); ?>. <?php echo e($q['text'] ?? ''); ?></div>
                                <div style="font-size:11px;color:#64748b;white-space:pre-line;"><?php echo e(is_array($ans) ? implode(', ', array_filter($ans)) : ($ans ?: 'tidak dijawab')); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

                <?php if($item->catatan && !$item->jawaban_form): ?>
                    <div style="font-size:11px;color:#64748b;font-style:italic;margin-bottom:8px;">"<?php echo e($item->catatan); ?>"</div>
                <?php endif; ?>

                
                <form method="POST" action="<?php echo e(route('tugas.review', $item)); ?>" style="border-top:1px solid #f1f5f9;padding-top:10px;">
                    <?php echo csrf_field(); ?>
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <label style="font-size:11px;font-weight:700;color:#64748b;flex-shrink:0;">Nilai:</label>
                        <input type="number" name="nilai" class="td-grade-input" min="0" max="100" step="1" value="<?php echo e($item->nilai ?? ''); ?>" placeholder="0-100" required>
                    </div>
                    <textarea name="feedback_guru" class="td-feedback" rows="2" placeholder="Feedback untuk siswa..."><?php echo e($item->feedback_guru); ?></textarea>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span style="font-size:11px;color:#64748b;">Minta revisi</span>
                            <label class="td-toggle">
                                <input type="checkbox" name="revisi_aktif" value="1" <?php if($item->revisi_aktif): echo 'checked'; endif; ?>>
                                <span class="td-toggle-bg"></span>
                            </label>
                        </div>
                        <button type="submit" style="padding:8px 20px;border-radius:10px;background:#246bfe;color:#fff;font-weight:700;font-size:12px;border:none;cursor:pointer;">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="td-card" style="text-align:center;padding:30px;">
                <i class="bi bi-inbox" style="font-size:32px;color:#cbd5e1;"></i>
                <div style="font-size:13px;font-weight:600;color:#94a3b8;margin-top:8px;">Belum ada pengumpulan</div>
            </div>
        <?php endif; ?>

        
        <?php $submittedIds = $tugas->pengumpulan->pluck('siswa_id'); $belum = $siswaKelas->whereNotIn('id', $submittedIds); ?>
        <?php if($belum->count() > 0): ?>
            <div style="margin-top:16px;font-size:12px;font-weight:700;color:#94a3b8;">Belum Mengumpulkan (<?php echo e($belum->count()); ?>)</div>
            <?php $__currentLoopData = $belum; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $siswa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid #f8fafc;">
                    <div style="width:28px;height:28px;border-radius:8px;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-weight:700;color:#dc2626;font-size:11px;"><?php echo e(strtoupper(substr($siswa->name,0,1))); ?></div>
                    <span style="font-size:12px;font-weight:600;"><?php echo e($siswa->name); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

    <?php else: ?>
        

        <?php if($submission && !$submission->revisi_aktif && $submission->nilai !== null): ?>
            <?php
                $gc = $submission->nilai >= 85 ? 'background:linear-gradient(135deg,#f0fdf4,#dcfce7);color:#15803d;' : ($submission->nilai >= 70 ? 'background:linear-gradient(135deg,#eef4ff,#dbeafe);color:#1d4ed8;' : ($submission->nilai >= 55 ? 'background:linear-gradient(135deg,#fefce8,#fef9c3);color:#a16207;' : 'background:linear-gradient(135deg,#fef2f2,#fee2e2);color:#dc2626;'));
                $ge = $submission->nilai >= 85 ? 'Luar Biasa!' : ($submission->nilai >= 70 ? 'Bagus!' : ($submission->nilai >= 55 ? 'Cukup' : 'Perlu Belajar'));
            ?>
            <div class="td-card fade-up" style="text-align:center;animation-delay:0.1s;">
                <div style="font-size:10px;font-weight:700;color:#94a3b8;letter-spacing:0.08em;margin-bottom:12px;">NILAI KAMU</div>
                <div class="td-grade-circle" style="<?php echo e($gc); ?>">
                    <div class="num"><?php echo e($submission->nilai); ?></div>
                    <div class="lbl">dari 100</div>
                </div>
                <div style="font-size:16px;font-weight:800;margin-top:12px;"><?php echo e($ge); ?></div>
                <?php if($submission->feedback_guru): ?>
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px;padding:12px;margin-top:14px;text-align:left;">
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                            <div style="width:20px;height:20px;border-radius:6px;background:#16a34a;color:#fff;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:800;"><?php echo e(strtoupper(substr($tugas->user->name,0,1))); ?></div>
                            <span style="font-size:12px;font-weight:700;"><?php echo e($tugas->user->name); ?></span>
                        </div>
                        <div style="font-size:12px;color:#475569;line-height:1.6;white-space:pre-line;"><?php echo e($submission->feedback_guru); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif($submission && $submission->revisi_aktif): ?>
            <div class="td-card fade-up" style="border:2px solid #fde68a;animation-delay:0.1s;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    <div style="width:40px;height:40px;border-radius:12px;background:#fef3c7;color:#b45309;display:flex;align-items:center;justify-content:center;"><i class="bi bi-arrow-repeat" style="font-size:18px;"></i></div>
                    <div><div style="font-size:14px;font-weight:700;">Perlu Revisi</div><div style="font-size:11px;color:#94a3b8;">Perbaiki jawaban kamu</div></div>
                </div>
                <?php if($submission->feedback_guru): ?>
                    <div style="background:#fffbeb;border-radius:10px;padding:10px;font-size:12px;line-height:1.6;white-space:pre-line;"><?php echo e($submission->feedback_guru); ?></div>
                <?php endif; ?>
            </div>
        <?php elseif($submission && $submission->nilai === null): ?>
            <div class="td-card fade-up" style="text-align:center;animation-delay:0.1s;">
                <div style="width:50px;height:50px;border-radius:16px;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;"><i class="bi bi-hourglass-split" style="font-size:22px;"></i></div>
                <div style="font-size:14px;font-weight:700;">Menunggu Penilaian</div>
                <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Dikirim <?php echo e($submission->dikumpulkan_pada?->format('d M Y, H:i') ?? 'baru saja'); ?></div>
            </div>
        <?php endif; ?>

        <?php if($canSubmit): ?>
            <?php if($tugas->tipe === 'form'): ?>
                <?php $formData = is_array($tugas->form_data) ? $tugas->form_data : (json_decode($tugas->form_data ?: '[]', true) ?: []); ?>
                <div class="td-card fade-up" style="animation-delay:0.15s;">
                    <div style="font-size:14px;font-weight:700;margin-bottom:14px;"><i class="bi bi-ui-checks-grid" style="color:#7c3aed;"></i> Formulir Pengerjaan</div>
                    <form method="POST" action="<?php echo e(route('tugas.submit', $tugas)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php $__currentLoopData = $formData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $req = $q['required'] ?? true; ?>
                            <div style="background:#f8fafc;border-radius:14px;padding:14px;margin-bottom:10px;">
                                <label style="font-size:13px;font-weight:700;display:block;margin-bottom:8px;">
                                    <?php echo e($idx+1); ?>. <?php echo e($q['text']); ?> <?php if($req): ?><span style="color:#dc2626;">*</span><?php endif; ?>
                                </label>
                                <?php if($q['type']==='text'): ?>
                                    <input type="text" name="jawaban[<?php echo e($idx); ?>]" class="pf-input" style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px;font-size:13px;" <?php if($req): ?> required <?php endif; ?>>
                                <?php elseif($q['type']==='essay'): ?>
                                    <textarea name="jawaban[<?php echo e($idx); ?>]" rows="3" class="pf-input" style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px;font-size:13px;resize:none;" <?php if($req): ?> required <?php endif; ?>></textarea>
                                <?php elseif($q['type']==='multiple'): ?>
                                    <?php $__currentLoopData = $q['options'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oi => $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <label style="display:flex;align-items:center;gap:8px;padding:8px;background:#fff;border-radius:8px;margin-bottom:4px;font-size:13px;cursor:pointer;">
                                            <input type="radio" name="jawaban[<?php echo e($idx); ?>]" value="<?php echo e($opt); ?>" <?php if($req): ?> required <?php endif; ?>> <?php echo e($opt); ?>

                                        </label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php elseif($q['type']==='checkbox'): ?>
                                    <?php $__currentLoopData = $q['options'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oi => $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <label style="display:flex;align-items:center;gap:8px;padding:8px;background:#fff;border-radius:8px;margin-bottom:4px;font-size:13px;cursor:pointer;">
                                            <input type="checkbox" name="jawaban[<?php echo e($idx); ?>][]" value="<?php echo e($opt); ?>" <?php if($req): ?> required <?php endif; ?>> <?php echo e($opt); ?>

                                        </label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php elseif($q['type']==='dropdown'): ?>
                                    <select name="jawaban[<?php echo e($idx); ?>]" class="pf-input" style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px;font-size:13px;" <?php if($req): ?> required <?php endif; ?>>
                                        <option value="">-- Pilih --</option>
                                        <?php $__currentLoopData = $q['options'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($opt); ?>"><?php echo e($opt); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <button type="submit" style="width:100%;padding:14px;border-radius:14px;background:#246bfe;color:#fff;font-weight:700;font-size:14px;border:none;cursor:pointer;margin-top:4px;">
                            <i class="bi bi-send-fill"></i> Kirim Jawaban
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="td-card fade-up" style="animation-delay:0.15s;">
                    <div style="font-size:14px;font-weight:700;margin-bottom:12px;"><i class="bi bi-cloud-arrow-up" style="color:#246bfe;"></i> Kirim Jawaban</div>
                    <?php if($submission && $submission->jawaban_file): ?>
                        <div style="display:flex;align-items:center;gap:8px;padding:10px;background:#f0fdf4;border-radius:10px;margin-bottom:10px;">
                            <i class="bi bi-check-circle-fill" style="color:#16a34a;"></i>
                            <div style="flex:1;"><div style="font-size:12px;font-weight:600;">Terkirim</div><div style="font-size:10px;color:#94a3b8;"><?php echo e($submission->dikumpulkan_pada?->format('d M Y, H:i')); ?></div></div>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('tugas.submit', $tugas)); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div style="margin-bottom:10px;">
                            <label style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;display:block;">CATATAN</label>
                            <textarea name="catatan" rows="2" class="pf-input" style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px;font-size:13px;resize:none;" placeholder="Catatan pengerjaan..." required><?php echo e($submission?->catatan); ?></textarea>
                        </div>
                        <div style="margin-bottom:12px;">
                            <label style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;display:block;">FILE JAWABAN</label>
                            <input type="file" name="jawaban_file" class="pf-input" style="padding:10px;" <?php echo e($submission && $submission->jawaban_file ? '' : 'required'); ?> accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.xls,.ppt,.pptx,.csv,.txt,.zip">
                            <div style="font-size:10px;color:#94a3b8;margin-top:4px;">PDF, Word, Excel, PPT, Gambar, ZIP (Maks 10MB)</div>
                        </div>
                        <button type="submit" style="width:100%;padding:14px;border-radius:14px;background:#246bfe;color:#fff;font-weight:700;font-size:14px;border:none;cursor:pointer;">
                            <i class="bi bi-send-fill"></i> <?php echo e($submission ? 'Perbarui' : 'Kirim'); ?>

                        </button>
                    </form>
                </div>
            <?php endif; ?>
        <?php elseif(!$submission && $tugas->isExpired()): ?>
            <div class="td-card fade-up" style="text-align:center;">
                <i class="bi bi-x-circle" style="font-size:32px;color:#dc2626;"></i>
                <div style="font-size:14px;font-weight:700;margin-top:8px;">Batas Waktu Terlewat</div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>


<?php if($isGuru): ?>
<div id="delModal" onclick="if(event.target===this)this.style.display='none'" style="position:fixed;inset:0;z-index:2000;display:none;align-items:flex-end;justify-content:center;background:rgba(0,0,0,0.4);">
    <div style="width:100%;max-width:640px;background:#fff;border-radius:24px 24px 0 0;padding:24px 20px;">
        <div style="font-size:16px;font-weight:800;margin-bottom:4px;">Hapus tugas?</div>
        <div style="font-size:12px;color:#94a3b8;margin-bottom:16px;"><?php echo e($tugas->pengumpulan->count()); ?> pengumpulan akan ikut terhapus.</div>
        <form method="POST" action="<?php echo e(route('tugas.destroy', $tugas)); ?>">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button type="submit" style="width:100%;padding:12px;border-radius:12px;background:#dc2626;color:#fff;font-weight:700;border:none;cursor:pointer;margin-bottom:8px;">Hapus Permanen</button>
            <button type="button" onclick="document.getElementById('delModal').style.display='none'" style="width:100%;padding:12px;border-radius:12px;background:#f1f5f9;color:#475569;font-weight:700;border:none;cursor:pointer;">Batal</button>
        </form>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\tugas-detail.blade.php ENDPATH**/ ?>