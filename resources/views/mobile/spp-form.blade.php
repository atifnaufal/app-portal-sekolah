@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
@php
    $isEdit = isset($spp) && $spp->exists;
    $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
@endphp

<style>
    .glass-card { padding: 0; background: var(--surface-card); border: 1px solid var(--line); border-radius: var(--radius-md); box-shadow: var(--shadow-card); overflow: hidden; margin-bottom: 16px; }

    .month-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .month-btn {
        border: 1px solid var(--line-strong); background: var(--surface); border-radius: 12px;
        padding: 10px 4px; text-align: center; cursor: pointer; transition: all 0.2s;
        font-size: 11px; font-weight: 700; color: var(--mist);
    }
    .month-btn:hover { border-color: var(--blue); color: var(--blue); }
    .month-btn.selected { background: var(--blue); color: #fff; border-color: var(--blue); }

    .currency-input { position: relative; }
    .currency-input::before {
        content: 'Rp'; position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        font-size: 13px; font-weight: 700; color: var(--mist); z-index: 1;
    }
    .currency-input input { padding-left: 40px !important; }

    .submit-area {
        position: sticky; bottom: 0; left: 0; right: 0;
        background: rgba(255,255,255,0.92); backdrop-filter: blur(16px);
        border-top: 1px solid var(--line); padding: 16px 20px; z-index: 100;
    }

    @keyframes slideUp { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .slide-up { animation: slideUp 0.4s ease both; }
</style>

<div class="pui-topbar" style="padding-top:16px;">
    <a href="{{ route('spp.index') }}" class="back"><i class="bi bi-chevron-left"></i></a>
    <h1>{{ $isEdit ? 'Edit SPP' : 'Catat SPP Baru' }}</h1>
    <div class="spacer"></div>
</div>

<form method="POST" action="{{ $isEdit ? route('spp.update', $spp) : route('spp.store') }}" id="sppForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="mobile-content px-3 pt-3">
        {{-- Pilih Siswa --}}
        <div class="glass-card slide-up">
            <div class="p-4">
                <div class="pui-section" style="margin-top:0;">
                    <h3 style="display:flex;align-items:center;gap:8px;"><i class="bi bi-person" style="color:var(--blue);"></i> Data Siswa</h3>
                </div>
                <select name="siswa_id" class="pui-select" required>
                    <option value="">Pilih siswa</option>
                    @foreach($siswas as $siswa)
                        <option value="{{ $siswa->id }}" @selected(old('siswa_id', $spp->siswa_id ?? '') == $siswa->id)>
                            {{ $siswa->kelas?->nama ? $siswa->kelas->nama . ' - ' : '' }}{{ $siswa->name }}
                            @if($siswa->nik) ({{ $siswa->nik }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Periode --}}
        <div class="glass-card slide-up" style="animation-delay: 0.1s;">
            <div class="p-4">
                <div class="pui-section" style="margin-top:0;">
                    <h3 style="display:flex;align-items:center;gap:8px;"><i class="bi bi-calendar3" style="color:#7c3aed;"></i> Periode Pembayaran</h3>
                </div>

                <label class="pui-label" style="text-transform:uppercase;font-size:10.5px;color:var(--faint);">Bulan</label>
                <input type="hidden" name="bulan" id="bulanInput" value="{{ old('bulan', $spp->bulan ?? '') }}">
                <div class="month-grid mb-3" id="monthGrid">
                    @for($m = 1; $m <= 12; $m++)
                        <div class="month-btn {{ old('bulan', $spp->bulan ?? '') == $m ? 'selected' : '' }}" data-month="{{ $m }}" onclick="selectMonth({{ $m }})">
                            {{ substr($namaBulan[$m], 0, 3) }}
                        </div>
                    @endfor
                </div>

                <label class="pui-label" style="text-transform:uppercase;font-size:10.5px;color:var(--faint);">Tahun</label>
                <input name="tahun" type="number" min="2020" max="2050" value="{{ old('tahun', $spp->tahun ?? date('Y')) }}" class="pui-input" required>
            </div>
        </div>

        {{-- Nominal --}}
        <div class="glass-card slide-up" style="animation-delay: 0.2s;">
            <div class="p-4">
                <div class="pui-section" style="margin-top:0;">
                    <h3 style="display:flex;align-items:center;gap:8px;"><i class="bi bi-cash-stack" style="color:#16a34a;"></i> Jumlah Pembayaran</h3>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <label class="pui-label" style="text-transform:uppercase;font-size:10.5px;color:var(--faint);">Tagihan</label>
                        <div class="currency-input">
                            <input name="nominal" type="number" min="0" id="nominalInput" value="{{ old('nominal', $spp->nominal ?? '') }}" class="pui-input" required oninput="updatePreview()">
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="pui-label" style="text-transform:uppercase;font-size:10.5px;color:var(--faint);">Sudah Dibayar</label>
                        <div class="currency-input">
                            <input name="dibayar" type="number" min="0" id="dibayarInput" value="{{ old('dibayar', $spp->dibayar ?? 0) }}" class="pui-input" oninput="updatePreview()">
                        </div>
                    </div>
                </div>

                <div id="paymentPreview" class="mt-3 p-3 rounded-4" style="background:var(--surface);display:none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small" style="color:var(--mist);">Status</span>
                        <span id="statusPreview" class="fw-bold" style="font-size:13px;"></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="small" style="color:var(--mist);">Kekurangan</span>
                        <span id="kekuranganPreview" class="fw-bold" style="font-size:13px;color:#dc2626;"></span>
                    </div>
                </div>

                <div class="mt-3 pui-field" style="margin-bottom:0;">
                    <label class="pui-label" style="text-transform:uppercase;font-size:10.5px;color:var(--faint);">Jatuh Tempo (Opsional)</label>
                    <input name="jatuh_tempo" type="date" value="{{ old('jatuh_tempo', $isEdit && $spp->jatuh_tempo ? $spp->jatuh_tempo->format('Y-m-d') : '') }}" class="pui-input">
                </div>
            </div>
        </div>

        <div style="height: 80px;"></div>
    </div>

    <div class="submit-area">
        <div class="d-flex gap-2" style="max-width:640px;margin:0 auto;">
            <a href="{{ route('spp.index') }}" class="pui-btn pui-btn-ghost pui-btn-round" style="padding:12px 20px;">Batal</a>
            <button type="submit" class="pui-btn pui-btn-primary pui-btn-block pui-btn-round" style="font-size:15px;flex:1;">
                <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan SPP' }}
            </button>
        </div>
    </div>
</form>

<script>
    function selectMonth(m) {
        document.getElementById('bulanInput').value = m;
        document.querySelectorAll('.month-btn').forEach(btn => {
            btn.classList.toggle('selected', parseInt(btn.dataset.month) === m);
        });
    }

    function updatePreview() {
        const nominal = parseFloat(document.getElementById('nominalInput').value) || 0;
        const dibayar = parseFloat(document.getElementById('dibayarInput').value) || 0;
        const preview = document.getElementById('paymentPreview');
        const status = document.getElementById('statusPreview');
        const kekurangan = document.getElementById('kekuranganPreview');

        if (nominal > 0) {
            preview.style.display = 'block';
            const sisa = Math.max(0, nominal - dibayar);
            if (sisa <= 0) {
                status.textContent = 'Lunas';
                status.style.color = '#16a34a';
                kekurangan.textContent = 'Rp 0';
                kekurangan.style.color = '#16a34a';
            } else {
                status.textContent = 'Belum Lunas';
                status.style.color = '#b45309';
                kekurangan.textContent = 'Rp ' + sisa.toLocaleString('id-ID');
                kekurangan.style.color = '#dc2626';
            }
        } else {
            preview.style.display = 'none';
        }
    }

    document.getElementById('sppForm').addEventListener('submit', function(e) {
        if (!document.getElementById('bulanInput').value) {
            e.preventDefault();
            alert('Pilih bulan pembayaran.');
        }
    });

    updatePreview();
</script>
@endsection
