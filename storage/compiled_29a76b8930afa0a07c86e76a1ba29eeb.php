<?php $__env->startSection('content'); ?>
<div class="mb-4">
    <a href="<?php echo e(route('tugas.index')); ?>" class="text-decoration-none">&larr; Kembali</a>
    <h1 class="h3 fw-bold mt-3">Buat Tugas Baru</h1>
    <p class="text-secondary small mb-0">
        Pilih <b>Pengiriman File</b> untuk tugas biasa, atau <b>Formulir Online</b> untuk membuat kuis/kuesioner
        gaya Google Forms yang dikerjakan siswa langsung di aplikasi.
    </p>
</div>

<div class="card form-card">
    <div class="card-body p-4">
        <form method="POST" action="<?php echo e(route('tugas.store')); ?>" enctype="multipart/form-data" id="tugasForm" novalidate>
            <?php echo csrf_field(); ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger border-0 rounded-4">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Periksa kembali isian Anda:</div>
                    <ul class="mb-0 small">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-md-7">
                    <div class="mb-3">
                        <label class="form-label">Judul tugas</label>
                        <input name="judul" value="<?php echo e(old('judul')); ?>" class="form-control" placeholder="Contoh: Ulangan Harian Bab 1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-select" required>
                            <option value="">Pilih kelas</option>
                            <?php $__currentLoopData = $kelases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kelas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($kelas->id); ?>" <?php if(old('kelas_id') == $kelas->id): echo 'selected'; endif; ?>><?php echo e($kelas->nama); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi / Instruksi</label>
                        <textarea name="deskripsi" rows="4" class="form-control" placeholder="Tulis instruksi pengerjaan tugas di sini..."><?php echo e(old('deskripsi')); ?></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipe Tugas</label>
                            <select name="tipe" class="form-select" id="tipeTugas" required>
                                <option value="file" <?php if(old('tipe', 'file') === 'file'): echo 'selected'; endif; ?>>Pengiriman File (PDF/Gambar)</option>
                                <option value="form" <?php if(old('tipe') === 'form'): echo 'selected'; endif; ?>>Formulir Online (Google Form Style)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Batas Pengumpulan</label>
                            <input name="batas_pengumpulan" type="date" value="<?php echo e(old('batas_pengumpulan')); ?>" class="form-control">
                        </div>
                    </div>

                    <div class="mt-4" id="fileUploadArea">
                        <label class="form-label fw-bold">Lampiran Tugas (Opsional)</label>
                        <input name="lampiran" type="file" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        <div class="small text-secondary mt-1">Siswa akan menerima notifikasi email jika lampiran berupa PDF.</div>
                    </div>
                </div>

                <div class="col-md-5" id="formBuilderArea" style="display:none; border-left: 1px solid #eee; padding-left: 25px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label fw-bold mb-0">Form Builder</label>
                        <span class="badge bg-primary-subtle text-primary rounded-pill" id="questionCount">0 Pertanyaan</span>
                    </div>

                    <div class="alert alert-info border-0 rounded-4 x-small py-2 px-3 mb-3">
                        <i class="bi bi-lightbulb-fill me-1"></i>
                        Siswa mengerjakan langsung di aplikasi seperti Google Forms. Jawaban terekap otomatis untuk penilaian.
                    </div>

                    <div id="questionsContainer"></div>
                    <div id="builderError" class="alert alert-danger border-0 rounded-4 x-small py-2 px-3 mt-2" style="display:none;"></div>

                    <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-3" id="addQuestionBtn">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Pertanyaan
                    </button>

                    <input type="hidden" name="form_data" id="formDataInput">
                </div>
            </div>

            <hr class="my-4">
            <button class="btn btn-primary px-5 py-2 fw-bold" style="border-radius: 10px;">Terbitkan Tugas Sekarang</button>
        </form>
    </div>
</div>

<template id="questionTemplate">
    <div class="question-card mb-3 p-3 border rounded-4 shadow-sm bg-light">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill question-number">1</span>
                <span class="x-small fw-bold text-secondary">PERTANYAAN</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="form-check form-switch mb-0" title="Tandai wajib diisi">
                    <input class="form-check-input question-required" type="checkbox" role="switch" checked>
                    <label class="form-check-label x-small fw-bold text-secondary">Wajib</label>
                </div>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary border-0 move-up" title="Naikkan"><i class="bi bi-arrow-up"></i></button>
                    <button type="button" class="btn btn-outline-secondary border-0 move-down" title="Turunkan"><i class="bi bi-arrow-down"></i></button>
                </div>
                <button type="button" class="btn-close remove-question" style="font-size: 0.7rem;" title="Hapus pertanyaan"></button>
            </div>
        </div>
        <input type="text" class="form-control form-control-sm mb-2 question-text fw-bold" placeholder="Tulis pertanyaan...">
        <select class="form-select form-select-sm mb-2 question-type">
            <option value="text">Jawaban Singkat</option>
            <option value="essay">Paragraf / Esai</option>
            <option value="multiple">Pilihan Ganda (Satu Jawaban)</option>
            <option value="checkbox">Kotak Centang (Banyak Jawaban)</option>
            <option value="dropdown">Dropdown</option>
        </select>
        <div class="options-container" style="display:none;">
            <div class="options-list mb-2"></div>
            <button type="button" class="btn btn-link btn-sm p-0 add-option">+ Tambah Opsi</button>
        </div>
    </div>
</template>

<script>
(function () {
    const QUESTION_TYPES = ['text', 'essay', 'multiple', 'checkbox', 'dropdown'];
    const container = document.getElementById('questionsContainer');
    const template = document.getElementById('questionTemplate');
    const typeSelect = document.getElementById('tipeTugas');
    const builderArea = document.getElementById('formBuilderArea');
    const fileArea = document.getElementById('fileUploadArea');
    const errorBox = document.getElementById('builderError');
    const countBadge = document.getElementById('questionCount');

    function showError(message) {
        errorBox.innerText = message;
        errorBox.style.display = 'block';
        errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function hideError() { errorBox.style.display = 'none'; }

    function refreshNumbers() {
        const cards = container.querySelectorAll('.question-card');
        cards.forEach((card, i) => {
            card.querySelector('.question-number').innerText = i + 1;
            card.querySelector('.move-up').disabled = i === 0;
            card.querySelector('.move-down').disabled = i === cards.length - 1;
        });
        countBadge.innerText = cards.length + ' Pertanyaan';
    }

    function wireOptionRow(card, optDiv) {
        optDiv.querySelector('.remove-option').onclick = () => {
            optDiv.remove();
            refreshOptionsHint(card);
        };
        optDiv.querySelector('.option-text').addEventListener('input', hideError);
    }

    function addOption(card, value = '') {
        const list = card.querySelector('.options-list');
        const optDiv = document.createElement('div');
        optDiv.className = 'd-flex gap-2 mb-1';
        optDiv.innerHTML = `<input type="text" class="form-control form-control-sm option-text" placeholder="Opsi..." value=""><button type="button" class="btn-close remove-option" style="font-size: 0.6rem; margin-top: 8px;" title="Hapus opsi"></button>`;
        optDiv.querySelector('.option-text').value = value;
        wireOptionRow(card, optDiv);
        list.appendChild(optDiv);
        refreshOptionsHint(card);
    }

    function refreshOptionsHint(card) {
        const hint = card.querySelector('.options-hint');
        if (hint) hint.remove();
        const count = card.querySelectorAll('.option-text').length;
        if (count > 0 && count < 2) {
            const div = document.createElement('div');
            div.className = 'x-small text-warning options-hint';
            div.innerText = 'Minimal 2 opsi jawaban diperlukan.';
            card.querySelector('.options-list').after(div);
        }
    }

    function addQuestion(data = {}) {
        const node = template.content.cloneNode(true);
        const card = node.querySelector('.question-card');
        container.appendChild(node);

        card.querySelector('.question-text').value = data.text || '';
        card.querySelector('.question-type').value = QUESTION_TYPES.includes(data.type) ? data.type : 'text';
        card.querySelector('.question-required').checked = data.required !== false;

        const typeSel = card.querySelector('.question-type');
        const optionsBox = card.querySelector('.options-container');

        const syncOptions = () => {
            optionsBox.style.display = ['multiple', 'checkbox', 'dropdown'].includes(typeSel.value) ? 'block' : 'none';
        };
        typeSel.addEventListener('change', syncOptions);
        syncOptions();

        (data.options || []).forEach(opt => addOption(card, opt));
        if (['multiple', 'checkbox', 'dropdown'].includes(typeSel.value) && !data.options?.length) {
            addOption(card); addOption(card);
        }

        card.querySelector('.add-option').addEventListener('click', () => addOption(card));
        card.querySelector('.remove-question').addEventListener('click', () => { card.remove(); refreshNumbers(); hideError(); });
        card.querySelector('.question-text').addEventListener('input', hideError);

        card.querySelector('.move-up').addEventListener('click', () => {
            const prev = card.previousElementSibling;
            if (prev) container.insertBefore(card, prev);
            refreshNumbers();
        });
        card.querySelector('.move-down').addEventListener('click', () => {
            const next = card.nextElementSibling;
            if (next) container.insertBefore(next, card);
            refreshNumbers();
        });

        refreshNumbers();
    }

    function collectQuestions() {
        return Array.from(container.querySelectorAll('.question-card')).map(card => ({
            text: card.querySelector('.question-text').value.trim(),
            type: card.querySelector('.question-type').value,
            required: card.querySelector('.question-required').checked,
            options: Array.from(card.querySelectorAll('.option-text'))
                .map(input => input.value.trim())
                .filter(Boolean)
        }));
    }

    function syncBuilderVisibility() {
        const isForm = typeSelect.value === 'form';
        builderArea.style.display = isForm ? 'block' : 'none';
        fileArea.style.display = isForm ? 'none' : 'block';
    }
    typeSelect.addEventListener('change', syncBuilderVisibility);

    document.getElementById('addQuestionBtn').addEventListener('click', () => { addQuestion(); hideError(); });

    document.getElementById('tugasForm').addEventListener('submit', function (e) {
        hideError();

        // Validasi dasar HTML5 tetap dijalankan untuk field non-builder.
        if (!this.checkValidity()) {
            this.reportValidity();
            return;
        }

        if (typeSelect.value !== 'form') return;

        const questions = collectQuestions();

        if (questions.length === 0) {
            e.preventDefault();
            showError('Tipe Formulir Online memerlukan minimal satu pertanyaan. Klik "Tambah Pertanyaan" untuk memulai.');
            return;
        }

        for (let i = 0; i < questions.length; i++) {
            const q = questions[i];
            if (!q.text) {
                e.preventDefault();
                showError('Pertanyaan ke-' + (i + 1) + ' belum ditulis. Lengkapi teks pertanyaannya.');
                return;
            }
            if (['multiple', 'checkbox', 'dropdown'].includes(q.type) && q.options.length < 2) {
                e.preventDefault();
                showError('Pertanyaan ke-' + (i + 1) + ' bertipe pilihan dan memerlukan minimal 2 opsi jawaban terisi.');
                return;
            }
        }

        document.getElementById('formDataInput').value = JSON.stringify(questions);
    });

    // Pulihkan pertanyaan bila validasi server gagal (old input).
    const initial = <?php echo json_encode(old('form_data'), 15, 512) ?>;
    if (typeof initial === 'string' && initial) {
        try {
            const parsed = JSON.parse(initial);
            if (Array.isArray(parsed)) parsed.forEach(q => addQuestion(q));
        } catch (_) {}
    } else if (Array.isArray(initial)) {
        initial.forEach(q => addQuestion(q));
    }
    if (!container.children.length && typeSelect.value === 'form') {
        addQuestion();
    }
    syncBuilderVisibility();
})();
</script>

<style>
    .form-card { border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
    .btn-primary { border-radius: 10px; padding: 10px 30px; }
    .question-card { border-color: #e9ecef !important; }
    .x-small { font-size: 11px; }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>