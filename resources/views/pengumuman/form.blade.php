@extends(session('user_role') === 'admin' ? 'layouts.app' : 'layouts.mobile-app')

@section('content')
<div class="mb-4">
    <a href="{{ route('pengumuman.index') }}" class="text-decoration-none small fw-bold"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h1 class="h3 fw-bold mt-3">{{ $pengumuman->exists ? 'Edit Pengumuman' : 'Buat Pengumuman Baru' }}</h1>
    <p class="text-secondary small">Siarkan informasi ke kelas, eskul, atau seluruh sekolah.</p>
</div>

<div class="card form-card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form method="POST" action="{{ $pengumuman->exists ? route('pengumuman.update', $pengumuman) : route('pengumuman.store') }}" enctype="multipart/form-data">
            @csrf
            @if($pengumuman->exists) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label fw-bold">Target Pengumuman</label>
                @php
                    $isPrivateEdit = $pengumuman->exists && method_exists($pengumuman,'isPrivate') && $pengumuman->isPrivate();
                    $currentTarget = old('target', $isPrivateEdit ? 'private' : ($pengumuman->eskul_id ? 'eskul:'.$pengumuman->eskul_id : ($pengumuman->kelas_id ? 'class' : (!$pengumuman->exists ? '' : 'general'))));
                @endphp
                <select name="target" id="targetSelect" class="form-select" required>
                    @if(session('user_role') === 'admin')
                        <option value="general" @selected($currentTarget === 'general')>Umum (Seluruh Sekolah)</option>
                    @endif

                    @if(isset($isWaliKelas) && $isWaliKelas)
                        <option value="class" @selected($currentTarget === 'class')>Wali Kelas ({{ $isWaliKelas->nama }})</option>
                    @endif

                    @if(isset($adminEskuls))
                        @foreach($adminEskuls as $ae)
                            <option value="eskul:{{ $ae->id }}" @selected($currentTarget === 'eskul:'.$ae->id)>Admin Eskul ({{ $ae->nama }})</option>
                        @endforeach
                    @endif

                    @if(session('user_role') === 'admin' || (isset($isWaliKelas) && $isWaliKelas))
                        <option value="private" @selected($currentTarget === 'private')>Pribadi (Siswa Tertentu)</option>
                    @endif
                </select>
                <div class="small text-muted mt-1">Pilih jangkauan informasi yang akan Anda bagikan.</div>
            </div>

            <div class="mb-3" id="privateBox" style="{{ $currentTarget === 'private' ? '' : 'display:none;' }}">
                <label class="form-label fw-bold">Pilih Siswa Penerima</label>
                <select name="siswa_ids[]" id="siswaSelect" class="form-select" multiple size="8">
                    @foreach($siswaList ?? collect() as $s)
                        <option value="{{ $s->id }}" @selected(in_array($s->id, old('siswa_ids', $selectedSiswa ?? []), true))>{{ $s->name }}</option>
                    @endforeach
                </select>
                <div class="small text-muted mt-1">Tahan Ctrl (atau sentuh) untuk memilih beberapa siswa. Pengumuman privat hanya terlihat oleh siswa terpilih.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Judul</label>
                <input name="judul" value="{{ old('judul',$pengumuman->judul) }}" class="form-control" required placeholder="Ketik judul pengumuman...">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Isi Informasi</label>
                <textarea name="isi" rows="6" class="form-control" required placeholder="Tuliskan detail informasi di sini...">{{ old('isi',$pengumuman->isi) }}</textarea>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tanggal Agenda (Opsional)</label>
                    <input name="tanggal_acara" type="date" value="{{ old('tanggal_acara',$pengumuman->tanggal_acara?->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Gambar Pendukung</label>
                    <input name="gambar" type="file" accept="image/*" class="form-control">
                    <div class="small text-secondary mt-1">Maksimal 5 MB</div>
                </div>
            </div>

            @if(session('user_role') === 'admin')
            <div class="form-check mt-4">
                <input name="is_landing" type="checkbox" value="1" class="form-check-input" id="is_landing" @checked(old('is_landing',$pengumuman->is_landing))>
                <label for="is_landing" class="form-check-label small fw-bold">Tampilkan di Banner Landing Page</label>
            </div>
            @endif

            <button class="btn btn-primary w-100 py-3 rounded-3 mt-4 fw-bold shadow-sm">
                {{ $pengumuman->exists ? 'Simpan Perubahan' : 'Terbitkan Sekarang' }}
            </button>
        </form>
    </div>
</div>

<script>
(function () {
    const sel = document.getElementById('targetSelect');
    const box = document.getElementById('privateBox');
    const siswa = document.getElementById('siswaSelect');
    if (!sel || !box || !siswa) return;

    function sync() {
        const isPrivate = sel.value === 'private';
        box.style.display = isPrivate ? '' : 'none';
        siswa.required = isPrivate;
    }
    sel.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
