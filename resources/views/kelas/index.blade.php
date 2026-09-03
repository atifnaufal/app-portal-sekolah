@extends('layouts.app')

@section('content')
@if(session('user_role') === 'admin')
<div class="cp-shell">@include('admin.partials.sidebar')<div class="cp-main">
@endif
<style>
    .ad-card { border-radius: 24px; border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow-sm); background: #fff; }
    .ad-card-head { padding: 24px 30px; border-bottom: 1px solid var(--border); background: #fff; }

    .table-premium thead th {
        background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.05em; padding: 16px 24px; border-bottom: 1px solid #f1f5f9;
    }
    .table-premium tbody td { padding: 16px 24px; border-bottom: 1px solid #f8fafc; vertical-align: middle; }

    .btn-action { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; border: 1px solid #e2e8f0; background: #fff; color: #64748b; }
    .btn-action:hover { background: #f1f5f9; color: var(--navy); border-color: #cbd5e1; }
    .btn-action.btn-edit:hover { color: var(--blue); border-color: var(--blue-light); background: #eff6ff; }
    .btn-action.btn-delete:hover { color: #ef4444; border-color: #fecaca; background: #fef2f2; }

    .stat-pill {
        padding: 20px 24px; border-radius: 20px; border: 1px solid var(--border);
        background: #fff; display: flex; align-items: center; gap: 16px; transition: all 0.3s;
    }
    .stat-pill:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; }

    .badge-premium { padding: 6px 14px; border-radius: 99px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
    .badge-blue { background: #eff6ff; color: #3b82f6; }
    .badge-green { background: #f0fdf4; color: #22c55e; }
</style>

<div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-4">
    <div>
        <div class="text-primary small fw-bold text-uppercase mb-1" style="letter-spacing: 0.1em;">Master Data</div>
        <h1 class="h2 fw-extrabold mb-1" style="color: var(--navy); letter-spacing: -0.02em;">Data Kelas</h1>
        <p class="text-secondary mb-0">Kelola rombongan belajar, tingkat, dan pembina kelas.</p>
    </div>
    <a href="{{ route('kelas.create') }}" class="btn btn-primary px-4 py-2 fw-bold" style="border-radius:14px;">
        <i class="bi bi-plus-circle-fill me-2"></i> Tambah Kelas
    </a>
</div>

{{-- Summary Cards --}}
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="stat-pill">
            <div class="stat-icon" style="background: #f5f3ff; color: #7c3aed;"><i class="bi bi-building"></i></div>
            <div>
                <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Total Kelas</div>
                <div class="h4 fw-extrabold mb-0">{{ number_format($totalKelas) }}</div>
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
            <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;"><i class="bi bi-person-badge"></i></div>
            <div>
                <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Total Guru</div>
                <div class="h4 fw-extrabold mb-0">{{ number_format($totalGuru) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="ad-card">
    <div class="table-responsive">
        <table class="table table-premium mb-0">
            <thead>
                <tr>
                    <th>Nama Kelas</th>
                    <th>Tingkat</th>
                    <th>Tahun Ajaran</th>
                    <th class="text-center">Siswa</th>
                    <th class="text-center">Guru</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kelases as $kelas)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 15px;">{{ $kelas->nama }}</div>
                        </td>
                        <td>
                            <span class="badge-premium badge-blue">Tingkat {{ $kelas->tingkat }}</span>
                        </td>
                        <td>
                            <div class="text-muted fw-semibold">{{ $kelas->tahun_ajaran }}</div>
                        </td>
                        <td class="text-center">
                            <span class="fw-extrabold text-primary">{{ number_format($kelas->siswa_count) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-extrabold text-success">{{ number_format($kelas->guru_count) }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('kelas.edit', $kelas) }}" class="btn-action btn-edit" title="Edit Kelas">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form method="POST" action="{{ route('kelas.destroy', $kelas) }}" onsubmit="return confirm('Hapus kelas ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-action btn-delete" title="Hapus Kelas">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-inbox h1"></i></div>
                            <div class="text-muted">Belum ada data kelas.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if(session('user_role') === 'admin')
</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}
@endif
@endsection
