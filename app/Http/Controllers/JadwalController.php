<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JadwalController extends Controller
{
    public function index()
    {
        $userId = session('user_id') ?: Auth::id();
        $user = User::with('kelas')->findOrFail($userId);
        $isGuru = $user->role === 'guru';

        $query = Jadwal::with(['mataPelajaran', 'kelas', 'guru']);

        if ($isGuru) {
            $query->where('guru_id', $user->id);
        } else {
            $query->where('kelas_id', $user->kelas_id);
        }

        $jadwals = $query->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('hari');

        // Auto-expire: hide sessions whose schedule window has already passed.
        $jadwals = $this->filterExpired($jadwals);

        // Statistik ringkas untuk guru / siswa
        $stat = [
            'total' => $jadwals->flatten()->count(),
            'mapel' => $jadwals->flatten()->pluck('mata_pelajaran_id')->unique()->count(),
            'hariIni' => $jadwals[now()->translatedFormat('l')] ?? collect(),
        ];

        return view('mobile.jadwal', compact('user', 'isGuru', 'jadwals', 'stat'));
    }

    // ===== Admin CRUD (desktop) =====

    public function adminIndex(Request $request): View
    {
        $hari = $request->get('hari');
        $kelasId = $request->get('kelas_id');

        $query = Jadwal::with(['mataPelajaran', 'kelas', 'guru']);

        if ($hari && $hari !== 'semua') {
            $query->where('hari', $hari);
        }
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        $jadwals = $query->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('jam_mulai')
            ->get();

        return view('admin.jadwal.index', [
            'jadwals' => $jadwals,
            'hari' => $hari,
            'kelasId' => $kelasId,
            'kelases' => Kelas::orderBy('tingkat')->orderBy('nama')->get(),
            'mapels' => MataPelajaran::orderBy('nama')->get(),
            'gurus' => User::where('role', 'guru')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'guru_id' => ['required', 'exists:users,id'],
            'hari' => ['required', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'ruangan' => ['required', 'max:255'],
        ]);

        Jadwal::create($data);

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(Request $request, Jadwal $jadwal): RedirectResponse
    {
        $data = $request->validate([
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'guru_id' => ['required', 'exists:users,id'],
            'hari' => ['required', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'ruangan' => ['required', 'max:255'],
        ]);

        $jadwal->update($data);

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * Premature schedule expiry: a day-of-week based schedule that is recurring
     * weekly has no date column, so we hide past days of the current week and,
     * for today, only keep sessions that have not finished yet. The schedule
     * "disappears" as time passes and admin re-creates it for the next week.
     */
    private function filterExpired(\Illuminate\Support\Collection $jadwals): \Illuminate\Support\Collection
    {
        $order = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        // Carbon dayOfWeek: 0=Sunday .. 6=Saturday. Map Mon(=1)->senin index 0, ..., Sat(=6)->5.
        $todayIndex = now()->dayOfWeek - 1;   // Monday => 0, ... Saturday => 5, Sunday => -1
        $nowTime = now()->format('H:i:s');

        // If today is not a scheduled day (Sunday => -1), the whole week is over -> hide all
        if ($todayIndex < 0) {
            return collect();
        }

        return $jadwals->filter(function ($list, $hari) use ($order, $todayIndex, $nowTime) {
            $dayIndex = array_search($hari, $order, true);

            // Hide days that already passed earlier this week
            if ($dayIndex < $todayIndex) {
                return false;
            }

            // For today, hide sessions that have already ended
            if ($dayIndex === $todayIndex) {
                $list = $list->filter(fn ($j) => $j->jam_selesai >= $nowTime);
                return $list->isNotEmpty();
            }

            return true;
        });
    }
}
