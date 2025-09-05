<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier; // Import the Cashier class
use App\Models\Agency;      // Import the Agency model

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
        // **THE FIX:** This line explicitly tells Laravel Cashier to use your
        // Agency model as the customer model for all billing operations.
        // This resolves the polymorphic relationship issue.
        Cashier::useCustomerModel(Agency::class);
    }
}

