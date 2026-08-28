<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Notifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    /**
     * Pusat informasi admin: ringkasan aktivitas absensi terbaru.
     */
    public function index(): View
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

    /**
     * Daftar notifikasi milik user yang sedang login, sekaligus menandainya sudah dibaca.
     */
    public function mine(): View
    {
        $userId = session('user_id');

        $notifications = Notifikasi::where('user_id', $userId)->latest()->get();
        Notifikasi::where('user_id', $userId)->whereNull('dibaca_pada')->update(['dibaca_pada' => now()]);

        return view('mobile.notifications', ['notifications' => $notifications]);
    }

    /**
     * Daftar notifikasi tugas untuk guru (jawaban siswa terbaru).
     */
    public function tugas(): View
    {
        return view('mobile.tugas-notifikasi', [
            'notifikasis' => Notifikasi::where('user_id', session('user_id'))->latest()->get(),
        ]);
    }

    /**
     * Fallback realtime: polling notifikasi baru (id > last_id) untuk menampilkan
     * toast popup langsung di mobile saat Echo/Reverb tidak tersedia.
     */
    public function poll(Request $request): JsonResponse
    {
        $userId = $request->session()->get('user_id');
        $lastId = (int) $request->query('last_id', 0);

        $notifikasi = Notifikasi::where('user_id', $userId)
            ->where('id', '>', $lastId)
            ->latest('id')
            ->take(10)
            ->get(['id', 'judul', 'pesan', 'url', 'created_at']);

        $unread = Notifikasi::where('user_id', $userId)->whereNull('dibaca_pada')->count();
        $newestId = $notifikasi->first()?->id ?? $lastId;

        return response()->json([
            'new_last_id' => $newestId,
            'unread' => (int) $unread,
            'items' => $notifikasi->map(fn ($n) => [
                'id' => $n->id,
                'judul' => $n->judul,
                'pesan' => $n->pesan,
                'url' => $n->url,
                'created_at' => $n->created_at?->diffForHumans(),
            ]),
        ]);
    }
}
