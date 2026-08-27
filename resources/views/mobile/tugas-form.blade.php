@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
@php
    $isEdit = $tugas->exists;
    $existingForm = old('form_data', $isEdit && $tugas->form_data ? json_encode($tugas->form_data) : '[]');
@endphp

<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.92); backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(226,232,240,0.7);
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 70px; padding-bottom: 48px; }

    .form-card {
        background: #fff; border: none; border-radius: 24px;
        box-shadow: 0 8px 28px rgba(0,0,0,0.04);
        overflow: hidden; margin-bottom: 16px;
    }
    .form-card-header {
        padding: 20px 20px 0;
    }
    .form-card-body {
        padding: 16px 20px 20px;
    }

    .type-selector {
        display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
    }
    .type-option {
        border: 2px solid #e2e8f0; border-radius: 20px; padding: 20px 16px;
        text-align: center; cursor: pointer; transition: all 0.25s ease;
        background: #fafbfc; position: relative;
    }
    .type-option:hover { border-color: #94b8ff; background: #f0f5ff; }
    .type-option.selected {
        border-color: #246bfe; background: linear-gradient(135deg, #eef4ff, #f8faff);
        box-shadow: 0 4px 16px rgba(36,107,254,0.12);
    }
    .type-option.selected::after {
        content: '\F26A'; font-family: 'bootstrap-icons'; position: absolute;
        top: 10px; right: 10px; width: 22px; height: 22px; border-radius: 50%;
        background: #246bfe; color: #fff; font-size: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .type-icon {
        width: 52px; height: 52px; border-radius: 16px; margin: 0 auto 10px;
        display: flex; align-items: center; justify-content: center; font-size: 24px;
    }
    .type-option:nth-child(1) .type-icon { background: #eef4ff; color: #246bfe; }
    .type-option:nth-child(2) .type-icon { background: #f0fdf4; color: #16a34a; }

    .question-card {
        background: #f8fafc; border: 1px solid #e8ecf1; border-radius: 20px;
        padding: 18px; margin-bottom: 12px; position: relative;
        transition: all 0.2s ease;
    }
    .question-card:hover { border-color: #cbd5e1; }
    .question-card .q-number {
        width: 28px; height: 28px; border-radius: 10px;
        background: #246bfe; color: #fff; font-size: 12px; font-weight: 800;
        display: inline-flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .question-card .q-remove {
        position: absolute; top: 12px; right: 12px;
        width: 30px; height: 30px; border-radius: 10px;
        border: none; background: #fff5f6; color: #d94b61;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 14px; transition: all 0.2s;
    }
    .question-card .q-remove:hover { background: #d94b61; color: #fff; }

    .q-type-selector {
        display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px;
    }
    .q-type-btn {
        border: 1px solid #e2e8f0; background: #fff; border-radius: 10px;
        padding: 6px 12px; font-size: 11px; font-weight: 700;
        cursor: pointer; transition: all 0.2s; color: #64748b;
    }
    .q-type-btn:hover { border-color: #246bfe; color: #246bfe; }
    .q-type-btn.active { background: #246bfe; color: #fff; border-color: #246bfe; }

    .option-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 6px;
    }
    .option-row input {
        flex: 1; border: 1px solid #e2e8f0; border-radius: 10px;
        padding: 8px 12px; font-size: 13px; background: #fff;
    }
    .option-row input:focus { outline: none; border-color: #246bfe; box-shadow: 0 0 0 3px rgba(36,107,254,0.1); }
    .option-remove {
        width: 28px; height: 28px; border-radius: 8px; border: none;
        background: #fee2e2; color: #dc2626; cursor: pointer;
        display: flex; align-items: center; justify-content: center; font-size: 12px;
    }

    .add-option-btn {
        border: 1px dashed #cbd5e1; background: transparent; border-radius: 10px;
        padding: 8px 14px; font-size: 12px; font-weight: 700; color: #64748b;
        cursor: pointer; width: 100%; text-align: left; margin-top: 4px;
    }
    .add-option-btn:hover { border-color: #246bfe; color: #246bfe; }

    .add-question-btn {
        border: 2px dashed #cbd5e1; background: transparent; border-radius: 20px;
        padding: 18px; font-size: 14px; font-weight: 700; color: #64748b;
        cursor: pointer; width: 100%; text-align: center;
        transition: all 0.2s;
    }
    .add-question-btn:hover { border-color: #246bfe; color: #246bfe; background: #f8faff; }

    .file-drop-zone {
        border: 2px dashed #cbd5e1; border-radius: 20px; padding: 28px 20px;
        text-align: center; cursor: pointer; transition: all 0.25s;
        background: #fafbfc;
    }
    .file-drop-zone:hover, .file-drop-zone.dragover {
        border-color: #246bfe; background: #f0f5ff;
    }
    .file-drop-zone.has-file {
        border-color: #16a34a; background: #f0fdf4; border-style: solid;
    }

    .section-divider {
        height: 1px; background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        margin: 8px 0;
    }

    .toggle-switch {
        position: relative; width: 44px; height: 24px; flex-shrink: 0;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; inset: 0; background: #e2e8f0; border-radius: 99px;
        cursor: pointer; transition: 0.3s;
    }
    .toggle-slider::before {
        content: ''; position: absolute; width: 18px; height: 18px;
        left: 3px; bottom: 3px; background: #fff; border-radius: 50%;
        transition: 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .toggle-switch input:checked + .toggle-slider { background: #246bfe; }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }

    .submit-area {
        position: sticky; bottom: 0; left: 0; right: 0;
        background: rgba(255,255,255,0.92); backdrop-filter: blur(16px);
        border-top: 1px solid #edf2f7; padding: 16px 20px;
        z-index: 100;
    }
</style>

<div class="page-header">
    <a href="{{ $isEdit ? route('tugas.show', $tugas) : route('tugas.index') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px;">{{ $isEdit ? 'Edit Tugas' : 'Buat Tugas Baru' }}</div>
</div>

<div class="page-container px-3 pt-3">
    <form method="POST" action="{{ $isEdit ? route('tugas.update', $tugas) : route('tugas.store') }}" enctype="multipart/form-data" id="tugasForm">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- Section: Judul & Deskripsi --}}
        <div class="form-card">
            <div class="form-card-header">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div style="width:32px;height:32px;border-radius:10px;background:#eef4ff;color:#246bfe;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-pencil-square" style="font-size:16px;"></i>
                    </div>
                    <span class="fw-bold" style="font-size:15px;">Detail Tugas</span>
                </div>
            </div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label x-small fw-bold text-muted mb-1">JUDUL TUGAS *</label>
                    <input type="text" name="judul" class="form-control border-0 shadow-sm" style="border-radius:14px;background:#f8fafc;font-size:15px;font-weight:600;" placeholder="Contoh: Tugas Matematika Bab 5" value="{{ old('judul', $tugas->judul) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label x-small fw-bold text-muted mb-1">DESKRIPSI / INSTRUKSI</label>
                    <textarea name="deskripsi" rows="3" class="form-control border-0 shadow-sm" style="border-radius:14px;background:#f8fafc;font-size:13px;line-height:1.6;" placeholder="Tuliskan instruksi, konteks, atau panduan pengerjaan...">{{ old('deskripsi', $tugas->deskripsi) }}</textarea>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label x-small fw-bold text-muted mb-1">KELAS *</label>
                        <select name="kelas_id" class="form-select border-0 shadow-sm" style="border-radius:14px;background:#f8fafc;font-size:13px;" required>
                            <option value="">Pilih kelas</option>
                            @foreach($kelases as $k)
                                <option value="{{ $k->id }}" @selected(old('kelas_id', $tugas->kelas_id) == $k->id)>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label x-small fw-bold text-muted mb-1">BATAS PENGUMPULAN</label>
                        <input type="date" name="batas_pengumpulan" class="form-control border-0 shadow-sm" style="border-radius:14px;background:#f8fafc;font-size:13px;" value="{{ old('batas_pengumpulan', $tugas->batas_pengumpulan?->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Tipe Tugas --}}
        <div class="form-card">
            <div class="form-card-header">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div style="width:32px;height:32px;border-radius:10px;background:#f0fdf4;color:#16a34a;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-grid-3x3-gap" style="font-size:16px;"></i>
                    </div>
                    <span class="fw-bold" style="font-size:15px;">Tipe Pengumpulan</span>
                </div>
                <p class="x-small text-muted mb-0">Pilih cara siswa mengumpulkan tugas</p>
            </div>
            <div class="form-card-body">
                <input type="hidden" name="tipe" id="tipeInput" value="{{ old('tipe', $tugas->tipe ?? 'file') }}">
                <div class="type-selector">
                    <div class="type-option {{ old('tipe', $tugas->tipe ?? 'file') === 'file' ? 'selected' : '' }}" data-type="file" onclick="selectType('file')">
                        <div class="type-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                        <div class="fw-bold" style="font-size:13px;">Upload File</div>
                        <div class="x-small text-muted mt-1">PDF, Word, Excel, Gambar</div>
                    </div>
                    <div class="type-option {{ old('tipe', $tugas->tipe) === 'form' ? 'selected' : '' }}" data-type="form" onclick="selectType('form')">
                        <div class="type-icon"><i class="bi bi-ui-checks-grid"></i></div>
                        <div class="fw-bold" style="font-size:13px;">Formulir Online</div>
                        <div class="x-small text-muted mt-1">Seperti Google Forms</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Lampiran Tugas --}}
        <div class="form-card">
            <div class="form-card-header">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div style="width:32px;height:32px;border-radius:10px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-paperclip" style="font-size:16px;"></i>
                    </div>
                    <span class="fw-bold" style="font-size:15px;">Lampiran Tugas</span>
                </div>
                <p class="x-small text-muted mb-0">File pendukung yang disertakan bersama tugas (opsional)</p>
            </div>
            <div class="form-card-body">
                @if($isEdit && $tugas->lampiran)
                    <div class="d-flex align-items-center justify-content-between p-3 mb-3 rounded-4" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-check-fill text-success" style="font-size:20px;"></i>
                            <div>
                                <div class="small fw-bold text-truncate" style="max-width:160px;">{{ $tugas->lampiran_nama }}</div>
                                <div class="x-small text-muted">File saat ini</div>
                            </div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="hapus_lampiran" id="hapusLampiran" value="1">
                            <label class="form-check-label x-small text-danger" for="hapusLampiran">Hapus</label>
                        </div>
                    </div>
                @endif
                <div class="file-drop-zone" id="lampiranZone" onclick="document.getElementById('lampiranInput').click()">
                    <input type="file" name="lampiran" id="lampiranInput" class="d-none" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.xls,.ppt,.pptx,.csv,.txt,.zip" onchange="handleLampiran(this)">
                    <div id="lampiranPreview">
                        <i class="bi bi-cloud-arrow-up" style="font-size:36px;color:#94a3b8;"></i>
                        <div class="fw-bold mt-2" style="font-size:14px;color:#475569;">Tap untuk upload file</div>
                        <div class="x-small text-muted mt-1">PDF, Word, Excel, PPT, Gambar, ZIP (Maks 10MB)</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Form Builder (hanya tampil jika tipe = form) --}}
        <div id="formBuilderSection" style="display: {{ old('tipe', $tugas->tipe ?? 'file') === 'form' ? 'block' : 'none' }};">
            <div class="form-card">
                <div class="form-card-header">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div style="width:32px;height:32px;border-radius:10px;background:#ede9fe;color:#7c3aed;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-question-circle" style="font-size:16px;"></i>
                        </div>
                        <span class="fw-bold" style="font-size:15px;">Pertanyaan Formulir</span>
                    </div>
                    <p class="x-small text-muted mb-0">Susun pertanyaan seperti membuat Google Forms</p>
                </div>
                <div class="form-card-body">
                    <input type="hidden" name="form_data" id="formDataInput" value="{{ $existingForm }}">
                    <div id="questionsContainer"></div>
                    <button type="button" class="add-question-btn mt-2" onclick="addQuestion()">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Pertanyaan
                    </button>
                </div>
            </div>
        </div>

        <div style="height: 80px;"></div>
    </form>
</div>

{{-- Sticky Submit --}}
<div class="submit-area">
    <div class="d-flex gap-2">
        <a href="{{ $isEdit ? route('tugas.show', $tugas) : route('tugas.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">Batal</a>
        <button type="submit" form="tugasForm" class="btn btn-primary rounded-pill flex-grow-1 py-2 fw-bold" style="font-size:15px;">
            <i class="bi bi-send-fill me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Terbitkan Tugas' }}
        </button>
    </div>
</div>

<script>
const questionTypes = [
    { key: 'text', label: 'Jawaban Singkat', icon: 'bi-type' },
    { key: 'essay', label: 'Esai', icon: 'bi-text-paragraph' },
    { key: 'multiple', label: 'Pilihan Ganda', icon: 'bi-record-circle' },
    { key: 'checkbox', label: 'Kotak Centang', icon: 'bi-check-square' },
    { key: 'dropdown', label: 'Dropdown', icon: 'bi-chevron-down' },
];

let questions = [];

function init() {
    try {
        const raw = document.getElementById('formDataInput').value;
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed) && parsed.length > 0) {
            questions = parsed.map((q, i) => ({
                text: q.text || '',
                type: q.type || 'text',
                options: Array.isArray(q.options) ? [...q.options] : [],
                required: q.required !== false,
            }));
        }
    } catch(e) {}
    if (questions.length === 0) {
        questions = [];
    }
    renderQuestions();
}

function selectType(type) {
    document.getElementById('tipeInput').value = type;
    document.querySelectorAll('.type-option').forEach(el => {
        el.classList.toggle('selected', el.dataset.type === type);
    });
    document.getElementById('formBuilderSection').style.display = type === 'form' ? 'block' : 'none';
}

function addQuestion() {
    questions.push({ text: '', type: 'text', options: ['Opsi 1', 'Opsi 2'], required: true });
    renderQuestions();
    syncFormData();
    const container = document.getElementById('questionsContainer');
    container.lastElementChild?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function removeQuestion(index) {
    questions.splice(index, 1);
    renderQuestions();
    syncFormData();
}

function changeType(index, type) {
    questions[index].type = type;
    renderQuestions();
    syncFormData();
}

function updateText(index, value) {
    questions[index].text = value;
    syncFormData();
}

function toggleRequired(index) {
    questions[index].required = !questions[index].required;
    renderQuestions();
    syncFormData();
}

function addOption(qIndex) {
    questions[qIndex].options.push('Opsi ' + (questions[qIndex].options.length + 1));
    renderQuestions();
    syncFormData();
}

function removeOption(qIndex, oIndex) {
    if (questions[qIndex].options.length <= 2) return;
    questions[qIndex].options.splice(oIndex, 1);
    renderQuestions();
    syncFormData();
}

function updateOption(qIndex, oIndex, value) {
    questions[qIndex].options[oIndex] = value;
    syncFormData();
}

function syncFormData() {
    document.getElementById('formDataInput').value = JSON.stringify(questions);
}

function renderQuestions() {
    const container = document.getElementById('questionsContainer');
    if (questions.length === 0) {
        container.innerHTML = '<div class="text-center py-4"><i class="bi bi-question-circle" style="font-size:32px;color:#cbd5e1;"></i><div class="small text-muted mt-2">Belum ada pertanyaan. Tap tombol di bawah untuk menambahkan.</div></div>';
        return;
    }

    container.innerHTML = questions.map((q, i) => {
        const hasOptions = ['multiple', 'checkbox', 'dropdown'].includes(q.type);
        return `
        <div class="question-card">
            <button type="button" class="q-remove" onclick="removeQuestion(${i})" title="Hapus pertanyaan"><i class="bi bi-x-lg"></i></button>
            <div class="d-flex align-items-start gap-2 mb-3">
                <span class="q-number">${i + 1}</span>
                <input type="text" class="form-control border-0 shadow-sm" style="border-radius:12px;background:#fff;font-size:14px;font-weight:600;" placeholder="Tulis pertanyaan..." value="${escapeHtml(q.text)}" oninput="updateText(${i}, this.value)">
            </div>

            <div class="q-type-selector">
                ${questionTypes.map(t => `
                    <button type="button" class="q-type-btn ${q.type === t.key ? 'active' : ''}" onclick="changeType(${i}, '${t.key}')">
                        <i class="${t.icon} me-1"></i>${t.label}
                    </button>
                `).join('')}
            </div>

            ${hasOptions ? `
                <div class="mt-3">
                    <div class="x-small fw-bold text-muted mb-2">PILIHAN JAWABAN</div>
                    ${q.options.map((opt, oi) => `
                        <div class="option-row">
                            <span style="width:20px;text-align:center;color:#94a3b8;font-size:12px;font-weight:700;">${String.fromCharCode(65 + oi)}</span>
                            <input type="text" placeholder="Opsi ${oi + 1}" value="${escapeHtml(opt)}" oninput="updateOption(${i}, ${oi}, this.value)">
                            <button type="button" class="option-remove" onclick="removeOption(${i}, ${oi})" ${q.options.length <= 2 ? 'disabled style="opacity:0.4;cursor:not-allowed;"' : ''}>
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    `).join('')}
                    <button type="button" class="add-option-btn" onclick="addOption(${i})">
                        <i class="bi bi-plus me-1"></i> Tambah opsi
                    </button>
                </div>
            ` : ''}

            <div class="d-flex align-items-center justify-content-between mt-3 pt-2" style="border-top:1px solid #e8ecf1;">
                <span class="x-small text-muted">Wajib diisi</span>
                <label class="toggle-switch">
                    <input type="checkbox" ${q.required ? 'checked' : ''} onchange="toggleRequired(${i})">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>`;
    }).join('');
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function handleLampiran(input) {
    const zone = document.getElementById('lampiranZone');
    const preview = document.getElementById('lampiranPreview');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const ext = file.name.split('.').pop().toLowerCase();
        const icons = { pdf: 'bi-file-earmark-pdf-fill', doc: 'bi-file-earmark-word-fill', docx: 'bi-file-earmark-word-fill', xlsx: 'bi-file-earmark-excel-fill', xls: 'bi-file-earmark-excel-fill', ppt: 'bi-file-earmark-ppt-fill', pptx: 'bi-file-earmark-ppt-fill', zip: 'bi-file-earmark-zip-fill', jpg: 'bi-file-earmark-image-fill', jpeg: 'bi-file-earmark-image-fill', png: 'bi-file-earmark-image-fill', csv: 'bi-file-earmark-spreadsheet-fill', txt: 'bi-file-earmark-text-fill' };
        const icon = icons[ext] || 'bi-file-earmark-fill';
        const size = (file.size / 1024 / 1024).toFixed(1);
        zone.classList.add('has-file');
        preview.innerHTML = `
            <i class="bi ${icon}" style="font-size:36px;color:#16a34a;"></i>
            <div class="fw-bold mt-2" style="font-size:14px;color:#15803d;">${file.name}</div>
            <div class="x-small text-muted mt-1">${size} MB - Tap untuk ganti file</div>
        `;
    }
}

document.getElementById('tugasForm').addEventListener('submit', function(e) {
    const tipe = document.getElementById('tipeInput').value;
    if (tipe === 'form') {
        if (questions.length === 0) {
            e.preventDefault();
            alert('Tambahkan minimal satu pertanyaan untuk tipe Formulir Online.');
            return;
        }
        for (let i = 0; i < questions.length; i++) {
            if (!questions[i].text.trim()) {
                e.preventDefault();
                alert('Pertanyaan ke-' + (i + 1) + ' belum diisi teksnya.');
                return;
            }
        }
        syncFormData();
    }
});

init();
</script>
@endsection
