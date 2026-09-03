@extends('layouts.app')

@section('content')
<style>
    .form-card { border-radius: 24px; border: 1px solid var(--border); box-shadow: var(--shadow-md); background: #fff; overflow: hidden; }
    .form-header { background: #f8fafc; padding: 24px 30px; border-bottom: 1px solid var(--border); }
    .form-body { padding: 30px; }
    .form-label { font-weight: 700; color: #475569; font-size: 13px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.02em; }
    .form-control { border-radius: 12px; padding: 12px 16px; border: 1.5px solid #e2e8f0; transition: all 0.2s; }
    .form-control:focus { border-color: var(--blue); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
</style>

<div class="mb-5">
    <a href="{{ route('kelas.index') }}" class="btn btn-link text-decoration-none p-0 text-muted fw-bold mb-3">
        <i class="bi bi-arrow-left me-2"></i> Kembali ke Data Kelas
    </a>
    <h1 class="h2 fw-extrabold" style="color: var(--navy);">{{ $kelas->exists ? 'Edit Rombongan Belajar' : 'Tambah Rombongan Belajar' }}</h1>
    <p class="text-secondary">Silakan lengkapi informasi kelas di bawah ini.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="form-card">
            <div class="form-header">
                <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i> Informasi Dasar</h5>
            </div>
            <div class="form-body">
                <form method="POST" action="{{ $kelas->exists ? route('kelas.update',$kelas) : route('kelas.store') }}">
                    @csrf
                    @if($kelas->exists) @method('PUT') @endif

                    <div class="row g-4">
                        <div class="col-md-7">
                            <label class="form-label">Nama Kelas / Rombel</label>
                            <input name="nama" value="{{ old('nama',$kelas->nama) }}" placeholder="Contoh: XII RPL 1" class="form-control" required>
                            <div class="form-text mt-2 small text-muted">Gunakan format yang konsisten, misal: [Tingkat] [Jurusan] [Urutan]</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Tingkat / Grade</label>
                            <select name="tingkat" class="form-select form-control" required>
                                <option value="" disabled selected>Pilih Tingkat</option>
                                @foreach(range(1, 13) as $t)
                                    <option value="{{ $t }}" @selected(old('tingkat', $kelas->tingkat) == $t)>Tingkat {{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Ajaran</label>
                            <input name="tahun_ajaran" value="{{ old('tahun_ajaran',$kelas->tahun_ajaran) }}" placeholder="2026/2027" class="form-control" required>
                        </div>
                        @if(!empty($isSuperAdmin))
                        <div class="col-md-6">
                            <label class="form-label">Sekolah <span class="text-danger">*</span></label>
                            <select name="school_id" class="form-select form-control" required>
                                <option value="" disabled {{ old('school_id', $kelas->school_id) ? '' : 'selected' }}>Pilih Sekolah</option>
                                @foreach($schools as $sc)
                                    <option value="{{ $sc->id }}" @selected(old('school_id', $kelas->school_id) == $sc->id)>[{{ $sc->id }}] {{ $sc->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text mt-2 small text-muted">Kelas milik sekolah ini (untuk filter per-sekolah).</div>
                        </div>
                        @endif
                    </div>

                    <div class="mt-5 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" style="border-radius: 12px;">
                            <i class="bi bi-check-circle-fill me-2"></i> Simpan Data
                        </button>
                        <a href="{{ route('kelas.index') }}" class="btn btn-light px-4 py-2 fw-bold text-muted" style="border-radius: 12px;">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 bg-primary text-white p-4 mb-4" style="border-radius: 24px; background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%) !important;">
            <h5 class="fw-bold mb-3"><i class="bi bi-lightbulb me-2"></i> Tips</h5>
            <p class="small mb-0 opacity-75">Nama kelas akan muncul di seluruh laporan nilai, absensi, dan jadwal pelajaran. Pastikan penulisan sudah benar sebelum disimpan.</p>
        </div>

        @if($kelas->exists)
            <div class="ad-card p-4">
                <h6 class="fw-bold mb-3">Statistik Saat Ini</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Siswa Terdaftar:</span>
                    <span class="fw-bold text-primary">{{ $kelas->users()->where('role', 'siswa')->count() }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Total Guru Mapel:</span>
                    <span class="fw-bold text-success">{{ $kelas->users()->where('role', 'guru')->count() }}</span>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
