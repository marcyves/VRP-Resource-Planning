<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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
        \Illuminate\Support\Facades\View::composer(
            ['layouts.navigation', 'layouts.topbar'],
            \App\Http\View\Composers\NavigationComposer::class
        );

        \Illuminate\Support\Facades\View::composer(
            'layouts.breadcrumbs',
            \App\Http\View\Composers\BreadcrumbComposer::class
        );

        Carbon::setLocale(app()->getLocale());

        Blade::directive('money', function ($value) {
            return "<?php echo number_format($value, 2); ?>€";
        });

        Blade::directive('moneyBoth', function ($value) {
            return "<?php echo number_format($value, 2); ?>€ / <?php echo number_format($value*1.2, 2); ?>€";
        });

        Blade::directive('moneyVAT', function ($value) {
            return "<?php echo number_format($value*1.2, 2); ?>€";
        });


        Blade::directive('formatDate', function ($value) {
            return "<?php echo ($value) ? \\Carbon\Carbon::parse($value)->format('d/m/Y') : ''; ?>";
        });


        Blade::directive('monthName', function ($value) {
            return "<?php echo ucfirst(\\Carbon\\Carbon::parse(mktime(0, 0, 0, (int) {$value}, 1, date('Y')))->locale(\\App\\Support\\TerminologyLocale::normalizeBaseLocale(app()->getLocale()))->translatedFormat('F')); ?>";
        });
    }
}
