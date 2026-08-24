<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
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
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Determine the proper redirect destination based on user role.
     * Parent and Student roles go to /portal, everyone else to /home.
     *
     * The RedirectsUsers trait calls this with zero arguments.
     * Must not accept parameters to match the trait's contract.
     */
    protected function redirectTo()
    {
        $user = Auth::user();
        if ($user && $user->hasAnyRole(['Parent', 'Student'])) {
            return '/portal';
        }
        return '/home';
    }

    /**
     * Record successful logins in the system audit trail.
     */
    protected function authenticated(Request $request, $user)
    {
        AuditTrail::log('Auth', 'LOGIN', $user->id, null, ['email' => $user->email]);
    }
}
