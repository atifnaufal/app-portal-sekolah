@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="text-primary small fw-semibold">MANAJEMEN PERPUSTAKAAN</div>
        <h1 class="h3 fw-bold mb-1">Koleksi Buku Digital</h1>
        <p class="text-secondary mb-0">Kelola buku-buku digital yang dapat diakses oleh siswa dan guru.</p>
    </div>
    <div>
        <a href="{{ route('admin.perpustakaan.kategori.index') }}" class="btn btn-outline-primary me-2">Kelola Kategori</a>
        <a href="{{ route('admin.perpustakaan.create') }}" class="btn btn-primary">+ Tambah Buku</a>
    </div>
</div>

<div class="card table-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">Cover</th>
                        <th>Judul Buku</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bukus as $buku)
                    <tr>
                        <td>
                            @if($buku->cover)
                                <img src="{{ asset('storage/'.$buku->cover) }}" class="rounded shadow-sm" style="width: 50px; aspect-ratio: 2/3; object-fit: cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; aspect-ratio: 2/3;">
                                    <i class="bi bi-book text-muted small"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $buku->judul }}</strong>
                            <div class="small text-secondary">{{ $buku->tahun_terbit ?? '-' }} · {{ $buku->penerbit ?? '-' }}</div>
                        </td>
                        <td><span class="badge text-bg-info">{{ $buku->kategori->nama }}</span></td>
                        <td>{{ $buku->penulis ?? '-' }}</td>
                        <td>{{ $buku->stok }}</td>
                        <td>
                            <a href="{{ route('admin.perpustakaan.edit', $buku) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" action="{{ route('admin.perpustakaan.destroy', $buku) }}" class="d-inline" onsubmit="return confirm('Hapus buku ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-5">
                            Belum ada koleksi buku. <a href="{{ route('admin.perpustakaan.create') }}">Tambah sekarang.</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
