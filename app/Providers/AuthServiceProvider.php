<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define admin gate
        Gate::define('admin', function ($user) {
            return Auth::user()->role === 'admin';
        });

        // Define coordinator gate
        Gate::define('coordinator', function ($user) {
            return Auth::user()->role === 'coordinator';
        });

        // Define teller gate
        Gate::define('teller', function ($user) {
            return  Auth::user()->role === 'teller';
        });
    }
}
