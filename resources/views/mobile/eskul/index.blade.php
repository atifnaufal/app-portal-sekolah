@extends('layouts.mobile-app')

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('dashboard') }}" class="text-decoration-none text-dark"><i class="bi bi-arrow-left h4 mb-0"></i></a>
        <h5 class="fw-bold mb-0">Ekstrakurikuler</h5>
        <div style="width: 24px;"></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #a78bfa, #7c3aed);">
        <div class="card-body p-4 text-white">
            <h4 class="fw-bold mb-1">Pilih Eskul Kamu!</h4>
            <p class="small opacity-75 mb-0">Gabung dengan komunitas minat dan bakat di sekolah.</p>
        </div>
    </div>

    <div class="row g-3">
        @forelse($eskuls as $eskul)
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-4 bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; flex-shrink: 0;">
                                @if($eskul->logo)
                                    <img src="{{ asset('storage/'.$eskul->logo) }}" class="w-100 h-100 object-fit-cover rounded-4">
                                @else
                                    <i class="bi bi-people-fill text-primary h3 mb-0"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-width-0">
                                <h6 class="fw-bold mb-1">{{ $eskul->nama }}</h6>
                                <p class="small text-muted mb-0 text-truncate">{{ $eskul->deskripsi ?? 'Belum ada deskripsi.' }}</p>
                                <div class="small mt-1 text-primary fw-bold">{{ $eskul->members_count }} Anggota</div>
                            </div>
                            <div>
                                <form action="{{ route('eskul.join', $eskul) }}" method="POST">
                                    @csrf
                                    @php $isJoined = in_array($eskul->id, $myEskuls); @endphp
                                    <button class="btn btn-sm {{ $isJoined ? 'btn-outline-danger' : 'btn-primary' }} rounded-pill px-3">
                                        {{ $isJoined ? 'Keluar' : 'Gabung' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">Belum ada kegiatan eskul aktif.</div>
        @endforelse
    </div>
</div>
@endsection
