<?php

namespace App\Http\Controllers;

use App\Helpers\UserContextHelper;
use App\Models\School;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Symfony\Component\Process\Process;

/**
 * AI Analyst & Terminal — khusus Super Admin (Admin Pusat).
 *
 * - Analyst: metrik + insight heuristik (jalan tanpa key apa pun).
 *   Jika admin mengisi Gemini API key → tombol "Analisis dengan AI".
 * - Terminal: HANYA perintah allowlist diagnostik (tanpa raw shell),
 *   tiap eksekusi diaudit ke user_histories.
 */
class AdminInsightController extends Controller
{
    /** Daftar perintah yang boleh jalan. Tanpa shell mentah. */
    public const ALLOWLIST = [
        'git-log' => ['label' => 'Git Log (10 commit)', 'cmd' => ['git', 'log', '--oneline', '-10']],
        'git-status' => ['label' => 'Git Status', 'cmd' => ['git', 'status', '--short', '--branch']],
        'git-remote' => ['label' => 'Git Remote', 'cmd' => ['git', 'remote', '-v']],
        'php-version' => ['label' => 'Versi PHP', 'cmd' => ['php', '-v']],
        'routes-admin' => ['label' => 'Daftar Route Admin', 'cmd' => ['php', 'artisan', 'route:list', '--name=admin']],
        'migrate-status' => ['label' => 'Status Migrasi', 'cmd' => ['php', 'artisan', 'migrate:status']],
        'cache-clear' => ['label' => 'Bersihkan Cache', 'cmd' => ['php', 'artisan', 'optimize:clear']],
    ];

    private function ensureSuper(): \App\Models\User
    {
        $me = UserContextHelper::user();
        abort_unless($me && $me->isSuperAdmin(), 403);

        return $me;
    }

    public function index(): View
    {
        $this->ensureSuper();
        $metrics = $this->metrics();
        $insights = $this->heuristicInsights($metrics);

        return view('admin.insights', [
            'metrics' => $metrics,
            'insights' => $insights,
            'health' => $this->serverHealth(),
            'commands' => collect(self::ALLOWLIST)->map(fn ($c) => $c['label']),
            'hasGeminiKey' => (bool) Setting::getValue('gemini_api_key'),
            'hasGithubToken' => (bool) Setting::getValue('github_token'),
            'githubRepo' => Setting::getValue('github_repo', 'atifnaufal/app-portal-sekolah'),
            'terminalResult' => session('terminal_result'),
            'aiResult' => session('ai_result'),
            'githubResult' => session('github_result'),
        ]);
    }

    /** Simpan key integrasi (Gemini / GitHub) ke settings. */
    public function saveKeys(Request $request): RedirectResponse
    {
        $this->ensureSuper();
        $data = $request->validate([
            'gemini_api_key' => ['nullable', 'string', 'max:200'],
            'github_token' => ['nullable', 'string', 'max:200'],
            'github_repo' => ['nullable', 'string', 'max:120'],
        ]);

        foreach (['gemini_api_key', 'github_token', 'github_repo'] as $k) {
            if (array_key_exists($k, $data)) {
                Setting::setValue($k, trim((string) $data[$k]));
            }
        }

        return back()->with('success', 'Kunci integrasi disimpan.');
    }

    /** Jalankan satu perintah allowlist. */
    public function terminal(Request $request): RedirectResponse
    {
        $me = $this->ensureSuper();
        $key = $request->input('command');
        abort_unless(isset(self::ALLOWLIST[$key]), 400, 'Perintah tidak diizinkan.');

        $label = self::ALLOWLIST[$key]['label'];
        try {
            $process = new Process(self::ALLOWLIST[$key]['cmd'], base_path());
            $process->setTimeout(25);
            $process->run();
            $output = trim($process->getOutput() ?: $process->getErrorOutput()) ?: '(tidak ada output)';
            $ok = $process->isSuccessful();
        } catch (\Throwable $e) {
            $output = 'Gagal menjalankan: '.$e->getMessage();
            $ok = false;
        }
        $output = mb_substr($output, 0, 4000);

        UserHistory::create([
            'user_id' => $me->id,
            'activity_type' => 'terminal',
            'description' => "Terminal: {$label} [".($ok ? 'OK' : 'GAGAL').']',
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);

        return back()->with('terminal_result', ['label' => $label, 'ok' => $ok, 'output' => $output]);
    }

    /** Status GitHub via API (butuh token) atau git lokal sebagai fallback. */
    public function github(Request $request): RedirectResponse
    {
        $me = $this->ensureSuper();
        $token = (string) Setting::getValue('github_token', '');
        $repo = (string) (Setting::getValue('github_repo', 'atifnaufal/app-portal-sekolah') ?: 'atifnaufal/app-portal-sekolah');

        if ($token !== '') {
            try {
                $res = Http::withToken($token)->timeout(15)->acceptJson()
                    ->get("https://api.github.com/repos/{$repo}/commits", ['per_page' => 5]);
                if ($res->successful()) {
                    $commits = collect($res->json())->map(fn ($c) => [
                        'sha' => substr((string) ($c['sha'] ?? ''), 0, 7),
                        'msg' => $c['commit']['message'] ?? '-',
                        'by' => $c['commit']['author']['name'] ?? '?',
                        'at' => $c['commit']['author']['date'] ?? '',
                    ])->values()->all();
                    $result = ['mode' => 'GitHub API', 'repo' => $repo, 'commits' => $commits, 'error' => null];
                } else {
                    $result = ['mode' => 'GitHub API', 'repo' => $repo, 'commits' => [], 'error' => 'HTTP '.$res->status().' — cek token/repo.'];
                }
            } catch (\Throwable $e) {
                $result = ['mode' => 'GitHub API', 'repo' => $repo, 'commits' => [], 'error' => $e->getMessage()];
            }
        } else {
            // Fallback: info git lokal container.
            $result = ['mode' => 'Git Lokal (tanpa token)', 'repo' => $repo, 'commits' => [], 'error' => null, 'local' => $this->runLocal(['git', 'log', '--oneline', '-5'])];
        }

        UserHistory::create([
            'user_id' => $me->id,
            'activity_type' => 'terminal',
            'description' => 'GitHub sync check ('.$result['mode'].')',
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);

        return back()->with('github_result', $result);
    }

    /** Analisis AI via Gemini (butuh key). Heuristik selalu tersedia di halaman. */
    public function analyze(Request $request): RedirectResponse
    {
        $me = $this->ensureSuper();
        $key = (string) Setting::getValue('gemini_api_key', '');
        abort_if($key === '', 400, 'Isi Gemini API key dulu di panel Integrasi.');

        $metrics = $this->metrics();
        $prompt = 'Kamu analis IT untuk platform portal sekolah multi-sekolah (Laravel + MySQL, hosting Railway). '
            .'Data metrik JSON: '.json_encode($metrics).'. '
            .'Berikan dalam Bahasa Indonesia: 1) ringkasan kesehatan sistem (1 kalimat), '
            .'2) 3 anomali/risiko prioritas, 3) 3 rekomendasi aksi konkret untuk admin pusat. '
            .'Singkat, bullet, tanpa basa-basi.';

        try {
            $res = Http::timeout(30)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key='.urlencode($key),
                ['contents' => [['parts' => [['text' => $prompt]]]]]
            );
            if ($res->successful()) {
                $text = $res->json('candidates.0.content.parts.0.text', 'AI tidak mengembalikan jawaban.');
            } else {
                $text = 'Gemini API error HTTP '.$res->status().': '.mb_substr($res->body(), 0, 300);
            }
        } catch (\Throwable $e) {
            $text = 'Gagal menghubungi Gemini: '.$e->getMessage();
        }

        UserHistory::create([
            'user_id' => $me->id,
            'activity_type' => 'ai_analyze',
            'description' => 'AI Analyst dijalankan.',
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);

        return back()->with('ai_result', $text);
    }

    /** Pemeriksaan kesehatan server (untuk tab Server). */
    private function serverHealth(): array
    {
        $dbOk = true;
        $dbInfo = DB::getDriverName();
        try {
            DB::select('select 1');
            $dbSize = null;
            if ($dbInfo === 'mysql') {
                $row = DB::selectOne('SELECT SUM(data_length + index_length) AS s FROM information_schema.tables WHERE table_schema = DATABASE()');
                $dbSize = $row && $row->s ? round($row->s / 1048576, 1).' MB' : null;
            } else {
                $f = DB::getConfig('database');
                $dbSize = (is_string($f) && is_file($f)) ? round(filesize($f) / 1048576, 1).' MB' : null;
            }
        } catch (\Throwable $e) {
            $dbOk = false;
            $dbSize = 'error: '.mb_substr($e->getMessage(), 0, 120);
        }
        $storagePath = storage_path('app/public');
        $publicStorage = is_link(public_path('storage')) || is_dir(public_path('storage'));

        $rows = [
            ['PHP', phpversion(), true],
            ['Laravel', app()->version(), true],
            ['Database ('.$dbInfo.')', $dbOk ? ('Terhubung'.($dbSize ? " • {$dbSize}" : '')) : $dbSize, $dbOk],
            ['APP_DEBUG', config('app.debug') ? 'ON (matikan di produksi!)' : 'OFF (aman)', ! config('app.debug')],
            ['Storage link', $publicStorage ? 'Terpasang' : 'HILANG — jalankan storage:link', $publicStorage],
            ['Ruang disk storage', ($m = $this->metrics())['disk_free_mb'] !== null ? $m['disk_free_mb'].' MB bebas' : 'Tidak terbaca', true],
            ['Queue', (string) config('queue.default'), true],
            ['Cache', (string) config('cache.default'), true],
            ['Session', (string) config('session.driver'), true],
            ['Firebase Storage', config('firebase.enabled') ? 'Aktif' : 'Nonaktif (fallback lokal)', true],
            ['AI Moderasi Gambar', Setting::getValue('gemini_api_key') ? 'Aktif (Gemini)' : 'Nonaktif (filter teks + laporan saja)', true],
        ];

        return $rows;
    }

    // ===== Internal =====

    private function runLocal(array $cmd): string
    {
        try {
            $p = new Process($cmd, base_path());
            $p->setTimeout(15);
            $p->run();

            return mb_substr(trim($p->getOutput() ?: $p->getErrorOutput()) ?: '(kosong)', 0, 2000);
        } catch (\Throwable $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    private function metrics(): array
    {
        $pending = User::where('aktif', false)->whereIn('role', ['guru', 'siswa'])->count();
        $loginsToday = UserHistory::ofType('login')->whereDate('created_at', today())->count();
        $errorsToday = 0;
        $logFile = storage_path('logs/laravel.log');
        $logTail = [];
        if (is_file($logFile)) {
            $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $logTail = array_slice($lines, -5);
            $today = today()->format('Y-m-d');
            foreach (array_slice($lines, -500) as $line) {
                if (str_contains($line, $today) && preg_match('/\b(ERROR|CRITICAL|EMERGENCY)\b/i', $line)) {
                    $errorsToday++;
                }
            }
        }
        $diskFree = function_exists('disk_free_space') ? @disk_free_space(storage_path()) : null;

        return [
            'sekolah' => School::count(),
            'sekolah_nonaktif' => School::where('is_active', false)->count(),
            'guru' => User::where('role', 'guru')->count(),
            'siswa' => User::where('role', 'siswa')->count(),
            'pending_approval' => $pending,
            'login_hari_ini' => $loginsToday,
            'error_log_hari_ini' => $errorsToday,
            'disk_free_mb' => $diskFree ? round($diskFree / 1048576) : null,
            'db_driver' => DB::getDriverName(),
            'waktu' => now()->toDateTimeString(),
            'log_tail' => $logTail,
        ];
    }

    /** Insight heuristik: selalu jalan, tanpa key. */
    private function heuristicInsights(array $m): array
    {
        $out = [];
        $push = function (string $tone, string $title, string $desc) use (&$out) {
            $out[] = compact('tone', 'title', 'desc');
        };

        if ($m['pending_approval'] > 0) {
            $push('warning', "{$m['pending_approval']} akun menunggu persetujuan",
                'Buka Akun Pengguna → pilih sekolah → setujui akun agar pendaftar bisa login.');
        } else {
            $push('success', 'Antrean persetujuan kosong', 'Semua pendaftar sudah diproses. Pertahankan.');
        }
        if ($m['sekolah_nonaktif'] > 0) {
            $push('danger', "{$m['sekolah_nonaktif']} sekolah nonaktif",
                'Sekolah nonaktif menutup pendaftaran & aksesnya. Cek Sekolah Terdaftar bila ini tidak disengaja.');
        }
        if ($m['sekolah'] === 0) {
            $push('danger', 'Belum ada data sekolah', 'Tambahkan sekolah dulu — fitur, akun, dan pendaftaran bergantung pada ID sekolah.');
        }
        if ($m['error_log_hari_ini'] > 0) {
            $push('danger', "{$m['error_log_hari_ini']} error hari ini di log",
                'Jalankan Terminal → lihat 5 baris log terakhir di bawah, atau cek Railway → Deployments → Logs.');
        } else {
            $push('success', 'Log bersih hari ini', 'Tidak ada error tercatat sejak tengah malam.');
        }
        if ($m['login_hari_ini'] === 0) {
            $push('info', 'Belum ada login hari ini', 'Wajar di luar jam sekolah. Jika jam aktif, cek sesi & kredensial user.');
        } else {
            $push('info', "{$m['login_hari_ini']} login hari ini", 'Aktivitas autentikasi berjalan normal.');
        }
        if ($m['disk_free_mb'] !== null && $m['disk_free_mb'] < 500) {
            $push('warning', "Ruang disk sisa {$m['disk_free_mb']} MB", 'Bersihkan log lama & file upload tak terpakai; Railway volume bisa penuh.');
        }

        return $out;
    }
}
