{{-- CRUD Akun Admin Sekolah — khusus Admin Pusat. --}}
@extends('layouts.app', ['title' => 'Admin Sekolah'])
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
    .tbl-card { border-radius: 20px; border: 1px solid var(--border); background: #fff; box-shadow: var(--shadow); overflow: hidden; }
    .tbl-card-head { padding: 20px 24px; border-bottom: 1px solid var(--border); }
    .tbl-card-title { font-size: 16px; font-weight: 800; color: var(--navy); margin: 0; }
    .table-premium thead th { background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; padding: 14px 20px; border: 0; }
    .table-premium tbody td { padding: 14px 20px; border-top: 1px solid #f8fafc; vertical-align: middle; font-size: 13.5px; }
    .adm-avatar { width: 42px; height: 42px; border-radius: 14px; display: grid; place-items: center; color: #fff; font-weight: 800; flex-shrink: 0; background: linear-gradient(135deg, #4f46e5, #2563eb); }
    .btn-mini { padding: 6px 12px; border-radius: 10px; font-size: 12px; font-weight: 700; border: 1.5px solid transparent; }
</style>

<div class="cp-shell">
@include('admin.partials.sidebar')
<div class="cp-main">

<div class="cp-page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 position-relative" style="z-index:1;">
        <div>
            <div class="small fw-bold" style="letter-spacing:.1em;color:#94a3b8;">SUPER ADMIN ONLY</div>
            <h1 class="cp-page-title">Admin Sekolah</h1>
            <p class="cp-page-sub mb-0">Buat & kelola akun admin tiap sekolah. Admin sekolah yang aktif bisa membuka/menutup pendaftaran sekolahnya sendiri.</p>
        </div>
        <button class="btn btn-light fw-bold px-4" style="border-radius:14px;" data-bs-toggle="modal" data-bs-target="#addAdmin">
            <i class="bi bi-plus-circle-fill me-2"></i>Tambah Admin
        </button>
    </div>
</div>

<div class="tbl-card">
    <div class="tbl-card-head d-flex justify-content-between align-items-center">
        <h2 class="tbl-card-title"><i class="bi bi-shield-check me-2 text-primary"></i>Daftar Admin ({{ $admins->count() }})</h2>
    </div>
    @if($errors->any())
    <div class="alert alert-danger border-0 mx-4 mt-3 mb-0" style="border-radius:14px;">
        <div class="fw-bold small mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i>Form belum tersimpan:</div>
        <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif
    <div class="table-responsive">
        <table class="table table-premium table-hover align-middle mb-0">
            <thead><tr><th class="ps-4">Admin</th><th>Sekolah</th><th class="text-center">Status</th><th class="text-end pe-4">Aksi</th></tr></thead>
            <tbody>
                @forelse($admins as $a)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex gap-3 align-items-center">
                            <div class="adm-avatar">{{ strtoupper(substr($a->name, 0, 1)) }}</div>
                            <div><div class="fw-bold">[ID: {{ $a->id }}] {{ $a->name }}</div><div class="text-muted small">{{ $a->email }}</div></div>
                        </div>
                    </td>
                    <td><span class="fw-semibold">{{ $a->school->name ?? '-' }}</span><div class="text-muted small">ID Sekolah: {{ $a->school_id }}</div></td>
                    <td class="text-center"><span class="badge rounded-pill {{ $a->aktif ? 'bg-success' : 'bg-danger' }}">{{ $a->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-2">
                            <form method="POST" action="{{ route('admin.school-admins.toggle', $a) }}">@csrf @method('PATCH')
                                <button class="btn btn-sm {{ $a->aktif ? 'btn-outline-warning' : 'btn-outline-success' }} btn-mini">{{ $a->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                            <button class="btn btn-sm btn-outline-primary btn-mini" data-bs-toggle="modal" data-bs-target="#edit{{ $a->id }}">Edit</button>
                            <form method="POST" action="{{ route('admin.school-admins.destroy', $a) }}" onsubmit="return confirm('Hapus admin {{ $a->name }}?')">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger btn-mini">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-5 text-muted">Belum ada admin sekolah. Buat lewat tombol Tambah Admin.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal edit DI LUAR tabel (div di dalam tbody merusak backdrop/posisi modal) --}}
@foreach($admins as $a)
<div class="modal fade" id="edit{{ $a->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:20px;overflow:hidden;">
        <div class="modal-header" style="background:#f8fafc;border-bottom:1px solid var(--border);">
            <div>
                <h6 class="fw-bold mb-0">Edit Admin [ID: {{ $a->id }}]</h6>
                <div class="small text-muted">{{ $a->school->name ?? '-' }}</div>
            </div>
            <button class="btn-close" data-bs-dismiss="modal" type="button"></button>
        </div>
        <form method="POST" action="{{ route('admin.school-admins.update', $a) }}">
            @csrf @method('PUT')
            <input type="hidden" name="admin_id" value="{{ $a->id }}">
            <div class="modal-body" style="padding:24px;">
                <label class="small fw-bold mb-1">Nama</label>
                <input name="name" value="{{ old('admin_id') == $a->id ? old('name') : $a->name }}" class="form-control mb-3" style="border-radius:12px;" required>
                <label class="small fw-bold mb-1">Email</label>
                <input name="email" type="email" value="{{ old('admin_id') == $a->id ? old('email') : $a->email }}" class="form-control mb-3" style="border-radius:12px;" required>
                <label class="small fw-bold mb-1">Sekolah</label>
                <select name="school_id" class="form-select mb-3" style="border-radius:12px;" required>@foreach($schools as $sc)<option value="{{ $sc->id }}" @selected((old('admin_id') == $a->id ? old('school_id') : $a->school_id) == $sc->id)>[{{ $sc->id }}] {{ $sc->name }}</option>@endforeach</select>
                <div class="p-3 rounded-3 mb-1" style="background:#f8fafc;border:1px dashed #e2e8f0;">
                    <label class="small fw-bold mb-1">Password Baru <span class="text-muted fw-normal">(kosongkan jika tidak diganti)</span></label>
                    <input name="password" type="password" class="form-control mb-2" style="border-radius:12px;" minlength="8" autocomplete="new-password">
                    <input name="password_confirmation" type="password" class="form-control" style="border-radius:12px;" placeholder="Konfirmasi password baru" autocomplete="new-password">
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--border);">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:12px;">Batal</button>
                <button class="btn btn-primary px-4" style="border-radius:12px;">Simpan</button>
            </div>
        </form>
    </div></div>
</div>
@endforeach

</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}

<div class="modal fade" id="addAdmin" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:20px;overflow:hidden;">
    <div class="modal-header" style="background:#f8fafc;border-bottom:1px solid var(--border);">
        <div><h6 class="fw-bold mb-0">Tambah Admin Sekolah</h6><div class="small text-muted">Akun langsung aktif & bisa kelola sekolahnya</div></div>
        <button class="btn-close" data-bs-dismiss="modal" type="button"></button>
    </div>
    <form method="POST" action="{{ route('admin.schools.admin.create') }}">@csrf
    <div class="modal-body" style="padding:24px;">
        <label class="small fw-bold mb-1">Nama Admin</label><input name="name" value="{{ old('name') }}" class="form-control mb-3" style="border-radius:12px;" required>
        <label class="small fw-bold mb-1">Email</label><input name="email" type="email" value="{{ old('email') }}" class="form-control mb-3" style="border-radius:12px;" required>
        <label class="small fw-bold mb-1">Password</label><input name="password" type="password" class="form-control mb-3" style="border-radius:12px;" required minlength="8" autocomplete="new-password">
        <label class="small fw-bold mb-1">Konfirmasi Password</label><input name="password_confirmation" type="password" class="form-control mb-3" style="border-radius:12px;" required autocomplete="new-password">
        <label class="small fw-bold mb-1">Sekolah</label>
        <select name="school_id" class="form-select" style="border-radius:12px;" required><option value="">Pilih Sekolah</option>@foreach($schools as $sc)<option value="{{ $sc->id }}" @selected(old('school_id') == $sc->id)>[{{ $sc->id }}] {{ $sc->name }}</option>@endforeach</select>
    </div>
    <div class="modal-footer" style="border-top:1px solid var(--border);">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:12px;">Batal</button>
        <button class="btn btn-primary px-4" style="border-radius:12px;">Buat Admin</button>
    </div>
    </form>
</div></div></div>
<script>
    // Buka kembali modal yang gagal validasi agar pesan error terlihat.
    document.addEventListener('DOMContentLoaded', function () {
        @if($errors->any() && old('admin_id'))
            new bootstrap.Modal(document.getElementById('edit{{ old('admin_id') }}')).show();
        @elseif($errors->any())
            new bootstrap.Modal(document.getElementById('addAdmin')).show();
        @endif
    });
</script>
@endsection
