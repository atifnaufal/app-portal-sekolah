@extends('layouts.app')
@php $canManageMahasiswa = session('user_role') === 'admin'; @endphp
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Data Siswa (Mahasiswa)</h1>
        <p class="text-secondary mb-0">Kelola seluruh data peserta didik (terintegrasi dengan LMS, nilai & jadwal).</p>
    </div>
    @if($canManageMahasiswa)
        <a href="{{ route('mahasiswa.create') }}" class="btn btn-primary">+ Tambah siswa</a>
    @endif
</div>

<div class="card table-card">
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-5">
                <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari NIS/NIM, nama, atau email...">
            </div>
            <div class="col-auto"><button class="btn btn-outline-secondary">Cari</button></div>
            @if(request('search'))
                <div class="col-auto"><a href="{{ route('mahasiswa.index') }}" class="btn btn-light">Reset</a></div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>NIS/NIM</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        @if($canManageMahasiswa)<th class="text-end">Aksi</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswas as $mahasiswa)
                        <tr>
                            <td><span class="badge text-bg-light">{{ $mahasiswa->nik ?? '-' }}</span></td>
                            <td class="fw-semibold">{{ $mahasiswa->name }}
                                <div class="small text-secondary">{{ $mahasiswa->email }}</div>
                            </td>
                            <td>{{ $mahasiswa->kelas?->nama ?? '-' }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $mahasiswa->aktif ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ $mahasiswa->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            @if($canManageMahasiswa)
                                <td class="text-end">
                                    <a href="{{ route('mahasiswa.edit', $mahasiswa) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form class="d-inline" method="POST" action="{{ route('mahasiswa.destroy', $mahasiswa) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus data siswa ini? Seluruh data terkait akan ikut terhapus.')">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManageMahasiswa ? 5 : 4 }}" class="text-center text-secondary py-4">
                                Belum ada data siswa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $mahasiswas->links() }}
    </div>
</div>
@endsection
