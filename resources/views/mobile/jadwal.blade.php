@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .lms-topbar {
        position: sticky; top: 0; z-index: 1000;
        background: rgba(255,255,255,0.9); backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--line);
        padding: 12px 16px; display: flex; align-items: center; gap: 12px;
    }
    .lms-body { max-width: 640px; margin: 0 auto; padding: 16px 16px 48px; }

    .day-header {
        display: flex; align-items: center; gap: 8px;
        font-weight: 800; font-size: 13px; text-transform: uppercase;
        letter-spacing: 0.1em; color: var(--mist);
    }
    .day-header::after { content: ''; flex: 1; height: 1px; background: var(--line-strong); }

    .schedule-card {
        background: var(--surface-card); border: 1px solid var(--line);
        border-radius: var(--radius-md); box-shadow: var(--shadow-card);
        margin-bottom: 14px; display: flex; overflow: hidden;
        animation: fadeUp 0.4s ease both;
    }
    .time-strip {
        width: 78px; background: var(--surface); padding: 20px 8px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        border-right: 1px dashed var(--line-strong); flex-shrink: 0;
    }
    .time-val { font-weight: 800; color: var(--ink); font-size: 14px; letter-spacing: -0.5px; }
    .time-end { font-size: 10px; color: var(--faint); font-weight: 700; margin-top: 2px; }

    .content-area { padding: 18px 20px; flex: 1; position: relative; }
    .subject-name { font-weight: 800; color: var(--ink); font-size: 16px; margin-bottom: 4px; letter-spacing: -0.2px; }
    .meta-info { font-size: 11px; color: var(--mist); display: flex; align-items: center; gap: 8px; font-weight: 600; }
    .meta-info i { color: var(--blue); }

    .status-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: var(--faint); position: absolute; top: 20px; right: 20px;
    }
    .status-dot.active { background: #10b981; box-shadow: 0 0 8px rgba(16,185,129,0.4); }

    @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="lms-topbar">
    <a href="{{ route('dashboard') }}" class="pui-btn pui-btn-ghost pui-btn-sm pui-btn-round" style="padding:0;width:40px;height:40px;">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div class="fw-bold" style="font-size:18px;letter-spacing:-0.4px;">Jadwal Pelajaran</div>
</div>

<div class="lms-body">
    <header class="mobile-hero" style="margin-bottom:16px;">
        <div class="eyebrow">
            {{ $user->kelas?->nama ?? ($isGuru ? 'Guru Pengampu' : 'Akademik') }}
        </div>
        <div class="hero-title mt-2">{{ $isGuru ? 'Agenda Mengajar' : 'Agenda Belajar' }}</div>
        <div class="hero-sub" style="font-size:12px;color:rgba(255,255,255,.6);line-height:1.5;margin-top:4px;">
            {{ $isGuru ? 'Pantau jadwal mengajar Anda setiap harinya.' : 'Lihat jadwal mata pelajaran Anda minggu ini.' }}
        </div>

        @if(isset($stat) && $stat['total'] > 0)
            <div style="display:flex;gap:10px;margin-top:18px;">
                <div style="flex:1;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:16px;padding:12px;text-align:center;">
                    <div style="font-size:22px;font-weight:800;">{{ $stat['total'] }}</div>
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;opacity:0.6;">Total Sesi</div>
                </div>
                <div style="flex:1;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:16px;padding:12px;text-align:center;">
                    <div style="font-size:22px;font-weight:800;">{{ $stat['mapel'] }}</div>
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;opacity:0.6;">{{ $isGuru ? 'Mapel Diampu' : 'Mata Pelajaran' }}</div>
                </div>
                <div style="flex:1;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:16px;padding:12px;text-align:center;">
                    <div style="font-size:22px;font-weight:800;color:#4ade80;">{{ $stat['hariIni']->count() }}</div>
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;opacity:0.6;">Hari Ini</div>
                </div>
            </div>
        @endif
    </header>

    <main class="mobile-content">
        @php($currentDay = \Carbon\Carbon::now()->translatedFormat('l'))
        @php($currentTime = \Carbon\Carbon::now()->format('H:i'))

        @forelse($jadwals as $hari => $list)
            <div class="day-header mt-3 mb-2">{{ $hari }}</div>

            @foreach($list as $j)
                @php($isActive = ($hari === $currentDay && $currentTime >= $j->jam_mulai && $currentTime <= $j->jam_selesai))
                <div class="schedule-card">
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
            <div class="pui-empty">
                <i class="bi bi-calendar-x ico"></i>
                <h4>Belum ada jadwal</h4>
                <p>Hubungi bagian kurikulum untuk informasi jadwal.</p>
            </div>
        @endforelse
    </main>
</div>
@endsection
