<?php

namespace App\Console\Commands;

use App\Models\Pendataan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpirePendingPendataanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pendataan:expire-pending {--hours=24 : Umur data dalam jam sebelum diubah menjadi gagal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ubah status pendataan yang masih pending dan melewati 1x24 jam menjadi gagal';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        if ($hours <= 0) {
            $hours = 24;
        }

        $cutoff = Carbon::now()->subHours($hours);

        $affected = Pendataan::where(function ($q) {
                $q->where('status', Pendataan::STATUS_PENDING)
                  ->orWhereNull('status');
            })
            ->where('created_at', '<=', $cutoff)
            ->update([
                'status' => Pendataan::STATUS_GAGAL,
            ]);

        $this->info("Berhasil memperbarui {$affected} data pendataan kadaluarsa (> {$hours} jam) menjadi status 'gagal'.");
        Log::info("Command pendataan:expire-pending executed. {$affected} records updated to status 'gagal'.");

        return Command::SUCCESS;
    }
}
