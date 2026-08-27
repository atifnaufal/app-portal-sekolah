@extends('layouts.app')

@section('content')
@php
    $totalUsers = $totalGuru + $totalSiswa;
    $sppPersen = $sppTagihan > 0 ? round(($sppTerbayar / $sppTagihan) * 100) : 0;
    $piutang = $sppTagihan - $sppTerbayar;
@endphp

<style>
    .ad-grid { display: grid; gap: 20px; }
    .ad-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 45%, #1d4ed8 100%);
        border-radius: 24px; padding: 28px 30px; color: #fff;
        position: relative; overflow: hidden;
        box-shadow: 0 16px 40px rgba(15,23,42,0.25);
    }
    .ad-hero::before {
        content:''; position:absolute; top:-60px; right:-40px;
        width:240px; height:240px; border-radius:50%;
        background: radial-gradient(circle, rgba(99,102,241,0.35) 0%, transparent 70%);
    }
    .ad-hero::after {
        content:''; position:absolute; bottom:-80px; left:30%;
        width:200px; height:200px; border-radius:50%;
        background: radial-gradient(circle, rgba(234,179,8,0.18) 0%, transparent 70%);
    }

    .ad-stat {
        background: #fff; border-radius: 20px; padding: 22px;
        box-shadow: 0 4px 20px rgba(15,23,42,0.05);
        position: relative; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #f1f5f9;
    }
    .ad-stat:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(15,23,42,0.1); }
    .ad-stat::before {
        content:''; position:absolute; top:0; left:0; width:100%; height:4px;
    }
    .ad-stat-icon {
        width: 52px; height: 52px; border-radius: 16px;
        display: flex; align-items: center; justify-content: center; font-size: 24px;
    }
    .ad-stat-num { font-size: 30px; font-weight: 800; letter-spacing: -0.02em; line-height: 1; }
    .ad-stat-lbl { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; }

    .ad-card {
        background: #fff; border-radius: 20px;
        box-shadow: 0 4px 20px rgba(15,23,42,0.05);
        border: 1px solid #f1f5f9; overflow: hidden;
    }
    .ad-card-head {
        padding: 20px 24px; border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between;
    }
    .ad-card-title { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0; }
    .ad-card-body { padding: 24px; }

    .ad-btn {
        padding: 9px 18px; border-radius: 12px; font-size: 13px; font-weight: 700;
        border: none; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s;
    }
    .ad-btn:active { transform: scale(0.97); }

    .ad-user-row {
        display: flex; align-items: center; gap: 12px; padding: 10px 0;
    }
    .ad-user-row + .ad-user-row { border-top: 1px solid #f8fafc; }
    .ad-user-avatar {
        width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 14px; color: #fff;
    }

    .ad-kelas-bar { height: 8px; border-radius: 99px; background: #f1f5f9; overflow: hidden; }
    .ad-kelas-fill { height: 100%; border-radius: 99px; }

    .ad-toggle {
        padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 700;
        border: 1.5px solid; cursor: pointer; background: #fff;
    }
</style>

<div class="ad-grid mb-4">
    {{-- Hero --}}
    <div class="ad-hero">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;position:relative;z-index:1;">
            <div>
                <div style="font-size:11px;font-weight:800;letter-spacing:0.15em;text-transform:uppercase;opacity:0.6;">Administrator Control Panel</div>
                <h1 style="font-size:28px;font-weight:800;margin:6px 0 4px;letter-spacing:-0.02em;">Dashboard Sekolah</h1>
                <p style="font-size:13px;opacity:0.7;margin:0;">Selamat datang kembali, kelola data sekolah dengan efisien.</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <form method="POST" action="{{ route('admin.registration.toggle') }}">
                    @csrf @method('PATCH') <input type="hidden" name="role" value="guru">
                    <button class="ad-toggle" style="border-color:{{ $registrationGuruEnabled ? '#fca5a5' : '#86efac' }};color:{{ $registrationGuruEnabled ? '#dc2626' : '#16a34a' }};">
                        <i class="bi bi-{{ $registrationGuruEnabled ? 'person-dash' : 'person-check' }}"></i>
                        Registrasi Guru: {{ $registrationGuruEnabled ? 'Aktif' : 'Nonaktif' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.registration.toggle') }}">
                    @csrf @method('PATCH') <input type="hidden" name="role" value="siswa">
                    <button class="ad-toggle" style="border-color:{{ $registrationSiswaEnabled ? '#fca5a5' : '#86efac' }};color:{{ $registrationSiswaEnabled ? '#dc2626' : '#16a34a' }};">
                        <i class="bi bi-{{ $registrationSiswaEnabled ? 'person-dash' : 'person-check' }}"></i>
                        Registrasi Siswa: {{ $registrationSiswaEnabled ? 'Aktif' : 'Nonaktif' }}
                    </button>
                </form>
                <a href="{{ route('pengumuman.create') }}" class="ad-btn" style="background:#fff;color:#1e3a5f;">
                    <i class="bi bi-megaphone-fill"></i> Pengumuman
                </a>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-4">
        <div class="col-lg-3 col-md-6">
            <div class="ad-stat">
                <div style="position:absolute;top:0;left:0;width:100%;height:4px;background:linear-gradient(90deg,#2563eb,#60a5fa);"></div>
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <div class="ad-stat-lbl">Total Guru</div>
                        <div class="ad-stat-num" style="color:#1e293b;margin-top:8px;">{{ $totalGuru }}</div>
                    </div>
                    <div class="ad-stat-icon" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-person-badge-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="ad-stat">
                <div style="position:absolute;top:0;left:0;width:100%;height:4px;background:linear-gradient(90deg,#16a34a,#4ade80);"></div>
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <div class="ad-stat-lbl">Total Siswa</div>
                        <div class="ad-stat-num" style="color:#1e293b;margin-top:8px;">{{ $totalSiswa }}</div>
                    </div>
                    <div class="ad-stat-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-people-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="ad-stat">
                <div style="position:absolute;top:0;left:0;width:100%;height:4px;background:linear-gradient(90deg,#d97706,#fbbf24);"></div>
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <div class="ad-stat-lbl">Kelas Aktif</div>
                        <div class="ad-stat-num" style="color:#1e293b;margin-top:8px;">{{ $totalKelas }}</div>
                    </div>
                    <div class="ad-stat-icon" style="background:#fefce8;color:#d97706;"><i class="bi bi-building"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="ad-stat">
                <div style="position:absolute;top:0;left:0;width:100%;height:4px;background:linear-gradient(90deg,#dc2626,#f87171);"></div>
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <div class="ad-stat-lbl">SPP Tertunggak</div>
                        <div class="ad-stat-num" style="color:#dc2626;margin-top:8px;">{{ $sppKurang }}</div>
                    </div>
                    <div class="ad-stat-icon" style="background:#fef2f2;color:#dc2626;"><i class="bi bi-exclamation-triangle-fill"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="ad-card h-100">
                <div class="ad-card-head">
                    <h2 class="ad-card-title"><i class="bi bi-graph-up-arrow" style="color:#246bfe;"></i> Tren Pembayaran SPP</h2>
                    <span style="font-size:12px;color:#94a3b8;font-weight:600;">6 bulan terakhir</span>
                </div>
                <div class="ad-card-body">
                    <div style="height:300px;"><canvas id="sppChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="ad-card h-100">
                <div class="ad-card-head">
                    <h2 class="ad-card-title"><i class="bi bi-pie-chart-fill" style="color:#7c3aed;"></i> Status Keuangan</h2>
                </div>
                <div class="ad-card-body">
                    <div style="position:relative;height:170px;margin-bottom:18px;">
                        <canvas id="sppDonut"></canvas>
                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">
                            <div style="font-size:28px;font-weight:800;color:#0f172a;">{{ $sppPersen }}%</div>
                            <div style="font-size:11px;color:#94a3b8;font-weight:700;">TERBAYAR</div>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;color:#64748b;font-weight:600;"><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#16a34a;margin-right:6px;"></span>Terbayar</span>
                            <span style="font-size:13px;font-weight:800;color:#16a34a;">Rp {{ number_format($sppTerbayar, 0, ',', '.') }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;color:#64748b;font-weight:600;"><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#f59e0b;margin-right:6px;"></span>Piutang</span>
                            <span style="font-size:13px;font-weight:800;color:#d97706;">Rp {{ number_format($piutang, 0, ',', '.') }}</span>
                        </div>
                        <div style="border-top:1px solid #f1f5f9;padding-top:12px;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;color:#64748b;font-weight:600;">Total Tagihan</span>
                            <span style="font-size:14px;font-weight:800;color:#0f172a;">Rp {{ number_format($sppTagihan, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="ad-card h-100">
                <div class="ad-card-head">
                    <h2 class="ad-card-title"><i class="bi bi-person-plus-fill" style="color:#2563eb;"></i> Akun Terbaru</h2>
                    <a href="{{ route('admin.users') }}" class="ad-btn" style="background:#f1f5f9;color:#475569;">Semua Akun</a>
                </div>
                <div class="ad-card-body">
                    @forelse($recentUsers as $u)
                        @php
                            $isGuruUser = $u->role === 'guru';
                            $avBg = $isGuruUser ? 'linear-gradient(135deg,#2563eb,#3b82f6)' : 'linear-gradient(135deg,#16a34a,#22c55e)';
                        @endphp
                        <div class="ad-user-row">
                            <div class="ad-user-avatar" style="background:{{ $avBg }};">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:14px;font-weight:700;color:#0f172a;">{{ $u->name }}</div>
                                <div style="font-size:12px;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $u->email }}</div>
                            </div>
                            <div style="text-align:right;">
                                <span style="font-size:10px;font-weight:800;padding:3px 10px;border-radius:99px;text-transform:uppercase;{{ $isGuruUser ? 'background:#dbeafe;color:#1d4ed8;' : 'background:#dcfce7;color:#15803d;' }}">{{ $u->role }}</span>
                                <div style="font-size:10px;margin-top:4px;color:{{ $u->aktif ? '#16a34a' : '#94a3b8' }};font-weight:700;">
                                    <i class="bi bi-circle-fill" style="font-size:6px;"></i> {{ $u->aktif ? 'Aktif' : 'Nonaktif' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;padding:24px;color:#94a3b8;">Belum ada data.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="ad-card h-100">
                <div class="ad-card-head">
                    <h2 class="ad-card-title"><i class="bi bi-bar-chart-fill" style="color:#d97706;"></i> Kapasitas Kelas</h2>
                    <a href="{{ route('kelas.index') }}" class="ad-btn" style="background:#f1f5f9;color:#475569;">Kelola Kelas</a>
                </div>
                <div class="ad-card-body">
                    @forelse($kelasSummaries as $k)
                        @php $maxSiswa = max(1, $kelasSummaries->max('siswa_count')); @endphp
                        <div style="margin-bottom:16px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                <span style="font-size:13px;font-weight:700;color:#0f172a;">{{ $k->nama }}</span>
                                <span style="font-size:11px;color:#94a3b8;font-weight:600;">
                                    <i class="bi bi-people-fill" style="color:#2563eb;"></i> {{ $k->siswa_count }} siswa
                                    <span style="margin-left:6px;"><i class="bi bi-person-badge-fill" style="color:#16a34a;"></i> {{ $k->guru_count }} guru</span>
                                </span>
                            </div>
                            <div class="ad-kelas-bar">
                                <div class="ad-kelas-fill" style="width:{{ min(100, ($k->siswa_count / $maxSiswa) * 100) }}%;background:linear-gradient(90deg,#246bfe,#60a5fa);"></div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;padding:24px;color:#94a3b8;">Belum ada data kelas.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var grad1 = document.getElementById('sppChart').getContext('2d').createLinearGradient(0,0,0,300);
        grad1.addColorStop(0, 'rgba(36,107,254,0.25)'); grad1.addColorStop(1, 'rgba(36,107,254,0)');
        var grad2 = document.getElementById('sppChart').getContext('2d').createLinearGradient(0,0,0,300);
        grad2.addColorStop(0, 'rgba(22,163,74,0.25)'); grad2.addColorStop(1, 'rgba(22,163,74,0)');

        new Chart(document.getElementById('sppChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Tagihan', data: {!! json_encode($chartTagihan) !!},
                    borderColor: '#246bfe', backgroundColor: grad1, borderWidth: 3, fill: true, tension: 0.4,
                    pointBackgroundColor: '#fff', pointBorderColor: '#246bfe', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6
                }, {
                    label: 'Terbayar', data: {!! json_encode($chartTerbayar) !!},
                    borderColor: '#16a34a', backgroundColor: grad2, borderWidth: 3, fill: true, tension: 0.4,
                    pointBackgroundColor: '#fff', pointBorderColor: '#16a34a', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { usePointStyle: true, padding: 20, font: { weight: 'bold', size: 12 } } } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, border: { display: false }, ticks: { callback: function(v){ return 'Rp ' + (v/1000) + 'rb'; }, font: { size: 11 } } },
                    x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });

        new Chart(document.getElementById('sppDonut'), {
            type: 'doughnut',
            data: {
                labels: ['Terbayar', 'Piutang'],
                datasets: [{ data: [{{ $sppTerbayar }}, {{ max(0, $piutang) }}], backgroundColor: ['#16a34a', '#f59e0b'], borderWidth: 0, hoverOffset: 6 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false } } }
        });
    });
</script>
@endsection
