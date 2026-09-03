<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Moderasi konten portal berlapis:
 *  1. Filter teks (caption) — SELALU jalan, tanpa key.
 *  2. AI vision (Gemini) — jalan bila admin mengisi gemini_api_key.
 *  3. Laporan komunitas + auto-hide — di controller (threshold 3 laporan).
 *
 * Return: ['safe' => bool, 'reason' => ?string, 'ai' => 'on'|'skipped'|'error']
 */
class ImageSafetyService
{
    /** Kata yang otomatis memblokir caption (kasar, pornografi, kekerasan, SARA). */
    public const BLOCKED_WORDS = [
        'porno', 'porn', 'bokep', 'xxx', 'telanjang', 'bugil', 'vulgar', 'mesum',
        'sange', 'ngentot', 'kontol', 'memek', 'jembut', 'pepek', 'lonte', 'pelacur',
        'bajingan', 'bangsat', 'anjing', 'babi', 'tolol', 'goblok', 'idiot',
        'bunuh', 'membunuh', 'bunuh diri', 'golok', 'bacok', 'tusuk', 'perkosa',
        'narkoba', 'sabu', 'ganja', 'judi', 'togel', 'slot gacor',
        'benci', 'kafir', 'murtad', 'teroris', 'bom bunuh',
    ];

    public const REPORT_THRESHOLD = 3;

    public static function checkText(?string $text): array
    {
        if (! $text) {
            return ['safe' => true, 'reason' => null];
        }
        $lower = mb_strtolower($text);
        foreach (self::BLOCKED_WORDS as $word) {
            if (str_contains($lower, $word)) {
                return ['safe' => false, 'reason' => 'Teks mengandung kata yang dilarang oleh sistem moderasi.'];
            }
        }

        return ['safe' => true, 'reason' => null];
    }

    public static function checkImage(?UploadedFile $file): array
    {
        if (! $file) {
            return ['safe' => true, 'reason' => null, 'ai' => 'skipped'];
        }

        // Lapisan dasar: pastikan benar file gambar valid.
        $info = @getimagesize($file->getRealPath());
        if ($info === false) {
            return ['safe' => false, 'reason' => 'File bukan gambar yang valid.', 'ai' => 'skipped'];
        }

        $key = (string) Setting::getValue('gemini_api_key', '');
        if ($key === '') {
            return ['safe' => true, 'reason' => null, 'ai' => 'skipped'];
        }

        // Lapisan AI: Gemini vision menilai keamanan gambar.
        try {
            $mime = $file->getMimeType() ?: 'image/jpeg';
            $b64 = base64_encode(file_get_contents($file->getRealPath()));
            $res = Http::timeout(30)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key='.urlencode($key),
                ['contents' => [[
                    'parts' => [
                        ['text' => 'You are a content safety classifier. Analyze this image. '
                            .'Respond ONLY with JSON: {"safe": true|false, "reason": "short Indonesian reason"}. '
                            .'Mark safe=false if the image contains: pornography/nudity/sexual content, blood/gore/violence/weapons used violently, self-harm, hate symbols, or illegal drug use. '
                            .'Normal school photos, food, landscapes, cartoons are safe.'],
                        ['inline_data' => ['mime_type' => $mime, 'data' => $b64]],
                    ],
                ]]]
            );
            $raw = (string) $res->json('candidates.0.content.parts.0.text', '');
            if (preg_match('/\{[^}]*\}/s', $raw, $m)) {
                $verdict = json_decode($m[0], true);
                if (is_array($verdict) && array_key_exists('safe', $verdict)) {
                    $safe = (bool) $verdict['safe'];

                    return [
                        'safe' => $safe,
                        'reason' => $safe ? null : ('Gambar diblokir AI: '.($verdict['reason'] ?? 'konten tidak pantas terdeteksi.')),
                        'ai' => 'on',
                    ];
                }
            }
            Log::warning('ImageSafety AI respons tak terparse: '.mb_substr($raw, 0, 200));

            return ['safe' => true, 'reason' => null, 'ai' => 'error'];
        } catch (\Throwable $e) {
            Log::warning('ImageSafety AI gagal: '.$e->getMessage());
            // Fail-open agar upload legit tak macet saat API down; teks + laporan tetap jalan.
            return ['safe' => true, 'reason' => null, 'ai' => 'error'];
        }
    }
}
