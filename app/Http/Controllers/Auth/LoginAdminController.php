<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Model\Admin;

class LoginAdminController extends BaseController
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
     protected $redirectTo = '/admin';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {   
        /*
        if(strpos(\URL::previous(), "admin"))
            $this->redirectTo = \URL::previous();
        else
            $this->redirectTo = '/';
        
        */
        $this->middleware('guest')->except('logout');
    }

    
    public function username()
    {
        return 'email';
    }
    public function login(Request $request)
    {   
        if (\Auth::guard('admin')->check()) {
            return $this->sendLoginResponse($request);
        }
        // Validate the form data
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);
        // Attempt to log the user in
        if (\Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password])) {
            // if successful, then redirect to their intended location
            $user = \App\Model\Admin::where('email', $request->email)->first();
            \Auth::guard('admin')->login($user);
            //return redirect()->intended('/admin/tdirections');
            $user->generateToken();
            return $this->sendLoginResponse($request);
        }
        // if unsuccessful, then redirect back to the login with the form data
        return redirect()->back()->with('flash_message_error', 'Invalid Access: Please Login With Your Credentials.');
    }
    public function logout()
    {
        \Auth::guard('admin')->logout();
        return redirect('admin')->with('flash_message_error', 'Successfully Logged Out');;
    }
    
    protected function guard()
    {
        return \Auth::guard('admin');
    }
    public function showLoginForm()
    {
        return view('Admin.auth.login');
    }
}
