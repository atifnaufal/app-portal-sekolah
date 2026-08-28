<?php $hideNav = true; ?>


<?php $__env->startSection('content'); ?>
<?php
    $isEdit = $tugas->exists;
    $currentTipe = old('tipe', $tugas->tipe ?? 'file');
    $existingForm = old('form_data', $isEdit && $tugas->form_data ? json_encode($tugas->form_data) : '[]');
?>

<style>
    .pf-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.95); backdrop-filter: blur(16px);
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 16px; display: flex; align-items: center; gap: 12px;
    }
    .pf-body { padding: 68px 16px 100px; max-width: 640px; margin: 0 auto; }

    .pf-section {
        background: #fff; border-radius: 20px; padding: 20px;
        margin-bottom: 14px; box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .pf-section-title {
        font-size: 15px; font-weight: 800; margin-bottom: 16px;
        display: flex; align-items: center; gap: 8px;
    }
    .pf-section-icon {
        width: 30px; height: 30px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; font-size: 15px;
    }

    .pf-input {
        width: 100%; border: 1.5px solid #e2e8f0; border-radius: 14px;
        padding: 12px 14px; font-size: 14px; background: #f8fafc;
        transition: border-color 0.2s;
        -webkit-appearance: none; appearance: none;
    }
    .pf-input:focus { outline: none; border-color: #246bfe; background: #fff; box-shadow: 0 0 0 3px rgba(36,107,254,0.08); }

    .pf-label { font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 6px; display: block; text-transform: uppercase; letter-spacing: 0.03em; }

    .pf-type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .pf-type-btn {
        border: 2px solid #e2e8f0; border-radius: 16px; padding: 16px 12px;
        text-align: center; cursor: pointer; transition: all 0.2s; background: #fafbfc;
        -webkit-tap-highlight-color: transparent;
    }
    .pf-type-btn.active { border-color: #246bfe; background: #eef4ff; }
    .pf-type-btn .icon { font-size: 24px; margin-bottom: 6px; }
    .pf-type-btn .label { font-size: 13px; font-weight: 700; }
    .pf-type-btn .desc { font-size: 10px; color: #94a3b8; margin-top: 2px; }

    .pf-file-zone {
        border: 2px dashed #cbd5e1; border-radius: 16px; padding: 24px 16px;
        text-align: center; cursor: pointer; transition: all 0.2s;
        -webkit-tap-highlight-color: transparent;
    }
    .pf-file-zone:active { border-color: #246bfe; background: #f0f5ff; }
    .pf-file-zone.has-file { border-color: #16a34a; border-style: solid; background: #f0fdf4; }

    .pf-q-card {
        background: #f8fafc; border: 1px solid #e8ecf1; border-radius: 16px;
        padding: 16px; margin-bottom: 10px; position: relative;
    }
    .pf-q-remove {
        position: absolute; top: 10px; right: 10px;
        width: 28px; height: 28px; border-radius: 50%; border: none;
        background: #fee2e2; color: #dc2626; font-size: 12px;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
    }

    .pf-q-type-row { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
    .pf-q-type-btn {
        border: 1px solid #e2e8f0; background: #fff; border-radius: 8px;
        padding: 5px 10px; font-size: 11px; font-weight: 600; cursor: pointer;
        color: #64748b; -webkit-tap-highlight-color: transparent;
    }
    .pf-q-type-btn.active { background: #246bfe; color: #fff; border-color: #246bfe; }

    .pf-opt-row { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; }
    .pf-opt-row input { flex: 1; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 10px; font-size: 13px; }
    .pf-opt-rm { width: 26px; height: 26px; border-radius: 50%; border: none; background: #fee2e2; color: #dc2626; font-size: 11px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

    .pf-bottom {
        position: fixed; bottom: 0; left: 0; right: 0;
        background: rgba(255,255,255,0.95); backdrop-filter: blur(16px);
        border-top: 1px solid #e2e8f0; padding: 12px 16px;
        z-index: 100; display: flex; gap: 10px;
    }
</style>

<div class="pf-header">
    <a href="<?php echo e($isEdit ? route('tugas.show', $tugas) : route('tugas.index')); ?>" style="width:38px;height:38px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#475569;">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div style="font-weight:800;font-size:17px;flex:1;"><?php echo e($isEdit ? 'Edit Tugas' : 'Buat Tugas'); ?></div>
</div>

<div class="pf-body">
    <form method="POST" action="<?php echo e($isEdit ? route('tugas.update', $tugas) : route('tugas.store')); ?>" enctype="multipart/form-data" id="tugasForm">
        <?php echo csrf_field(); ?>
        <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

        
        <div class="pf-section">
            <div class="pf-section-title">
                <div class="pf-section-icon" style="background:#eef4ff;color:#246bfe;"><i class="bi bi-pencil-square"></i></div>
                Detail Tugas
            </div>
            <div style="margin-bottom:12px;">
                <label class="pf-label">Judul *</label>
                <input type="text" name="judul" class="pf-input" placeholder="Contoh: Tugas Matematika Bab 5" value="<?php echo e(old('judul', $tugas->judul)); ?>" required>
            </div>
            <div style="margin-bottom:12px;">
                <label class="pf-label">Deskripsi / Instruksi</label>
                <textarea name="deskripsi" rows="3" class="pf-input" style="resize:none;" placeholder="Tuliskan instruksi pengerjaan..."><?php echo e(old('deskripsi', $tugas->deskripsi)); ?></textarea>
            </div>
            <div style="margin-bottom:12px;">
                <label class="pf-label">Mata Pelajaran *</label>
                <select name="mata_pelajaran_id" class="pf-input" required>
                    <option value="">Pilih mata pelajaran</option>
                    <?php $__currentLoopData = $mapels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m->id); ?>" <?php if(old('mata_pelajaran_id', $tugas->mata_pelajaran_id) == $m->id): echo 'selected'; endif; ?>><?php echo e($m->nama); ?> (<?php echo e($m->kelas->nama); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div>
                    <label class="pf-label">Kelas *</label>
                    <select name="kelas_id" class="pf-input" required>
                        <option value="">Pilih kelas</option>
                        <?php $__currentLoopData = $kelases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($k->id); ?>" <?php if(old('kelas_id', $tugas->kelas_id) == $k->id): echo 'selected'; endif; ?>><?php echo e($k->nama); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="pf-label">Batas Pengumpulan</label>
                    <input type="date" name="batas_pengumpulan" class="pf-input" value="<?php echo e(old('batas_pengumpulan', $tugas->batas_pengumpulan?->format('Y-m-d'))); ?>">
                </div>
            </div>
        </div>

        
        <div class="pf-section">
            <div class="pf-section-title">
                <div class="pf-section-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-grid-3x3-gap"></i></div>
                Tipe Pengumpulan
            </div>
            <input type="hidden" name="tipe" id="tipeInput" value="<?php echo e($currentTipe); ?>">
            <div class="pf-type-grid">
                <div class="pf-type-btn <?php echo e($currentTipe === 'file' ? 'active' : ''); ?>" data-type="file" onclick="pilihTipe('file')">
                    <div class="icon"><i class="bi bi-cloud-arrow-up-fill" style="color:#246bfe;"></i></div>
                    <div class="label">Upload File</div>
                    <div class="desc">PDF, Word, Excel</div>
                </div>
                <div class="pf-type-btn <?php echo e($currentTipe === 'form' ? 'active' : ''); ?>" data-type="form" onclick="pilihTipe('form')">
                    <div class="icon"><i class="bi bi-ui-checks-grid" style="color:#16a34a;"></i></div>
                    <div class="label">Formulir Online</div>
                    <div class="desc">Seperti Google Forms</div>
                </div>
            </div>
        </div>

        
        <div class="pf-section">
            <div class="pf-section-title">
                <div class="pf-section-icon" style="background:#fef3c7;color:#d97706;"><i class="bi bi-paperclip"></i></div>
                Lampiran <span style="font-size:11px;font-weight:400;color:#94a3b8;">(opsional)</span>
            </div>
            <?php if($isEdit && $tugas->lampiran): ?>
                <div style="display:flex;align-items:center;gap:10px;padding:12px;background:#f0fdf4;border-radius:14px;margin-bottom:10px;">
                    <i class="bi bi-file-earmark-check-fill" style="font-size:20px;color:#16a34a;"></i>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($tugas->lampiran_nama); ?></div>
                        <div style="font-size:11px;color:#94a3b8;">File saat ini</div>
                    </div>
                    <label style="font-size:11px;color:#dc2626;display:flex;align-items:center;gap:4px;">
                        <input type="checkbox" name="hapus_lampiran" value="1"> Hapus
                    </label>
                </div>
            <?php endif; ?>
            <div class="pf-file-zone" id="lampiranZone" onclick="document.getElementById('lampiranFile').click()">
                <input type="file" name="lampiran" id="lampiranFile" style="display:none" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.xls,.ppt,.pptx,.csv,.txt,.zip" onchange="previewLampiran(this)">
                <div id="lampiranPreview">
                    <i class="bi bi-cloud-arrow-up" style="font-size:28px;color:#94a3b8;"></i>
                    <div style="font-size:13px;font-weight:600;color:#475569;margin-top:8px;">Tap untuk upload</div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:2px;">PDF, Word, Excel, PPT, Gambar, ZIP (Maks 10MB)</div>
                </div>
            </div>
        </div>

        
        <div id="formBuilder" style="display:<?php echo e($currentTipe === 'form' ? 'block' : 'none'); ?>;">
            <div class="pf-section">
                <div class="pf-section-title">
                    <div class="pf-section-icon" style="background:#ede9fe;color:#7c3aed;"><i class="bi bi-question-circle"></i></div>
                    Pertanyaan Formulir
                </div>
                <input type="hidden" name="form_data" id="formDataInput" value="<?php echo e($existingForm); ?>">
                <div id="qContainer"></div>
                <button type="button" onclick="tambahPertanyaan()" style="width:100%;border:2px dashed #cbd5e1;background:transparent;border-radius:14px;padding:14px;font-size:13px;font-weight:700;color:#64748b;cursor:pointer;margin-top:8px;">
                    <i class="bi bi-plus-circle"></i> Tambah Pertanyaan
                </button>
            </div>
        </div>

        <div style="height: 80px;"></div>

        <div class="pf-bottom">
            <a href="<?php echo e($isEdit ? route('tugas.show', $tugas) : route('tugas.index')); ?>" style="flex:0 0 auto;padding:12px 20px;border-radius:14px;background:#f1f5f9;font-weight:700;font-size:14px;text-decoration:none;color:#475569;text-align:center;">Batal</a>
            <button type="submit" style="flex:1;padding:12px;border-radius:14px;background:#246bfe;color:#fff;font-weight:700;font-size:14px;border:none;cursor:pointer;">
                <i class="bi bi-send-fill"></i> <?php echo e($isEdit ? 'Simpan' : 'Terbitkan'); ?>

            </button>
        </div>
    </form>
</div>

<script>
var qTypes = [
    {k:'text',l:'Singkat'},{k:'essay',l:'Esai'},{k:'multiple',l:'Pilihan Ganda'},
    {k:'checkbox',l:'Centang'},{k:'dropdown',l:'Dropdown'}
];
var questions = [];

function pilihTipe(t) {
    document.getElementById('tipeInput').value = t;
    document.querySelectorAll('.pf-type-btn').forEach(function(el) { el.classList.toggle('active', el.dataset.type === t); });
    document.getElementById('formBuilder').style.display = t === 'form' ? 'block' : 'none';
}

function initQuestions() {
    try {
        var raw = document.getElementById('formDataInput').value;
        var parsed = JSON.parse(raw);
        if (Array.isArray(parsed) && parsed.length > 0) {
            questions = parsed.map(function(q) {
                return { text: q.text||'', type: q.type||'text', options: Array.isArray(q.options) ? q.options.slice() : [], required: q.required !== false };
            });
        }
    } catch(e) {}
    renderQ();
}

function tambahPertanyaan() {
    questions.push({text:'',type:'text',options:['Opsi 1','Opsi 2'],required:true});
    renderQ(); syncFD();
}

function hapusPertanyaan(i) { questions.splice(i,1); renderQ(); syncFD(); }
function ubahTipe(i,t) { questions[i].type=t; renderQ(); syncFD(); }
function ubahTeks(i,v) { questions[i].text=v; syncFD(); }
function ubahWajib(i) { questions[i].required=!questions[i].required; renderQ(); syncFD(); }
function tambahOpsi(i) { questions[i].options.push('Opsi '+(questions[i].options.length+1)); renderQ(); syncFD(); }
function hapusOpsi(i,oi) { if(questions[i].options.length>2){questions[i].options.splice(oi,1);renderQ();syncFD();} }
function ubahOpsi(i,oi,v) { questions[i].options[oi]=v; syncFD(); }
function syncFD() { document.getElementById('formDataInput').value = JSON.stringify(questions); }

function esc(s) { var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }

function renderQ() {
    var c = document.getElementById('qContainer');
    if (questions.length === 0) {
        c.innerHTML = '<div style="text-align:center;padding:20px;color:#94a3b8;font-size:13px;">Belum ada pertanyaan</div>';
        return;
    }
    var html = '';
    for (var i=0; i<questions.length; i++) {
        var q = questions[i];
        var hasOpt = q.type==='multiple'||q.type==='checkbox'||q.type==='dropdown';
        html += '<div class="pf-q-card">';
        html += '<button type="button" class="pf-q-remove" onclick="hapusPertanyaan('+i+')"><i class="bi bi-x"></i></button>';
        html += '<div style="display:flex;align-items:start;gap:8px;margin-bottom:8px;">';
        html += '<span style="min-width:24px;height:24px;border-radius:8px;background:#246bfe;color:#fff;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;">'+(i+1)+'</span>';
        html += '<input type="text" class="pf-input" style="padding:10px 12px;font-size:14px;font-weight:600;" placeholder="Tulis pertanyaan..." value="'+esc(q.text)+'" oninput="ubahTeks('+i+',this.value)">';
        html += '</div>';
        html += '<div class="pf-q-type-row">';
        for (var j=0; j<qTypes.length; j++) {
            html += '<button type="button" class="pf-q-type-btn'+(q.type===qTypes[j].k?' active':'')+'" onclick="ubahTipe('+i+',\''+qTypes[j].k+'\')">'+qTypes[j].l+'</button>';
        }
        html += '</div>';
        if (hasOpt) {
            html += '<div style="margin-top:10px;">';
            for (var oi=0; oi<q.options.length; oi++) {
                html += '<div class="pf-opt-row">';
                html += '<span style="width:18px;text-align:center;color:#94a3b8;font-size:11px;font-weight:700;">'+String.fromCharCode(65+oi)+'</span>';
                html += '<input type="text" placeholder="Opsi '+(oi+1)+'" value="'+esc(q.options[oi])+'" oninput="ubahOpsi('+i+','+oi+',this.value)">';
                html += '<button type="button" class="pf-opt-rm" onclick="hapusOpsi('+i+','+oi+')"'+(q.options.length<=2?' disabled style="opacity:0.3"':'')+'><i class="bi bi-x"></i></button>';
                html += '</div>';
            }
            html += '<button type="button" onclick="tambahOpsi('+i+')" style="width:100%;border:1px dashed #cbd5e1;background:transparent;border-radius:8px;padding:6px;font-size:11px;font-weight:600;color:#64748b;cursor:pointer;margin-top:4px;">+ Tambah opsi</button>';
            html += '</div>';
        }
        html += '<div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;padding-top:8px;border-top:1px solid #e8ecf1;">';
        html += '<span style="font-size:11px;color:#94a3b8;">Wajib diisi</span>';
        html += '<label style="position:relative;width:40px;height:22px;cursor:pointer;">';
        html += '<input type="checkbox"'+(q.required?' checked':'')+' onchange="ubahWajib('+i+')" style="opacity:0;width:0;height:0;">';
        html += '<span style="position:absolute;inset:0;background:'+(q.required?'#246bfe':'#e2e8f0')+';border-radius:99px;transition:0.2s;"></span>';
        html += '<span style="position:absolute;width:16px;height:16px;left:'+(q.required?'21':'3')+'px;top:3px;background:#fff;border-radius:50%;transition:0.2s;box-shadow:0 1px 2px rgba(0,0,0,0.15);"></span>';
        html += '</label></div></div>';
    }
    c.innerHTML = html;
}

function previewLampiran(input) {
    if (input.files && input.files[0]) {
        var f = input.files[0];
        var sz = (f.size/1024/1024).toFixed(1);
        document.getElementById('lampiranZone').classList.add('has-file');
        document.getElementById('lampiranPreview').innerHTML = '<i class="bi bi-file-earmark-check-fill" style="font-size:28px;color:#16a34a;"></i><div style="font-size:13px;font-weight:600;color:#15803d;margin-top:8px;word-break:break-all;">'+f.name+'</div><div style="font-size:11px;color:#94a3b8;margin-top:2px;">'+sz+' MB</div>';
    }
}

document.getElementById('tugasForm').addEventListener('submit', function(e) {
    var tipe = document.getElementById('tipeInput').value;
    if (tipe === 'form') {
        if (questions.length === 0) { e.preventDefault(); alert('Tambahkan minimal satu pertanyaan.'); return; }
        for (var i=0; i<questions.length; i++) {
            if (!questions[i].text.trim()) { e.preventDefault(); alert('Pertanyaan ke-'+(i+1)+' belum diisi.'); return; }
        }
        syncFD();
    }
});

initQuestions();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\tugas-form.blade.php ENDPATH**/ ?>