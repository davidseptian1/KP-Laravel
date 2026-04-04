<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use App\Models\DataRequest;
use App\Models\Deposit;
use App\Models\LoanRequest;
use App\Models\Minusan;
use App\Models\PersediaanStok;
use App\Models\Reimburse;
use App\Models\TagLainnya;
use App\Models\TagNomorPascaBayar;
use App\Models\TagPlnInternet;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $monthlyLabels = collect(range(5, 0))
            ->map(fn ($offset) => Carbon::now()->subMonths($offset)->format('Y-m'))
            ->push(Carbon::now()->format('Y-m'))
            ->values();

        $monthlyLabelText = $monthlyLabels
            ->map(fn ($month) => Carbon::createFromFormat('Y-m', $month)->translatedFormat('M Y'))
            ->values();

        $depositMonthly = $this->mapMonthlyCounts(
            Deposit::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym'),
            $monthlyLabels
        );

        $reimburseMonthly = $this->mapMonthlyCounts(
            Reimburse::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym'),
            $monthlyLabels
        );

        $dataRequestMonthly = $this->mapMonthlyCounts(
            DataRequest::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym'),
            $monthlyLabels
        );

        $loanMonthly = $this->mapMonthlyCounts(
            LoanRequest::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym'),
            $monthlyLabels
        );

        $persediaanMonthly = $this->mapMonthlyCounts(
            PersediaanStok::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym'),
            $monthlyLabels
        );

        $minusanMonthly = $this->mapMonthlyCounts(
            Minusan::selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as ym, COUNT(*) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym'),
            $monthlyLabels
        );

        $statusOverview = [
            'Deposit' => [
                'pending' => Deposit::where('status', 'pending')->count(),
                'approved' => Deposit::where('status', 'approved')->count(),
                'rejected' => Deposit::where('status', 'rejected')->count(),
                'selesai' => Deposit::whereIn('status', ['selesai', 'selesai_belum_lunas'])->count(),
                'lunas' => Deposit::where('status', 'lunas')->count(),
            ],
            'Reimburse' => [
                'pending' => Reimburse::where('status', 'pending')->count(),
                'approved' => Reimburse::where('status', 'approved')->count(),
                'rejected' => Reimburse::where('status', 'rejected')->count(),
                'selesai' => Reimburse::whereIn('status', ['done', 'selesai'])->count(),
                'lunas' => 0,
            ],
            'Pengajuan Data' => [
                'pending' => DataRequest::where('status', 'pending')->count(),
                'approved' => DataRequest::where('status', 'approved')->count(),
                'rejected' => DataRequest::where('status', 'rejected')->count(),
                'selesai' => DataRequest::whereIn('status', ['done', 'selesai'])->count(),
                'lunas' => 0,
            ],
            'Peminjaman' => [
                'pending' => LoanRequest::where('status', 'pending')->count(),
                'approved' => LoanRequest::where('status', 'approved')->count(),
                'rejected' => LoanRequest::where('status', 'rejected')->count(),
                'selesai' => LoanRequest::whereIn('status', ['done', 'selesai'])->count(),
                'lunas' => 0,
            ],
            'Persediaan' => [
                'pending' => PersediaanStok::where('status', 'pending')->count(),
                'approved' => PersediaanStok::where('status', 'approved')->count(),
                'rejected' => PersediaanStok::where('status', 'rejected')->count(),
                'selesai' => PersediaanStok::whereIn('status', ['done', 'selesai'])->count(),
                'lunas' => 0,
            ],
        ];

        $nominalByFeature = [
            'Minusan' => (float) (Minusan::selectRaw('SUM(qty * total_per_orang) as total')->value('total') ?? 0),
            'Deposit' => (float) (Deposit::sum('nominal') ?? 0),
            'Reimburse' => (float) (Reimburse::sum('nominal') ?? 0),
            'Persediaan' => (float) (PersediaanStok::sum('total_amount') ?? 0),
            'Laba Transaksi' => (float) (Transaksi::sum('laba') ?? 0),
        ];

        $totalDataMatrix = TagNomorPascaBayar::count() + TagPlnInternet::count() + TagLainnya::count();

        $data = [
            'title' => 'Dashboard',
            'menuDashboard' => 'active',
            'totalUser' => User::count(),
            'totalMinusan' => (float) (Minusan::selectRaw('SUM(qty * total_per_orang) as total')->value('total') ?? 0),
            'totalTransaksiMinusan' => Minusan::count(),
            'totalDeposit' => Deposit::count(),
            'totalHutang' => Deposit::where('jenis_transaksi', 'hutang')->count(),
            'nominalHutangBelumLunas' => (float) (Deposit::where('jenis_transaksi', 'hutang')
                ->whereNotIn('status', ['lunas'])
                ->sum('nominal') ?? 0),
            'totalReimburse' => Reimburse::count(),
            'totalDataRequest' => DataRequest::count(),
            'totalLoanRequest' => LoanRequest::count(),
            'totalPersediaan' => PersediaanStok::count(),
            'totalDataMatrix' => $totalDataMatrix,
            'totalTransaksiApi' => Transaksi::count(),
            'adminActionsToday' => AdminActivityLog::whereDate('created_at', Carbon::today())
                ->whereIn('action_type', ['created', 'updated', 'deleted'])
                ->count(),
            'chartMonthlyLabels' => $monthlyLabelText,
            'chartMonthlySeries' => [
                'Minusan' => $minusanMonthly,
                'Deposit/Hutang' => $depositMonthly,
                'Reimburse' => $reimburseMonthly,
                'Pengajuan Data' => $dataRequestMonthly,
                'Peminjaman Barang' => $loanMonthly,
                'Persediaan Stok' => $persediaanMonthly,
            ],
            'chartStatusOverview' => $statusOverview,
            'chartNominalByFeature' => $nominalByFeature,
        ];

        return view('dashboard', $data);
    }

    private function mapMonthlyCounts($rows, $labels): array
    {
        $indexed = collect($rows);

        return $labels
            ->map(fn ($month) => (int) ($indexed[$month] ?? 0))
            ->values()
            ->all();
    }
}
