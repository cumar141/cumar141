<?php
namespace App\Http\Controllers\Admin2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;
use Session;

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
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::join('roles', 'users.role_id', '=', 'roles.id')
            ->where('roles.user_type', 'Staff')
            ->where('users.email', $request->email)
            ->first();

        if ($user && $user->status != 'Inactive' && \Hash::check($request->password, $user->password)) {
            // dd($user);
            $user_email = $request->email;
            $usersinfo = User::where('email', $user_email)->first();

            if ($usersinfo) {
                session()->put('user_data', [
                    'id' => $usersinfo->id,
                    'staff_id' => $usersinfo->id,
                    'name' => $usersinfo->first_name,
                    'lastname' => $usersinfo->last_name,
                    'phone' => $usersinfo->phone,
                    'teller_uuid' => $usersinfo->teller_uuid,
                ]);

                return redirect()->route('staff.dashboard');
            } else {
                return back()->with('error', 'User not found');
            }
        } elseif ($user && $user->status == 'Inactive') {
            session()->flash('message', __('This staff account is inactive.'));
            session()->flash('alert-class', 'alert-danger');

            return redirect()->route('staff.login');
        } else {
            session()->flash('message', __('Please check your Email/Password.'));
            session()->flash('alert-class', 'alert-danger');

            return redirect()->route('staff.login');
        }
    }

    public function logout()
    {
        // destroy session
        session()->forget('user_data');

        return redirect()->route('staff.login');
    }

    public function showLoginForm()
    {
        return view('manager.login');
    }
}
