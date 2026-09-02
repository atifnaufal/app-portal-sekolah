<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Helpers\UserHistoryHelper;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->session()->get('user_role');
        $userId = $request->session()->get('user_id');
        $user = User::with('kelas')->findOrFail($userId);
        $today = now()->toDateString();

        $attendanceActive = (bool) Setting::getValue('attendance_active', false);
        $startTime = Setting::getValue('attendance_start_time', '07:00');
        $endTime = Setting::getValue('attendance_end_time', '15:00');

        $myAttendance = Absensi::where('user_id', $userId)->whereDate('tanggal', $today)->first();

        if ($role === 'guru') {
            // Guru melihat siswa di kelasnya
            $students = User::where('role', 'siswa')
                ->where('kelas_id', $user->kelas_id)
                ->with(['absensi' => fn ($q) => $q->whereDate('tanggal', $today)])
                ->get();

            return view('mobile.absensi-monitoring', [
                'students' => $students,
                'user' => $user,
                'today' => $today,
            ]);
        }

        $absensiBulan = Absensi::where('user_id', $userId)
            ->whereMonth('tanggal', now()->month)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('mobile.absensi', [
            'myAttendance' => $myAttendance,
            'user' => $user,
            'attendanceActive' => $attendanceActive,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'isWithinTime' => now()->between(now()->setTimeFromTimeString($startTime), now()->setTimeFromTimeString($endTime)),
            'absensiBulan' => $absensiBulan,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Log::info('Absensi submission started', $request->all());

        $userId = $request->session()->get('user_id');
        $user = User::findOrFail($userId);

        if (! Setting::getValue('attendance_active', false)) {
            return back()->with('error', 'Absensi saat ini dinonaktifkan oleh Admin.');
        }

        $today = now()->toDateString();
        $now = now();
        $attendance = Absensi::firstOrNew(['user_id' => $user->id, 'tanggal' => $today]);

        if (! $request->hasFile('foto')) {
            Log::error('Absensi failed: Foto is missing in request');

            return back()->with('error', 'Foto verifikasi tidak terdeteksi. Silakan coba lagi.');
        }

        try {
            $request->validate([
                'foto' => 'required|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'lat' => 'nullable|numeric',
                'long' => 'nullable|numeric',
                'tipe' => 'required|in:masuk,pulang',
            ]);
        } catch (ValidationException $e) {
            Log::error('Absensi validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        if ($request->tipe === 'masuk') {
            if ($attendance->waktu_masuk) {
                return back()->with('error', 'Anda sudah absen masuk hari ini.');
            }

            $path = $request->file('foto')->store('absensi/'.$today, 'public');

            $lateTime = Setting::getValue('attendance_late_time', '07:30');
            $status = $now->gt(now()->setTimeFromTimeString($lateTime)) ? 'terlambat' : 'hadir';

            $attendance->fill([
                'kelas_id' => $user->kelas_id,
                'waktu_masuk' => $now->format('H:i:s'),
                'foto_masuk' => $path,
                'lat_masuk' => $request->lat,
                'long_masuk' => $request->long,
                'status' => $status,
            ]);
            $attendance->save();

            if ($status === 'terlambat') {
                // Cari guru kelas ini
                $guru = User::where('role', 'guru')->where('kelas_id', $user->kelas_id)->first();
                if ($guru) {
                    NotificationHelper::send($guru->id, 'Siswa Terlambat', $user->name.' terlambat masuk hari ini.', route('absensi.index'), 'attendance');
                }
            }

            UserHistoryHelper::logAbsensi($user->id, 'masuk', $status, $request->lat, $request->long, $request);

            return back()->with('success', 'Absensi masuk berhasil dicatat. Status: '.ucfirst($status));
        } else {
            if (! $attendance->waktu_masuk) {
                return back()->with('error', 'Anda harus absen masuk terlebih dahulu.');
            }
            if ($attendance->waktu_pulang) {
                return back()->with('error', 'Anda sudah absen pulang hari ini.');
            }

            $path = $request->file('foto')->store('absensi/'.$today, 'public');
            $attendance->update([
                'waktu_pulang' => $now->format('H:i:s'),
                'foto_pulang' => $path,
                'lat_pulang' => $request->lat,
                'long_pulang' => $request->long,
            ]);

            UserHistoryHelper::logAbsensi($user->id, 'pulang', 'hadir', $request->lat, $request->long, $request);

            return back()->with('success', 'Absensi pulang berhasil dicatat.');
        }
    }

    /**
     * Otorisasi unduh rekap absensi: admin selalu boleh, guru hanya untuk kelasnya.
     */
    protected function authorizeRecap(?int $kelasId): void
    {
        $role = session('user_role');
        $userId = session('user_id');

        if ($role === 'admin') {
            return;
        }

        abort_unless($role === 'guru', 403);

        abort_unless(
            $kelasId === null
            || Kelas::where('id', $kelasId)->where('pembina_id', $userId)->exists()
            || User::where('id', $userId)->where('kelas_id', $kelasId)->exists(),
            403
        );
    }

    /**
     * Kumpulkan data rekap absensi per siswa untuk periode bulanan/tahunan.
     */
    protected function dataRecapAbsensi(string $periode, int $tahun, ?int $bulan, ?int $kelasId): array
    {
        $students = User::where('role', 'siswa')
            ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
            ->orderBy('name')
            ->get();

        $query = Absensi::query()->whereYear('tanggal', $tahun);
        if ($periode === 'bulanan' && $bulan) {
            $query->whereMonth('tanggal', $bulan);
        }
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }
        $recs = $query->get();

        $rows = $students->map(function ($s) use ($recs) {
            $rs = $recs->where('user_id', $s->id);
            $hadir = $rs->where('status', 'hadir')->count();
            $terlambat = $rs->where('status', 'terlambat')->count();
            $izin = $rs->where('status', 'izin')->count();
            $sakit = $rs->where('status', 'sakit')->count();
            $alpha = $rs->where('status', 'alpha')->count();
            $masuk = $rs->whereIn('status', ['hadir', 'terlambat'])->count();
            $total = $rs->count();
            $pct = $total > 0 ? round(($masuk / $total) * 100, 1) : 0;

            return [
                'user' => $s,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpha' => $alpha,
                'total' => $total,
                'hadir_persen' => $pct,
            ];
        });

        return [
            'periode' => $periode,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'kelasId' => $kelasId,
            'kelas' => $kelasId ? Kelas::find($kelasId) : null,
            'rows' => $rows,
            'today' => now()->translatedFormat('d F Y'),
        ];
    }

    protected function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    public function recapPdf(Request $request)
    {
        $periode = $request->periode === 'tahunan' ? 'tahunan' : 'bulanan';
        $tahun = (int) ($request->tahun ?: now()->year);
        $bulan = $periode === 'bulanan' ? (int) ($request->bulan ?: now()->month) : null;
        $kelasId = $request->kelas_id ? (int) $request->kelas_id : null;

        $this->authorizeRecap($kelasId);

        try {
            app()->setLocale('id');
            Carbon::setLocale('id');

            // Optimasi memori
            ini_set('memory_limit', '256M');
            ini_set('max_execution_time', '120');

            $data = $this->dataRecapAbsensi($periode, $tahun, $bulan, $kelasId);

            $pdf = Pdf::loadView('pdf.rekap-absensi', $data);
            $pdf->setPaper('a4', 'landscape');

            $label = $periode === 'bulanan' ? 'bulan-'.$bulan.'-'.$tahun : 'tahun-'.$tahun;

            return $pdf->download('rekap-absensi-'.$label.'.pdf');
        } catch (\Throwable $e) {
            Log::error('Absensi recap PDF gagal, fallback ke Excel: '.$e->getMessage());

            return $this->recapExcel($request);
        }
    }

    public function recapExcel(Request $request)
    {
        $periode = $request->periode === 'tahunan' ? 'tahunan' : 'bulanan';
        $tahun = (int) ($request->tahun ?: now()->year);
        $bulan = $periode === 'bulanan' ? (int) ($request->bulan ?: now()->month) : null;
        $kelasId = $request->kelas_id ? (int) $request->kelas_id : null;

        $this->authorizeRecap($kelasId);
        $d = $this->dataRecapAbsensi($periode, $tahun, $bulan, $kelasId);

        $namaKelas = $d['kelas'] ? $d['kelas']->nama : 'Semua Kelas';
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
        $xml .= '</Styles>';

        $xml .= '<Worksheet ss:Name="Rekap Absensi"><Table>';
        $xml .= '<Column ss:Width="50"/><Column ss:Width="220"/>';
        foreach (range(1, 7) as $i) {
            $xml .= '<Column ss:Width="80"/>';
        }

        $periodeLabel = $periode === 'bulanan'
            ? 'Bulan '.now()->month($bulan)->translatedFormat('F').' '.$tahun
            : 'Tahun Ajaran / Tahun '.$tahun;

        $xml .= '<Row ss:Height="30"><Cell ss:StyleID="title" ss:MergeAcross="7"><Data ss:Type="String">REKAPITULASI KEHADIRAN SISWA</Data></Cell></Row>';
        $xml .= '<Row ss:Height="20"><Cell ss:StyleID="subtitle" ss:MergeAcross="7"><Data ss:Type="String">'.$this->esc($namaKelas).' | '.$this->esc($periodeLabel).'</Data></Cell></Row>';

        $xml .= '<Row ss:Height="22">';
        foreach (['No', 'Nama Siswa', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha', '% Hadir'] as $h) {
            $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">'.$h.'</Data></Cell>';
        }
        $xml .= '</Row>';

        $d['rows']->each(function ($r, $i) use (&$xml) {
            $xml .= '<Row>';
            $xml .= '<Cell ss:StyleID="bordered cell"><Data ss:Type="Number">'.($i + 1).'</Data></Cell>';
            $xml .= '<Cell ss:StyleID="bordered left"><Data ss:Type="String">'.$this->esc($r['user']->name).'</Data></Cell>';
            foreach (['hadir', 'terlambat', 'izin', 'sakit', 'alpha'] as $k) {
                $xml .= '<Cell ss:StyleID="bordered cell"><Data ss:Type="Number">'.$r[$k].'</Data></Cell>';
            }
            $xml .= '<Cell ss:StyleID="bordered cell"><Data ss:Type="Number">'.$r['hadir_persen'].'</Data></Cell>';
            $xml .= '</Row>';
        });

        $xml .= '<Row ss:Height="30"><Cell ss:StyleID="subtitle" ss:MergeAcross="7"><Data ss:Type="String">Dicetak pada '.$this->esc($d['today']).'</Data></Cell></Row>';
        $xml .= '</Table></Worksheet></Workbook>';

        $label = $periode === 'bulanan' ? 'bulan-'.$bulan.'-'.$tahun : 'tahun-'.$tahun;

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="rekap-absensi-'.$label.'.xls"',
        ]);
    }
}
