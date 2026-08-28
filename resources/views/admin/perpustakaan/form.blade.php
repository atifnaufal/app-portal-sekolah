@extends('layouts.app')
@section('content')
<div class="mb-4">
    <a href="{{ route('admin.perpustakaan.index') }}" class="text-decoration-none small fw-bold"><i class="bi bi-arrow-left"></i> Kembali ke Daftar</a>
    <h1 class="h3 fw-bold mt-2">{{ isset($buku) ? 'Edit Buku' : 'Tambah Buku Baru' }}</h1>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ isset($buku) ? route('admin.perpustakaan.update', $buku) : route('admin.perpustakaan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($buku)) @method('PUT') @endif

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Judul Buku</label>
                        <input type="text" name="judul" class="form-control" value="{{ old('judul', $buku->judul ?? '') }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kategori</label>
                            <select name="kategori_buku_id" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->id }}" @selected(old('kategori_buku_id', $buku->kategori_buku_id ?? '') == $kat->id)>{{ $kat->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Stok</label>
                            <input type="number" name="stok" class="form-control" value="{{ old('stok', $buku->stok ?? 1) }}" required min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Penulis</label>
                            <input type="text" name="penulis" class="form-control" value="{{ old('penulis', $buku->penulis ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Penerbit</label>
                            <input type="text" name="penerbit" class="form-control" value="{{ old('penerbit', $buku->penerbit ?? '') }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">Tahun</label>
                            <input type="number" name="tahun_terbit" class="form-control" value="{{ old('tahun_terbit', $buku->tahun_terbit ?? '') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="5">{{ old('deskripsi', $buku->deskripsi ?? '') }}</textarea>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bg-light p-3 rounded-4 mb-3 border">
                        <label class="form-label fw-bold">Sampul Buku (Image)</label>
                        @if(isset($buku) && $buku->cover)
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$buku->cover) }}" class="rounded shadow-sm" style="width: 100px; aspect-ratio: 2/3; object-fit: cover;">
                            </div>
                        @endif
                        <input type="file" name="cover" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
                        <div class="small text-secondary mt-1">Maks. 2MB. Kosongkan jika tidak ingin mengubah.</div>
                    </div>

                    <div class="bg-light p-3 rounded-4 border">
                        <label class="form-label fw-bold">File Buku (PDF)</label>
                        @if(isset($buku) && $buku->file_pdf)
                            <div class="mb-2">
                                <a href="{{ asset('storage/'.$buku->file_pdf) }}" target="_blank" class="btn btn-sm btn-info text-white"><i class="bi bi-file-pdf"></i> Lihat PDF Saat Ini</a>
                            </div>
                        @endif
                        <input type="file" name="file_pdf" class="form-control" accept=".pdf" {{ isset($buku) ? '' : 'required' }}>
                        <div class="small text-secondary mt-1">Maks. 20MB. PDF only.</div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top text-end">
                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">Simpan Buku</button>
            </div>
        </form>
    </div>
</div>
@endsection
