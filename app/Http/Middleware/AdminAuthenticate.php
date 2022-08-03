<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\User;

class AdminAuthenticate 
{
    use AuthenticatesUsers; 
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    /*protected function redirectTo($request)
    {   
        
        
        if (\Auth::guard('admin')->check()) {
            return redirect()->back();
        }
        dd(\Auth::guard('admin')->check());
        return route('login');
    }*/
    public function handle($request, Closure $next, $guard = null)
    {
        if (!Auth::guard('admin')->check()) {
            
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
