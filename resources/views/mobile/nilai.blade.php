@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.88); backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 70px; padding-bottom: 48px; }
    .ai-card {
        background: #fff; border: none; border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        position: relative; overflow: hidden; margin-bottom: 16px;
    }
    .ai-card::before {
        content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%;
        background: var(--blue); opacity: 0.85;
    }
    .grade-badge {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 16px;
    }
    .subject-icon {
        width: 40px; height: 40px; border-radius: 12px;
        background: #f1f5f9; color: #475569;
        display: flex; align-items: center; justify-content: center;
    }
    @keyframes slideUp { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<div class="page-header">
    <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px; letter-spacing: -0.4px;">Nilai Akademik</div>
</div>

<div class="page-container">
    <header class="mobile-hero" style="border-radius: 0 0 28px 28px; margin-bottom: 20px; background: linear-gradient(135deg, #0f172a, #1e293b); padding: 32px 24px 28px;">
        <div class="eyebrow" style="color: #94a3b8; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;">
            {{ $user->kelas?->nama ?? ($isGuru ? 'Panel Pengajar' : 'Akademik') }}
        </div>
        <div class="hero-title mt-2 text-white" style="font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">{{ $isGuru ? 'Kelola Nilai' : 'Laporan Nilai' }}</div>
        <p class="mb-0 mt-1" style="font-size: 12px; color: rgba(255,255,255,.6); line-height: 1.5;">
            {{ $isGuru ? 'Lihat dan evaluasi pencapaian siswa di mata pelajaran Anda.' : 'Pantau perkembangan nilai tugas dan ujian Anda semester ini.' }}
        </p>
    </header>

    <main class="mobile-content px-3">
        @if($isGuru)
            @if(!$selectedSubject)
                <h6 class="fw-bold mb-3 px-1">Mata Pelajaran Anda</h6>
                @forelse($mataPelajarans as $mp)
                    <a href="{{ route('nilai.index', ['subject_id' => $mp->id]) }}" class="card ai-card text-decoration-none text-dark" style="animation: slideUp 0.4s ease both;">
                        <div class="card-body p-4 d-flex align-items-center gap-3">
                            <div class="subject-icon">
                                <i class="bi bi-book-half"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold" style="font-size: 16px;">{{ $mp->nama }}</div>
                                <div class="small text-muted">{{ $mp->kelas?->nama }} · {{ $mp->kode }}</div>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-journal-x h1 text-muted"></i>
                        <p class="text-muted mt-2">Belum ada mata pelajaran yang diampu.</p>
                    </div>
                @endforelse
            @else
                <div class="d-flex align-items-center justify-content-between mb-4 px-1">
                    <div>
                        <h6 class="fw-bold mb-0">{{ $selectedSubject->nama }}</h6>
                        <div class="small text-muted">{{ $selectedSubject->kelas?->nama }}</div>
                    </div>
                    <a href="{{ route('nilai.index') }}" class="btn btn-light btn-sm rounded-pill px-3 fw-bold">Ganti</a>
                </div>

                @forelse($students as $siswaId => $studentNilais)
                    @php($siswa = $studentNilais->first()->siswa)
                    <div class="card ai-card" style="animation: slideUp 0.4s ease both;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="{{ $siswa->foto ? asset('storage/'.$siswa->foto) : 'https://ui-avatars.com/api/?name='.urlencode($siswa->name).'&background=random' }}"
                                     class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <div class="fw-bold">{{ $siswa->name }}</div>
                                    <div class="small text-muted">NIS: {{ $siswa->nik ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="row g-2">
                                @foreach($studentNilais as $n)
                                    <div class="col-4 text-center">
                                        <div class="p-2 rounded-3" style="background: #f8fafc;">
                                            <div class="small text-muted fw-bold" style="font-size: 9px; text-transform: uppercase;">{{ $n->semester }}</div>
                                            <div class="fw-bold text-primary">{{ $n->uas ?? $n->uts ?? $n->tugas }}</div>
                                            <div class="small" style="font-size: 8px;">UAS/UTS/TGS</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-people h1 text-muted"></i>
                        <p class="text-muted mt-2">Belum ada nilai siswa untuk mapel ini.</p>
                    </div>
                @endforelse
            @endif
        @else
            {{-- Siswa View --}}
            @forelse($nilais as $mpId => $mpNilais)
                @php($mp = $mpNilais->first()->mataPelajaran)
                @php
                    $avg = $mpNilais->avg(function($n) {
                        return ($n->tugas + $n->uts + $n->uas) / 3;
                    });
                    $tone = $avg >= 85 ? 'success' : ($avg >= 75 ? 'primary' : ($avg >= 60 ? 'warning' : 'danger'));
                    $bgColor = match($tone) {
                        'success' => '#f0fdf4',
                        'primary' => '#eff6ff',
                        'warning' => '#fffbeb',
                        'danger' => '#fef2f2',
                    };
                    $textColor = match($tone) {
                        'success' => '#16a34a',
                        'primary' => '#2563eb',
                        'warning' => '#d97706',
                        'danger' => '#dc2626',
                    };
                @endphp
                <div class="card ai-card" style="animation: slideUp 0.4s ease both;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h3 class="h6 fw-bold mb-1" style="font-size: 16px;">{{ $mp->nama }}</h3>
                                <p class="small text-muted mb-0">KKM: {{ $mp->kkm ?? 75 }} · Semester {{ $mpNilais->first()->semester }}</p>
                            </div>
                            <div class="grade-badge" style="background: {{ $bgColor }}; color: {{ $textColor }};">
                                {{ round($avg) }}
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-4 text-center">
                                <div class="fw-bold" style="font-size: 14px;">{{ $mpNilais->avg('tugas') ?: '-' }}</div>
                                <div class="small text-muted" style="font-size: 10px; font-weight: 700; text-transform: uppercase;">Tugas</div>
                            </div>
                            <div class="col-4 text-center border-start border-end">
                                <div class="fw-bold" style="font-size: 14px;">{{ $mpNilais->avg('uts') ?: '-' }}</div>
                                <div class="small text-muted" style="font-size: 10px; font-weight: 700; text-transform: uppercase;">UTS</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="fw-bold" style="font-size: 14px;">{{ $mpNilais->avg('uas') ?: '-' }}</div>
                                <div class="small text-muted" style="font-size: 10px; font-weight: 700; text-transform: uppercase;">UAS</div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-award h1 text-muted"></i>
                    <div class="fw-bold mt-2">Belum ada nilai</div>
                    <p class="small text-muted mt-1">Nilai Anda akan muncul di sini setelah diproses guru.</p>
                </div>
            @endforelse
        @endif
    </main>
</div>
@endsection
