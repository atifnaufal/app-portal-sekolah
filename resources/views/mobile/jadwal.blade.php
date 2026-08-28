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
    .day-header {
        position: sticky; top: 65px; z-index: 900;
        background: #f8fafc; padding: 12px 4px;
        font-weight: 800; font-size: 13px; text-transform: uppercase;
        letter-spacing: 0.1em; color: #64748b;
        display: flex; align-items: center; gap: 8px;
    }
    .day-header::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

    .schedule-card {
        background: #fff; border: none; border-radius: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        margin-bottom: 12px; display: flex; overflow: hidden;
    }
    .time-strip {
        width: 70px; background: #f8fafc; padding: 16px 8px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        border-right: 1px dashed #e2e8f0;
    }
    .time-val { font-weight: 800; color: #1e293b; font-size: 13px; }
    .time-end { font-size: 10px; color: #94a3b8; font-weight: 600; }

    .content-area { padding: 16px 20px; flex: 1; position: relative; }
    .subject-name { font-weight: 800; color: #1e293b; font-size: 15px; margin-bottom: 2px; }
    .meta-info { font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 8px; }

    .status-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: #cbd5e1; position: absolute; top: 20px; right: 20px;
    }
    .status-dot.active { background: #10b981; box-shadow: 0 0 8px rgba(16,185,129,0.4); }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="page-header">
    <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px; letter-spacing: -0.4px;">Jadwal Pelajaran</div>
</div>

<div class="page-container">
    <header class="mobile-hero" style="border-radius: 0 0 28px 28px; margin-bottom: 10px; background: linear-gradient(135deg, #0f172a, #1e293b); padding: 32px 24px 28px;">
        <div class="eyebrow" style="color: #94a3b8; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;">
            {{ $user->kelas?->nama ?? ($isGuru ? 'Guru Pengampu' : 'Akademik') }}
        </div>
        <div class="hero-title mt-2 text-white" style="font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">{{ $isGuru ? 'Agenda Mengajar' : 'Agenda Belajar' }}</div>
        <p class="mb-0 mt-1" style="font-size: 12px; color: rgba(255,255,255,.6); line-height: 1.5;">
            {{ $isGuru ? 'Pantau jadwal mengajar Anda setiap harinya.' : 'Lihat jadwal mata pelajaran Anda minggu ini.' }}
        </p>
    </header>

    <main class="mobile-content px-3">
        @php($currentDay = \Carbon\Carbon::now()->translatedFormat('l'))
        @php($currentTime = \Carbon\Carbon::now()->format('H:i'))

        @forelse($jadwals as $hari => $list)
            <div class="day-header mt-3 mb-2">{{ $hari }}</div>

            @foreach($list as $j)
                @php($isActive = ($hari === $currentDay && $currentTime >= $j->jam_mulai && $currentTime <= $j->jam_selesai))
                <div class="schedule-card" style="animation: fadeIn 0.4s ease both;">
                    <div class="time-strip">
                        <div class="time-val">{{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }}</div>
                        <div class="time-end">{{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}</div>
                    </div>
                    <div class="content-area">
                        @if($isActive)
                            <div class="status-dot active"></div>
                        @endif
                        <div class="subject-name">{{ $j->mataPelajaran->nama }}</div>
                        <div class="meta-info mb-1">
                            <i class="bi bi-geo-alt"></i> {{ $j->ruangan ?: 'Ruang Kelas' }}
                        </div>
                        <div class="meta-info">
                            <i class="bi {{ $isGuru ? 'bi-people' : 'bi-person-badge' }}"></i>
                            {{ $isGuru ? $j->kelas?->nama : $j->guru?->name }}
                        </div>
                    </div>
                </div>
            @endforeach
        @empty
            <div class="text-center py-5">
                <i class="bi bi-calendar-x h1 text-muted"></i>
                <div class="fw-bold mt-2">Belum ada jadwal</div>
                <p class="small text-muted">Hubungi bagian kurikulum untuk informasi jadwal.</p>
            </div>
        @endforelse
    </main>
</div>
@endsection
