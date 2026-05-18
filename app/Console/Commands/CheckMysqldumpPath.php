<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DataCutLog;
use Carbon\Carbon;

class CheckMysqldumpPath extends Command
{
    protected $signature = 'app:check-mysqldump-path';
    protected $description = 'Cek path mysqldump untuk Windows';

    public function handle()
    {
        $paths = [
            'C:\\xampp\\mysql\\bin',
            'C:\\laragon\\bin\\mysql\\mysql-5.7.26-win32-x64\\bin',
            'C:\\laragon\\bin\\mysql\\mysql-5.7.36-winx64\\bin',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin',
            'C:\\Program Files (x86)\\MySQL\\MySQL Server 8.0\\bin',
        ];

        foreach ($paths as $path) {
            $mysqldump = $path . '\\mysqldump.exe';
            if (file_exists($mysqldump)) {
                $this->info("✅ Ditemukan: $mysqldump");
                return self::SUCCESS;
            }
        }

        $this->warn('⚠️ mysqldump.exe tidak ditemukan di path standar');
        $this->line('Silakan set path mysqldump di .env atau tambahkan path ke system PATH');
        
        return self::FAILURE;
    }
}
