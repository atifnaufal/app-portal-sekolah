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
                <select name="target" class="form-select" required>
                    @if(session('user_role') === 'admin')
                        <option value="general" @selected(old('target', !$pengumuman->kelas_id && !$pengumuman->eskul_id ? 'general' : '') == 'general')>Umum (Seluruh Sekolah)</option>
                    @endif

                    @if(isset($isWaliKelas) && $isWaliKelas)
                        <option value="class" @selected(old('target', $pengumuman->kelas_id ? 'class' : '') == 'class')>Wali Kelas ({{ $isWaliKelas->nama }})</option>
                    @endif

                    @if(isset($adminEskuls))
                        @foreach($adminEskuls as $ae)
                            <option value="eskul:{{ $ae->id }}" @selected(old('target', $pengumuman->eskul_id == $ae->id ? 'eskul:'.$ae->id : '') == 'eskul:'.$ae->id)>Admin Eskul ({{ $ae->nama }})</option>
                        @endforeach
                    @endif
                </select>
                <div class="small text-muted mt-1">Pilih jangkauan informasi yang akan Anda bagikan.</div>
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
@endsection
