@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="text-primary small fw-semibold">MANAJEMEN SEKOLAH</div>
        <h1 class="h3 fw-bold mb-1">Kegiatan Ekstrakurikuler</h1>
        <p class="text-secondary mb-0">Buat eskul, tentukan pembina, dan tunjuk admin eskul.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">+ Tambah Eskul</button>
</div>

<div class="row">
    @foreach($eskuls as $eskul)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-light rounded p-2" style="width: 50px; height: 50px;">
                        @if($eskul->logo)
                            <img src="{{ asset('storage/'.$eskul->logo) }}" class="w-100 h-100 object-fit-cover">
                        @else
                            <i class="bi bi-flag text-primary h4 mb-0"></i>
                        @endif
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $eskul->nama }}</h5>
                        <span class="badge {{ $eskul->aktif ? 'text-bg-success' : 'text-bg-secondary' }} small">
                            {{ $eskul->aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>

                <div class="mb-3 small">
                    <div class="text-muted">Pembina:</div>
                    <div class="fw-bold text-dark">{{ $eskul->pembina ? $eskul->pembina->name : 'Belum ditentukan' }}</div>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <div class="small fw-bold">{{ $eskul->members_count }} Siswa</div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.eskul.toggle', $eskul) }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm {{ $eskul->aktif ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                {{ $eskul->aktif ? 'Matikan' : 'Aktifkan' }}
                            </button>
                        </form>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#adminModal{{ $eskul->id }}">Admin Eskul</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Eskul Modal -->
    <div class="modal fade" id="adminModal{{ $eskul->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Admin Eskul {{ $eskul->nama }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Pilih anggota yang akan dijadikan Admin Eskul untuk mengelola chat dan pengumuman.</p>
                    <ul class="list-group list-group-flush">
                        @foreach($eskul->members as $member)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <div class="fw-bold">{{ $member->name }}</div>
                                    <div class="small text-muted">{{ ucfirst($member->role) }}</div>
                                </div>
                                <form action="{{ route('admin.eskul.set-admin', $eskul) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $member->id }}">
                                    <button class="btn btn-sm {{ $member->pivot->is_admin ? 'btn-success' : 'btn-outline-secondary' }}">
                                        {{ $member->pivot->is_admin ? 'Admin' : 'Jadikan Admin' }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.eskul.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Eskul Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Eskul</label>
                    <input type="text" name="nama" class="form-control" required placeholder="Contoh: Pramuka, Basket">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Pembina (Guru)</label>
                    <select name="pembina_id" class="form-select">
                        <option value="">Pilih Guru</option>
                        @foreach($gurus as $guru)
                            <option value="{{ $guru->id }}">{{ $guru->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Logo</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
