<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\FeesLimit;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Services\Mail\Deposit\DepositViaAdminMailService;
use DB;
use Illuminate\Http\Request;
use Session, Hash;
use App\Http\Helpers\UserPermission;

class ManagerControlPanel extends Controller
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

    
    public function index(){
        $user="";
        $branchID = auth()->guard('staff')->user()->branch_id;
       
        // get branch manager
        $managerID  = $this->getBranchManager($branchID);
        $manager= auth()->guard('staff')->user();
        
        $user = User::with('wallets')->whereHas('role', function($query) {
            $query->where('name', 'Teller');
        })->where(['status' =>'Active', 'branch_id' => $branchID])->get();
        
        if(UserPermission::has_permission(auth()->guard('staff')->user()->id, 'Treasurers')){
            $user = User::with('wallets')->whereHas('role', function($query) {
                $query->where('name', 'Manager');
            })->where('status', 'Active')->get();
        }

        $currencies = Currency::where('status', 'Active')->get();

        return view('staff.ManagerControlPanel', compact('manager','user', 'currencies'));
    }
    
    public function getBranchManager($branch_id)
    {
        $user = auth()->guard('staff')->user()->id;
        if(empty($user)) {
            return redirect()->route('staff.login');
        }
        // get all users whose role is manager
        $managers = User::whereHas('role', function($query) {
            $query->where('name', 'Manager');
        })->where('branch_id', $branch_id)->first();
        
        return $managers->id;
    }

    function getUserWalletsInfo($userIDs) {
        $userWalletsInfo = [];
    
        foreach ($userIDs as $userID) {
            $user = User::find($userID); 
            $tellerUUID = $user->teller_uuid; 
    
            $wallets = Wallet::where('user_id', $userID)->get();
            $currencyBalances = [];
    
            foreach ($wallets as $wallet) {
                $currency = $wallet->currency->name;
                $balance = $wallet->balance;
    
                if (!isset($currencyBalances[$currency])) {
                    $currencyBalances[$currency] = $balance;
                } else {
                    $currencyBalances[$currency] += $balance;
                }
            }
    
            foreach ($currencyBalances as $currency => $balance) {
                $userWalletsInfo[] = [
                    'teller_uuid' => $tellerUUID,
                    'currency' => $currency,
                    'total_balance' => $balance,
                ];
            }
        }
    
        return $userWalletsInfo;
    }
    function getUserInfo($userIDs) {
        $userWalletsInfo = [];
    
        foreach ($userIDs as $userID) {
            $wallets = Wallet::where('user_id', $userID)->get();
    
            foreach ($wallets as $wallet) {
                $currencyID = $wallet->currency_id; // Retrieve currency ID
                $balance = $wallet->balance;
    
                // Check if balance is greater than or equal to zero
                if ($balance > 0) {
                    $userWalletsInfo[] = [
                        'user_id' => $userID,
                        'currency_id' => $currencyID,
                        'balance' => $balance,
                    ];
                }
            }
        }
    
        return $userWalletsInfo;
    }

    public function handleTransaction(Request $request) {
        try {
    
            $password=$this->checkPassword($request->password);
            if($password !==true){
                return redirect()->back()->with('error', 'the password is wrong.');
            }
            $notes=$request->note;
            if(empty($notes)){
                return redirect()->back()->with('error', 'Provide notes for this transaction.');
            }
       
            $status = "Success";
            $UserIds="";
    
            $userId =  auth()->guard('staff')->user()->id;
    
            $user = User::find($userId);
            if (!$user) {
                return redirect()->route('staff.login')->withErrors('User not found');
            }
            $UserIds = User::where(['branch_id' => $user->branch_id, 'status' => 'Active'])
            ->whereHas('role', function ($q) {
                $q->where('name', 'Teller');
            })->pluck('id');
            
            if(UserPermission::has_permission(auth()->guard('staff')->user()->id, 'Treasurers')){
                $UserIds = User::where('status', 'Active')->whereHas('role', function ($q) {
                    $q->where('name', 'Manager');
                })->pluck('id');
            }
        
            $tellersInfo = $this->getUserInfo($UserIds);
    
            if (!is_array($tellersInfo) || empty($tellersInfo)) {
                return redirect()->back()->with('error', 'no Teller Information Found');
            }
    
            $withdrawal = $this->makeWithdrawal($tellersInfo, $userId, $status,$notes);

            if ($withdrawal !== true) {
                throw new \Exception("Failed to process withdrawal. Details: " . (string)$withdrawal);
            }
          
            return redirect()->back()->with('success', 'Transactions completed successfully.');
        } catch (\Exception $e) {
            // Log the error here for further investigation
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while processing transactions. Please try again and check all users balance before withdrawing.');
        }
    }

    public function makeWithdrawal($tellerInfo, $managerID, $status,$notes) {
        try {
            DB::beginTransaction();
    
            foreach ($tellerInfo as $info) {
                $uuid = unique_code();
                
                // Update the teller's wallet balance
                $wallet = Wallet::firstOrCreate(['user_id' => $info['user_id'], 'currency_id' => $info['currency_id']], ['balance' => 0]);
                $balance = $wallet->balance - $info['balance'];
                
                // Create a new withdrawal record
                $withdrawal = new Withdrawal();
                $withdrawal->currency_id = $info['currency_id'];
                $withdrawal->user_id = $info['user_id'];
                $withdrawal->payment_method_id = 1; 
                $withdrawal->uuid = $uuid;
                $withdrawal->subtotal = $info['balance'];
                $withdrawal->amount = $info['balance'];
                $withdrawal->balance = $balance; 
                $withdrawal->charge_percentage = 0; 
                $withdrawal->charge_fixed = 0; 
                $withdrawal->payment_method_info = 1;
                $withdrawal->status = $status;
                $withdrawal->save();
    
                $referenceId = $withdrawal->id; // Withdrawal ID as reference ID
    
                // Make transactions for the withdrawal
                $transaction = new Transaction(); 
                $transaction->currency_id = $info['currency_id'];
                $transaction->user_id = $info['user_id'];
                $transaction->end_user_id = $managerID;
                $transaction->transaction_type_id = Withdrawal;
                $transaction->total = $info['balance'];
                $transaction->balance = $balance; 
                $transaction->subtotal = $info['balance'];
                $transaction->charge_percentage = 0;
                $transaction->charge_fixed = 0;
                $transaction->status = $status;
                $transaction->uuid = $uuid;
                $transaction->note = $notes;
                $transaction->transaction_reference_id = $referenceId;
                $transaction->payment_method_id = 1; 
                $transaction->save();
    
                $wallet->balance -= $info['balance']; 
                $wallet->save();
    
                // Make deposit for each teller inside the loop
                $this->makeDeposit($info['currency_id'], $info['balance'], $info['user_id'], $managerID, $status,$notes);
            }
         
            DB::commit();
            return true;
        
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }
    
    public function makeDeposit($currencyId, $amount, $tellerID, $managerID, $status,$notes) {
        try {
            $uuid = unique_code();
    
            DB::beginTransaction();
            
            // Update the manager's wallet balance
            $wallet = Wallet::firstOrCreate(['user_id' => $managerID, 'currency_id' => $currencyId], ['balance' => 0]);
            $balance = $wallet->balance + $amount;
            
            $deposit = new Deposit();
            $deposit->currency_id = $currencyId;
            $deposit->user_id = $managerID;
            $deposit->payment_method_id = 1;
            $deposit->uuid = $uuid;
            $deposit->amount = $amount;
            $deposit->charge_percentage = 0;
            $deposit->charge_fixed = 0;
            $deposit->balance = $balance;
            $deposit->status = $status;
            $deposit->save();
            
            $referenceId = $deposit->id;
    
            // Make a single transaction for the deposit
            $transaction = new Transaction(); 
            $transaction->currency_id = $currencyId;
            $transaction->user_id = $managerID; 
            $transaction->end_user_id = $tellerID;
            $transaction->transaction_type_id = Deposit;
            $transaction->total = $amount;
            $transaction->balance = $balance;
            $transaction->subtotal = $amount;
            $transaction->charge_percentage = 0;
            $transaction->charge_fixed = 0;
            $transaction->status = $status;
            $transaction->uuid = $uuid;
            $transaction->note = $notes;
            $transaction->transaction_reference_id = $referenceId;
            $transaction->payment_method_id = 1; 
            $transaction->save();
    
            $wallet->balance += $amount;
            $wallet->save();
    
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }
    
    public function checkPassword($password){
    
        // Check password validity
        $user_id = auth()->guard('staff')->user()->id;
        $storedPassword = User::find($user_id)->password;
        if (!$storedPassword || !Hash::check($password, $storedPassword)) {
            return false;
        }
        return true;
    }
}
