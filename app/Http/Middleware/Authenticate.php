<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {   
        if (\Auth::guard()->check()) {
            return '';
        }
        if ($request->is('api/*')){
            if (! $request->expectsJson()) {
                return route('loginapi', $request->all());
            }
        }else{
            return route('login');
        }
    }

}
