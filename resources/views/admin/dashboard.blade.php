@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 p-4 bg-white rounded-4 shadow-sm border-start border-primary border-5">
        <div>
            <div class="text-primary small fw-bold text-uppercase ls-1">Administrator Control Panel</div>
            <h1 class="h3 fw-bold mb-1">Dashboard Sekolah</h1>
            <p class="text-secondary mb-0">Selamat datang kembali, kelola data sekolah dengan efisien.</p>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.registration.toggle') }}" class="d-flex gap-2">
                @csrf
                @method('PATCH')
                <input type="hidden" name="role" value="guru">
                <button class="btn {{ $registrationGuruEnabled ? 'btn-outline-danger' : 'btn-outline-success' }} px-3 rounded-pill fw-bold">
                    {{ $registrationGuruEnabled ? '⛔ Guru' : '✅ Guru' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.registration.toggle') }}" class="d-flex gap-2">
                @csrf
                @method('PATCH')
                <input type="hidden" name="role" value="siswa">
                <button class="btn {{ $registrationSiswaEnabled ? 'btn-outline-danger' : 'btn-outline-success' }} px-3 rounded-pill fw-bold">
                    {{ $registrationSiswaEnabled ? '⛔ Siswa' : '✅ Siswa' }}
                </button>
            </form>
            <a href="{{ route('pengumuman.create') }}" class="btn btn-primary px-4 rounded-pill fw-bold">+ Pengumuman</a>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-primary-subtle text-primary rounded-3 p-3 me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM1.5 13s-1 0-1-1 1-4 5-4a11.723 11.723 0 0 1 1.45.101c-.381.42-.595.947-.6 1.491h-5.07z"/></svg>
                    </div>
                    <div>
                        <div class="text-secondary small fw-bold">Total Guru</div>
                        <div class="h2 fw-black mb-0">{{ $totalGuru }}</div>
                    </div>
                </div>
                <div class="progress rounded-0" style="height: 4px;"><div class="progress-bar bg-primary" style="width: 100%"></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-success-subtle text-success rounded-3 p-3 me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.547.21-1.074.587-1.504A11.722 11.722 0 0 0 6 11.33c-3.15 0-4.664 1.353-5.26 1.83C.348 13.528 0 13.996 0 14.5a.5.5 0 0 0 .5.5h6.5a.5.5 0 0 0 .5-.5c0-.012-.001-.024-.002-.036zM7.5 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM5.5 1a2 2 0 1 1 0 4 2 2 0 0 1 0-4zM9.438 11.944c.412.225.941.332 1.562.332 1.5 0 2.25-.8 2.25-1.5s-.75-1.5-2.25-1.5c-.621 0-1.15.107-1.562.332-.413.225-.942.332-1.563.332-1.5 0-2.25-.8-2.25-1.5s.75-1.5 2.25-1.5c.621 0 1.15.107 1.562.332.413-.225.942-.332 1.563-.332z"/></svg>
                    </div>
                    <div>
                        <div class="text-secondary small fw-bold">Total Siswa</div>
                        <div class="h2 fw-black mb-0">{{ $totalSiswa }}</div>
                    </div>
                </div>
                <div class="progress rounded-0" style="height: 4px;"><div class="progress-bar bg-success" style="width: 100%"></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-warning-subtle text-warning rounded-3 p-3 me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M8.235 1.559a.5.5 0 0 0-.47 0l-7.5 4a.5.5 0 0 0 0 .882L3.188 8 .264 9.559a.5.5 0 0 0 0 .882l7.5 4a.5.5 0 0 0 .47 0l7.5-4a.5.5 0 0 0 0-.882L12.812 8l2.924-1.559a.5.5 0 0 0 0-.882l-7.5-4zM8 2.354 14.835 6 8 9.646 1.165 6 8 2.354zM3.188 8.441 8 11.008l4.812-2.567L15.736 10 8 14.127 0.264 10l2.924-1.559z"/></svg>
                    </div>
                    <div>
                        <div class="text-secondary small fw-bold">Kelas Aktif</div>
                        <div class="h2 fw-black mb-0">{{ $totalKelas }}</div>
                    </div>
                </div>
                <div class="progress rounded-0" style="height: 4px;"><div class="progress-bar bg-warning" style="width: 100%"></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-danger-subtle text-danger rounded-3 p-3 me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0z"/><path d="M9.438 11.944c.412.225.941.332 1.562.332 1.5 0 2.25-.8 2.25-1.5s-.75-1.5-2.25-1.5c-.621 0-1.15.107-1.562.332-.413.225-.942.332-1.563.332-1.5 0-2.25-.8-2.25-1.5s.75-1.5 2.25-1.5c.621 0 1.15.107 1.562.332.413-.225.942-.332 1.563-.332z"/></svg>
                    </div>
                    <div>
                        <div class="text-secondary small fw-bold">SPP Tertunggak</div>
                        <div class="h2 fw-black mb-0 text-danger">{{ $sppKurang }}</div>
                    </div>
                </div>
                <div class="progress rounded-0" style="height: 4px;"><div class="progress-bar bg-danger" style="width: 100%"></div></div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h5 fw-bold mb-0">Tren Pembayaran SPP</h2>
                        <div class="small text-secondary">Statistik 6 bulan terakhir</div>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="sppChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">Distribusi Keuangan</h2>
                    <div class="p-3 mb-3 bg-light rounded-4">
                        <div class="small text-secondary fw-bold text-uppercase">Total Tagihan</div>
                        <div class="h4 fw-black mb-0">Rp {{ number_format($sppTagihan, 0, ',', '.') }}</div>
                    </div>
                    <div class="p-3 mb-3 bg-success-subtle rounded-4">
                        <div class="small text-success fw-bold text-uppercase">Sudah Terbayar</div>
                        <div class="h4 fw-black mb-0 text-success">Rp {{ number_format($sppTerbayar, 0, ',', '.') }}</div>
                    </div>
                    <div class="p-3 bg-danger-subtle rounded-4">
                        <div class="small text-danger fw-bold text-uppercase">Sisa Piutang</div>
                        <div class="h4 fw-black mb-0 text-danger">Rp {{ number_format($sppTagihan - $sppTerbayar, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Users -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <h2 class="h5 fw-bold mb-0">Akun Terbaru</h2>
                    <a href="{{ route('admin.users') }}" class="btn btn-sm btn-light rounded-pill px-3">Semua Akun</a>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <tbody>
                                @forelse($recentUsers as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle me-3 d-grid place-items-center" style="width: 32px; height: 32px; font-size: 12px; font-weight: bold;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                <div class="small text-secondary text-truncate" style="max-width: 150px;">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge rounded-pill bg-light text-dark border">{{ ucfirst($user->role) }}</span></td>
                                    <td class="text-end">
                                        <span class="badge rounded-circle p-1 {{ $user->aktif ? 'bg-success' : 'bg-secondary' }}" title="{{ $user->aktif ? 'Aktif' : 'Nonaktif' }}">
                                            <span class="visually-hidden">Status</span>
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 text-secondary">Belum ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Class Summary -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <h2 class="h5 fw-bold mb-0">Kapasitas Kelas</h2>
                    <a href="{{ route('kelas.index') }}" class="btn btn-sm btn-light rounded-pill px-3">Kelola Kelas</a>
                </div>
                <div class="card-body p-4">
                    @forelse($kelasSummaries as $kelas)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold">{{ $kelas->nama }}</span>
                            <span class="small text-secondary">{{ $kelas->siswa_count }} Siswa · {{ $kelas->guru_count }} Guru</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: {{ min(100, $kelas->siswa_count * 5) }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-secondary">Belum ada data kelas.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ls-1 { letter-spacing: 1px; }
    .fw-black { font-weight: 900; }
    .ls-tight { letter-spacing: -0.5px; }
    .rounded-4 { border-radius: 1.25rem !important; }
    .stats-icon { width: 50px; height: 50px; display: grid; place-items: center; }
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('sppChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Tagihan',
                    data: {!! json_encode($chartTagihan) !!},
                    borderColor: '#246bfe',
                    backgroundColor: 'rgba(36, 107, 254, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Terbayar',
                    data: {!! json_encode($chartTerbayar) !!},
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { weight: 'bold' }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5] },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endsection
