@extends('layouts.app')
@section('content')
<div class="mb-4">
    <a href="{{ route('mahasiswa.index') }}" class="text-decoration-none">&larr; Kembali</a>
    <h1 class="h3 fw-bold mt-3">{{ $mahasiswa->exists ? 'Edit Siswa' : 'Tambah Siswa' }}</h1>
</div>

<div class="card form-card">
    <div class="card-body p-4">
        <form method="POST" action="{{ $mahasiswa->exists ? route('mahasiswa.update', $mahasiswa) : route('mahasiswa.store') }}">
            @csrf
            @if($mahasiswa->exists) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">NIS / NIM</label>
                    <input name="nik" value="{{ old('nik', $mahasiswa->nik) }}" class="form-control" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Nama lengkap</label>
                    <input name="name" value="{{ old('name', $mahasiswa->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input name="email" type="email" value="{{ old('email', $mahasiswa->email) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. HP</label>
                    <input name="no_hp" value="{{ old('no_hp', $mahasiswa->no_hp) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-select" required>
                        <option value="">Pilih kelas</option>
                        @foreach($kelases as $kelas)
                            <option value="{{ $kelas->id }}" @selected(old('kelas_id', $mahasiswa->kelas_id) == $kelas->id)>{{ $kelas->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!$mahasiswa->exists)
                    <div class="col-md-6">
                        <label class="form-label">Password awal</label>
                        <input name="password" type="text" value="password" class="form-control">
                        <div class="form-text text-secondary">Default: <code>password</code>. Boleh diubah.</div>
                    </div>
                @endif
            </div>

            <button class="btn btn-primary mt-4">Simpan</button>
        </form>
    </div>
</div>
@endsection
