@extends('layouts.app')
@section('content')
@if(session('user_role') === 'admin')
<div class="cp-shell">@include('admin.partials.sidebar')<div class="cp-main">
@endif<div class="mb-4"><a href="{{ route('jurusan.index') }}" class="text-decoration-none">&larr; Kembali</a><h1 class="h3 fw-bold mt-3">{{ $jurusan->exists ? 'Edit Jurusan' : 'Tambah Jurusan' }}</h1></div><div class="card form-card"><div class="card-body p-4"><form method="POST" action="{{ $jurusan->exists ? route('jurusan.update',$jurusan) : route('jurusan.store') }}">@csrf @if($jurusan->exists) @method('PUT') @endif<div class="row g-3"><div class="col-md-4"><label class="form-label">Kode jurusan</label><input name="kode" value="{{ old('kode',$jurusan->kode) }}" class="form-control" required></div><div class="col-md-8"><label class="form-label">Nama jurusan</label><input name="nama" value="{{ old('nama',$jurusan->nama) }}" class="form-control" required></div></div><button class="btn btn-primary mt-4">Simpan</button></form></div></div>@if(session('user_role') === 'admin')
</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}
@endif
@endsection
