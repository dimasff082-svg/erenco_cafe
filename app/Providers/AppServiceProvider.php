<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
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
        // Bagikan data pengaturan toko dan logo secara global ke seluruh view Blade
        View::composer('*', function ($view) {
            $settings = Setting::allMapped();
            $storeLogo = Setting::getLogoUrl();
            [$storeNameLine1, $storeNameLine2] = Setting::getStoreNameLines();

            $view->with([
                'storeSettings' => $settings,
                'storeLogo' => $storeLogo,
                'storeName' => Setting::getStoreName(),
                'storeNameLine1' => $storeNameLine1,
                'storeNameLine2' => $storeNameLine2,
                'storePhone' => $settings['store_phone'] ?? Setting::DEFAULTS['store_phone'],
                'storeEmail' => $settings['store_email'] ?? Setting::DEFAULTS['store_email'],
                'storeAddress' => $settings['store_address'] ?? Setting::DEFAULTS['store_address'],
                'storeHours' => $settings['store_hours'] ?? Setting::DEFAULTS['store_hours'],
            ]);
        });
    }
}
