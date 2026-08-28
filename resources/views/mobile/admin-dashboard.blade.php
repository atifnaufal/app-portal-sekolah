@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .am-body { padding: 16px 14px 40px; max-width: 640px; margin: 0 auto; }

    .am-hero {
        background: linear-gradient(135deg, #0f172a, #1e3a5f);
        border-radius: 26px; padding: 24px 20px; color: #fff;
        margin-bottom: 16px; position: relative; overflow: hidden;
    }
    .am-hero::before {
        content:''; position:absolute; top:-30px; right:-30px;
        width:130px; height:130px; border-radius:50%;
        background: radial-gradient(circle, rgba(99,102,241,0.3), transparent 70%);
    }

    .am-stat {
        background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15);
        border-radius: 16px; padding: 12px 8px; text-align: center; flex: 1;
    }
    .am-stat .n { font-size: 20px; font-weight: 800; line-height: 1.1; }
    .am-stat .l { font-size: 9px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; opacity: 0.65; margin-top: 3px; }

    .am-card {
        background: #fff; border-radius: 20px; padding: 18px;
        margin-bottom: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .am-action {
        display: flex; align-items: center; gap: 12px; padding: 16px;
        background: #fff; border-radius: 20px; text-decoration: none; color: #1e293b;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04); margin-bottom: 12px;
        transition: transform 0.15s;
    }
    .am-action:active { transform: scale(0.98); }
    .am-action-icon {
        width: 46px; height: 46px; border-radius: 14px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
    }

    .am-note {
        background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 18px;
        padding: 14px 16px; display: flex; gap: 10px; align-items: flex-start;
    }
</style>

<div class="am-body">
    <div class="am-hero">
        <div style="position:relative;z-index:1;">
            <div style="font-size:11px;font-weight:800;letter-spacing:0.13em;text-transform:uppercase;opacity:0.6;">Portal Administrator</div>
            <div style="font-size:22px;font-weight:800;margin-top:4px;">Halo, {{ explode(' ', session('admin_name') ?? 'Admin')[0] }}!</div>
            <div style="font-size:12px;opacity:0.65;margin-top:4px;">{{ now()->format('d M Y') }}</div>

            <div style="display:flex;gap:8px;margin-top:16px;">
                <div class="am-stat"><div class="n">{{ $totalGuru }}</div><div class="l">Guru</div></div>
                <div class="am-stat"><div class="n">{{ $totalSiswa }}</div><div class="l">Siswa</div></div>
                <div class="am-stat"><div class="n">{{ $totalKelas }}</div><div class="l">Kelas</div></div>
                <div class="am-stat"><div class="n" style="color:#fca5a5;">{{ $sppKurang }}</div><div class="l">Tunggakan</div></div>
            </div>
        </div>
    </div>

    @if(session('info'))
        <div class="am-note mb-3">
            <i class="bi bi-info-circle-fill" style="color:#2563eb;flex-shrink:0;margin-top:1px;"></i>
            <div style="font-size:12px;color:#1e40af;line-height:1.5;">{{ session('info') }}</div>
        </div>
    @endif

    {{-- LMS Overview --}}
    <div class="am-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <div style="font-size:13px;font-weight:800;">
                <i class="bi bi-mortarboard-fill" style="color:#7c3aed;"></i> E-Learning (LMS)
            </div>
            <span style="font-size:9px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;color:#7c3aed;background:#f5f3ff;padding:3px 8px;border-radius:99px;">Active</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;">
            <div style="background:#f5f8ff;border-radius:14px;padding:14px;text-align:center;">
                <div style="font-size:24px;font-weight:800;color:#2563eb;">{{ $totalMapel }}</div>
                <div style="font-size:9px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Mapel</div>
            </div>
            <div style="background:#f0fdf4;border-radius:14px;padding:14px;text-align:center;">
                <div style="font-size:24px;font-weight:800;color:#16a34a;">{{ $totalMateri }}</div>
                <div style="font-size:9px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Materi</div>
            </div>
            <div style="background:#fffbeb;border-radius:14px;padding:14px;text-align:center;">
                <div style="font-size:24px;font-weight:800;color:#d97706;">{{ $totalTugas }}</div>
                <div style="font-size:9px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Tugas</div>
            </div>
            <div style="background:#fff5f6;border-radius:14px;padding:14px;text-align:center;">
                <div style="font-size:24px;font-weight:800;color:{{ $tugasBelumDinilai > 0 ? '#dc2626' : '#94a3b8' }};">{{ $tugasBelumDinilai }}</div>
                <div style="font-size:9px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Perlu Nilai</div>
            </div>
        </div>
        <div style="font-size:10px;color:#94a3b8;margin-top:10px;line-height:1.5;">
            <i class="bi bi-info-circle me-1"></i> Kelola mata pelajaran &amp; materi secara lengkap tersedia di dashboard desktop.
        </div>
    </div>

    {{-- Akademik & Kehadiran --}}
    <div class="am-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <div style="font-size:13px;font-weight:800;">
                <i class="bi bi-graph-up-arrow" style="color:#2563eb;"></i> Akademik &amp; Kehadiran
            </div>
        </div>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:8px;margin-bottom:10px;">
            <div style="background:#ecfeff;border-radius:14px;padding:14px;">
                <div style="display:flex;align-items:baseline;gap:3px;">
                    <span style="font-size:24px;font-weight:800;color:#0e9aa7;">{{ $rataNilai }}</span>
                    <span style="font-size:10px;color:#64748b;font-weight:700;">/100</span>
                </div>
                <div style="font-size:9px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Rata-rata Nilai</div>
            </div>
            <div style="background:#f5f3ff;border-radius:14px;padding:14px;text-align:center;">
                <div style="font-size:24px;font-weight:800;color:#7c3aed;">{{ $totalNilai }}</div>
                <div style="font-size:9px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Nilai Tersimpan</div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
            <div style="background:#f0fdf4;border-radius:14px;padding:12px;text-align:center;">
                <div style="font-size:20px;font-weight:800;color:#16a34a;">{{ $hadirHariIni }}</div>
                <div style="font-size:8px;font-weight:700;color:#64748b;text-transform:uppercase;">Hadir<br>Hari Ini</div>
            </div>
            <div style="background:#fffbeb;border-radius:14px;padding:12px;text-align:center;">
                <div style="font-size:20px;font-weight:800;color:#d97706;">{{ $terlambatHari }}</div>
                <div style="font-size:8px;font-weight:700;color:#64748b;text-transform:uppercase;">Terlambat<br>Hari Ini</div>
            </div>
            <div style="background:#fff5f6;border-radius:14px;padding:12px;text-align:center;">
                <div style="font-size:20px;font-weight:800;color:#d94b61;">{{ $totalPengumpulan }}</div>
                <div style="font-size:8px;font-weight:700;color:#64748b;text-transform:uppercase;">Pengumpulan<br>Tugas</div>
            </div>
        </div>
        <div style="display:flex;gap:8px;margin-top:8px;">
            <div style="flex:1;background:#f8fafc;border-radius:14px;padding:12px;text-align:center;">
                <div style="font-size:20px;font-weight:800;color:#2563eb;">{{ $tugasBelumDinilai }}</div>
                <div style="font-size:8px;font-weight:700;color:#64748b;text-transform:uppercase;">Perlu Dinilai</div>
            </div>
            <div style="flex:1;background:#f8fafc;border-radius:14px;padding:12px;text-align:center;">
                <div style="font-size:20px;font-weight:800;color:#dc2626;">{{ $sppKurang }}</div>
                <div style="font-size:8px;font-weight:700;color:#64748b;text-transform:uppercase;">Tunggakan SPP</div>
            </div>
        </div>
    </div>

    <div style="font-size:13px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px;">
        Aksi Cepat
    </div>
    <a href="{{ route('pengumuman.create') }}" class="am-action">
        <div class="am-action-icon" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#2563eb;">
            <i class="bi bi-megaphone-fill"></i>
        </div>
        <div style="flex:1;">
            <div style="font-size:14px;font-weight:700;">Buat Pengumuman</div>
            <div style="font-size:11px;color:#94a3b8;">Publikasikan info ke seluruh sekolah</div>
        </div>
        <i class="bi bi-chevron-right" style="color:#cbd5e1;"></i>
    </a>

    <a href="{{ route('pengumuman.index') }}" class="am-action">
        <div class="am-action-icon" style="background:linear-gradient(135deg,#fef3c7,#fde68a);color:#d97706;">
            <i class="bi bi-list-check"></i>
        </div>
        <div style="flex:1;">
            <div style="font-size:14px;font-weight:700;">Daftar Pengumuman</div>
            <div style="font-size:11px;color:#94a3b8;">Lihat, ubah, atau hapus</div>
        </div>
        <i class="bi bi-chevron-right" style="color:#cbd5e1;"></i>
    </a>

    <div class="am-card">
        <div style="font-size:13px;font-weight:800;margin-bottom:10px;">
            <i class="bi bi-graph-up-arrow" style="color:#16a34a;"></i> Ringkasan SPP
        </div>
        @php $pct = $sppTagihan > 0 ? round(($sppTerbayar / $sppTagihan) * 100) : 0; @endphp
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px;">
            <span style="color:#64748b;font-weight:600;">Terbayar</span>
            <span style="font-weight:800;color:#16a34a;">{{ $pct }}%</span>
        </div>
        <div style="height:8px;border-radius:99px;background:#f1f5f9;overflow:hidden;margin-bottom:12px;">
            <div style="height:100%;border-radius:99px;width:{{ $pct }}%;background:linear-gradient(90deg,#16a34a,#4ade80);"></div>
        </div>
        <div style="display:flex;justify-content:space-between;">
            <div>
                <div style="font-size:10px;color:#94a3b8;font-weight:700;">TOTAL TAGIHAN</div>
                <div style="font-size:14px;font-weight:800;color:#1e293b;">Rp {{ number_format($sppTagihan, 0, ',', '.') }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:10px;color:#94a3b8;font-weight:700;">SISA PIUTANG</div>
                <div style="font-size:14px;font-weight:800;color:#d97706;">Rp {{ number_format(max(0, $sppTagihan - $sppTerbayar), 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="am-note">
        <i class="bi bi-pc-display-horizontal" style="color:#2563eb;flex-shrink:0;margin-top:1px;"></i>
        <div style="font-size:12px;color:#1e40af;line-height:1.6;">
            <strong>Manajemen lengkap hanya di desktop.</strong><br>
            Kelola akun, kelas, jurusan, pengaturan, dan laporan SPP buka melalui peramban desktop di
            <span style="font-weight:700;word-break:break-all;">{{ url('/dashboard') }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('logout') }}" style="margin-top:16px;">
        @csrf
        <button style="width:100%;padding:14px;border-radius:16px;background:#fff5f6;border:1px solid #fecdd3;color:#d94b61;font-weight:700;font-size:14px;cursor:pointer;">
            <i class="bi bi-box-arrow-right"></i> Keluar Akun
        </button>
    </form>
</div>
@endsection
