<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Model\User;

class LoginController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
     protected $redirectTo = '/catalog';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {   
        if(strpos(\URL::previous(), "/"))
            $this->redirectTo = \URL::previous();
        else
            $this->redirectTo = '/';
        $this->middleware('guest')->except('logout');
    }

    public function loginapi(Request $request)
    {
        $d = $request->all();
        
        $key = [array_keys($d)][0][0];
        $post_data = json_decode($d[$key], true);
        
        $request->replace($post_data);
        try{
            $validator = \Validator::make($request->all(), [
                "login"  =>  'required',
                "password"  =>  'required',
            ]);
            if ($validator->fails()) {
                $d_ret = array( false, config('loadapi.HTTP_CODE_INVALID_SESSION'), $validator->messages()->first());
                return $this->setJsonAnswer($d_ret);
            }
            $login = $post_data['login'];
            $user = User::where('login', $login)->first();
            if(empty($user))
                throw new \Exception(config('loadapi.http_code_msg')[config('loadapi.HTTP_CODE_LOGIN_FAILED')], config('loadapi.HTTP_CODE_LOGIN_FAILED'));

            $tm = $post_data['timemark'];
            if(abs(intval($tm)-time())>config('loadapi.LOGIN_TIMEMARK_DISTANCE'))
                    throw new \Exception(config('loadapi.http_code_msg')[config('loadapi.HTTP_CODE_LOGIN_SYNC_ERROR')] . ' server time '. time(), 
                              config('loadapi.HTTP_CODE_LOGIN_SYNC_ERROR'));                    

            if($post_data['password']!=hash("sha256", $login . $user->password . $tm))
                $this->setJsonAnswer(false, config('loadapi.http_code_msg')[config('loadapi.HTTP_CODE_LOGIN_FAILED')], config('loadapi.HTTP_CODE_LOGIN_FAILED')); 
        
            \Auth::login($user);
            $user->generateToken();
            $user->resetUpdateTm();
            
            $d_ret = array('id' => $user->id, 'fio' => $user->fio, 
							'image' => '', 'session' => $user->session, 'loginTime' => $user->login_time, 
							'sessionEndTime' => $user->session_end_time);
            
            return $this->setJsonAnswer($d_ret);
        
        }catch (Exception $e){
            return $e->getMessage();
        }
    }
    public function username()
    {
        return 'email';
    }
    public function login(Request $request)
    {   
        
        if (\Auth::check()) {
            return $this->sendLoginResponse($request);
        }

        // Validate the form data
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);
        // Attempt to log the user in
        if (\Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // if successful, then redirect to their intended location
            $user = \App\Model\User::where('email', $request->email)->first();
            $user->generateToken();
            \Auth::login($user);
            return redirect()->intended('/catalog');
            //return $this->sendLoginResponse($request);
        }
        // if unsuccessful, then redirect back to the login with the form data
        return redirect()->back()->with('flash_message_error', 'Ошибка доступа: введите правильные логин и пароль');
    }
    public function logout()
    {
        \Auth::guard()->logout();
        return redirect('/')->with('flash_message_error', 'Успешный выход');;
    }
    
    
}
