@extends('layouts.app')

@section('content')
<style>
    .ad-card { border-radius: 24px; border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow-sm); background: #fff; }
    .ad-card-head { padding: 24px 30px; border-bottom: 1px solid var(--border); background: #fff; }
    .ad-card-title { font-size: 18px; font-weight: 800; color: var(--navy); margin-bottom: 0; }

    .nav-premium { border-bottom: 2px solid #f1f5f9; gap: 8px; }
    .nav-premium .nav-link {
        border: none; color: #64748b; font-weight: 700; font-size: 14px; padding: 12px 24px;
        border-radius: 12px 12px 0 0; position: relative; transition: all 0.3s;
    }
    .nav-premium .nav-link:hover { color: var(--blue); background: #f8fafc; }
    .nav-premium .nav-link.active { color: var(--blue); background: transparent; }
    .nav-premium .nav-link.active::after {
        content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 3px;
        background: var(--blue); border-radius: 99px;
    }

    .badge-premium { padding: 6px 14px; border-radius: 99px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.02em; }
    .badge-success-p { background: #dcfce7; color: #166534; }
    .badge-warning-p { background: #fef9c3; color: #854d0e; }

    /* Chip kelas — tampilan premium per kelas */
    .class-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 12px; border-radius: 99px;
        background: #eef2ff; color: #4f46e5;
        border: 1px solid #e0e7ff;
        font-size: 12px; font-weight: 800; letter-spacing: 0.01em;
        transition: all 0.2s; cursor: default; user-select: none;
    }
    .class-chip i { font-size: 12px; }
    .class-chip:hover { background: #e0e7ff; transform: translateY(-1px); box-shadow: 0 3px 8px rgba(79,70,229,0.14); }

    .table-premium thead th {
        background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.05em; padding: 16px 24px; border-bottom: 1px solid #f1f5f9;
    }
    .table-premium tbody td { padding: 16px 24px; border-bottom: 1px solid #f8fafc; vertical-align: middle; }

    .btn-action { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; border: 1px solid #e2e8f0; background: #fff; color: #64748b; }
    .btn-action:hover { background: #f1f5f9; color: var(--navy); border-color: #cbd5e1; }
    .btn-action.btn-edit:hover { color: var(--blue); border-color: var(--blue-light); background: #eff6ff; }
    .btn-action.btn-delete:hover { color: #ef4444; border-color: #fecaca; background: #fef2f2; }
    .btn-action.btn-approve:hover { color: #22c55e; border-color: #bbf7d0; background: #f0fdf4; }

    .stat-pill {
        padding: 20px 24px; border-radius: 20px; border: 1px solid var(--border);
        background: #fff; display: flex; align-items: center; gap: 16px; transition: all 0.3s;
    }
    .stat-pill:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; }

    @media (max-width: 768px) {
        .nav-premium { overflow-x: auto; flex-wrap: nowrap; }
        .stat-pill { margin-bottom: 12px; }
    }
</style>

<div class="cp-shell">
@include('admin.partials.sidebar')
<div class="cp-main">

@if(!empty($isSuperAdmin) && !empty($filterSchool))
<a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-secondary mb-3" style="border-radius:10px;"><i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Sekolah</a>
<div class="alert border-0 shadow-sm mb-4 d-flex align-items-center gap-3" style="border-radius:16px;background:#eef2ff;">
    <div style="width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,#4f46e5,#2563eb);display:grid;place-items:center;color:#fff;font-weight:800;">{{ strtoupper(substr($filterSchool->name, 0, 1)) }}</div>
    <div>
        <div class="fw-bold">[ID: {{ $filterSchool->id }}] {{ $filterSchool->name }}</div>
        <div class="small text-muted">{{ $filterSchool->city ?? '-' }} &bull; {{ $filterSchool->slug }}</div>
    </div>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-4">
    <div>
        <div class="text-primary small fw-bold text-uppercase mb-1" style="letter-spacing: 0.1em;">Account Management</div>
        <h1 class="h2 fw-extrabold mb-1" style="color: var(--navy); letter-spacing: -0.02em;">Guru & Siswa</h1>
        <p class="text-secondary mb-0">Manage user access, verify accounts, and organize class assignments.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('register') }}" class="btn btn-primary px-4 py-2 fw-bold" style="border-radius:14px;">
            <i class="bi bi-person-plus-fill me-2"></i> Tambah Akun
        </a>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="stat-pill">
            <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;"><i class="bi bi-person-badge"></i></div>
            <div>
                <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Total Guru</div>
                <div class="h4 fw-extrabold mb-0">{{ number_format($totalGuru) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-pill">
            <div class="stat-icon" style="background: #f0fdf4; color: #22c55e;"><i class="bi bi-people"></i></div>
            <div>
                <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Total Siswa</div>
                <div class="h4 fw-extrabold mb-0">{{ number_format($totalSiswa) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-pill">
            <div class="stat-icon" style="background: #fffbeb; color: #d97706;"><i class="bi bi-clock-history"></i></div>
            <div>
                <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Menunggu Persetujuan</div>
                <div class="h4 fw-extrabold mb-0 text-warning">{{ number_format($pendingUsers) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="ad-card">
    <div class="ad-card-head d-flex justify-content-between align-items-center flex-wrap gap-3">
        <ul class="nav nav-tabs nav-premium border-0" id="userTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="siswa-tab" data-bs-toggle="tab" data-bs-target="#siswa" type="button" role="tab">Siswa</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="guru-tab" data-bs-toggle="tab" data-bs-target="#guru" type="button" role="tab">Guru</button>
            </li>
        </ul>
        <form class="ms-auto" action="{{ route('admin.users') }}" method="GET">
            @if(request('school_id'))<input type="hidden" name="school_id" value="{{ request('school_id') }}">@endif
            <div class="input-group" style="width: 300px;">
                <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;"><i class="bi bi-search text-muted"></i></span>
                <input name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Cari nama, NIK..." style="border-radius: 0 12px 12px 0;">
            </div>
        </form>
    </div>

    <div class="tab-content" id="userTabsContent">
        {{-- TAB SISWA --}}
        <div class="tab-pane fade show active" id="siswa" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-premium mb-0">
                    <thead>
                        <tr>
                            <th>Identitas Siswa</th>
                            <th>Kelas</th>
                            <th>Status Akun</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users->where('role', 'siswa') as $user)
                            @include('admin.partials.user-row', ['user' => $user])
                        @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted">Tidak ada data siswa ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB GURU --}}
        <div class="tab-pane fade" id="guru" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-premium mb-0">
                    <thead>
                        <tr>
                            <th>Identitas Guru</th>
                            <th>Mata Pelajaran / Bidang</th>
                            <th>Status Akun</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users->where('role', 'guru') as $user)
                            @include('admin.partials.user-row', ['user' => $user])
                        @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted">Tidak ada data guru ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal / Offcanvas for Editing can be added here, or keep the collapse mechanism --}}
@foreach($users as $user)
    <div class="modal fade" id="editModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 24px; box-shadow: var(--shadow-xl);">
                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="fw-extrabold mb-0">Edit Account: {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.user.update', $user) }}">
                    @csrf @method('PUT')
                    <div class="modal-body px-4 pb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-1">Nama Lengkap</label>
                                <input name="name" value="{{ $user->name }}" class="form-control" required style="border-radius: 12px;">
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-1">NIK / Username</label>
                                <input name="nik" value="{{ $user->nik }}" class="form-control" required style="border-radius: 12px;">
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-1">Email</label>
                                <input name="email" type="email" value="{{ $user->email }}" class="form-control" required style="border-radius: 12px;">
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-1">No. HP</label>
                                <input name="no_hp" value="{{ $user->no_hp }}" class="form-control" required style="border-radius: 12px;">
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-1">Role</label>
                                <select name="role" class="form-select" required style="border-radius: 12px;">
                                    <option value="guru" @selected($user->role === 'guru')>Guru</option>
                                    <option value="siswa" @selected($user->role === 'siswa')>Siswa</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-1">Kelas</label>
                                <select name="kelas_id" class="form-select" required style="border-radius: 12px;">
                                    @foreach($kelases as $kelas)
                                        <option value="{{ $kelas->id }}" @selected($user->kelas_id === $kelas->id)>{{ $kelas->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                        <button type="submit" class="btn btn-primary px-4" style="border-radius: 12px;">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}
@endsection
