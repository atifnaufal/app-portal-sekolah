@extends('layouts.app')

@section('content')
@php
    $isEdit = isset($spp) && $spp->exists;
    $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
@endphp

<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <div class="text-primary small fw-semibold">TAGIHAN SPP</div>
        <h1 class="h3 fw-bold mb-1">{{ $isEdit ? 'Edit Data SPP' : 'Tambah Tagihan SPP' }}</h1>
        <p class="text-secondary mb-0">Pilih siswa berdasarkan kelas, nama, dan NIK.</p>
    </div>
    <a href="{{ route('spp.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card form-card">
            <div class="card-body p-4">
                <form method="POST" action="{{ $isEdit ? route('spp.update', $spp) : route('spp.store') }}">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Siswa</label>
                        <select name="siswa_id" class="form-select form-select-lg" required>
                            <option value="">-- Pilih siswa --</option>
                            @foreach($siswas as $siswa)
                                <option value="{{ $siswa->id }}" @selected(old('siswa_id', $spp->siswa_id ?? '') == $siswa->id)>
                                    {{ $siswa->kelas?->nama ?? '-' }} &middot; {{ $siswa->name }} &middot; NIK {{ $siswa->nik ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bulan</label>
                            <select name="bulan" class="form-select" required>
                                <option value="">-- Pilih bulan --</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected(old('bulan', $spp->bulan ?? '') == $m)>{{ $namaBulan[$m] }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tahun</label>
                            <input name="tahun" type="number" min="2020" max="2050" value="{{ old('tahun', $spp->tahun ?? date('Y')) }}" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nominal Tagihan (Rp)</label>
                            <input name="nominal" type="number" min="0" id="nominalInput" value="{{ old('nominal', $spp->nominal ?? '') }}" class="form-control" required oninput="updatePreview()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sudah Dibayar (Rp)</label>
                            <input name="dibayar" type="number" min="0" id="dibayarInput" value="{{ old('dibayar', $spp->dibayar ?? 0) }}" class="form-control" oninput="updatePreview()">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Jatuh Tempo</label>
                        <input name="jatuh_tempo" type="date" value="{{ old('jatuh_tempo', $isEdit && $spp->jatuh_tempo ? $spp->jatuh_tempo->format('Y-m-d') : '') }}" class="form-control">
                    </div>

                    <div id="paymentPreview" class="p-3 rounded-3 mb-4" style="background:#f8fafc;display:none;">
                        <div class="row">
                            <div class="col-4 text-center">
                                <div class="small text-muted">Status</div>
                                <div id="statusPreview" class="fw-bold"></div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="small text-muted">Kekurangan</div>
                                <div id="kekuranganPreview" class="fw-bold"></div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="small text-muted">Progress</div>
                                <div id="pctPreview" class="fw-bold"></div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary btn-lg rounded-pill px-5">
                        <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Buat Tagihan' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card stat-card" style="border-top-color: #246bfe;">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Panduan</h6>
                <ul class="small text-secondary mb-0" style="line-height:2;">
                    <li>Pilih <strong>siswa</strong> dari daftar</li>
                    <li>Tentukan <strong>bulan & tahun</strong> periode SPP</li>
                    <li>Isi <strong>nominal tagihan</strong> sesuai ketentuan</li>
                    <li>Isi <strong>sudah dibayar</strong> jika ada pembayaran parsial</li>
                    <li>Set <strong>jatuh tempo</strong> untuk pengingat</li>
                    <li>Status otomatis: <strong>Lunas</strong> jika bayar >= tagihan</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    function updatePreview() {
        const nominal = parseFloat(document.getElementById('nominalInput').value) || 0;
        const dibayar = parseFloat(document.getElementById('dibayarInput').value) || 0;
        const preview = document.getElementById('paymentPreview');

        if (nominal > 0) {
            preview.style.display = 'block';
            const sisa = Math.max(0, nominal - dibayar);
            const pct = Math.min(100, Math.round((dibayar / nominal) * 100));

            const status = document.getElementById('statusPreview');
            const kekurangan = document.getElementById('kekuranganPreview');
            const pctEl = document.getElementById('pctPreview');

            status.textContent = sisa <= 0 ? 'Lunas' : 'Belum Lunas';
            status.style.color = sisa <= 0 ? '#16a34a' : '#b45309';

            kekurangan.textContent = 'Rp ' + sisa.toLocaleString('id-ID');
            kekurangan.style.color = sisa <= 0 ? '#16a34a' : '#dc2626';

            pctEl.textContent = pct + '%';
            pctEl.style.color = '#246bfe';
        } else {
            preview.style.display = 'none';
        }
    }
    updatePreview();
</script>
@endsection
