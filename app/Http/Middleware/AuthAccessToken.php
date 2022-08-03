<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Model\User;

class AuthAccessToken {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $headerToken = $request->header('X-Access-Token');
        if (!$headerToken) {
            return response()->json(['error' => 'X-Access-Token is required.']);
        }
        $user = User::where('api_token', $headerToken)->first();
        if (!$user) {
            return response()->json(['error' => 'You are not authenticated with our system.']);
        }
        \Auth::login($user);
        return $next($request);
    }

}
