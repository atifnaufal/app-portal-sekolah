@extends('layouts.app')
@section('title','Manajemen Sekolah')
@section('content')
<style>
    .cp-page-header {
        background: linear-gradient(135deg, var(--navy) 0%, #1e293b 100%);
        border-radius: 24px; padding: 32px 36px; color: #fff;
        position: relative; overflow: hidden; margin-bottom: 24px;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
    }
    .cp-page-header::after {
        content: ''; position: absolute; top: -70px; right: -70px;
        width: 220px; height: 220px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36,107,254,0.18) 0%, transparent 70%);
    }
    .cp-page-title { font-size: 26px; font-weight: 800; letter-spacing: -0.02em; position: relative; z-index: 1; }
    .cp-page-sub { font-size: 13px; color: #94a3b8; position: relative; z-index: 1; }
    @media (max-width: 768px) {
        .cp-page-header { padding: 24px; border-radius: 20px; }
        .cp-page-title { font-size: 22px; }
    }
</style>
<div class="cp-shell">
@include('admin.partials.sidebar')
<div class="cp-main">

<div class="cp-page-header">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 position-relative" style="z-index:1;">
        <div>
            <div class="small fw-bold" style="letter-spacing:.1em;color:#94a3b8;">PREMIUM ADMIN ONLY</div>
            <h2 class="cp-page-title mb-1">Sekolah Terdaftar</h2>
            <div class="cp-page-sub">ID Sekolah wajib diketahui saat registrasi — hanya admin bisa menambah.</div>
        </div>
        <button class="btn btn-light fw-bold" data-bs-toggle="modal" data-bs-target="#addSchool" style="border-radius:12px"><i class="bi bi-plus-lg me-1"></i> Tambah Sekolah</button>
    </div>
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
          <span class="badge rounded-pill bg-dark" title="Kode Pendaftaran umum" style="cursor:pointer;" onclick="navigator.clipboard&&navigator.clipboard.writeText('{{ $s->enroll_code }}');this.innerHTML='Disalin!';setTimeout(()=>this.innerHTML='Kode: {{ $s->enroll_code }}',1200);">Kode: {{ $s->enroll_code ?? '-' }}</span>
          <span class="badge rounded-pill {{ $s->is_active?'bg-success':'bg-danger' }}">{{ $s->is_active?'Aktif':'Nonaktif' }}</span>
          <form method="POST" action="{{ route('admin.schools.toggle',$s) }}" class="ms-auto">@csrf @method('PATCH')<button class="btn btn-sm {{ $s->is_active?'btn-warning':'btn-success' }}" style="border-radius:10px">{{ $s->is_active?'Nonaktifkan':'Aktifkan' }}</button></form>
          <a href="{{ route('admin.schools.detail',$s) }}" class="btn btn-sm btn-primary" style="border-radius:10px"><i class="bi bi-eye me-1"></i>Detail</a>
          <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#edit{{ $s->id }}" style="border-radius:10px">Edit</button>
          <form method="POST" action="{{ route('admin.schools.destroy',$s) }}" onsubmit="return confirm('Hapus sekolah ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" style="border-radius:10px">Hapus</button></form>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="edit{{ $s->id }}" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('admin.schools.update',$s) }}" class="modal-content" style="border-radius:20px">@csrf @method('PUT')<div class="modal-header"><h6 class="fw-bold">Edit Sekolah #{{ $s->id }}</h6><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input name="name" value="{{ $s->name }}" class="form-control mb-2" required><div class="row g-2 mb-2"><div class="col-7"><input name="city" value="{{ $s->city }}" class="form-control" placeholder="Kota"></div><div class="col-5"><input name="city_code" value="{{ $s->city_code }}" class="form-control" placeholder="Kode kota" required minlength="3" maxlength="6"></div></div><input name="slug" value="{{ $s->slug }}" class="form-control mb-2" required><div class="alert alert-light border small mb-2" style="border-radius:12px;">Kode Pendaftaran: <b>{{ $s->enroll_code ?? '-' }}</b> <span class="text-muted">(otomatis: ID + kode kota)</span></div><div class="form-check"><input type="checkbox" name="is_active" value="1" @checked($s->is_active) class="form-check-input" id="act{{ $s->id }}"><label class="form-check-label" for="act{{ $s->id }}">Aktif (bisa daftar)</label></div></div><div class="modal-footer"><button class="btn btn-primary" style="border-radius:12px">Simpan</button></div></form></div></div>
@endforeach
</div>

<div class="modal fade" id="addSchool" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('admin.schools.store') }}" class="modal-content" style="border-radius:20px">@csrf<div class="modal-header"><h6 class="fw-bold">Tambah Sekolah Baru</h6><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input name="name" placeholder="Nama Sekolah" class="form-control mb-2" required><div class="row g-2 mb-2"><div class="col-7"><input name="city" placeholder="Kota" class="form-control"></div><div class="col-5"><input name="city_code" placeholder="Kode kota (mis. 51372)" class="form-control" required minlength="3" maxlength="6"></div></div><input name="slug" placeholder="slug unik (tanpa spasi)" class="form-control mb-2" required><div class="alert alert-info border-0 small mb-2" style="border-radius:12px;background:#eef2ff;color:#4f46e5;"><i class="bi bi-magic"></i> Kode Pendaftaran digenerate otomatis: <b>ID + kode kota</b> (mis. ID 18 + 51372 → <b>1851372</b>).</div><div class="form-check"><input type="checkbox" name="is_active" value="1" checked class="form-check-input" id="addActive"><label class="form-check-label" for="addActive">Aktif (bisa daftar)</label></div></div><div class="modal-footer"><button class="btn btn-primary" style="border-radius:12px">Buat</button></div></form></div></div>

<div class="card mt-4 border-0 shadow-sm" style="border-radius:20px">
  <div class="card-body p-4 d-flex align-items-center gap-3 flex-wrap">
    <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#4f46e5,#2563eb);display:grid;place-items:center;color:#fff;font-size:20px;"><i class="bi bi-shield-check"></i></div>
    <div class="flex-fill" style="min-width:200px;">
      <h6 class="fw-bold mb-1">Kelola Admin Sekolah</h6>
      <div class="small text-muted">Buat, edit, aktif/nonaktif, dan hapus akun admin tiap sekolah di halaman khusus.</div>
    </div>
    <a href="{{ route('admin.school-admins.index') }}" class="btn btn-primary" style="border-radius:12px">Buka CRUD Admin Sekolah <i class="bi bi-arrow-right ms-1"></i></a>
  </div>
</div>

</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
