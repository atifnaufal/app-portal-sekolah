<?php

/*
|--------------------------------------------------------------------------
| Dompdf Konfigurasi (PDF recap nilai & absensi)
|--------------------------------------------------------------------------
|
| Override default paket barryvdh/laravel-dompdf agar selalu memakai direktori
| font & cache di dalam storage (yang dijamin ada & writable di produksi).
| Tanpa ini, di Railway/ephemeral filesystem font_dir "storage/fonts" bisa
| tidak ada/tak writable sehingga render PDF gagal (404/500).
|
*/

$fonts = storage_path('fonts');
$cache = storage_path('framework/cache/data');

// Pastikan direktori yang dibutuhkan dompdf ada & writable sebelum instance dibuat.
foreach ([$fonts, $cache] as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

return [
    'show_warnings' => false,

    'public_path' => null,

    'convert_entities' => true,

    'options' => [
        'font_dir' => $fonts,
        'font_cache' => $fonts,
        'temp_dir' => sys_get_temp_dir(),
        'chroot' => realpath(base_path()),

        'allowed_protocols' => [
            'data://' => ['rules' => []],
            'file://' => ['rules' => []],
            'http://' => ['rules' => []],
            'https://' => ['rules' => []],
        ],

        'artifactPathValidation' => null,
        'log_output_file' => null,
        'enable_font_subsetting' => true,
        'pdf_backend' => 'CPDF',
        'default_media_type' => 'print',
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',
        'default_font' => 'DejaVu Sans',
        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => true,
        'enable_remote' => false,
        'allowed_remote_hosts' => null,
        'font_height_ratio' => 1.1,
        'enable_html5_parser' => true,
    ],
];
