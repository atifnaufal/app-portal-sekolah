@extends('layouts.mobile-app')

@section('content')
<header class="mobile-hero"><div class="eyebrow">RUANG BELAJAR</div><div class="hero-title mt-2">Tugas sekolah</div><div class="class-pill mt-3">{{ $user->kelas?->nama ?? 'Belum ada kelas' }}</div></header>
<main class="mobile-content">
    <div class="d-flex justify-content-between align-items-center mb-4"><p class="text-secondary small mb-0">Tugas aktif untuk kelasmu.</p>@if($user->role === 'guru')<a href="{{ route('tugas.create') }}" class="btn btn-sm btn-primary">+ Buat</a>@endif</div>
    <div class="stagger">
        @forelse($tugas as $item)
            @php($submission = $item->pengumpulan->first())
            <a href="{{ route('tugas.show', $item) }}" class="card mobile-card tap-card text-decoration-none text-dark mb-3"><div class="card-body"><div class="d-flex justify-content-between align-items-start gap-3"><div><div class="small text-primary fw-bold">{{ $item->kelas->nama }}</div><h2 class="h6 fw-bold mt-2 mb-1">{{ $item->judul }}</h2></div><span class="badge rounded-pill text-bg-warning">{{ $item->batas_pengumpulan?->format('d M') ?? 'Terbuka' }}</span></div><p class="small text-secondary mt-2 mb-0">{{ $item->deskripsi ?: 'Tidak ada deskripsi tambahan.' }}</p>@if($submission && $submission->revisi_aktif)<div class="small text-warning fw-semibold mt-3">Perlu revisi, buka untuk kirim ulang</div>@else<div class="small text-primary fw-semibold mt-3">Buka detail &rarr;</div>@endif</div></a>
        @empty
            <div class="card mobile-card"><div class="card-body text-secondary">Tidak ada tugas aktif saat ini.</div></div>
        @endforelse
    </div>
    @if($user->role === 'siswa' && $completedTugas->isNotEmpty())
        <h2 class="section-title mt-4 mb-3">Tugas selesai dan nilai</h2>
        @foreach($completedTugas as $item)
            @php($submission = $item->pengumpulan->first())
            <div class="card mobile-card mb-2"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="fw-semibold">{{ $item->judul }}</div><div class="small text-secondary">{{ $item->batas_pengumpulan?->format('d M Y') ?? 'Selesai' }}</div></div><div class="text-end"><div class="h5 fw-bold text-success mb-0">{{ $submission->nilai }}</div><div class="small text-secondary">Nilai</div></div></div>@if($submission->feedback_guru)<div class="card-body pt-0 small text-secondary">{{ $submission->feedback_guru }}</div>@endif</div>
        @endforeach
    @endif
</main>
@endsection
