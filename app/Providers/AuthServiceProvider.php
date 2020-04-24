<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // user management
        $this->registerPolicies();

        Gate::define('admin-role', function($user){
            return $user->hasRole('admin');
        });

        Gate::define('staff-role', function($user){
            return $user->hasRole('staff');
        });

        Gate::define('supplier-role', function($user){
            return $user->hasRole(['supplier']);
        });

        Gate::define('customer-role', function($user){
            return $user->hasRole(['customer']);
        });

        Gate::define('managing-requests', function($user){
            return $user->hasAnyRoles(['customer','admin']);
        });

        Gate::define('request-products', function($user){
            return $user->hasAnyRoles(['customer','admin']);
        });

        Gate::define('manage-shipped-products', function($user){
            return $user->hasAnyRoles(['admin','staff']);
        });

        Gate::define('supproducts', function($user){
            return $user->hasAnyRoles(['admin','supplier']);
        });

    }
}
