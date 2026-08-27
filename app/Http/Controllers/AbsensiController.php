<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use App\Models\Setting;
use App\Helpers\NotificationHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

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
                ->with(['absensi' => fn($q) => $q->whereDate('tanggal', $today)])
                ->get();

            return view('mobile.absensi-monitoring', [
                'students' => $students,
                'user' => $user,
                'today' => $today
            ]);
        }

        return view('mobile.absensi', [
            'myAttendance' => $myAttendance,
            'user' => $user,
            'attendanceActive' => $attendanceActive,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'isWithinTime' => now()->between(now()->setTimeFromTimeString($startTime), now()->setTimeFromTimeString($endTime))
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Log::info('Absensi submission started', $request->all());

        $userId = $request->session()->get('user_id');
        $user = User::findOrFail($userId);

        if (!Setting::getValue('attendance_active', false)) {
            return back()->with('error', 'Absensi saat ini dinonaktifkan oleh Admin.');
        }

        $today = now()->toDateString();
        $now = now();
        $attendance = Absensi::firstOrNew(['user_id' => $user->id, 'tanggal' => $today]);

        if (!$request->hasFile('foto')) {
             \Illuminate\Support\Facades\Log::error('Absensi failed: Foto is missing in request');
             return back()->with('error', 'Foto verifikasi tidak terdeteksi. Silakan coba lagi.');
        }

        try {
            $request->validate([
                'foto' => 'required|image|max:2048',
                'lat' => 'nullable|numeric',
                'long' => 'nullable|numeric',
                'tipe' => 'required|in:masuk,pulang'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Absensi validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        $path = $request->file('foto')->store('absensi/'.$today, 'public');

        if ($request->tipe === 'masuk') {
            if ($attendance->waktu_masuk) return back()->with('error', 'Anda sudah absen masuk hari ini.');

            $lateTime = Setting::getValue('attendance_late_time', '07:30');
            $status = $now->gt(now()->setTimeFromTimeString($lateTime)) ? 'terlambat' : 'hadir';

            $attendance->fill([
                'kelas_id' => $user->kelas_id,
                'waktu_masuk' => $now->format('H:i:s'),
                'foto_masuk' => $path,
                'lat_masuk' => $request->lat,
                'long_masuk' => $request->long,
                'status' => $status
            ]);
            $attendance->save();

            if ($status === 'terlambat') {
                // Cari guru kelas ini
                $guru = User::where('role', 'guru')->where('kelas_id', $user->kelas_id)->first();
                if ($guru) {
                    NotificationHelper::send($guru->id, 'Siswa Terlambat', $user->name . ' terlambat masuk hari ini.', route('absensi.index'), 'attendance');
                }
            }

            return back()->with('success', 'Absensi masuk berhasil dicatat. Status: ' . ucfirst($status));
        } else {
            if (!$attendance->waktu_masuk) return back()->with('error', 'Anda harus absen masuk terlebih dahulu.');
            if ($attendance->waktu_pulang) return back()->with('error', 'Anda sudah absen pulang hari ini.');

            $attendance->update([
                'waktu_pulang' => $now->format('H:i:s'),
                'foto_pulang' => $path,
                'lat_pulang' => $request->lat,
                'long_pulang' => $request->long
            ]);

            return back()->with('success', 'Absensi pulang berhasil dicatat.');
        }
    }

    public function notifications(): View
    {
        abort_unless(session('user_role') === 'admin', 403);
        $today = now()->toDateString();
        return view('notifikasi.index', [
            'latest' => Absensi::with(['user', 'kelas'])->latest('waktu_masuk')->take(20)->get(),
            'todayCount' => Absensi::whereDate('tanggal', $today)->count(),
            'teacherCount' => Absensi::whereDate('tanggal', $today)->whereHas('user', fn ($q) => $q->where('role', 'guru'))->count(),
            'studentCount' => Absensi::whereDate('tanggal', $today)->whereHas('user', fn ($q) => $q->where('role', 'siswa'))->count(),
        ]);
    }
}
