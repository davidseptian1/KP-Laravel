<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;
use App\Models\Minusan;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Blade directive for accounting currency format: @acct($value) - no rounding
        Blade::directive('acct', function ($expression) {
            return "<?php echo ($expression) < 0 ? '('.'Rp '.number_format((int)$expression, 0, ',', '.').')' : 'Rp '.number_format((int)$expression, 0, ',', '.'); ?>";
        });

        View::composer('layouts.sidebar', function ($view) {
            $jumlahReportKhusus = Minusan::whereNotNull('note')
                ->where('note', '!=', '')
                ->count();

            $view->with('jumlahReportKhusus', $jumlahReportKhusus);
        });
    }
}
