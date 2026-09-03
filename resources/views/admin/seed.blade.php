{{-- Cek & Jalankan Seed Data — khusus Super Admin. Idempotent, aman diulang. --}}
@extends('layouts.app', ['title' => 'Seed Data Portal'])
@section('content')
@php
$targets = ['kelas' => 3, 'guru' => 8, 'siswa' => 10, 'tugas' => 48, 'jadwal' => 120];
@endphp
<style>
    .cp-page-header {
        background: linear-gradient(135deg, var(--navy) 0%, #1e293b 100%);
        border-radius: 24px; padding: 32px 36px; color: #fff;
        position: relative; overflow: hidden; margin-bottom: 24px;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
    }
    .cp-page-header::after {
        content: ''; position: absolute; top: -70px; right: -70px;
        width: 220px; height: 220px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36,107,254,0.18) 0%, transparent 70%);
    }
    .cp-page-title { font-size: 26px; font-weight: 800; letter-spacing: -0.02em; position: relative; z-index: 1; }
    .cp-page-sub { font-size: 13px; color: #94a3b8; position: relative; z-index: 1; }
    .seed-card { border-radius: 20px; border: 1px solid var(--border); background: #fff; box-shadow: var(--shadow); overflow: hidden; }
    .seed-card-head { padding: 18px 24px; border-bottom: 1px solid var(--border); }
    .seed-card-title { font-size: 15.5px; font-weight: 800; color: var(--navy); margin: 0; }
    .term-box { background: #0f172a; color: #e2e8f0; border-radius: 14px; padding: 18px; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 12.5px; white-space: pre-wrap; max-height: 300px; overflow-y: auto; line-height: 1.6; }
    .table-premium thead th { background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; padding: 12px 16px; border: 0; }
    .table-premium tbody td { padding: 12px 16px; border-top: 1px solid #f8fafc; vertical-align: middle; font-size: 13px; }
    @media (max-width: 768px) {
        .cp-page-header { padding: 24px; border-radius: 20px; }
        .cp-page-title { font-size: 22px; }
    }
</style>

<div class="cp-shell">
@include('admin.partials.sidebar')
<div class="cp-main">

<div class="cp-page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 position-relative" style="z-index:1;">
        <div>
            <div class="small fw-bold" style="letter-spacing:.1em;color:#94a3b8;">SUPER ADMIN ONLY</div>
            <h1 class="cp-page-title">Seed Data Portal</h1>
            <p class="cp-page-sub mb-0">Cek isi riil database & jalankan seeder manual (idempotent — aman diulang).</p>
        </div>
        <form method="POST" action="{{ route('admin.insights.seed.run') }}" onsubmit="return confirm('Jalankan seeder? Bisa 2–5 menit (download gambar). Jangan tutup halaman.')">
            @csrf
            <button class="btn btn-light fw-bold px-4" style="border-radius:14px;"><i class="bi bi-play-fill me-1"></i> Jalankan Seed</button>
        </form>
    </div>
</div>

@if(!empty($seedResult))
<div class="alert border-0 shadow-sm mb-4 {{ $seedResult['ok'] ? 'alert-success' : 'alert-danger' }}" style="border-radius:16px;">
    <div class="fw-bold small mb-1">{{ $seedResult['ok'] ? 'Seeder selesai.' : 'Seeder GAGAL — ini penyebabnya:' }}</div>
    <div class="term-box mt-2">{{ $seedResult['output'] }}</div>
</div>
@endif

<div class="seed-card mb-4">
    <div class="seed-card-head"><h2 class="seed-card-title"><i class="bi bi-table me-2 text-primary"></i>Audit per Sekolah (target: 3 kelas • 8 guru • 10 siswa • 48 tugas • 120 jadwal)</h2></div>
    <div class="table-responsive">
        <table class="table table-premium table-hover align-middle mb-0">
            <thead><tr><th class="ps-4">Sekolah</th><th class="text-center">Kelas</th><th class="text-center">Guru</th><th class="text-center">Siswa</th><th class="text-center">Tugas</th><th class="text-center">Jadwal</th><th class="text-center">SPP Agu</th><th class="text-center">Posts</th><th class="text-center">Stories</th><th class="text-end pe-4">Status</th></tr></thead>
            <tbody>
                @foreach($seedAudit as $row)
                @if(($row['slug'] ?? '') === '_global') @continue @endif
                <tr>
                    <td class="ps-4">
                        @if(empty($row['ada']))
                            <span class="badge bg-danger">{{ $row['slug'] }} — TIDAK ADA</span>
                        @else
                            <div class="fw-bold">[ID: {{ $row['id'] }}] {{ $row['name'] }}</div>
                            <div class="text-muted small">{{ $row['slug'] }}</div>
                        @endif
                    </td>
                    @if(!empty($row['ada']))
                        @foreach(['kelas', 'guru', 'siswa', 'tugas', 'jadwal'] as $k)
                        <td class="text-center fw-bold {{ $row[$k] >= $targets[$k] ? 'text-success' : 'text-danger' }}">{{ number_format($row[$k]) }}<div class="text-muted" style="font-size:10px;font-weight:400;">/{{ $targets[$k] }}</div></td>
                        @endforeach
                        <td class="text-center">{{ number_format($row['spp_agustus']) }}</td>
                        <td class="text-center">{{ number_format($row['posts']) }}</td>
                        <td class="text-center">{{ number_format($row['stories']) }}</td>
                        <td class="text-end pe-4"><span class="badge rounded-pill {{ ($row['kelas'] >= 3 && $row['guru'] >= 8 && $row['siswa'] >= 10) ? 'bg-success' : 'bg-warning' }}">{{ ($row['kelas'] >= 3 && $row['guru'] >= 8 && $row['siswa'] >= 10) ? 'Lengkap' : 'Kurang' }}</span></td>
                    @else
                        <td colspan="9" class="text-center text-muted small">Buat sekolah bawaan dulu (migrasi + tambah sekolah).</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="seed-card">
    <div class="seed-card-head"><h2 class="seed-card-title"><i class="bi bi-globe me-2 text-primary"></i>Global (target: 10 buku • 10 eskul)</h2></div>
    <div class="p-4 d-flex gap-3 flex-wrap">
        @foreach($seedAudit as $row)
        @if(($row['slug'] ?? '') === '_global')
        <div class="flex-fill p-3 rounded-4 text-center" style="background:#f8fafc;min-width:140px;">
            <div class="h4 fw-extrabold mb-0 {{ ($row['buku'] ?? 0) >= 10 ? 'text-success' : 'text-danger' }}">{{ $row['buku'] ?? 0 }}<span class="text-muted" style="font-size:12px;">/10</span></div>
            <div class="small text-muted fw-bold text-uppercase" style="font-size:10px;">Buku</div>
        </div>
        <div class="flex-fill p-3 rounded-4 text-center" style="background:#f8fafc;min-width:140px;">
            <div class="h4 fw-extrabold mb-0 {{ ($row['eskul'] ?? 0) >= 10 ? 'text-success' : 'text-danger' }}">{{ $row['eskul'] ?? 0 }}<span class="text-muted" style="font-size:12px;">/10</span></div>
            <div class="small text-muted fw-bold text-uppercase" style="font-size:10px;">Eskul</div>
        </div>
        @endif
        @endforeach
    </div>
</div>

</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}
@endsection
