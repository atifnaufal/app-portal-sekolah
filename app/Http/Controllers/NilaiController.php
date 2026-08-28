<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class NilaiController extends Controller
{
    /**
     * Hitung nilai akhir seorang siswa pada satu record nilai.
     */
    protected function hitungAkhir(?Nilai $n): ?float
    {
        if (! $n || ($n->tugas === null && $n->uts === null && $n->uas === null)) {
            return null;
        }
        return round((($n->tugas ?? 0) + ($n->uts ?? 0) + ($n->uas ?? 0)) / 3, 2);
    }

    /**
     * Konversi angka (0-100) menjadi predikat huruf.
     */
    protected function predikat(float $nilai): string
    {
        return match (true) {
            $nilai >= 90 => 'A',
            $nilai >= 80 => 'B',
            $nilai >= 70 => 'C',
            $nilai >= 60 => 'D',
            default     => 'E',
        };
    }

    public function index(Request $request)
    {
        $userId = session('user_id') ?: Auth::id();
        $user = User::with('kelas')->findOrFail($userId);
        $isGuru = $user->role === 'guru';

        $managedClass = Kelas::where('pembina_id', $user->id)->first();

        if ($isGuru) {
            $mataPelajarans = MataPelajaran::where('guru_id', $user->id)
                ->with('kelas')
                ->get();

            $selectedSubject = null;
            $students = collect();

            if ($request->has('subject_id')) {
                $selectedSubject = MataPelajaran::with('kelas')->findOrFail($request->subject_id);
                if ($selectedSubject->guru_id !== $user->id) {
                    abort(403);
                }

                $allStudents = User::where('role', 'siswa')
                    ->where('kelas_id', $selectedSubject->kelas_id)
                    ->orderBy('name')
                    ->get();

                // Nilai per siswa untuk mapel ini (SEMUA semester, agar bisa diedit semua)
                $nilais = Nilai::where('mata_pelajaran_id', $selectedSubject->id)
                    ->get()
                    ->groupBy('siswa_id');

                $students = $allStudents->map(function ($student) use ($nilais) {
                    $student->nilai_records = $nilais->get($student->id, collect());
                    return $student;
                });
            }

            return view('mobile.nilai', compact(
                'user', 'isGuru', 'mataPelajarans', 'selectedSubject', 'students', 'managedClass'
            ));
        }

        $nilais = Nilai::where('siswa_id', $user->id)
            ->with('mataPelajaran')
            ->get()
            ->groupBy('mata_pelajaran_id');

        return view('mobile.nilai', compact('user', 'isGuru', 'nilais'));
    }

    /**
     * Simpan/perbarui nilai (guru pengampu).
     */
    public function upsert(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'siswa_id'          => 'required|integer|exists:users,id',
            'mata_pelajaran_id' => 'required|integer|exists:mata_pelajaran,id', // PERBAIKAN: tabel tunggal
            'semester'          => 'required|integer|between:1,2',
            'tahun_ajaran'      => 'nullable|string|max:10',
            'tugas'             => 'nullable|numeric|min:0|max:100',
            'uts'               => 'nullable|numeric|min:0|max:100',
            'uas'               => 'nullable|numeric|min:0|max:100',
        ]);

        $userId = session('user_id') ?: Auth::id();
        $subject = MataPelajaran::with('kelas')->findOrFail($data['mata_pelajaran_id']);
        abort_unless($subject->guru_id == $userId, 403);

        Nilai::updateOrCreate(
            [
                'siswa_id'          => $data['siswa_id'],
                'mata_pelajaran_id' => $data['mata_pelajaran_id'],
                'semester'          => $data['semester'],
            ],
            [
                'kelas_id'     => $subject->kelas_id,
                'tahun_ajaran' => $data['tahun_ajaran'] ?? $subject->kelas?->tahun_ajaran ?? now()->format('Y').'/'.(now()->format('Y')+1),
                'tugas'        => $data['tugas'] ?? 0,
                'uts'          => $data['uts'] ?? 0,
                'uas'          => $data['uas'] ?? 0,
            ]
        );

        return back()->with('success', 'Nilai berhasil diperbarui.');
    }

    /**
     * Muat data rekap untuk kelas & semester (dipakai PDF & Excel).
     */
    protected function dataRecap(Kelas $kelas, int $semester, ?string $tahunAjaran = null): array
    {
        $tahunAjaran = $tahunAjaran ?: ($kelas->tahun_ajaran ?? now()->format('Y').'/'.(now()->format('Y')+1));

        $students = User::where('role', 'siswa')
            ->where('kelas_id', $kelas->id)
            ->orderBy('name')
            ->get();

        $mapels = MataPelajaran::where('kelas_id', $kelas->id)->orderBy('nama')->get();

        $nilais = Nilai::where('kelas_id', $kelas->id)
            ->where('semester', $semester)
            ->get();

        $normalized = [];
        foreach ($nilais as $n) {
            $normalized[$n->siswa_id][$n->mata_pelajaran_id] = $n;
        }

        return [
            'kelas'       => $kelas,
            'semester'    => $semester,
            'tahunAjaran' => $tahunAjaran,
            'students'    => $students,
            'mapels'      => $mapels,
            'nilais'      => $normalized,
            'today'       => now()->translatedFormat('d F Y'),
        ];
    }

    protected function authorizeRecap(Kelas $kelas): void
    {
        $userId = session('user_id') ?: Auth::id();
        abort_unless($kelas->pembina_id == $userId || session('user_role') === 'admin'
            || \App\Models\MataPelajaran::where('kelas_id', $kelas->id)->where('guru_id', $userId)->exists(), 403);
    }

    public function recapPdf(Request $request, Kelas $kelas)
    {
        $this->authorizeRecap($kelas);

        $semester = (int) ($request->semester ?: 1);
        $data = $this->dataRecap($kelas, $semester, $request->tahun_ajaran);

        $pdf = Pdf::loadView('pdf.rekap-nilai', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'rekap-nilai-'.$kelas->nama.'-smt-'.$semester.'-'.str_replace('/', '_', $data['tahunAjaran']).'.pdf'
        );
    }

    /**
     * Export rekap nilai ke Excel (.xlsx) yang rapi menggunakan PhpSpreadsheet.
     */
    public function recapExcel(Request $request, Kelas $kelas)
    {
        $this->authorizeRecap($kelas);

        $semester = (int) ($request->semester ?: 1);
        $d = $this->dataRecap($kelas, $semester, $request->tahun_ajaran);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Nilai');

        $navy   = '0F172A';
        $blue   = '2563EB';
        $light  = 'F1F5F9';
        $border = 'CBD5E1';

        // ---- Baris judul ----
        $sheet->setCellValue('A1', 'REKAPITULASI NILAI SISWA');
        $sheet->mergeCells('A1:'.($this->colLetter(3 + $d['mapels']->count())).'1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($navy);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->setCellValue('A2', $kelas->nama.' | Semester '.$semester.' | Tahun Ajaran '.$d['tahunAjaran']);
        $sheet->mergeCells('A2:'.($this->colLetter(3 + $d['mapels']->count())).'2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setSize(11)->getColor()->setARGB('475569');
        $sheet->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($light);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // ---- Header kolom ----
        $headerRow = 4;
        $sheet->setCellValue('A'.$headerRow, 'No');
        $sheet->setCellValue('B'.$headerRow, 'Nama Siswa');
        $col = 3;
        foreach ($d['mapels'] as $mp) {
            $sheet->setCellValue($this->colLetter($col).$headerRow, $mp->nama);
            $col++;
        }
        $sheet->setCellValue($this->colLetter($col).$headerRow, 'Rata-rata');
        $sheet->setCellValue($this->colLetter($col + 1).$headerRow, 'Predikat');

        $lastCol = $this->colLetter(3 + $d['mapels']->count() + 1); // +2 untuk rata & predikat
        $sheet->getStyle('A'.$headerRow.':'.$lastCol.$headerRow)->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle('A'.$headerRow.':'.$lastCol.$headerRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A'.$headerRow.':'.$lastCol.$headerRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($blue);
        $sheet->getRowDimension($headerRow)->setRowHeight(22);

        // ---- Data ----
        $row = $headerRow + 1;
        foreach ($d['students'] as $i => $s) {
            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValue('B'.$row, $s->name);

            $total = 0;
            $count = 0;
            $col = 3;
            foreach ($d['mapels'] as $mp) {
                $n = $d['nilais'][$s->id][$mp->id] ?? null;
                $val = $this->hitungAkhir($n);
                $sheet->setCellValue($this->colLetter($col).$row, $val === null ? '-' : $val);
                if ($val !== null) {
                    $total += $val;
                    $count++;
                }
                $col++;
            }

            $avg = $count > 0 ? round($total / $count, 2) : null;
            $sheet->setCellValue($this->colLetter($col).$row, $avg === null ? '-' : $avg);
            $sheet->setCellValue($this->colLetter($col + 1).$row, $avg === null ? '-' : $this->predikat($avg));

            if ($i % 2 === 1) {
                $sheet->getStyle('A'.$row.':'.$lastCol.$row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F8FAFC');
            }
            $row++;
        }

        $lastDataRow = $row - 1;

        // ---- Footer ----
        $row += 1;
        $sheet->setCellValue('B'.$row, 'Dicetak pada '.$d['today']);
        $sheet->getStyle('B'.$row)->getFont()->setItalic(true)->getColor()->setARGB('64748B');
        $row += 4;
        $sheet->setCellValue('D'.$row, 'Wali Kelas,');
        $sheet->mergeCells('D'.$row.':E'.$row);
        $row += 5;
        $sheet->setCellValue('D'.$row, $kelas->pembina->name ?? '..........................');
        $sheet->mergeCells('D'.$row.':E'.$row);
        $sheet->getStyle('D'.$row)->getFont()->setBold(true);
        $sheet->getStyle('D'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ---- Styling keseluruhan ----
        $styleArray = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $border]],
            ],
        ];
        if ($lastDataRow >= $headerRow) {
            $sheet->getStyle('A'.$headerRow.':'.$lastCol.$lastDataRow)->applyFromArray($styleArray);
        }
        $sheet->getStyle('B'.$headerRow.':B'.$lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A'.$headerRow.':A'.$lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(30);
        foreach (range(3, 3 + $d['mapels']->count() + 1) as $cc) {
            $sheet->getColumnDimension($this->colLetter($cc))->setWidth(12);
        }

        $sheet->freezePane('C5');

        $writer = new Xlsx($spreadsheet);
        $filename = 'rekap-nilai-'.$kelas->nama.'-smt-'.$semester.'-'.str_replace('/', '_', $d['tahunAjaran']).'.xlsx';

        return $this->streamXlsx($writer, $filename);
    }

    protected function colLetter(int $n): string
    {
        $letter = '';
        while ($n > 0) {
            $mod = ($n - 1) % 26;
            $letter = chr(65 + $mod).$letter;
            $n = intdiv($n - 1, 26);
        }
        return $letter;
    }

    protected function streamXlsx(Xlsx $writer, string $filename)
    {
        $temp = tempnam(sys_get_temp_dir(), 'rekap');
        $writer->save($temp);

        return response()->streamDownload(function () use ($temp) {
            readfile($temp);
            @unlink($temp);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
