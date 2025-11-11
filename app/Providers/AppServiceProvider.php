<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use Laravel\Cashier\Cashier; // REMOVED - This class no longer exists
// use App\Models\Agency;       // REMOVED - No longer needed here

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
        // REMOVED - This line was for Stripe/Cashier, which has been uninstalled.
        // Cashier::useCustomerModel(Agency::class);
    }
}