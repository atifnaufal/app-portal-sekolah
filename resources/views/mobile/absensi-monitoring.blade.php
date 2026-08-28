@extends('layouts.mobile-app')
@section('content')
@php
    $totalStudents = $students->count();
    $presentCount = $students->filter(fn($s) => $s->absensi->isNotEmpty())->count();
    $absentCount = $totalStudents - $presentCount;
    $pctHadir = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100) : 0;
@endphp
<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.88); backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 70px; padding-bottom: 48px; }

    .stat-banner {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 32px; padding: 24px; margin-bottom: 24px; color: #fff;
        position: relative; overflow: hidden;
    }
    .stat-banner::after {
        content: ''; position: absolute; top: -20px; right: -20px;
        width: 100px; height: 100px; border-radius: 50%;
        background: radial-gradient(circle, rgba(99,102,241,0.2) 0%, transparent 70%);
    }

    .student-card {
        background: #fff; border-radius: 20px; padding: 14px;
        margin-bottom: 12px; border: 1px solid #f1f5f9;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        transition: all 0.2s;
    }
    .student-avatar {
        width: 48px; height: 48px; border-radius: 14px;
        background: #f1f5f9; display: flex; align-items: center; justify-content: center;
        font-weight: 800; color: #475569; flex-shrink: 0; overflow: hidden;
    }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; }
</style>

<div class="page-header">
    <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px; letter-spacing: -0.4px;">Presensi Siswa</div>
</div>

<div class="page-container">
    <div class="px-3 mt-2">
        <div class="stat-banner">
            <div class="eyebrow" style="color: rgba(255,255,255,0.5);">MONITORING KELAS</div>
            <h1 class="h3 fw-bold mt-1 mb-3">{{ $user->kelas?->nama ?? 'Kelas Anda' }}</h1>

            <div class="row g-2">
                <div class="col-4">
                    <div style="background: rgba(255,255,255,0.08); padding: 10px; border-radius: 16px; text-align: center;">
                        <div style="font-size: 18px; font-weight: 800;">{{ $totalStudents }}</div>
                        <div style="font-size: 9px; font-weight: 700; opacity: 0.6; text-transform: uppercase;">Siswa</div>
                    </div>
                </div>
                <div class="col-4">
                    <div style="background: rgba(22,163,74,0.15); border: 1px solid rgba(22,163,74,0.3); padding: 10px; border-radius: 16px; text-align: center;">
                        <div style="font-size: 18px; font-weight: 800; color: #4ade80;">{{ $presentCount }}</div>
                        <div style="font-size: 9px; font-weight: 700; color: #4ade80; text-transform: uppercase;">Hadir</div>
                    </div>
                </div>
                <div class="col-4">
                    <div style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); padding: 10px; border-radius: 16px; text-align: center;">
                        <div style="font-size: 18px; font-weight: 800; color: #f87171;">{{ $absentCount }}</div>
                        <div style="font-size: 9px; font-weight: 700; color: #f87171; text-transform: uppercase;">Belum</div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <div class="d-flex justify-content-between small fw-bold mb-1" style="font-size: 10px; opacity: 0.8;">
                    <span>RASIO KEHADIRAN</span>
                    <span>{{ $pctHadir }}%</span>
                </div>
                <div style="height: 6px; background: rgba(255,255,255,0.1); border-radius: 100px; overflow: hidden;">
                    <div style="width: {{ $pctHadir }}%; height: 100%; background: #4ade80; border-radius: 100px;"></div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 px-1">
            <h2 class="fw-bold mb-0" style="font-size: 15px;">Daftar Siswa</h2>
            <span class="badge bg-light text-dark rounded-pill">{{ now()->translatedFormat('d F Y') }}</span>
        </div>

        <div class="stagger">
            @forelse($students as $student)
                @php $absensi = $student->absensi->first(); @endphp
                <div class="student-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="student-avatar">
                            @if($student->foto)
                                <img src="{{ asset('storage/'.$student->foto) }}" class="w-100 h-100 object-fit-cover">
                            @else
                                {{ strtoupper(substr($student->name,0,1)) }}
                            @endif
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-bold text-dark text-truncate" style="font-size: 14px;">{{ $student->name }}</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                @if(!$absensi)
                                    <div class="status-dot bg-danger"></div>
                                    <span class="small text-danger fw-bold" style="font-size: 11px;">Belum Absen</span>
                                @else
                                    <div class="status-dot {{ $absensi->status === 'terlambat' ? 'bg-warning' : 'bg-success' }}"></div>
                                    <span class="small {{ $absensi->status === 'terlambat' ? 'text-warning' : 'text-success' }} fw-bold" style="font-size: 11px;">
                                        {{ $absensi->status === 'terlambat' ? 'Terlambat' : 'Hadir Tepat Waktu' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <div style="font-size: 11px; font-weight: 800; color: #64748b;">M: {{ $absensi && $absensi->waktu_masuk ? substr($absensi->waktu_masuk, 0, 5) : '--:--' }}</div>
                            <div style="font-size: 11px; font-weight: 800; color: #94a3b8;">P: {{ $absensi && $absensi->waktu_pulang ? substr($absensi->waktu_pulang, 0, 5) : '--:--' }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-box">
                    <i class="bi bi-people h1 opacity-25"></i>
                    <div class="fw-bold mt-2">Tidak ada siswa</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
