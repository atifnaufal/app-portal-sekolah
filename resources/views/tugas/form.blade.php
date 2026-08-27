@extends('layouts.app')
@section('content')
<div class="mb-4">
    <a href="{{ route('tugas.index') }}" class="text-decoration-none">&larr; Kembali</a>
    <h1 class="h3 fw-bold mt-3">Buat Tugas Pro</h1>
</div>

<div class="card form-card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('tugas.store') }}" enctype="multipart/form-data" id="tugasForm">
            @csrf
            <div class="row g-4">
                <div class="col-md-7">
                    <div class="mb-3">
                        <label class="form-label">Judul tugas</label>
                        <input name="judul" class="form-control" placeholder="Contoh: Ulangan Harian Bab 1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-select" required>
                            <option value="">Pilih kelas</option>
                            @foreach($kelases as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi / Instruksi</label>
                        <textarea name="deskripsi" rows="4" class="form-control" placeholder="Tulis instruksi pengerjaan tugas di sini..."></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipe Tugas</label>
                            <select name="tipe" class="form-select" id="tipeTugas" required>
                                <option value="file">Pengiriman File (PDF/Gambar)</option>
                                <option value="form">Formulir Online (Google Form Style)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Batas Pengumpulan</label>
                            <input name="batas_pengumpulan" type="date" class="form-control">
                        </div>
                    </div>

                    <div class="mt-4" id="fileUploadArea">
                        <label class="form-label fw-bold">Lampiran Tugas (Opsional)</label>
                        <input name="lampiran" type="file" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        <div class="small text-secondary mt-1">Siswa akan menerima notifikasi email jika lampiran berupa PDF.</div>
                    </div>
                </div>

                <div class="col-md-5" id="formBuilderArea" style="display:none; border-left: 1px solid #eee; padding-left: 25px;">
                    <label class="form-label fw-bold mb-3">Form Builder</label>
                    <div id="questionsContainer"></div>

                    <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-3" id="addQuestionBtn">
                        + Tambah Pertanyaan
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
    <div class="question-card mb-3 p-3 border rounded shadow-sm bg-light">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="badge bg-secondary">Pertanyaan</span>
            <button type="button" class="btn-close remove-question" style="font-size: 0.7rem;"></button>
        </div>
        <input type="text" class="form-control form-control-sm mb-2 question-text" placeholder="Tulis pertanyaan..." required>
        <select class="form-select form-select-sm mb-2 question-type">
            <option value="text">Jawaban Singkat</option>
            <option value="essay">Esai Panjang</option>
            <option value="multiple">Pilihan Ganda</option>
        </select>
        <div class="options-container" style="display:none;">
            <div class="options-list mb-2"></div>
            <button type="button" class="btn btn-link btn-sm p-0 add-option">+ Tambah Opsi</button>
        </div>
    </div>
</template>

<script>
document.getElementById('tipeTugas').addEventListener('change', function() {
    const isForm = this.value === 'form';
    document.getElementById('formBuilderArea').style.display = isForm ? 'block' : 'none';
    document.getElementById('fileUploadArea').style.opacity = isForm ? '0.5' : '1';
});

document.getElementById('addQuestionBtn').addEventListener('click', function() {
    const container = document.getElementById('questionsContainer');
    const template = document.getElementById('questionTemplate').content.cloneNode(true);

    const questionCard = template.querySelector('.question-card');
    const typeSelect = questionCard.querySelector('.question-type');
    const optionsContainer = questionCard.querySelector('.options-container');
    const addOptionBtn = questionCard.querySelector('.add-option');
    const removeBtn = questionCard.querySelector('.remove-question');

    typeSelect.addEventListener('change', function() {
        optionsContainer.style.display = this.value === 'multiple' ? 'block' : 'none';
    });

    addOptionBtn.addEventListener('click', function() {
        const list = questionCard.querySelector('.options-list');
        const optDiv = document.createElement('div');
        optDiv.className = 'd-flex gap-2 mb-1';
        optDiv.innerHTML = `<input type="text" class="form-control form-control-sm option-text" placeholder="Opsi..." required><button type="button" class="btn-close remove-option" style="font-size: 0.6rem; margin-top: 8px;"></button>`;
        optDiv.querySelector('.remove-option').onclick = () => optDiv.remove();
        list.appendChild(optDiv);
    });

    removeBtn.onclick = () => questionCard.remove();

    container.appendChild(template);
});

document.getElementById('tugasForm').addEventListener('submit', function(e) {
    if (document.getElementById('tipeTugas').value === 'form') {
        const questions = [];
        document.querySelectorAll('.question-card').forEach(card => {
            const q = {
                text: card.querySelector('.question-text').value,
                type: card.querySelector('.question-type').value,
                options: []
            };
            if (q.type === 'multiple') {
                card.querySelectorAll('.option-text').forEach(opt => q.options.push(opt.value));
            }
            questions.push(q);
        });

        if (questions.length === 0) {
            alert('Harap tambahkan minimal satu pertanyaan untuk tipe tugas Formulir.');
            e.preventDefault();
            return;
        }

        document.getElementById('formDataInput').value = JSON.stringify(questions);
    }
});
</script>

<style>
    .form-card { border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
    .btn-primary { border-radius: 10px; padding: 10px 30px; }
</style>
@endsection
