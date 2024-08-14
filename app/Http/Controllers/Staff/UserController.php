<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Http\Controllers\Users\EmailController;
use  App\DataTables\Admin\StaffUserDataTable;
use Hash, Validator, Session, DB, Exception;

use App\Services\Mail\{UserStatusChangeMailService,
    UserVerificationMailService
};
use App\Models\{ActivityLog,
    CryptoProvider,
    VerifyUser,
    PaymentMethod,
    Transaction,
    Withdrawal,
    FeesLimit,
    Currency,
    RoleUser,
    Dispute,
    Deposit,
    Wallet,
    Ticket,
    QrCode,
    User,
    Role,
    Branch
};

class UserController extends Controller
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
    public function index(StaffUserDataTable $dataTable)
    {
       return $dataTable->render('staff.user.index');
    }

    public function create()
    {
        $roles = Role::select('id', 'display_name')->where('customer_type', "user")->get();
        $branch = Branch::select('id', 'name')->get();
        return view('staff.user.create', compact('roles', 'branch'));
    }
    
    public function show($id)
    {
        $user = User::find($id);
        return view('staff.user.show', compact('user'));
    }

    public function edit($id)
    {
        $users = User::find($id);
        $wallets = Wallet::where(['user_id' => $id])->get();
        $transactions =Transaction::with('currency', 'user', 'end_user', 'transaction_type')->where(['user_id' => $id])->get();
        $tickets = Ticket::where(['user_id' => $id])->get();
        $branch = Branch::select('id', 'name')->get();
        $roles = Role::select('id', 'display_name')->where('customer_type', "user")->get();
        return view('staff.user.edit', compact('users', 'roles', 'wallets', 'transactions', 'tickets', 'branch'));
    }
    public function wallets($id)
    {
        $users = User::find($id);
        $wallets = Wallet::with('currency')->where(['user_id' => $id])->get();
        $transactions = Transaction::with('currency', 'user', 'end_user', 'transaction_type')->where(['user_id' => $id])->get();
        $tickets = Ticket::where(['user_id' => $id])->get();
        
        $roles = Role::select('id', 'display_name')->where('customer_type', "user")->get();
        return view('staff.user.wallets', compact('users', 'roles', 'wallets', 'transactions', 'tickets'));
    }
    public function transactions($id)
    {
        $users = User::find($id);
        $wallets = Wallet::with('currency')->where(['user_id' => $id])->get();
        $transactions =Transaction::with('currency', 'user', 'end_user', 'transaction_type')->where(['user_id' => $id])->get();
        $tickets = Ticket::where(['user_id' => $id])->get();
        $roles = Role::select('id', 'display_name')->where('customer_type', "user")->get();
        return view('staff.user.transactions', compact('users', 'roles', 'wallets', 'transactions', 'tickets'));
    }

    public function update(Request $request)
    {
        $rules = array(
            'first_name' => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
            'last_name' => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
            'email' => 'required|email|unique:users,email,' . $request->id,
            'password' => 'nullable|min:4|confirmed',
            'password_confirmation' => 'nullable|min:4',
            'status' => 'required',
            'branch_id' => 'required|integer|exists:branchs,id',
            'role' => 'required|integer|exists:roles,id',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
          
        } else {

            try {
                DB::beginTransaction();
                $user = User::find($request->id);
                $user->first_name = $request->first_name;
                $user->last_name  = $request->last_name;
                $user->email      = $request->email;
                $user->role_id    = $request->role;
                $user->status     = $request->status;
                $user->branch_id  = $request->branch_id;
                

                $formattedPhone = ltrim($request->phone, '0');
                if (!empty($request->phone)) {
                    $user->phone          = preg_replace("/[\s-]+/", "", $formattedPhone);
                    $user->defaultCountry = $request->user_defaultCountry;
                    $user->carrierCode    = $request->user_carrierCode;
                    $user->formattedPhone = $request->formattedPhone;
                } else {
                    $user->phone          = null;
                    $user->defaultCountry = null;
                    $user->carrierCode    = null;
                    $user->formattedPhone = null;
                }

                if (!is_null($request->password) && !is_null($request->password_confirmation)) {
                    $user->password = \Hash::make($request->password);
                }
                $user->branch_id = auth()->guard('staff')->user()->branch_id;


                $user->save();

                RoleUser::where(['user_id' => $request->id, 'user_type' => 'User'])->update(['role_id' => $request->role]);

                DB::commit();

                if ($request->status != $user->status) {
                    (new UserStatusChangeMailService)->send($user);
                }

                session()->flash('success', 'User saved successfully');
               return redirect()->route('staff.user.index');
            } catch (Exception $e) {
                DB::rollBack();
                $this->helper->one_time_message('error', $e->getMessage());
               return redirect()->route('staff.user.index');
            }
        }
    }

    public function store(Request $request)
    {
   
        if ($request->isMethod('post')) {
            $rules = array(
                'first_name'            => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
                'last_name'             => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
                'email'                 => 'required|unique:users,email',
                'password'              => 'required|min:4|confirmed',
                'password_confirmation' => 'required|min:4',
                'status'                => 'required',
                'branch_id'             => 'required|integer|exists:branchs,id',
            );

            $fieldNames = array(
                'first_name'            => 'First Name',
                'last_name'             => 'Last Name',
                'email'                 => 'Email',
                'password'              => 'Password',
                'password_confirmation' => 'Confirm Password',
                'status'                => 'Status',
                'branch_id'             => 'Branch',
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                try {
                    DB::beginTransaction();
                    $checkUser = User::where('email', $request->email)->first();

                    if ($checkUser) {
                        return redirect()->route('staff.user.index')->withErrors(['email' => 'User already exists']);
                    }
                   $phone =$request->formattedPhone;
                            $checkPhone = User::where('formattedPhone', $phone)
                                          ->orWhere('phone', $phone)
                                    ->orWhere('phone1', $phone)
                                    ->orWhere('phone2', $phone)
                                    ->orWhere('phone3', $phone)
                                    ->first();

                    if ($checkPhone) {
                        return redirect()->route('staff.user.index')->withErrors(['phone' => 'Phone number already taken']);
                    }
                
                    // Create user
                    $user = $this->user->createNewUser($request, 'admin');

                    // Assigning user_type and role id to new user
                    RoleUser::insert(['user_id' => $user->id, 'role_id' => $user->role_id, 'user_type' => 'User']);

                    // Create user detail
                    $this->user->createUserDetail($user->id);

                    // Create user's default wallet
                    $this->user->createUserDefaultWallet($user->id, settings('default_currency'));

                    // Create wallets that are allowed by admin
                    if (settings('allowed_wallets') != 'none') {
                        $this->user->createUserAllowedWallets($user->id, settings('allowed_wallets'));
                    }

                    if (isActive('BlockIo') && CryptoProvider::getStatus('BlockIo') == 'Active' && $user->status == 'Active') {
                        $generateUserCryptoWalletAddress = $this->user->generateUserBlockIoWalletAddress($user);
                        if ($generateUserCryptoWalletAddress['status'] == 401) {
                            DB::rollBack();
                            $this->helper->one_time_message('error', $generateUserCryptoWalletAddress['message']);
                            return redirect()->route('staff.user.index');
                        }
                    }

                    if (isActive('TatumIo') && CryptoProvider::getStatus('TatumIo') == 'Active' && $user->status == 'Active') {
                        $generateUserCryptoWalletAddress = $this->user->generateUserTatumIoWalletAddress($user);
                        if ($generateUserCryptoWalletAddress['status'] == 401) {
                            DB::rollBack();
                            $this->helper->one_time_message('error', $generateUserCryptoWalletAddress['message']);
                            return redirect()->route('staff.user.index');
                        }
                    }



                    //Entry for User's QrCode Generation - starts
                    QrCode::createUserQrCode($user);

                    $userEmail          = $user->email;
                    $userFormattedPhone = $user->formattedPhone;

                    // Process Registered User Transfers
                    $this->user->processUnregisteredUserTransfers($userEmail, $userFormattedPhone, $user, settings('default_currency'));

                    // Process Registered User Request Payments
                    $this->user->processUnregisteredUserRequestPayments($userEmail, $userFormattedPhone, $user, settings('default_currency'));

                    // Email verification
                    if (!$user->user_detail->email_verification) {
                        if (preference('verification_mail') == "Enabled") {
                            VerifyUser::generateVerificationToken($user->id);
                            try {
                                (new UserVerificationMailService)->send($user);
                                DB::commit();
                                return redirect()->route('staff.user.index')->with('success', 'An email has been sent to ' . $user->email . ' with verification code.');
                            } catch (Exception $e) {
                                DB::rollBack();
                                
                                return redirect()->route('staff.user.index')->withErrors($validator)->withInput();
                            }
                        }
                    }
                    DB::commit();
                   
                    return redirect()->route('staff.user.index')->with('success', 'User created successfully');
                } catch (Exception $e) {
                    DB::rollBack();
                    
                    return redirect()->route('staff.user.index')->withErrors($e->getMessage());
                }
            }
        }
    }

    public function checkPhone(Request $request)
    {
        $phone = $request->phone;
        $userId = $request->user_id; // Assuming user_id is passed from the AJAX request
        $query = User::where('formattedPhone', $phone)
                     ->orWhere('phone', $phone)
                     ->orWhere('phone1', $phone)
                     ->orWhere('phone2', $phone)
                     ->orWhere('phone3', $phone);
        
        if ($userId) {
            $query->where('id', '!=', $userId); // Exclude the current user when updating
        }
    
        $user = $query->first();
        
        if ($user) {
            return response()->json(['status' => true, 'message' => 'Phone number already exists']);
        } else {
            return response()->json(['status' => false, 'message' => 'Phone number is available']);
        }
    }
    
    public function checkEmail(Request $request)
    {
        $email = $request->email;
        $userId = $request->user_id; // Assuming user_id is passed from the AJAX request
        $query = User::where('email', $email);
        
        if ($userId) {
            $query->where('id', '!=', $userId); // Exclude the current user when updating
        }
    
        $user = $query->first();
        
        if ($user) {
            return response()->json(['status' => true, 'message' => 'Email already exists']);
        } else {
            return response()->json(['status' => false, 'message' => 'Email is available']);
        }
    }
    
    function displayTab()
    {
        return view('staff.user.tab');
    }
}
