<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::select('SHOW COLUMNS FROM transaksis');
if (empty($cols)) {
    echo "Table transaksis not found or no columns returned\n";
    exit(0);
}
foreach ($cols as $c) {
    echo $c->Field . "\t" . $c->Type . PHP_EOL;
}
