<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$views = [
    'resources/views/mobile/tugas-detail.blade.php',
    'resources/views/mobile/tugas.blade.php',
    'resources/views/tugas/form.blade.php',
    'resources/views/mobile/profile.blade.php',
    'resources/views/mobile/profile-edit.blade.php',
    'resources/views/mobile/absensi.blade.php',
];

foreach ($views as $view) {
    $compiled = app('blade.compiler')->compileString(file_get_contents(__DIR__.'/../'.$view));
    $out = __DIR__.'/compiled_'.md5($view).'.php';
    file_put_contents($out, $compiled);
    echo $view."\n";
}
