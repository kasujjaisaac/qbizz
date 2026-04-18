<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('ugx', function (string $expression): string {
            return "<?php echo 'UGX ' . number_format((float) ($expression), 0); ?>";
        });

        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();

            if (! $user) {
                return;
            }

            $outstandingBalance = (float) $user->invoices()
                ->open()
                ->select(DB::raw('COALESCE(SUM(total_amount - paid_amount), 0) as aggregate'))
                ->value('aggregate');

            $overdueQuery = $user->invoices()
                ->open()
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->toDateString());

            $overdueCount = $overdueQuery->count();
            $overdueBalance = (float) $user->invoices()
                ->open()
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->toDateString())
                ->select(DB::raw('COALESCE(SUM(total_amount - paid_amount), 0) as aggregate'))
                ->value('aggregate');

            $notifications = [];

            if ($overdueCount > 0) {
                $notifications[] = [
                    'tone' => 'rose',
                    'label' => 'Overdue invoices',
                    'value' => $overdueCount.' pending',
                ];
            }

            if ($outstandingBalance > 0) {
                $notifications[] = [
                    'tone' => 'amber',
                    'label' => 'Unpaid balances',
                    'value' => 'UGX '.number_format($outstandingBalance, 0),
                ];
            }

            if ($notifications === []) {
                $notifications[] = [
                    'tone' => 'emerald',
                    'label' => 'Collections',
                    'value' => 'All clear',
                ];
            }

            if ($overdueBalance > 0 && $overdueCount > 0) {
                $notifications[] = [
                    'tone' => 'violet',
                    'label' => 'Overdue value',
                    'value' => 'UGX '.number_format($overdueBalance, 0),
                ];
            }

            $view->with([
                'topbarNotifications' => array_slice($notifications, 0, 3),
            ]);
        });
    }
}
