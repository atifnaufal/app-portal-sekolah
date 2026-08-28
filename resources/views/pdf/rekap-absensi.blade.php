<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica','Arial',sans-serif; font-size: 11px; color: #1e293b; margin: 24px; }
        .kop { text-align: center; border-bottom: 3px double #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
        .kop h1 { margin: 0; font-size: 18px; text-transform: uppercase; letter-spacing: 1px; color: #0f172a; }
        .kop .sub { font-size: 11px; color: #475569; margin-top: 4px; }
        .kop .line { font-size: 9px; color: #64748b; }
        .judul { text-align: center; margin: 16px 0 12px; }
        .judul h2 { margin: 0; font-size: 15px; text-transform: uppercase; letter-spacing: 1px; }
        .judul .meta { font-size: 11px; color: #475569; margin-top: 4px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 6px 5px; text-align: center; }
        table.data thead th { background: #0f172a !important; color: #fff !important; font-size: 10px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        table.data tbody tr:nth-child(even) { background: #f8fafc; }
        .tleft { text-align: left !important; }
        .footer { margin-top: 34px; display: flex; justify-content: space-between; font-size: 11px; }
        .sig { width: 220px; text-align: center; }
        .sig .nama { font-weight: bold; margin-top: 70px; border-top: 1px dotted #94a3b8; padding-top: 6px; }
        .keterangan { font-size: 9px; color: #64748b; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="kop">
        <h1>{{ config('app.name', 'Portal Sekolah') }}</h1>
        <div class="sub">Jl. Pendidikan No. 1, Indonesia</div>
        <div class="line">Email: info@sekolah.sch.id &bull; Telp: (021) 1234567</div>
    </div>

    <div class="judul">
        <h2>Rekapitulasi Kehadiran Siswa</h2>
        <div class="meta">
            {{ $kelas ? 'Kelas '.$kelas->nama : 'Semua Kelas' }} &bull;
            @if($periode === 'bulanan')
                {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }}
            @else
                Tahun {{ $tahun }}
            @endif
        </div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Hadir</th>
                <th>Terlambat</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpha</th>
                <th>% Hadir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="tleft">{{ $r['user']->name }}</td>
                    <td>{{ $r['hadir'] }}</td>
                    <td>{{ $r['terlambat'] }}</td>
                    <td>{{ $r['izin'] }}</td>
                    <td>{{ $r['sakit'] }}</td>
                    <td>{{ $r['alpha'] }}</td>
                    <td>{{ $r['hadir_persen'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="8">Belum ada data absensi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="keterangan">
        Keterangan: Hadir + Terlambat dihitung sebagai kehadiran. Alpha = tidak hadir tanpa keterangan.
    </div>

    <div class="footer">
        <div>Dicetak pada: {{ $today }}</div>
        <div class="sig">
            <div>Mengetahui,<br>{{ $kelas ? 'Wali Kelas' : 'Kepala Sekolah' }},</div>
            <div class="nama">{{ $kelas ? ($kelas->pembina->name ?? '..........................') : '..........................' }}</div>
            <div>NIP. ..........................</div>
        </div>
    </div>
</body>
</html>
