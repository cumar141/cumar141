<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\{
    Currency,
    Deposit,
    FeesLimit,
    Transaction,
    User,
    Wallet,
    Withdrawal
};
use App\Services\Mail\Deposit\DepositViaAdminMailService;
use App\Services\SmsService;
use DB, Session;
use Illuminate\Http\Request;

class DepositController extends Controller
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
    public function showDeposit()
    {
        $users = User::whereHas('role', function ($query) {
            $query->where('customer_type', 'user');
        })
        ->with('role')
        ->get();
        $currencies = Currency::where(['status' => 'Active'])->get();

        return view('staff.Deposit', compact('users', 'currencies'));
    }
    
    public function createDeposit(Request $request)
    {
        try {
            DB::beginTransaction();
            $staff_id =  auth()->guard('staff')->user()->id;
            $note=$request->note;
        
            $user_id = $request->userID;
            $amount = $request->amount;
            $currency = $request->currency;
    
            // Make withdrawal
            $withdrawalData = $this->makeWithdrawal($staff_id, $currency, $amount,$user_id,$note);
      
            if (isset($withdrawalData['error'])) {
                return redirect()->route('showDeposit')->with('error',$withdrawalData['error']);
            } else {
                $depositData = $this->staffDeposit($user_id, $currency, $amount,$staff_id,$note);
                if (isset($depositData['error'])) {
                    return redirect()->route('showDeposit')->with('error',$depositData['error']);
                } else {
                    DB::commit();
                    return view('staff.print.index', $depositData);
                }
            }
        } catch(\Exception $ex) {
            DB::rollback();
            return ['error' => 'There\'s an issue'];
        }
    }

    public function makeWithdrawal($staff_id, $currency, $amount,$user_id,$note)
    {
        $uuid = unique_code();
    
        $wallet = Wallet::firstOrCreate(['user_id' => $staff_id, 'currency_id' => $currency], ['balance' => 0]);
        $balance = $wallet->balance - $amount;
        
        if ($wallet->balance < $amount) {
            return ['error' => 'The staff balance is not enough'];
        }
    
        $withdrawal = new Withdrawal();
        $withdrawal->user_id = $staff_id;
        $withdrawal->currency_id = $currency;
        $withdrawal->payment_method_id = 1;
        $withdrawal->uuid = $uuid;
        $withdrawal->charge_percentage = 0;
        $withdrawal->charge_fixed =  0;
        $withdrawal->subtotal = $amount;
        $withdrawal->amount = $amount;
        $withdrawal->balance = $balance;
        $withdrawal->status = 'Success';
        $withdrawal->save();
    
        // Transaction
        $transaction = new Transaction();
        $transaction->user_id = $staff_id;
        $transaction->end_user_id=$user_id; 
        $transaction->currency_id = $currency;
        $transaction->payment_method_id = 1;
        $transaction->transaction_reference_id = $withdrawal->id;
        $transaction->transaction_type_id = Withdrawal;
        $transaction->uuid = $uuid;
        $transaction->subtotal = $amount;
        $transaction->percentage = 0;
        $transaction->charge_percentage = $withdrawal->charge_percentage;
        $transaction->charge_fixed = $withdrawal->charge_fixed;
        $transaction->note = $note; 
        $transaction->total = '-'.$amount ;
        $transaction->balance = $balance;
        $transaction->status = 'Success';
        $transaction->save();
        
        $wallet->balance -= $amount;
        $wallet->save();
        
        return [
            'transInfo' => [
                'currency_id' => $transaction->currency->id,
                'currSymbol' => $transaction->currency->symbol,
                'subtotal' => $transaction->subtotal,
                'id' => $transaction->id,
                'users' => User::find($staff_id ),
                'transactionDetails' => $transaction,
            ]
        ];
    }
    public function staffDeposit($user_id, $currency_id, $amount,$staff_id,$note)
    {
        $uuid = unique_code();

        $wallet = Wallet::firstOrCreate(['user_id' => $user_id, 'currency_id' => $currency_id], ['balance' => 0]);
        $balance = $wallet->balance + $amount;

        // Deposit
        $deposit = new Deposit();
        $deposit->user_id = $user_id;
        $deposit->currency_id = $currency_id;
        $deposit->payment_method_id = 1;
        $deposit->uuid = $uuid;
        $deposit->charge_percentage =  0;
        $deposit->charge_fixed =  0;
        $deposit->amount = $amount;
        $deposit->balance = $balance;
        $deposit->status = 'Success';
        $deposit->save();

        // Transaction
        $transaction = new Transaction();
        $transaction->user_id = $user_id;
        $transaction->end_user_id=$staff_id;
        $transaction->currency_id = $currency_id;
        $transaction->payment_method_id = 1;
        $transaction->transaction_reference_id = $deposit->id;
        $transaction->transaction_type_id = Deposit;
        $transaction->note = $note;
        $transaction->uuid = $uuid;
        $transaction->subtotal = $amount;
        $transaction->percentage =  0;
        $transaction->charge_percentage = $deposit->charge_percentage;
        $transaction->charge_fixed = $deposit->charge_fixed;
        $transaction->total = $amount ;
        $transaction->balance = $balance;
        $transaction->status = 'Success';
        $transaction->save();
        
        $wallet->balance += $amount;
        $wallet->save();
        
        $staff = User::where(['id' => $staff_id])->first();
        $date = date("d/m/Y H:i:s", time());
        (new SmsService)->sendSMS($wallet->user->phone, 'Deposit', "[-somxchange-] waxaad \${$amount} ka heshay {$staff->first_name} {$staff->last_name} ({$staff->phone}), Tar: {$date}");

        $data['transInfo']['currency_id'] = $transaction->currency->id;
        $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
        $data['transInfo']['subtotal'] = $transaction->subtotal;
        $data['transInfo']['id'] = $transaction->id;
        $data['transInfo']['note'] = $transaction->note;
        $data['users'] = User::find($user_id, ['id']);
        $data['transactionDetails'] = $transaction;

        return $data;
    }
    
    public function checkWalletBalance($userid, $currency_id)
    {
        $wallet = Wallet::where(['user_id' => $userid, 'currency_id' => $currency_id])->first(['balance']);
        return $wallet ? $wallet->balance : 0;
    }
    
    public function depositAmountCheck(Request $request)
    {
        $staffUserId = auth()->guard('staff')->user()->id;
        
        if ($request->has('depositAmount') && $request->has('currencyId')) {
            $staffBalance = $this->checkWalletBalance($staffUserId, $request->currencyId);
            $isBalanceSufficient = ($staffBalance >= $request->depositAmount);
            
            return response()->json(['isBalanceSufficient' => $isBalanceSufficient]);
        }
    
        // Return a JSON response indicating missing data
        return response()->json(['error' => 'Missing data'], 400);
    }
}
