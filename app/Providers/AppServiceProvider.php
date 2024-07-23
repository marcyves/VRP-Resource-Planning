<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Carbon\Carbon;

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

        Carbon::setLocale(app()->getLocale());

        Blade::directive('money', function ($value) {
            return "<?php echo number_format($value, 2); ?>";
        });

        Blade::directive('monthName', function ($value){
            return "<?php echo  date('F', mktime(0, 0, 0, $value, 1)); ?>";
        });
    }
}
