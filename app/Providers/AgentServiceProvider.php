<?php

namespace App\Providers;

use View;
use Jenssegers\Agent\Agent;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\BaseController as Basectrl;
use Faker\Provider\Base;

class AgentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $agent = new Agent();
        $base = new Basectrl();
        $course = \App\Model\Courses::getCourse();

        \View::share('agent', $agent);
        \View::share('meta', $base->getOption('meta'));
        \View::share('course', $course);
        \View::share('api_token', csrf_token());
    }
}
