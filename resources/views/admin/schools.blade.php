@extends('layouts.app')
@section('title','Manajemen Sekolah')
@section('content')
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
  <div>
    <div class="small fw-bold text-uppercase" style="letter-spacing:.1em;color:#6366f1">Premium Admin Only</div>
    <h2 class="fw-bold" style="letter-spacing:-.02em">Sekolah Terdaftar</h2>
    <div class="text-muted small">ID Sekolah wajib diketahui saat registrasi — hanya admin bisa menambah.</div>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSchool" style="border-radius:12px"><i class="bi bi-plus-lg"></i> Tambah Sekolah</button>
</div>

<div class="row g-3">
@foreach($schools as $s)
  <div class="col-md-6 col-lg-4">
    <div class="card border-0 shadow-sm" style="border-radius:20px;overflow:hidden">
      <div style="height:6px;background:linear-gradient(90deg,#6366f1,#22c55e)"></div>
      <div class="card-body p-3">
        <div class="d-flex gap-3 align-items-center">
          <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#4f46e5,#2563eb);display:grid;place-items:center;color:#fff;font-weight:800">{{ substr($s->name,0,1) }}</div>
          <div style="flex:1;min-width:0">
            <div style="font-weight:800" class="text-truncate">[ID: {{ $s->id }}] {{ $s->name }}</div>
            <div style="font-size:11px;color:#64748b">{{ $s->city }} • {{ $s->slug }}</div>
          </div>
          <span class="badge bg-light text-dark border">{{ $s->users_count }} user</span>
        </div>
        <div class="d-flex gap-2 mt-3 align-items-center flex-wrap">
          <span class="badge rounded-pill" style="background:#eef2ff;color:#4f46e5">{{ $s->posts_count }} post</span>
          <span class="badge rounded-pill {{ $s->is_active?'bg-success':'bg-danger' }}">{{ $s->is_active?'Aktif':'Nonaktif' }}</span>
          <form method="POST" action="{{ route('admin.schools.toggle',$s) }}" class="ms-auto">@csrf @method('PATCH')<button class="btn btn-sm {{ $s->is_active?'btn-warning':'btn-success' }}" style="border-radius:10px">{{ $s->is_active?'Nonaktifkan':'Aktifkan' }}</button></form>
          <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#edit{{ $s->id }}" style="border-radius:10px">Edit</button>
          <form method="POST" action="{{ route('admin.schools.destroy',$s) }}" onsubmit="return confirm('Hapus sekolah ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" style="border-radius:10px">Hapus</button></form>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="edit{{ $s->id }}" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('admin.schools.update',$s) }}" class="modal-content" style="border-radius:20px">@csrf @method('PUT')<div class="modal-header"><h6 class="fw-bold">Edit Sekolah #{{ $s->id }}</h6><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input name="name" value="{{ $s->name }}" class="form-control mb-2" required><input name="city" value="{{ $s->city }}" class="form-control mb-2" placeholder="Kota"><input name="slug" value="{{ $s->slug }}" class="form-control mb-2" required><div class="form-check"><input type="checkbox" name="is_active" value="1" @checked($s->is_active) class="form-check-input" id="act{{ $s->id }}"><label class="form-check-label" for="act{{ $s->id }}">Aktif (bisa daftar)</label></div></div><div class="modal-footer"><button class="btn btn-primary" style="border-radius:12px">Simpan</button></div></form></div></div>
@endforeach
</div>

<div class="modal fade" id="addSchool" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('admin.schools.store') }}" class="modal-content" style="border-radius:20px">@csrf<div class="modal-header"><h6 class="fw-bold">Tambah Sekolah Baru</h6><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input name="name" placeholder="Nama Sekolah" class="form-control mb-2" required><input name="city" placeholder="Kota" class="form-control mb-2"><input name="slug" placeholder="slug unik (tanpa spasi)" class="form-control mb-2" required><div class="form-check"><input type="checkbox" name="is_active" value="1" checked class="form-check-input" id="addActive"><label class="form-check-label" for="addActive">Aktif (bisa daftar)</label></div></div><div class="modal-footer"><button class="btn btn-primary" style="border-radius:12px">Buat</button></div></form></div></div>

<div class="card mt-4 border-0 shadow-sm" style="border-radius:20px">
  <div class="card-body p-4">
    <h6 class="fw-bold"><i class="bi bi-shield-lock"></i> Buat Admin Sekolah (Hanya Admin Pusat)</h6>
    <div class="small text-muted mb-3">Akun admin sekolah bisa kelola sekolahnya sendiri. Email: adminpusat@pusat.com bisa semua.</div>
    <form method="POST" action="{{ route('admin.schools.admin.create') }}" class="row g-2">
      @csrf
      <div class="col-md-3"><input name="name" placeholder="Nama Admin" class="form-control" required></div>
      <div class="col-md-3"><input name="email" type="email" placeholder="Email" class="form-control" required></div>
      <div class="col-md-2"><input name="password" type="password" placeholder="Password" class="form-control" required></div>
      <div class="col-md-2"><input name="password_confirmation" type="password" placeholder="Konfirmasi" class="form-control" required></div>
      <div class="col-md-2"><select name="school_id" class="form-select" required><option value="">Pilih Sekolah</option>@foreach($schools as $sc)<option value="{{ $sc->id }}">[{{ $sc->id }}] {{ $sc->name }}</option>@endforeach</select></div>
      <div class="col-12"><button class="btn btn-dark" style="border-radius:12px">Buat Admin Sekolah</button></div>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
