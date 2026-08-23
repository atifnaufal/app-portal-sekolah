@extends('layouts.mobile-page')
@section('content')
<header class="mobile-hero">
    <div class="eyebrow">MONITORING KELAS</div>
    <div class="hero-title mt-2">Daftar Kehadiran Siswa</div>
    <div class="class-pill mt-3">{{ $user->kelas?->nama ?? 'Kelas Anda' }}</div>
</header>
<main class="mobile-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="section-title mb-0">Status Hari Ini</h2>
        <span class="small text-secondary">{{ now()->format('d M Y') }}</span>
    </div>

    <div class="stagger">
        @forelse($students as $student)
            @php $absensi = $student->absensi->first(); @endphp
            <div class="card mobile-card mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar shadow-sm" style="background:#e8efff; color:var(--blue); width:40px; height:40px; font-size:14px;">
                                @if($student->foto)
                                    <img src="{{ asset('storage/'.$student->foto) }}" alt="P" style="width:100%;height:100%;object-fit:cover;border-radius:inherit">
                                @else
                                    {{ strtoupper(substr($student->name,0,1)) }}
                                @endif
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ $student->name }}</div>
                                <div class="small text-secondary">
                                    @if(!$absensi)
                                        <span class="text-danger">&#10007; Belum Absen</span>
                                    @else
                                        @if($absensi->status === 'terlambat')
                                            <span class="text-warning">&#9200; Terlambat</span>
                                        @else
                                            <span class="text-success">&#10003; Hadir</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="small fw-bold {{ $absensi && $absensi->waktu_masuk ? 'text-success' : 'text-muted' }}">
                                M: {{ $absensi && $absensi->waktu_masuk ? substr($absensi->waktu_masuk, 0, 5) : '--:--' }}
                            </div>
                            <div class="small fw-bold {{ $absensi && $absensi->waktu_pulang ? 'text-primary' : 'text-muted' }}">
                                P: {{ $absensi && $absensi->waktu_pulang ? substr($absensi->waktu_pulang, 0, 5) : '--:--' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 opacity-50">
                <div class="h1">&#128101;</div>
                <p>Belum ada siswa di kelas ini.</p>
            </div>
        @endforelse
    </div>
</main>
@endsection
