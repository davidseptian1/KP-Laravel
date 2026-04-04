<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;
use App\Models\DataRequest;
use App\Models\Deposit;
use App\Models\LoanRequest;
use App\Models\Minusan;
use App\Models\Reimburse;
use App\Observers\AdminModelAuditObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        foreach (File::files(app_path('Models')) as $file) {
            $class = 'App\\Models\\' . $file->getFilenameWithoutExtension();
            if (class_exists($class) && is_subclass_of($class, Model::class)) {
                $class::observe(AdminModelAuditObserver::class);
            }
        }

        // Blade directive for accounting currency format: @acct($value) - no rounding
        Blade::directive('acct', function ($expression) {
            return "<?php echo ($expression) < 0 ? '('.'Rp '.number_format((int)$expression, 0, ',', '.').')' : 'Rp '.number_format((int)$expression, 0, ',', '.'); ?>";
        });

        View::composer('layouts.sidebar', function ($view) {
            $jumlahReportKhusus = Minusan::whereNotNull('note')
                ->where('note', '!=', '')
                ->count();

            $jumlahHutangBelumLunas = Deposit::whereRaw("LOWER(TRIM(jenis_transaksi)) IN ('hutang','bon')")
                ->whereRaw("LOWER(REPLACE(REPLACE(REPLACE(REPLACE(status, ' ', ''), '_', ''), '(', ''), ')', '')) = ?", ['selesaibelumlunas'])
                ->count();

            $jumlahReimbursePending = Reimburse::where('status', 'pending')->count();
            $jumlahDataRequestPending = DataRequest::where('status', 'pending')->count();
            $jumlahLoanRequestPending = LoanRequest::where('status', 'pending')->count();
            $jumlahDepositPending = Deposit::where('status', 'pending')->count();

            $view->with('jumlahReportKhusus', $jumlahReportKhusus)
                ->with('jumlahHutangBelumLunas', $jumlahHutangBelumLunas)
                ->with('jumlahReimbursePending', $jumlahReimbursePending)
                ->with('jumlahDataRequestPending', $jumlahDataRequestPending)
                ->with('jumlahLoanRequestPending', $jumlahLoanRequestPending)
                ->with('jumlahDepositPending', $jumlahDepositPending);
        });
    }
}
