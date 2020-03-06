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

        Gate::define('manage-products', function($user){
            return $user->hasRole('admin');
        });

        Gate::define('sub-manage-products', function($user){
            return $user->hasRole('staff');
        });       

        //supplier management

        Gate::define('manage-suppliers', function($user){
            return $user->hasRole('admin');
        });

        //shipment management

        Gate::define('manage-shipment', function($user){
            return $user->hasRole('admin');
        });

        //request management

        Gate::define('manage-productRequest', function($user){
            return $user->hasRole(['admin', 'customer']);
        });

        Gate::define('upload-edit-supplier-products', function($user){
            return $user->hasRole(['supplier']);
        });

        Gate::define('notifications', function($user){
            return $user->hasRole(['staff']);
        });
    }
}
