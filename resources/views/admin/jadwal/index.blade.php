@extends('layouts.app')

@section('content')
<style>
    .ajd-card { border-radius: 20px; border: 1px solid var(--border); overflow: hidden; }
    .ajd-table th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); }
    .ajd-table td { vertical-align: middle; }
    .ajd-badge { font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.03em; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Jadwal Pelajaran</h1>
        <p class="text-muted mb-0 small">Kelola agenda mengajar guru dan jadwal kelas. Jadwal yang dibuat di sini otomatis tampil untuk guru &amp; siswa.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="bi bi-plus-circle me-2"></i>Tambah Jadwal
    </button>
</div>

{{-- Filter --}}
<div class="ajd-card card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Kelas</label>
                <select name="kelas_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    @foreach($kelases as $k)
                        <option value="{{ $k->id }}" @selected((string) $k->id === (string) $kelasId)>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Hari</label>
                <select name="hari" class="form-select" onchange="this.form.submit()">
                    <option value="semua" @selected($hari === null || $hari === 'semua')>Semua Hari</option>
                    @foreach(['senin','selasa','rabu','kamis','jumat','sabtu'] as $h)
                        <option value="{{ $h }}" @selected($hari === $h)>{{ ucfirst($h) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-secondary w-100">Reset Filter</a>
            </div>
        </form>
    </div>
</div>

{{-- Daftar Jadwal --}}
<div class="ajd-card card shadow-sm">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <span class="fw-bold">Daftar Jadwal ({{ $jadwals->count() }})</span>
    </div>
    <div class="table-responsive">
        <table class="table ajd-table mb-0">
            <thead class="table-light">
                <tr>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas</th>
                    <th>Guru</th>
                    <th>Ruangan</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwals as $j)
                    <tr>
                        <td><span class="ajd-badge" style="background:#eef2ff;color:#4338ca;">{{ ucfirst($j->hari) }}</span></td>
                        <td class="fw-bold">{{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}</td>
                        <td class="fw-semibold">{{ $j->mataPelajaran->nama }}</td>
                        <td>{{ $j->kelas->nama }}</td>
                        <td>{{ $j->guru->name }}</td>
                        <td>{{ $j->ruangan }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $j->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.jadwal.destroy', $j) }}" class="d-inline" onsubmit="return confirm('Hapus jadwal ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    @include('admin.jadwal.modal-edit', ['j' => $j])
                @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada jadwal. Tambahkan jadwal baru.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Create --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.jadwal.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @include('admin.jadwal.fields')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
