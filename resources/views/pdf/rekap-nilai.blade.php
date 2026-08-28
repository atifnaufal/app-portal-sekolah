<!DOCTYPE html>
<html>
<head>
    <title>Rekap Nilai Siswa</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        .info { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-left { text-align: left; }
        .footer { margin-top: 30px; text-align: right; }
        .signature { margin-top: 60px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Rekapitulasi Nilai Siswa</h2>
        <div>{{ config('app.name', 'School Portal') }}</div>
    </div>

    <div class="info">
        <table style="border: none; width: auto;">
            <tr style="border: none;">
                <td style="border: none; text-align: left; padding: 2px;">Kelas</td>
                <td style="border: none; text-align: left; padding: 2px;">: {{ $kelas->nama }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; text-align: left; padding: 2px;">Semester</td>
                <td style="border: none; text-align: left; padding: 2px;">: {{ $semester }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; text-align: left; padding: 2px;">Tahun Ajaran</td>
                <td style="border: none; text-align: left; padding: 2px;">: {{ $kelas->tahun_ajaran ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">No</th>
                <th rowspan="2">Nama Siswa</th>
                <th colspan="{{ $mapels->count() }}">Mata Pelajaran</th>
                <th rowspan="2">Rata-rata</th>
            </tr>
            <tr>
                @foreach($mapels as $mp)
                    <th>{{ $mp->nama }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $s)
                @php
                    $total = 0;
                    $count = 0;
                    $studentNilais = $nilais->get($s->id, collect());
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $s->name }}</td>
                    @foreach($mapels as $mp)
                        @php
                            $n = $studentNilais->where('mata_pelajaran_id', $mp->id)->first();
                            $val = $n ? round(($n->tugas + $n->uts + $n->uas) / 3) : '-';
                            if (is_numeric($val)) {
                                $total += $val;
                                $count++;
                            }
                        @endphp
                        <td>{{ $val }}</td>
                    @endforeach
                    <td>{{ $count > 0 ? round($total / $count, 2) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div>Dicetak pada: {{ $today }}</div>
        <div class="signature">
            <p>Wali Kelas,</p>
            <br><br><br>
            <p><strong>{{ $kelas->pembina->name ?? '..........................' }}</strong></p>
        </div>
    </div>
</body>
</html>
