@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
@php
    $isGuru = $user->role === 'guru';
    $isSiswa = $user->role === 'siswa';
    $stats = $stats ?? ['total' => 0, 'lunas' => 0, 'belum' => 0, 'total_nominal' => 0, 'total_terbayar' => 0, 'total_kekurangan' => 0];
    $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $grouped = $spps->groupBy(function($item) { return $item->tahun . '-' . str_pad($item->bulan, 2, '0', STR_PAD_LEFT); });
@endphp

<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.92); backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--line-strong);
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 70px; padding-bottom: 48px; }

    .glass-card {
        background: var(--surface-card); border: none; border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        overflow: hidden; margin-bottom: 16px;
    }

    .stat-card {
        border-radius: var(--radius-md); padding: 16px; text-align: center; flex: 1;
        position: relative; overflow: hidden;
    }
    .stat-card::before {
        content: ''; position: absolute; top: -10px; right: -10px;
        width: 50px; height: 50px; border-radius: 50%; opacity: 0.1;
    }
    .stat-card .stat-num { font-size: 22px; font-weight: 800; line-height: 1.1; }
    .stat-card .stat-lbl { font-size: 9px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; margin-top: 4px; opacity: 0.7; }

    .spp-row {
        background: var(--surface-card); border: 1px solid var(--line-strong); border-radius: var(--radius-md);
        padding: 16px; margin-bottom: 10px; transition: all 0.2s;
    }
    .spp-row:hover { border-color: var(--faint); box-shadow: var(--shadow-hover); }

    .progress-slim { height: 6px; border-radius: 99px; background: var(--surface); overflow: hidden; }
    .progress-slim > span { display: block; height: 100%; border-radius: 99px; }

    .month-group-header {
        font-size: 12px; font-weight: 800; color: var(--mist); letter-spacing: 0.06em;
        text-transform: uppercase; padding: 8px 0; margin-top: 8px;
        display: flex; align-items: center; gap: 8px;
    }
    .month-group-header::after {
        content: ''; flex: 1; height: 1px; background: var(--line-strong);
    }

    .status-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; border-radius: 99px; font-size: 10px; font-weight: 800;
        letter-spacing: 0.03em; text-transform: uppercase;
    }

    .currency-display { font-variant-numeric: tabular-nums; }

    @keyframes slideUp { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .slide-up { animation: slideUp 0.4s ease both; }
</style>

<div class="page-header">
    <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px; color: var(--ink);">SPP & Pembayaran</div>
    @if($isGuru)
        <a href="{{ route('spp.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 ms-auto" style="font-weight: 700;">+ Catat</a>
    @endif
</div>

<div class="page-container px-3 pt-3">
    {{-- Hero --}}
    <div class="slide-up" style="background: linear-gradient(135deg, #1e293b, #0f766e); border-radius: var(--radius-lg); padding: 24px 20px; margin-bottom: 18px; color: #fff; position: relative; overflow: hidden;">
        <div style="position:absolute;top:-20px;right:-20px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,0.06);"></div>
        <div style="position:absolute;bottom:-30px;right:40px;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.04);"></div>

        <div class="eyebrow" style="color: rgba(255,255,255,0.6); font-size: 11px; letter-spacing: 0.13em; font-weight: 800;">
            <i class="bi bi-wallet2 me-1"></i> KEUANGAN SEKOLAH
        </div>
        <div class="hero-title mt-2 text-white" style="font-size: 22px; font-weight: 800;">
            {{ $isGuru ? 'Monitor SPP Kelas' : 'Tagihan SPP Saya' }}
        </div>
        <p class="mb-3 mt-1" style="font-size: 12px; color: rgba(255,255,255,.6);">
            {{ $user->kelas?->nama ?? ($isGuru ? 'Panel Pembayaran' : '') }}
        </p>

        <div class="d-flex gap-2">
            <div class="stat-card" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15);">
                <div class="stat-num text-white">{{ $stats['total'] }}</div>
                <div class="stat-lbl" style="color: rgba(255,255,255,0.6);">Tagihan</div>
            </div>
            <div class="stat-card" style="background: rgba(16,185,129,0.2); border: 1px solid rgba(16,185,129,0.25);">
                <div class="stat-num" style="color: #6ee7b7;">{{ $stats['lunas'] }}</div>
                <div class="stat-lbl" style="color: rgba(110,231,183,0.7);">Lunas</div>
            </div>
            <div class="stat-card" style="background: rgba(251,191,36,0.2); border: 1px solid rgba(251,191,36,0.25);">
                <div class="stat-num" style="color: #fde68a;">{{ $stats['belum'] }}</div>
                <div class="stat-lbl" style="color: rgba(253,230,138,0.7);">Belum</div>
            </div>
        </div>
    </div>

    {{-- Summary Card --}}
    @if($isSiswa)
        <div class="glass-card slide-up" style="animation-delay: 0.1s;">
            <div class="p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div style="width:28px;height:28px;border-radius:10px;background:#f0fdf4;color:#16a34a;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-piggy-bank" style="font-size:14px;"></i>
                    </div>
                    <span class="fw-bold" style="font-size:14px;color:var(--ink);">Ringkasan Pembayaran</span>
                </div>
                @php
                    $pctBayar = $stats['total_nominal'] > 0 ? round(($stats['total_terbayar'] / $stats['total_nominal']) * 100) : 0;
                @endphp
                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-muted">Total terbayar</span>
                    <span class="fw-bold" style="font-size:13px;">{{ $pctBayar }}%</span>
                </div>
                <div class="progress-slim mb-3">
                    <span style="width: {{ $pctBayar }}%; background: linear-gradient(90deg, #16a34a, #4ade80);"></span>
                </div>
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="x-small text-muted">Terbayar</div>
                        <div class="fw-bold currency-display" style="font-size:14px; color:#16a34a;">Rp {{ number_format($stats['total_terbayar'], 0, ',', '.') }}</div>
                    </div>
                    <div class="text-end">
                        <div class="x-small text-muted">Kekurangan</div>
                        <div class="fw-bold currency-display" style="font-size:14px; color:#dc2626;">Rp {{ number_format($stats['total_kekurangan'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Filter --}}
    <div class="d-flex gap-2 mb-3 slide-up" style="animation-delay: 0.15s;">
        <button type="button" class="btn btn-sm rounded-pill fw-bold spp-filter active" data-filter="all" style="font-size:11px;">Semua</button>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold spp-filter" data-filter="lunas" style="font-size:11px;">Lunas</button>
        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill fw-bold spp-filter" data-filter="belum" style="font-size:11px;">Belum Lunas</button>
    </div>

    {{-- SPP List --}}
    <div class="slide-up" style="animation-delay: 0.2s;">
        @forelse($grouped as $period => $items)
            @php
                $first = $items->first();
                $periodLabel = ($namaBulan[(int)$first->bulan] ?? '') . ' ' . $first->tahun;
            @endphp
            <div class="month-group-header">{{ $periodLabel }}</div>

            @foreach($items as $spp)
                @php
                    $pct = $spp->nominal > 0 ? min(100, round(((float)$spp->dibayar / (float)$spp->nominal) * 100)) : 0;
                    $isLunas = $spp->status === 'lunas';
                    $isOverdue = $spp->jatuh_tempo && $spp->jatuh_tempo->isPast() && !$isLunas;
                @endphp
                <div class="spp-row" data-spp data-status="{{ $isLunas ? 'lunas' : 'belum' }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            @if($isGuru)
                                <div class="fw-bold text-dark" style="font-size:14px;">{{ $spp->siswa->name ?? 'Siswa' }}</div>
                                <div class="x-small text-muted">{{ $spp->siswa->kelas?->nama ?? '' }}</div>
                            @else
                                <div class="fw-bold text-dark" style="font-size:14px;">SPP {{ $namaBulan[$spp->bulan] ?? '' }} {{ $spp->tahun }}</div>
                            @endif
                        </div>
                        @if($isLunas)
                            <span class="status-badge" style="background:#dcfce7;color:#15803d;"><i class="bi bi-check-circle-fill"></i> Lunas</span>
                        @elseif($isOverdue)
                            <span class="status-badge" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-exclamation-circle-fill"></i> Jatuh Tempo</span>
                        @else
                            <span class="status-badge" style="background:#fef3c7;color:#b45309;"><i class="bi bi-hourglass-split"></i> Belum Lunas</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Rp {{ number_format($spp->dibayar, 0, ',', '.') }} / {{ number_format($spp->nominal, 0, ',', '.') }}</span>
                        <span class="fw-bold currency-display" style="font-size:12px; color: {{ $isLunas ? '#16a34a' : '#dc2626' }};">
                            {{ $isLunas ? 'Lunas' : 'Sisa Rp ' . number_format($spp->kekurangan, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="progress-slim">
                        <span style="width: {{ $pct }}%; background: {{ $isLunas ? 'linear-gradient(90deg, #16a34a, #4ade80)' : 'linear-gradient(90deg, #f59e0b, #fbbf24)' }};"></span>
                    </div>

                    @if($spp->jatuh_tempo && !$isLunas)
                        <div class="x-small text-muted mt-2">
                            <i class="bi bi-calendar-event me-1"></i>Jatuh tempo: {{ $spp->jatuh_tempo->format('d M Y') }}
                        </div>
                    @endif

                    @if($isGuru && !$isLunas)
                        <form method="POST" action="{{ route('spp.remind', $spp) }}" class="mt-3">
                            @csrf
                            <button class="btn btn-outline-warning btn-sm w-100 rounded-pill" style="font-size:12px;font-weight:700;">
                                <i class="bi bi-bell me-1"></i> Kirim Pengingat
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        @empty
            <div class="glass-card">
                <div class="p-5 text-center">
                    <i class="bi bi-receipt" style="font-size:40px;color:var(--faint);"></i>
                    <div class="fw-bold mt-2 text-muted">Belum ada data SPP</div>
                    <div class="x-small text-muted mt-1">
                        @if($isGuru)
                            Tap "+ Catat" untuk membuat tagihan pertama.
                        @else
                            Tagihan SPP akan muncul di sini.
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<script>
    document.querySelectorAll('.spp-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.spp-filter').forEach(b => {
                b.classList.remove('active');
                b.className = b.className.replace(/btn-outline-\w+/g, '').replace(/btn-primary/g, '');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('active');
            const filter = this.dataset.filter;
            document.querySelectorAll('[data-spp]').forEach(el => {
                if (filter === 'all') { el.style.display = ''; }
                else { el.style.display = el.dataset.status === filter ? '' : 'none'; }
            });
        });
    });
</script>
@endsection
