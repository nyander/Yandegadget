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

        Gate::define('manage-users', function($user){
            return $user->hasRole('admin');
        });

        Gate::define('manage-requests', function($user){
            return $user->hasRole('admin');
        });

        Gate::define('edit-users', function($user){
            return $user->hasRole('admin');
        });

        Gate::define('delete-users', function($user){
            return $user->hasRole('admin');
        });

        //condition management

        Gate::define('add-product', function($user){
            return $user->hasRole('admin');
        });

        Gate::define('edit-product', function($user){
            return $user->hasRole('admin');
        });

        Gate::define('delete-product', function($user){
            return $user->hasRole('admin');
        });

        //supplier management

        Gate::define('supplier-management', function($user){
            return $user->hasRole('admin');
        });

        //shipment management

        Gate::define('shipment-management', function($user){
            return $user->hasRole('admin');
        });

        //request management

        Gate::define('productRequest-management', function($user){
            return $user->hasRole(['admin', 'customer']);
        });
    }
}
