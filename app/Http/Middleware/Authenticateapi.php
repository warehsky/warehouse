<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\User;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */

class Authenticateapi 
{
    use AuthenticatesUsers; 
    
    public function handle($request, Closure $next, $guard = null)
    {
        $d = $request->all();
        $key = [array_keys($d)][0][0];
        $post_data = json_decode($d[$key], true);
        
        $request->replace($post_data);
        $validator = Validator::make($request->all(), [
            "session"  =>  'required',
        ]);
        if ($validator->fails()) {
            $d_ret = array( 'code' => config('loadapi.HTTP_CODE_INVALID_SESSION'), 'message' => config('loadapi.http_code_msg')[config('loadapi.HTTP_CODE_INVALID_SESSION')], 'data' => 0);
            $d_ret['error']=$validator->messages()->first();
            return response()->json($d_ret);
        }
        User::loginByToken( $request->input("session") );
        if( !\Auth::check() ){
            $d_ret = array( 'code' => config('loadapi.HTTP_CODE_SESSION_TIMEOUT'), 'message' => config('loadapi.http_code_msg')[config('loadapi.HTTP_CODE_SESSION_TIMEOUT')], 'data' => 0);
            return response()->json($d_ret);
        }
        return $next($request);
    }
    
    public function username()
    {
        return 'login';
    }

}
