<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

/**
 * Push notification native via Firebase Cloud Messaging (kreait/firebase-php).
 *
 * Prinsip: best-effort & gagal aman. Bila Firebase mati / kredensial kosong /
 * tanpa token terdaftar → return mode 'off', TIDAK melempar exception,
 * sehingga alur utama (notifikasi DB + polling) tidak pernah terganggu.
 *
 * Konfigurasi Railway:
 *   FIREBASE_ENABLED=true
 *   FIREBASE_CREDENTIALS=<isi JSON service account satu baris>
 */
class FcmService
{
    public static function isConfigured(): bool
    {
        if (! config('firebase.enabled')) {
            return false;
        }

        return self::serviceAccount() !== null;
    }

    /** Resolve kredensial: file path ATAU string JSON (untuk env Railway). */
    public static function serviceAccount(): array|string|null
    {
        $cred = config('firebase.credentials');
        if (! $cred) {
            return null;
        }
        if (is_file($cred)) {
            return $cred;
        }
        $decoded = json_decode((string) $cred, true);
        if (is_array($decoded) && isset($decoded['private_key'])) {
            return $decoded;
        }

        return null;
    }

    /**
     * Kirim push ke semua perangkat user.
     * @return array{mode: string, sent: int, error: ?string}
     */
    public static function sendToUser(int $userId, string $title, string $body, array $data = []): array
    {
        $tokens = DeviceToken::where('user_id', $userId)->pluck('token')->all();
        if ($tokens === []) {
            return ['mode' => 'off', 'sent' => 0, 'error' => 'tanpa token perangkat'];
        }
        if (! self::isConfigured()) {
            return ['mode' => 'off', 'sent' => 0, 'error' => 'FCM belum dikonfigurasi'];
        }

        try {
            $factory = (new \Kreait\Firebase\Factory)->withServiceAccount(self::serviceAccount());
            $messaging = $factory->createMessaging();
            $message = \Kreait\Firebase\Messaging\CloudMessage::new()
                ->withNotification(\Kreait\Firebase\Messaging\Notification::create($title, mb_substr($body, 0, 180)))
                ->withData(array_map('strval', $data));

            $report = $messaging->sendMulticast($message, $tokens);
            $sent = $report->successes()->count();

            // Bersihkan token mati agar pengiriman berikutnya ringan.
            try {
                foreach ($report->failures()->getItems() as $failure) {
                    $token = $failure->target()->value();
                    if (str_contains(strtolower($failure->error()->getMessage()), 'not-registered')
                        || str_contains(strtolower($failure->error()->getMessage()), 'invalid-registration')) {
                        DeviceToken::where('token', $token)->delete();
                    }
                }
            } catch (\Throwable $e) {
            }

            // Catat key API untuk tab Integrasi (status saja).
            Setting::setValue('fcm_last_status', $sent > 0 ? "terkirim {$sent}" : 'gagal semua');

            return ['mode' => 'fcm', 'sent' => $sent, 'error' => $sent > 0 ? null : 'semua gagal'];
        } catch (\Throwable $e) {
            Log::warning('FCM gagal: '.$e->getMessage());

            return ['mode' => 'error', 'sent' => 0, 'error' => mb_substr($e->getMessage(), 0, 200)];
        }
    }

    public static function statusLine(): string
    {
        if (! config('firebase.enabled')) {
            return 'Nonaktif (FIREBASE_ENABLED=false)';
        }
        if (! self::serviceAccount()) {
            return 'Kredensial hilang (isi FIREBASE_CREDENTIALS)';
        }
        $n = DeviceToken::count();

        return "Aktif • project data01-c6d26 • {$n} perangkat terdaftar";
    }
}
