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
use Illuminate\Support\Str;
use Illuminate\View\View;

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

        // Kelas yang dikelola guru untuk akses rekap: wali kelas (pembina),
        // atau kelas "rumah" tempat guru ditugaskan (home guru).
        $managedClass = Kelas::where('pembina_id', $user->id)->first()
            ?? ($user->kelas_id ? Kelas::find($user->kelas_id) : null);

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
            'mata_pelajaran_id' => 'required|integer|exists:mata_pelajaran,id',
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
        $userRole = session('user_role') ?: optional(Auth::user())->role;

        $isWaliKelas   = $kelas->pembina_id == $userId;
        $isMapelGuru   = MataPelajaran::where('kelas_id', $kelas->id)->where('guru_id', $userId)->exists();
        $isHomeGuru    = optional(User::find($userId))->kelas_id == $kelas->id;

        abort_unless(
            $isWaliKelas
            || $isMapelGuru
            || $isHomeGuru
            || $userRole === 'admin',
            403
        );
    }

    /**
     * Hanya guru pengampu mapel, wali kelas, atau admin yang boleh mengunduh rekap mapelnya.
     */
    protected function authorizeMapel(MataPelajaran $mp): void
    {
        $userId = session('user_id') ?: Auth::id();
        $userRole = session('user_role') ?: optional(Auth::user())->role;

        $isWaliKelas = $mp->kelas && $mp->kelas->pembina_id == $userId;
        $isHomeGuru  = optional(User::find($userId))->kelas_id == $mp->kelas_id;

        abort_unless(
            $mp->guru_id == $userId
            || $isWaliKelas
            || $isHomeGuru
            || $userRole === 'admin',
            403
        );
    }

    /**
     * Data rekap untuk SATU mata pelajaran (dipakai per-mapel PDF & Excel oleh guru pengampu).
     */
    protected function dataRecapMapel(MataPelajaran $mp, int $semester, ?string $tahunAjaran = null): array
    {
        $kelas = $mp->kelas;
        $tahunAjaran = $tahunAjaran ?: ($kelas->tahun_ajaran ?? now()->format('Y').'/'.(now()->format('Y')+1));

        $students = User::where('role', 'siswa')
            ->where('kelas_id', $mp->kelas_id)
            ->orderBy('name')
            ->get();

        $nilais = Nilai::where('mata_pelajaran_id', $mp->id)
            ->where('semester', $semester)
            ->get()
            ->keyBy('siswa_id');

        return [
            'mapel'       => $mp,
            'kelas'       => $kelas,
            'semester'    => $semester,
            'tahunAjaran' => $tahunAjaran,
            'students'    => $students,
            'nilais'      => $nilais,
            'today'       => now()->translatedFormat('d F Y'),
        ];
    }

    public function recapMapelPdf(Request $request, MataPelajaran $mp)
    {
        $this->authorizeMapel($mp);

        try {
            $this->prepPdf();
            $semester = (int) ($request->semester ?: 1);
            $data = $this->dataRecapMapel($mp, $semester, $request->tahun_ajaran);

            $pdf = Pdf::loadView('pdf.rekap-nilai-mapel', $data);
            $pdf->setPaper('a4', 'landscape');

            return $pdf->download(
                'rekap-nilai-'.Str::slug($mp->nama).'-smt-'.$semester.'-'.str_replace('/', '_', $data['tahunAjaran']).'.pdf'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Recap PDF gagal, fallback ke Excel: '.$e->getMessage());
            return $this->recapMapelExcel($request, $mp);
        }
    }

    public function recapMapelExcel(Request $request, MataPelajaran $mp)
    {
        $this->authorizeMapel($mp);

        $semester = (int) ($request->semester ?: 1);
        $d = $this->dataRecapMapel($mp, $semester, $request->tahun_ajaran);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>'."\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:html="http://www.w3.org/TR/REC-html40">';

        $xml .= '<Styles>';
        $xml .= '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="11"/></Style>';
        $xml .= '<Style ss:ID="title"><Font ss:Bold="1" ss:Size="16" ss:Color="#FFFFFF"/><Interior ss:Color="#0F172A" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="subtitle"><Font ss:Color="#475569" ss:Size="11"/><Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center"/></Style>';
        $xml .= '<Style ss:ID="header"><Font ss:Bold="1" ss:Color="#FFFFFF" ss:Size="11"/><Interior ss:Color="#2563EB" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="bordered"><Borders>'
            .'<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'</Borders></Style>';
        $xml .= '<Style ss:ID="cell"><Alignment ss:Horizontal="Center"/></Style>';
        $xml .= '<Style ss:ID="alt"><Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/></Style>';
        $xml .= '<Style ss:ID="left"><Alignment ss:Horizontal="Left"/></Style>';
        $xml .= '<Style ss:ID="footer"><Font ss:Italic="1" ss:Color="#64748B"/></Style>';
        $xml .= '<Style ss:ID="sig"><Font ss:Bold="1"/><Alignment ss:Horizontal="Center"/></Style>';
        $xml .= '<Style ss:ID="score"><Font ss:Bold="1" ss:Color="#0F172A"/></Style>';
        $xml .= '</Styles>';

        $xml .= '<Worksheet ss:Name="Rekap '.$this->esc(substr($mp->nama,0,28)).'"><Table>';
        $xml .= '<Column ss:Width="50"/><Column ss:Width="240"/>';
        foreach (range(1,4) as $i) { $xml .= '<Column ss:Width="90"/>'; }

        // Judul
        $xml .= '<Row ss:Height="30"><Cell ss:StyleID="title" ss:MergeAcross="4"><Data ss:Type="String">REKAPITULASI NILAI MATA PELAJARAN</Data></Cell></Row>';
        $xml .= '<Row ss:Height="20"><Cell ss:StyleID="subtitle" ss:MergeAcross="4"><Data ss:Type="String">'.$this->esc($mp->nama).' | Kelas '.$this->esc($d['kelas']->nama ?? '-').' | Semester '.$semester.' | '.$this->esc($d['tahunAjaran']).'</Data></Cell></Row>';

        // Header
        $xml .= '<Row ss:Height="22">';
        foreach (['No','Nama Siswa','Tugas','UTS','UAS','Rata-rata','Predikat'] as $hdr) {
            $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">'.$hdr.'</Data></Cell>';
        }
        $xml .= '</Row>';

        foreach ($d['students'] as $i => $s) {
            $n = $d['nilais'][$s->id] ?? null;
            $rowStyle = (($i % 2) === 1) ? 'alt' : 'default';
            $val = $this->hitungAkhir($n);
            $xml .= '<Row>';
            $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="Number">'.($i + 1).'</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered left '.$rowStyle.'"><Data ss:Type="String">'.$this->esc($s->name).'</Data></Cell>';
            foreach (['tugas','uts','uas'] as $col) {
                $c = $n->{$col} ?? null;
                $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="Number">'.($c === null ? $c : (float) $c).'</Data></Cell>';
            }
            if ($val === null) {
                $xml .= '<Cell ss:StyleID="bordered score cell '.$rowStyle.'"><Data ss:Type="String">-</Data></Cell>';
                $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="String">-</Data></Cell>';
            } else {
                $xml .= '<Cell ss:StyleID="bordered score cell '.$rowStyle.'"><Data ss:Type="Number">'.$val.'</Data></Cell>';
                $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="String">'.$this->predikat($val).'</Data></Cell>';
            }
            $xml .= '</Row>';
        }

        $xml .= '<Row ss:Height="30"><Cell ss:StyleID="footer" ss:MergeAcross="4"><Data ss:Type="String">Dicetak pada '.$this->esc($d['today']).'</Data></Cell></Row>';
        $xml .= '<Row ss:Height="40"></Row>';

        $xml .= '</Table></Worksheet></Workbook>';

        $filename = 'rekap-nilai-'.Str::slug($mp->nama).'-smt-'.$semester.'-'.str_replace('/', '_', $d['tahunAjaran']).'.xls';

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function recapPdf(Request $request, Kelas $kelas)
    {
        $this->authorizeRecap($kelas);

        try {
            $this->prepPdf();
            $semester = (int) ($request->semester ?: 1);
            $data = $this->dataRecap($kelas, $semester, $request->tahun_ajaran);

            $pdf = Pdf::loadView('pdf.rekap-nilai', $data);
            $pdf->setPaper('a4', 'landscape');

            return $pdf->download(
                'rekap-nilai-'.$kelas->nama.'-smt-'.$semester.'-'.str_replace('/', '_', $data['tahunAjaran']).'.pdf'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Recap kelas PDF gagal, fallback ke Excel: '.$e->getMessage());
            return $this->recapExcel($request, $kelas);
        }
    }

    /**
     * Export rekap nilai ke Excel (.xls) yang rapi.
     *
     * Menggunakan format SpreadsheetML 2003 (XML) sehingga TIDAK membutuhkan
     * ekstensi PHP tambahan (gd/zip) — aman di semua environment, termasuk
     * server produksi Docker/Railway.
     */
    public function recapExcel(Request $request, Kelas $kelas)
    {
        $this->authorizeRecap($kelas);

        $semester = (int) ($request->semester ?: 1);
        $d = $this->dataRecap($kelas, $semester, $request->tahun_ajaran);

        $colCount = $d['mapels']->count() + 4; // No + Nama + mapel + rata + predikat

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>'."\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:html="http://www.w3.org/TR/REC-html40">';

        // Styles
        $xml .= '<Styles>';
        $xml .= '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="11"/></Style>';
        $xml .= '<Style ss:ID="title"><Font ss:Bold="1" ss:Size="16" ss:Color="#FFFFFF"/><Interior ss:Color="#0F172A" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="subtitle"><Font ss:Color="#475569" ss:Size="11"/><Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center"/></Style>';
        $xml .= '<Style ss:ID="header"><Font ss:Bold="1" ss:Color="#FFFFFF" ss:Size="11"/><Interior ss:Color="#2563EB" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="bordered"><Borders>'
            .'<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'</Borders></Style>';
        $xml .= '<Style ss:ID="cell"><Alignment ss:Horizontal="Center"/></Style>';
        $xml .= '<Style ss:ID="alt"><Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/></Style>';
        $xml .= '<Style ss:ID="left"><Alignment ss:Horizontal="Left"/></Style>';
        $xml .= '<Style ss:ID="footer"><Font ss:Italic="1" ss:Color="#64748B"/></Style>';
        $xml .= '<Style ss:ID="sig"><Font ss:Bold="1"/><Alignment ss:Horizontal="Center"/></Style>';
        $xml .= '</Styles>';

        // Worksheet
        $mergeTitle = ($colCount - 1);
        $xml .= '<Worksheet ss:Name="Rekap Nilai"><Table>';
        $xml .= '<Column ss:Width="50"/>';
        $xml .= '<Column ss:Width="220"/>';
        foreach (range(1, $d['mapels']->count() + 2) as $i) {
            $xml .= '<Column ss:Width="95"/>';
        }

        // Judul
        $xml .= '<Row ss:Height="30"><Cell ss:StyleID="title" ss:MergeAcross="'.$mergeTitle.'"><Data ss:Type="String">REKAPITULASI NILAI SISWA</Data></Cell></Row>';
        $xml .= '<Row ss:Height="20"><Cell ss:StyleID="subtitle" ss:MergeAcross="'.$mergeTitle.'"><Data ss:Type="String">'.$this->esc($d['kelas']->nama).' | Semester '.$semester.' | Tahun Ajaran '.$this->esc($d['tahunAjaran']).'</Data></Cell></Row>';

        // Header
        $mergeMapel = ($d['mapels']->count() - 1);
        $xml .= '<Row ss:Height="22">';
        $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">No</Data></Cell>';
        $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">Nama Siswa</Data></Cell>';
        if ($d['mapels']->count() > 0) {
            $xml .= '<Cell ss:StyleID="header" ss:MergeAcross="'.$mergeMapel.'"><Data ss:Type="String">Mata Pelajaran</Data></Cell>';
        }
        $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">Rata-rata</Data></Cell>';
        $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">Predikat</Data></Cell>';
        $xml .= '</Row>';

        // Baris nama mapel (kedua dari header hanya jika perlu dipisah). Simpan satu baris per siswa:
        foreach ($d['students'] as $i => $s) {
            $total = 0;
            $count = 0;
            $rowStyle = (($i % 2) === 1) ? 'alt' : 'default';
            $xml .= '<Row>';
            $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="Number">'.($i + 1).'</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered left '.$rowStyle.'"><Data ss:Type="String">'.$this->esc($s->name).'</Data></Cell>';

            foreach ($d['mapels'] as $mp) {
                $n = $d['nilais'][$s->id][$mp->id] ?? null;
                $val = $this->hitungAkhir($n);
                if ($val === null) {
                    $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="String">-</Data></Cell>';
                } else {
                    $total += $val;
                    $count++;
                    $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="Number">'.$val.'</Data></Cell>';
                }
            }

            $avg = $count > 0 ? round($total / $count, 2) : null;
            if ($avg === null) {
                $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="String">-</Data></Cell>';
                $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="String">-</Data></Cell>';
            } else {
                $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="Number">'.$avg.'</Data></Cell>';
                $xml .= '<Cell ss:StyleID="bordered cell '.$rowStyle.'"><Data ss:Type="String">'.$this->predikat($avg).'</Data></Cell>';
            }
            $xml .= '</Row>';
        }

        // Footer
        $xml .= '<Row ss:Height="30"><Cell ss:StyleID="footer" ss:MergeAcross="'.$mergeTitle.'"><Data ss:Type="String">Dicetak pada '.$this->esc($d['today']).'</Data></Cell></Row>';
        $xml .= '<Row ss:Height="40"></Row>';
        $xml .= '<Row><Cell ss:MergeAcross="2"></Cell><Cell ss:StyleID="sig" ss:MergeAcross="1"><Data ss:Type="String">Wali Kelas,</Data></Cell></Row>';
        $xml .= '<Row ss:Height="40"><Cell ss:MergeAcross="2"></Cell><Cell ss:StyleID="sig" ss:MergeAcross="1"><Data ss:Type="String">'.$this->esc($d['kelas']->pembina->name ?? '..........................').'</Data></Cell></Row>';

        $xml .= '</Table>';

        // Freeze panes (baris header)
        $xml .= '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">'
            .'<FreezePanes/><FrozenNoSplit/>'
            .'<SplitHorizontal>1</SplitHorizontal><TopRowBottomPane>0</TopRowBottomPane>'
            .'<SplitVertical>2</SplitVertical><RightColumnLeftPane>2</RightColumnLeftPane>'
            .'<ActivePane>0</ActivePane>'
            .'</WorksheetOptions>';

        $xml .= '</Worksheet></Workbook>';

        $filename = 'rekap-nilai-'.$kelas->nama.'-smt-'.$semester.'-'.str_replace('/', '_', $d['tahunAjaran']).'.xls';

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    protected function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    protected function authorizePeriode(): void
    {
        $userId = session('user_id') ?: Auth::id();
        $role = session('user_role') ?: optional(Auth::user())->role;
        abort_unless($role === 'admin' || $role === 'guru', 403);
    }

    /**
     * Pastikan locale Indonesia untuk tanggal PDF + lokasi font dompdf aman di produksi.
     */
    protected function prepPdf(): void
    {
        app()->setLocale('id');
        \Carbon\Carbon::setLocale('id');
    }

    /**
     * Rekap nilai lintas mapel untuk seluruh siswa pada periode tertentu.
     * - bulanan : nilai yang dicatat (created_at) pada bulan & tahun tertentu
     * - tahunan : nilai dengan tahun_ajaran tertentu (lintas semester)
     */
    protected function dataRecapPeriode(string $periode, int $tahun, ?int $bulan, ?string $tahunAjaran): array
    {
        $query = Nilai::query();
        if ($periode === 'bulanan') {
            $query->whereYear('created_at', $tahun)
                  ->whereMonth('created_at', $bulan);
        } else {
            $query->where('tahun_ajaran', $tahunAjaran);
        }
        $all = $query->with('mataPelajaran')->with('siswa')->get();

        $students = collect();
        $mapels = collect();
        foreach ($all as $n) {
            if ($n->siswa) { $students->put($n->siswa_id, $n->siswa); }
            if ($n->mataPelajaran) { $mapels->put($n->mata_pelajaran_id, $n->mataPelajaran); }
        }
        $students = $students->sortBy('name')->values();
        $mapels = $mapels->sortBy('nama')->values();

        // Ambil nilai TERBARU per (siswa, mapel)
        $normalized = [];
        foreach ($all as $n) {
            $cur = $normalized[$n->siswa_id][$n->mata_pelajaran_id] ?? null;
            if (! $cur || $n->created_at->gte($cur->created_at)) {
                $normalized[$n->siswa_id][$n->mata_pelajaran_id] = $n;
            }
        }

        return [
            'periode'    => $periode,
            'tahun'      => $tahun,
            'bulan'      => $bulan,
            'tahunAjaran'=> $tahunAjaran,
            'students'   => $students,
            'mapels'     => $mapels,
            'nilais'     => $normalized,
            'today'      => now()->translatedFormat('d F Y'),
        ];
    }

    public function recapPeriodePdf(Request $request)
    {
        $this->authorizePeriode();

        try {
            $this->prepPdf();

            $periode = $request->periode === 'tahunan' ? 'tahunan' : 'bulanan';
            $tahun   = (int) ($request->tahun ?: now()->year);
            $bulan   = $periode === 'bulanan' ? (int) ($request->bulan ?: now()->month) : null;
            $tahunAjaran = $periode === 'tahunan' ? ($request->tahun_ajaran ?: (now()->format('Y').'/'.(now()->format('Y')+1))) : null;

            $data = $this->dataRecapPeriode($periode, $tahun, $bulan, $tahunAjaran);
            $pdf = Pdf::loadView('pdf.rekap-nilai-periode', $data);
            $pdf->setPaper('a4', 'landscape');

            $label = $periode === 'bulanan' ? 'bulan-'.$bulan.'-'.$tahun : 'tahun-'.str_replace('/', '_', $tahunAjaran);
            return $pdf->download('rekap-nilai-'.$label.'.pdf');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Recap periode PDF gagal, fallback ke Excel: '.$e->getMessage());
            return $this->recapPeriodeExcel($request);
        }
    }

    public function recapPeriodeExcel(Request $request)
    {
        $this->authorizePeriode();

        $periode = $request->periode === 'tahunan' ? 'tahunan' : 'bulanan';
        $tahun   = (int) ($request->tahun ?: now()->year);
        $bulan   = $periode === 'bulanan' ? (int) ($request->bulan ?: now()->month) : null;
        $tahunAjaran = $periode === 'tahunan' ? ($request->tahun_ajaran ?: (now()->format('Y').'/'.(now()->format('Y')+1))) : null;

        $d = $this->dataRecapPeriode($periode, $tahun, $bulan, $tahunAjaran);
        $colCount = $d['mapels']->count() + 4;
        $mergeTitle = $colCount - 1;

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>'."\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';

        $xml .= '<Styles>';
        $xml .= '<Style ss:ID="Default"><Alignment ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="11"/></Style>';
        $xml .= '<Style ss:ID="title"><Font ss:Bold="1" ss:Size="16" ss:Color="#FFFFFF"/><Interior ss:Color="#0F172A" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="subtitle"><Font ss:Color="#475569" ss:Size="11"/><Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center"/></Style>';
        $xml .= '<Style ss:ID="header"><Font ss:Bold="1" ss:Color="#FFFFFF" ss:Size="11"/><Interior ss:Color="#2563EB" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $xml .= '<Style ss:ID="bordered"><Borders>'
            .'<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>'
            .'</Borders></Style>';
        $xml .= '<Style ss:ID="cell"><Alignment ss:Horizontal="Center"/></Style>';
        $xml .= '<Style ss:ID="left"><Alignment ss:Horizontal="Left"/></Style>';
        $xml .= '<Style ss:ID="score"><Font ss:Bold="1"/></Style>';
        $xml .= '</Styles>';

        $periodeLabel = $periode === 'bulanan'
            ? \Carbon\Carbon::create()->month($bulan)->translatedFormat('F').' '.$tahun
            : 'Tahun Ajaran '.$tahunAjaran;

        $xml .= '<Worksheet ss:Name="Rekap Nilai"><Table>';
        $xml .= '<Column ss:Width="50"/><Column ss:Width="220"/>';
        foreach (range(1, $d['mapels']->count() + 2) as $i) { $xml .= '<Column ss:Width="95"/>'; }

        $xml .= '<Row ss:Height="30"><Cell ss:StyleID="title" ss:MergeAcross="'.$mergeTitle.'"><Data ss:Type="String">REKAPITULASI NILAI SISWA ('.$this->esc(strtoupper($periode)).')</Data></Cell></Row>';
        $xml .= '<Row ss:Height="20"><Cell ss:StyleID="subtitle" ss:MergeAcross="'.$mergeTitle.'"><Data ss:Type="String">'.$this->esc($periodeLabel).'</Data></Cell></Row>';

        $xml .= '<Row ss:Height="22">';
        $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">No</Data></Cell>';
        $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">Nama Siswa</Data></Cell>';
        if ($d['mapels']->count() > 0) {
            $xml .= '<Cell ss:StyleID="header" ss:MergeAcross="'.($d['mapels']->count() - 1).'"><Data ss:Type="String">Mata Pelajaran</Data></Cell>';
        }
        $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">Rata-rata</Data></Cell>';
        $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">Predikat</Data></Cell>';
        $xml .= '</Row>';

        foreach ($d['students'] as $i => $s) {
            $total = 0; $count = 0;
            $xml .= '<Row>';
            $xml .= '<Cell ss:StyleID="bordered cell"><Data ss:Type="Number">'.($i + 1).'</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered left"><Data ss:Type="String">'.$this->esc($s->name).'</Data></Cell>';
            foreach ($d['mapels'] as $mp) {
                $n = $d['nilais'][$s->id][$mp->id] ?? null;
                $val = $this->hitungAkhir($n);
                if ($val === null) {
                    $xml .= '<Cell ss:StyleID="bordered cell"><Data ss:Type="String">-</Data></Cell>';
                } else {
                    $total += $val; $count++;
                    $xml .= '<Cell ss:StyleID="bordered cell score"><Data ss:Type="Number">'.$val.'</Data></Cell>';
                }
            }
            $avg = $count > 0 ? round($total / $count, 2) : null;
            $xml .= '<Cell ss:StyleID="bordered cell score"><Data ss:Type="String">'.($avg === null ? '-' : $avg).'</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered cell"><Data ss:Type="String">'.($avg === null ? '-' : $this->predikat($avg)).'</Data></Cell>';
            $xml .= '</Row>';
        }

        $xml .= '<Row ss:Height="30"><Cell ss:StyleID="subtitle" ss:MergeAcross="'.$mergeTitle.'"><Data ss:Type="String">Dicetak pada '.$this->esc($d['today']).'</Data></Cell></Row>';
        $xml .= '</Table></Worksheet></Workbook>';

        $label = $periode === 'bulanan' ? 'bulan-'.$bulan.'-'.$tahun : 'tahun-'.str_replace('/', '_', $tahunAjaran);
        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="rekap-nilai-'.$label.'.xls"',
        ]);
    }
}
