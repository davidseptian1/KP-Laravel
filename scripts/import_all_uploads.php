<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

$dir = __DIR__ . '/../storage/app/imports';
$files = array_values(array_filter(scandir($dir), fn($f)=>!in_array($f,['.','..'])));
if (empty($files)) {
    echo "No files found in storage/app/imports\n";
    exit(0);
}

$totalImported = 0;
foreach ($files as $f) {
    $path = $dir . '/' . $f;
    echo "\n--- Importing: $f ---\n";
    try {
        $before = DB::table('transaksis')->count();
        Excel::import(new App\Imports\TransaksiImport, $path);
        $after = DB::table('transaksis')->count();
        $diff = $after - $before;
        $totalImported += max(0, $diff);
        echo "Imported from $f : +$diff rows (total now $after)\n";
    } catch (Throwable $e) {
        echo "Error importing $f: " . $e->getMessage() . "\n";
    }
}

echo "\nAll done. Total new rows imported: $totalImported\n";
