@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
@php $isEdit = $materi->exists; @endphp

<style>
    .lms-topbar {
        position: sticky; top: 0; z-index: 1000;
        background: rgba(255,255,255,0.9); backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--line);
        padding: 12px 16px; display: flex; align-items: center; gap: 10px;
    }
    .lms-body { max-width: 640px; margin: 0 auto; padding: 16px 16px 40px; }

    .lms-card {
        background: var(--surface-card); border: 1px solid var(--line);
        border-radius: var(--radius-md); padding: 18px;
        margin-bottom: 14px; box-shadow: var(--shadow-card);
    }

    .mf-file {
        border: 2px dashed #c7d2fe; border-radius: var(--radius-md);
        padding: 24px 16px; text-align: center; cursor: pointer;
        background: #f8faff; transition: all 0.2s;
        display: flex; flex-direction: column; align-items: center; gap: 8px;
    }
    .mf-file.dragover { border-color: var(--blue); background: #eff4ff; }
    .mf-file.has-file { border-style: solid; border-color: #16a34a; background: #f0fdf4; }

    .file-row {
        display: flex; align-items: center; gap: 8px; padding: 10px;
        background: var(--surface); border-radius: var(--radius-sm);
        margin-top: 10px;
    }

    @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .fade-up { animation: fadeUp 0.35s ease both; }
</style>

<div class="lms-topbar">
    <a href="{{ route('mapel.show', $mapel) }}" class="pui-btn pui-btn-ghost pui-btn-sm pui-btn-round" style="padding:0;width:36px;height:36px;">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div style="font-weight:800;font-size:16px;flex:1;">{{ $isEdit ? 'Edit Materi' : 'Buat Materi' }}</div>
</div>

<div class="lms-body">
    <div class="lms-card fade-up" style="background:var(--grad-hero);color:#fff;border:none;box-shadow:var(--shadow-card);">
        <div style="font-size:10px;font-weight:800;letter-spacing:0.1em;text-transform:uppercase;opacity:0.5;margin-bottom:6px;">{{ $mapel->kode }}</div>
        <div style="font-size:18px;font-weight:800;">{{ $mapel->nama }}</div>
        <div style="font-size:11px;opacity:0.6;margin-top:4px;">Bagikan dokumen, video, atau catatan pembelajaran kepada siswa.</div>
    </div>

    <form method="POST" action="{{ $isEdit ? route('materi.update', [$mapel, $materi]) : route('materi.store', $mapel) }}" enctype="multipart/form-data">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- Judul --}}
        <div class="lms-card fade-up" style="animation-delay:0.05s;">
            <label class="pui-label"><i class="bi bi-type me-1" style="color:var(--blue);"></i> Judul Materi *</label>
            <input type="text" name="judul" class="pui-input" placeholder="cth: Bab 1 - Pengenalan" value="{{ old('judul', $materi->judul) }}" required>
            @error('judul')<div style="font-size:11px;color:#dc2626;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        {{-- Deskripsi --}}
        <div class="lms-card fade-up" style="animation-delay:0.1s;">
            <label class="pui-label"><i class="bi bi-card-text me-1" style="color:var(--blue);"></i> Deskripsi</label>
            <textarea name="deskripsi" rows="4" class="pui-textarea" style="resize:none;" placeholder="Tulis ringkasan atau catatan pembelajaran...">{{ old('deskripsi', $materi->deskripsi) }}</textarea>
        </div>

        {{-- File upload --}}
        <div class="lms-card fade-up" style="animation-delay:0.15s;">
            <label class="pui-label"><i class="bi bi-paperclip me-1" style="color:var(--blue);"></i> File Materi</label>
            <label class="mf-file" id="fileZone">
                <input type="file" name="file_materi" id="fileInput" style="display:none;" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.xls,.ppt,.pptx,.csv,.txt,.zip,.mp4,.mov">
                <div style="width:46px;height:46px;border-radius:var(--radius-sm);background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:var(--blue);display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-cloud-arrow-up-fill" style="font-size:20px;"></i>
                </div>
                <div style="font-size:13px;font-weight:700;color:var(--ink);">Pilih File</div>
                <div style="font-size:10px;color:var(--faint);">PDF, Dokumen, Gambar, Video (maks 50MB)</div>
                <div id="fileName" style="font-size:11px;font-weight:600;color:#16a34a;display:none;"></div>
            </label>
            @if($isEdit && $materi->file_materi)
                <div class="file-row">
                    <i class="bi bi-file-earmark-fill" style="color:var(--blue);"></i>
                    <div style="flex:1;font-size:12px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $materi->file_nama }}</div>
                    <label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#dc2626;font-weight:600;cursor:pointer;">
                        <input type="checkbox" name="hapus_file" value="1"> Hapus
                    </label>
                </div>
            @endif
            @error('file_materi')<div style="font-size:11px;color:#dc2626;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        {{-- Video URL --}}
        <div class="lms-card fade-up" style="animation-delay:0.2s;">
            <label class="pui-label"><i class="bi bi-youtube me-1" style="color:#dc2626;"></i> Link Video (opsional)</label>
            <input type="url" name="video_url" class="pui-input" placeholder="https://www.youtube.com/watch?v=..." value="{{ old('video_url', $materi->video_url) }}">
            @error('video_url')<div style="font-size:11px;color:#dc2626;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="pui-btn pui-btn-primary pui-btn-block pui-btn-round" style="padding:15px;font-size:15px;">
            <i class="bi bi-check-lg me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Terbitkan Materi' }}
        </button>
    </form>
</div>

<script>
    var fileInput = document.getElementById('fileInput');
    var fileZone = document.getElementById('fileZone');
    var fileName = document.getElementById('fileName');

    fileInput.addEventListener('change', function() {
        if (fileInput.files && fileInput.files[0]) {
            fileZone.classList.add('has-file');
            fileName.style.display = 'block';
            fileName.textContent = fileInput.files[0].name;
        }
    });

    ['dragover','dragenter'].forEach(function(ev){
        fileZone.addEventListener(ev, function(e){ e.preventDefault(); fileZone.classList.add('dragover'); });
    });
    ['dragleave','drop'].forEach(function(ev){
        fileZone.addEventListener(ev, function(e){ e.preventDefault(); fileZone.classList.remove('dragover'); });
    });
    fileZone.addEventListener('drop', function(e){
        var files = e.dataTransfer.files;
        if (files.length) {
            fileInput.files = files;
            fileZone.classList.add('has-file');
            fileName.style.display = 'block';
            fileName.textContent = files[0].name;
        }
    });
</script>
@endsection
