<?php

namespace App\Providers;

use App\Models\Client;
use App\Policies\ClientPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // This line maps your Client model to the ClientPolicy.
        // Now, Laravel will automatically use your policy's rules
        // whenever you call authorize() with a Client instance.
        Client::class => ClientPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}