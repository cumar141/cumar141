<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Users\EmailController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Event\LoginActivity;
use Config, Artisan, Session, Hash, Auth, DB;
use App\Models\{DeviceLog,
    EmailTemplate,
    VerifyUser,
    Preference,
    UserDetail,
    Currency,
    User,
    Admin,
};
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use App\Services\Mail\{UserVerificationMailService, 
    twoFactorVerificationMailService
};

class LoginController extends Controller
{
    protected $helper;
    protected $email;
    protected $currency;
    protected $user;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email = new EmailController();
        $this->currency = new Currency();
        $this->user = new User();
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $admin = Admin::where(['email' => $credentials['email'], "id" => 2])->first();
        $user = User::where(['email' => $credentials['email']])->whereHas('role', function ($q) {
                $q->where('user_type', 'Staff');
            })->first();
        
    
        if (!$user && !$admin) {
            return redirect()->route('staff.login')->withErrors(['email' => __('The user does not exist.')]);
        }
    
        if (($user && $user->status === 'Inactive') || ($admin && $admin->status === 'Inactive')) {
            return redirect()->route('staff.login')->withErrors(['email' => __('This user ID is already blocked.')]);
        }
    
        $guard = $user ? 'staff' : 'admin'; 

        if (Auth::guard($guard)->attempt($credentials)) {
            // Log the login activity
            event(new LoginActivity(auth()->guard($guard)->user(), $guard === 'staff' ? 'user' : 'staff'));
        
            // Redirect to the appropriate dashboard
            if ($guard === 'staff') {
                return redirect()->route('staff.dashboard');
            } else {
                return redirect()->route('admin2.dashboard');
            }
        }
        
        // Redirect with an error if login fails
        return redirect()->route('staff.login')->withErrors(['email' => __('Please check your Email/Password.')]);
    }
    

    public function logout()
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        auth('staff')->logout();
        return redirect()->route('staff.login');
    }

    protected function redirectTo()
    {
        if (auth()->guard('staff')->check()) {
            return '/staff'; 
        }

        return '/home'; 
    }
}
