<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AdminAuthServiceProvider extends ServiceProvider
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
        $this->registerPolicies();
        Passport::routes();
        //
    }
    public function register(){
      \Auth::extend('adminEloquent', function($app){
        // you can use Config::get() to retrieve the model class name from config file
        $myProvider = new EloquentUserProvider($app['hash'], '\App\Model\AdminModel'); 
        return new Guard($myProvider, $app['session.store']);
      });
      $app = app();
      $app->singleton('auth.driver_admin', function($app){
        return \Auth::driver('adminEloquent');
      });
   }
}
