<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
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
    // protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    // checks whether user has a role or not
    public function redirectTo(){
        //if the user is an admin , t will take them to the administation index page, otherwise, it will take them to home page
        if(Auth::user()->hasRole('admin')){
            $this->redirectTo = route('admin.users.index');
            return $this->redirectTo;
        }

        $this->redirectTo = route('home');
        return $this->redirectTo;
    }
}
