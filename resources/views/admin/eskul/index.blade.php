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

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">{{ session('error') }}</div>
@endif

<div class="row">
    @foreach($eskuls as $eskul)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card stat-card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light rounded p-2" style="width: 50px; height: 50px;">
                            @if($eskul->logo)
                                <img src="{{ asset('storage/'.$eskul->logo) }}" data-name="{{ $eskul->nama }}" onerror="adminLogoFallback(this);" class="w-100 h-100 object-fit-cover rounded">
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
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal{{ $eskul->id }}"><i class="bi bi-pencil me-2"></i> Edit</button></li>
                            <li>
                                <form action="{{ route('admin.eskul.destroy', $eskul) }}" method="POST" onsubmit="return confirm('Hapus eskul ini?')">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> Hapus</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mb-3 small">
                    <div class="text-muted">Pembina:</div>
                    <div class="fw-bold text-dark">{{ $eskul->pembina ? $eskul->pembina->name : 'Belum ditentukan' }}</div>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <div class="small fw-bold text-primary">{{ $eskul->members_count }} Anggota</div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.eskul.toggle', $eskul) }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm {{ $eskul->aktif ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill px-3">
                                {{ $eskul->aktif ? 'Matikan' : 'Aktifkan' }}
                            </button>
                        </form>
                        <button class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#adminModal{{ $eskul->id }}">Admin</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal{{ $eskul->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.eskul.update', $eskul) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
                @csrf @method('PUT')
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Edit Eskul: {{ $eskul->nama }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Eskul</label>
                        <input type="text" name="nama" class="form-control rounded-3" value="{{ $eskul->nama }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pembina (Guru) <span class="text-muted">— 1 guru = 1 eskul</span></label>
                        <select name="pembina_id" class="form-select rounded-3">
                            <option value="">Pilih Guru</option>
                            @foreach($gurus as $guru)
                                @php
                                    $alreadyAdminElsewhere = in_array($guru->id, $adminGuruIds) && $eskul->pembina_id != $guru->id;
                                @endphp
                                <option value="{{ $guru->id }}" {{ $eskul->pembina_id == $guru->id ? 'selected' : '' }} {{ $alreadyAdminElsewhere ? 'disabled' : '' }}>
                                    {{ $guru->name }}{{ $alreadyAdminElsewhere ? ' (sudah admin eskul lain)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if($eskul->pembina)
                            <div class="form-text small text-success">{{ $eskul->pembina->name }} adalah pembina saat ini.</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="3">{{ $eskul->deskripsi }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ganti Logo</label>
                        <input type="file" name="logo" class="form-control rounded-3" accept="image/jpeg,image/png,image/jpg,image/webp">
                        <div class="form-text small">Format: JPG, PNG, WEBP. Maks 2MB.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Admin Eskul Modal -->
    <div class="modal fade" id="adminModal{{ $eskul->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Manajemen Admin: {{ $eskul->nama }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Anggota eskul yang ditunjuk sebagai admin dapat mengelola anggota &amp; chat grup. <strong>Catatan:</strong> satu orang hanya bisa menjadi admin 1 (satu) eskul.</p>
                    @php $approvedMembers = $eskul->members->where('pivot.status', 'approved'); @endphp
                    <ul class="list-group list-group-flush">
                        @forelse($approvedMembers as $member)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-light">
                                <div class="min-width-0">
                                    <div class="fw-bold text-truncate">{{ $member->name }}
                                        @if($member->role === 'guru')<span class="badge bg-primary-subtle text-primary ms-1 small">Guru</span>@endif
                                    </div>
                                    <div class="small text-muted">{{ ucfirst($member->role) }} · {{ $member->kelas?->nama ?? 'Umum' }}</div>
                                </div>
                                <form action="{{ route('admin.eskul.set-admin', $eskul) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $member->id }}">
                                    @php
                                        $isAdminElsewhere = in_array($member->id, $adminGuruIds) && !$member->pivot->is_admin;
                                    @endphp
                                    @if($member->pivot->is_admin)
                                        <button class="btn btn-sm btn-success rounded-pill px-3" type="submit">Admin</button>
                                    @else
                                        <button class="btn btn-sm {{ $isAdminElsewhere ? 'btn-outline-secondary' : 'btn-outline-primary' }} rounded-pill px-3"
                                                type="submit" @if($isAdminElsewhere) disabled title="Sudah admin eskul lain — 1 orang = 1 eskul" @endif>
                                            {{ $isAdminElsewhere ? 'Sudah admin lain' : 'Jadikan Admin' }}
                                        </button>
                                    @endif
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted border-0">Belum ada anggota yang disetujui.</li>
                        @endforelse
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
        <form action="{{ route('admin.eskul.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Tambah Eskul Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nama Eskul</label>
                    <input type="text" name="nama" class="form-control rounded-3" required placeholder="Misal: Futsal, Coding Club">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Pembina (Guru) <span class="text-muted">— 1 guru = 1 eskul</span></label>
                    <select name="pembina_id" class="form-select rounded-3">
                        <option value="">Pilih Guru</option>
                        @foreach($gurus as $guru)
                            <option value="{{ $guru->id }}" {{ in_array($guru->id, $adminGuruIds) ? 'disabled' : '' }}>
                                {{ $guru->name }}{{ in_array($guru->id, $adminGuruIds) ? ' (sudah admin eskul lain)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text small text-muted">Guru yang sudah menjadi pembina eskul lain otomatis disembunyikan.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Deskripsi Singkat</label>
                    <textarea name="deskripsi" class="form-control rounded-3" rows="3" placeholder="Jelaskan visi atau kegiatan eskul..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Logo Eskul</label>
                    <input type="file" name="logo" class="form-control rounded-3" accept="image/jpeg,image/png,image/jpg,image/webp">
                    <div class="form-text small">Mendukung format JPG, PNG, dan WEBP.</div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Eskul</button>
            </div>
        </form>
    </div>
</div>
@endsection

<script>
function adminLogoFallback(el) {
    var name = el.getAttribute('data-name') || 'E';
    var letter = (name.charAt(0) || 'E').toUpperCase();
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200">'
        + '<defs><linearGradient id="ag" x1="0" y1="0" x2="1" y2="1">'
        + '<stop offset="0%" stop-color="#7c3aed"/><stop offset="100%" stop-color="#a78bfa"/>'
        + '</linearGradient></defs>'
        + '<rect width="100%" height="100%" fill="url(#ag)"/>'
        + '<text x="50%" y="54%" font-family="sans-serif" font-size="90" font-weight="700" fill="#fff" text-anchor="middle" dominant-baseline="middle">' + letter + '</text></svg>';
    el.onerror = null;
    el.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
}
</script>
