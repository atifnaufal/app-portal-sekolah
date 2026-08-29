@extends('layouts.mobile-app')
@section('content')
@php
    $totalStudents = $students->count();
    $presentCount = $students->filter(fn($s) => $s->absensi->isNotEmpty())->count();
    $absentCount = $totalStudents - $presentCount;
    $pctHadir = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100) : 0;
@endphp
<style>
    .stat-banner {
        background: var(--grad-hero);
        border-radius: var(--radius-lg); padding: 24px; margin-bottom: 24px; color: #fff;
        position: relative; overflow: hidden;
    }
    .stat-banner::after {
        content: ''; position: absolute; top: -20px; right: -20px;
        width: 100px; height: 100px; border-radius: 50%;
        background: radial-gradient(circle, rgba(99,102,241,0.2) 0%, transparent 70%);
    }
    .student-card { padding: 14px; }
    .student-avatar {
        width: 48px; height: 48px; border-radius: 14px;
        background: var(--surface); display: flex; align-items: center; justify-content: center;
        font-weight: 800; color: var(--mist); flex-shrink: 0; overflow: hidden;
    }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; }
</style>

<div class="pui-topbar" style="padding-top:16px;">
    <a href="{{ route('dashboard') }}" class="back"><i class="bi bi-chevron-left"></i></a>
    <h1>Presensi Siswa</h1>
    <div class="spacer"></div>
</div>

<main class="mobile-content px-3 mt-2">
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
        <h2 class="fw-bold mb-0" style="font-size: 15px; color:var(--ink);">Daftar Siswa</h2>
        <span class="pui-chip" style="background:#fff; border:1px solid var(--line);">{{ now()->translatedFormat('d F Y') }}</span>
    </div>

    <div class="mb-4 pui-card" style="background: var(--navy); padding: 16px; color: #fff; position: relative; overflow: hidden; border:none;">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-calendar3" style="color:#fbbf24;"></i>
            <div class="fw-bold" style="font-size: 14px;">Rekap Kehadiran</div>
        </div>
        <div class="small mb-3" style="color: rgba(255,255,255,.65);">Unduh rekap absensi siswa per bulan atau per tahun dalam format PDF / Excel.</div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <select id="absRecapPeriode" class="form-select form-select-sm rounded-pill text-dark fw-semibold" style="width:auto; background:#fff; border:none;">
                <option value="bulanan">Bulanan</option>
                <option value="tahunan">Tahunan</option>
            </select>
            <select id="absRecapTahun" class="form-select form-select-sm rounded-pill text-dark fw-semibold" style="width:auto; background:#fff; border:none;">
                @for($y = now()->year; $y >= now()->year - 4; $y--)
                    <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <select id="absRecapBulan" class="form-select form-select-sm rounded-pill text-dark fw-semibold" style="width:auto; background:#fff; border:none;">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endfor
            </select>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" onclick="goAbsRecap('pdf'); return false;" class="btn btn-sm rounded-pill px-3 fw-bold shadow-sm" style="background:#f59e0b; color:#0f172a;"><i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF</button>
            <button type="button" onclick="goAbsRecap('excel'); return false;" class="btn btn-sm rounded-pill px-3 fw-bold shadow-sm" style="background:#22c55e; color:#0f172a;"><i class="bi bi-file-earmark-excel-fill me-1"></i> Excel</button>
        </div>
    </div>
    <script>
        function goAbsRecap(type) {
            var periode = document.getElementById('absRecapPeriode').value;
            var tahun = document.getElementById('absRecapTahun').value;
            var bulan = document.getElementById('absRecapBulan').value;
            var base = "{{ route('absensi.recap') }}";
            var url = base + (type === 'excel' ? '/excel' : '')
                + '?periode=' + periode + '&tahun=' + tahun + '&kelas_id=' + {{ $user->kelas_id ?? 'null' }};
            if (periode === 'bulanan') url += '&bulan=' + bulan;
            window.open(url, '_blank');
        }
    </script>

    <div class="stagger">
        @forelse($students as $student)
            @php $absensi = $student->absensi->first(); @endphp
            <div class="student-card pui-card mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="student-avatar">
                        @if($student->foto)
                            <img src="{{ asset('storage/'.$student->foto) }}" class="w-100 h-100 object-fit-cover">
                        @else
                            {{ strtoupper(substr($student->name,0,1)) }}
                        @endif
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-bold text-truncate" style="font-size: 14px; color:var(--ink);">{{ $student->name }}</div>
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
                        <div style="font-size: 11px; font-weight: 800; color: var(--mist);">M: {{ $absensi && $absensi->waktu_masuk ? substr($absensi->waktu_masuk, 0, 5) : '--:--' }}</div>
                        <div style="font-size: 11px; font-weight: 800; color: var(--faint);">P: {{ $absensi && $absensi->waktu_pulang ? substr($absensi->waktu_pulang, 0, 5) : '--:--' }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="pui-empty">
                <div class="ico"><i class="bi bi-people"></i></div>
                <h4>Tidak ada siswa</h4>
                <p>Belum ada data siswa pada kelas ini.</p>
            </div>
        @endforelse
    </div>
</main>
@endsection
