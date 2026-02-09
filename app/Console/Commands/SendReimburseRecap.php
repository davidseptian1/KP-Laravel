<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reimburse;
use App\Services\WhatsAppMetricService;
use Carbon\Carbon;

class SendReimburseRecap extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reimburse:recap {--from=} {--to=}';

    /**
     * The console command description.
     */
    protected $description = 'Kirim rekap reimburse ke WhatsApp admin';

    public function handle(WhatsAppMetricService $whatsApp): int
    {
        $fromOption = $this->option('from');
        $toOption = $this->option('to');

        $from = $fromOption ? Carbon::parse($fromOption)->startOfDay() : now()->startOfMonth();
        $to = $toOption ? Carbon::parse($toOption)->endOfDay() : now();

        $query = Reimburse::query()->whereBetween('tanggal_pengajuan', [$from, $to]);

        $total = (clone $query)->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $approved = (clone $query)->where('status', 'approved')->count();
        $rejected = (clone $query)->where('status', 'rejected')->count();
        $revision = (clone $query)->where('status', 'revision')->count();
        $nominal = (clone $query)->sum('nominal');

        $message = "📊 REKAP REIMBURSE\n\n" .
            "Periode  : " . $from->format('d/m/Y') . " - " . $to->format('d/m/Y') . "\n" .
            "Total    : {$total}\n" .
            "Pending  : {$pending}\n" .
            "Approved : {$approved}\n" .
            "Rejected : {$rejected}\n" .
            "Revision : {$revision}\n" .
            "Nominal  : Rp " . number_format($nominal, 0, ',', '.');

        $whatsApp->sendToAdmins($message);

        $this->info('Rekap reimburse terkirim.');
        return Command::SUCCESS;
    }
}
