<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reimburse;
use App\Services\WhatsAppMetricService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class SendAllRecapPdf extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reports:send-wa {--from=} {--to=}';

    /**
     * The console command description.
     */
    protected $description = 'Kirim semua rekap (reimburse, rekap bulanan, rekap tilangan) via WhatsApp admin';

    public function handle(WhatsAppMetricService $whatsApp): int
    {
        $fromOption = $this->option('from');
        $toOption = $this->option('to');

        if ($fromOption && $toOption) {
            $from = Carbon::parse($fromOption)->startOfDay();
            $to = Carbon::parse($toOption)->endOfDay();
        } else {
            [$from, $to] = $this->defaultPeriod();
        }

        $reimbursePdf = $this->buildReimbursePdf($from, $to);
        $rekapBulananPdf = $this->buildRekapBulananPdf($from, $to);
        $rekapTilanganPdf = $this->buildRekapTilanganPdf($from, $to);

        $links = [
            'Rekap Reimburse' => $this->signedUrl($reimbursePdf),
            'Rekap Bulanan' => $this->signedUrl($rekapBulananPdf),
            'Rekap Tilangan' => $this->signedUrl($rekapTilanganPdf),
        ];

        $message = "📎 *REKAP PDF OTOMATIS*\n" .
            "🗓 *Periode:* " . $from->format('d M Y') . " - " . $to->format('d M Y H:i') . "\n" .
            "👇 *Silakan salin/klik link untuk download:*\n\n" .
            "*1) Rekap Reimburse*\n" . $links['Rekap Reimburse'] . "\n\n" .
            "*2) Rekap Bulanan*\n" . $links['Rekap Bulanan'] . "\n\n" .
            "*3) Rekap Tilangan*\n" . $links['Rekap Tilangan'];

        $whatsApp->sendToAdmins($message);

        $this->info('Rekap PDF terkirim ke WhatsApp admin.');
        return Command::SUCCESS;
    }

    private function buildReimbursePdf(Carbon $from, Carbon $to): string
    {
        $items = Reimburse::whereBetween('tanggal_pengajuan', [$from, $to])
            ->orderBy('tanggal_pengajuan')
            ->get();

        $summary = [
            'total' => $items->count(),
            'pending' => $items->where('status', 'pending')->count(),
            'approved' => $items->where('status', 'approved')->count(),
            'rejected' => $items->where('status', 'rejected')->count(),
            'revision' => $items->where('status', 'revision')->count(),
            'nominal' => $items->sum('nominal'),
        ];

        $pdf = Pdf::loadView('admin.reimburse.rekap-pdf', [
            'items' => $items,
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
        ]);

        $filename = 'rekap-reimburse-' . now()->format('YmdHis') . '.pdf';
        $path = 'recaps/' . $filename;
        Storage::disk('local')->put($path, $pdf->output());

        return $filename;
    }

    private function buildRekapBulananPdf(Carbon $from, Carbon $to): string
    {
        $bulan = (int) $to->format('m');
        $tahun = (int) $to->format('Y');

        $minusan = DB::table('minusans')
            ->whereBetween('tanggal', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->get();

        $pdf = Pdf::loadView('admin.rekap-bulanan.rekap-pdf', [
            'minusan' => $minusan,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);

        $filename = 'rekap-bulanan-' . now()->format('YmdHis') . '.pdf';
        $path = 'recaps/' . $filename;
        Storage::disk('local')->put($path, $pdf->output());

        return $filename;
    }

    private function buildRekapTilanganPdf(Carbon $from, Carbon $to): string
    {
        $start = $from->copy();
        $end = $to->copy();

        $rows = DB::table('minusans')
            ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->select('nama', 'total', 'qty', 'total_per_orang')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $names = preg_split('/\s*,\s*/u', trim($row->nama));
            $share = null;
            if (!is_null($row->total_per_orang) && is_numeric($row->total_per_orang) && floatval($row->total_per_orang) > 0) {
                $share = floatval($row->total_per_orang);
            } else {
                if (!is_null($row->qty) && intval($row->qty) > 0) {
                    $share = floatval($row->total) / intval($row->qty);
                } else {
                    $count = max(1, count($names));
                    $share = floatval($row->total) / $count;
                }
            }

            foreach ($names as $name) {
                $key = mb_strtolower(trim($name));
                if ($key === '') continue;
                if (!isset($map[$key])) {
                    $map[$key] = [
                        'display' => mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8'),
                        'total' => 0.0,
                    ];
                }
                $map[$key]['total'] += $share;
            }
        }

        $minusan = collect($map)
            ->map(function ($item) {
                return (object) [
                    'nama' => $item['display'],
                    'total_tilangan' => round($item['total'], 2),
                ];
            })
            ->sortByDesc('total_tilangan')
            ->values();

        $pdf = Pdf::loadView('admin.rekap-tilangan.rekap-pdf', [
            'minusan' => $minusan,
            'start' => $start,
            'end' => $end,
        ]);

        $filename = 'rekap-tilangan-' . now()->format('YmdHis') . '.pdf';
        $path = 'recaps/' . $filename;
        Storage::disk('local')->put($path, $pdf->output());

        return $filename;
    }

    private function defaultPeriod(): array
    {
        $now = now();

        if ($now->day >= 24) {
            $from = $now->copy()->day(24)->startOfDay();
            $to = $now->copy()->addMonthNoOverflow()->day(23)->endOfDay();
        } else {
            $from = $now->copy()->subMonthNoOverflow()->day(24)->startOfDay();
            $to = $now->copy()->day(23)->endOfDay();
        }

        return [$from, $to];
    }

    private function signedUrl(string $filename): string
    {
        return URL::temporarySignedRoute('recap.download', now()->addDays(3), [
            'file' => $filename,
        ]);
    }
}
