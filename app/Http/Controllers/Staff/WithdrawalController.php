<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\{
    Currency,
    Deposit,
    FeesLimit,
    Role,
    Transaction,
    User,
    Receipt,
    Wallet,
    Withdrawal
};

use Carbon\Carbon;
use Illuminate\Http\Request;
use Session, Auth, DB, Exception;
use Illuminate\Support\Facades\Redirect;

class WithdrawalController extends Controller
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

    public function ShowWithdrawal()
    {
        $currencies = Currency::where(['status' => 'Active'])->get();
        return view('staff.withdrawal', compact('currencies'));
    }

    public function eachUserWithdrawSuccess(Request $request)
    {
        try {
            DB::beginTransaction();
            $user_id = $request->userID;
            $currency_id = $request->currency;
            $amount = $request->withdrawalAmount;
            $note = $request->note;
    
            $staffUserId = auth()->guard('staff')->user()->id;
            $withdrawalData = $this->withdraw($user_id, $currency_id, $amount, $note, $staffUserId);
    
            if (isset($withdrawalData['error'])) {
                $errorMessage = $withdrawalData['error'];
                return  redirect()->route('Withdrawal')->with('error', $errorMessage);
            }
            
            $staffDepositData = $this->staffDeposit($user_id, $currency_id, $amount, $note, $staffUserId);
    
            if (isset($staffDepositData['error'])) {
                $errorMessage = $staffDepositData['error'];
                return redirect()->route('Withdrawal')->with('error', $errorMessage);
            }
            DB::commit();
            
            return view('staff.print.index', $withdrawalData);
        } catch(\Exception $ex) {
            DB::rollback();
            return redirect()->route('Withdrawal')->with('error', 'There\'s an issue');
        }
    }

    public function withdraw($user_id, $currency_id, $amount, $note, $staffUserId)
    {
        $status = 'Success';
        $uuid = unique_code();

        $wallet = Wallet::firstOrCreate(['user_id' => $user_id, 'currency_id' => $currency_id], ['balance' => 0]);
        $balance = $wallet->balance - $amount;

        if ($wallet->balance < $amount) {
            return ['error' => 'Insufficient balance on user Account'];
        }
        // Withdrawal
        $withdrawal = new Withdrawal();
        $withdrawal->user_id = $user_id;
        $withdrawal->currency_id = $currency_id;
        $withdrawal->payment_method_id = 1;
        $withdrawal->uuid = $uuid;
        $withdrawal->charge_percentage =  0;
        $withdrawal->charge_fixed =  0;
        $withdrawal->subtotal = $amount;
        $withdrawal->amount = $amount;
        $withdrawal->balance = $balance;
        $withdrawal->status = 'Success';
        $withdrawal->save();

        // Transaction
        $transaction = new Transaction();
        $transaction->user_id = $user_id;
        $transaction->end_user_id = $staffUserId;
        $transaction->currency_id = $currency_id;
        $transaction->payment_method_id = 1;
        $transaction->transaction_reference_id = $withdrawal->id;
        $transaction->transaction_type_id = Withdrawal;
        $transaction->uuid = $uuid;
        $transaction->subtotal = $amount;
        $transaction->percentage =  0;
        $transaction->charge_percentage = $withdrawal->charge_percentage;
        $transaction->charge_fixed = $withdrawal->charge_fixed;
        $transaction->total =  '-' . ($amount);
        $transaction->balance = $balance;
        $transaction->status = 'Success';
        $transaction->note = $note;
        $transaction->save();
        
        $wallet->balance -= $amount;
        $wallet->save();

        $data['transInfo']['currency_id'] = $transaction->currency->id;
        $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
        $data['transInfo']['subtotal'] = $transaction->subtotal;
        $data['transInfo']['id'] = $transaction->id;
        $data['users'] = User::find($user_id, ['id']);
        $data['transactionDetails'] = $transaction;

        return $data;
    }

    public function staffDeposit($user_id, $currency_id, $amount, $note, $staffUserId)
    {
        $uuid = unique_code();
        
        $wallet = Wallet::firstOrCreate(['user_id' => $staffUserId, 'currency_id' => $currency_id], ['balance' => 0]);
        $balance = $wallet->balance + $amount;

        // Deposit
        $deposit = new Deposit();
        $deposit->user_id = $staffUserId;
        $deposit->currency_id = $currency_id;
        $deposit->payment_method_id = 1;
        $deposit->uuid = $uuid;
        $deposit->charge_percentage = 0;
        $deposit->charge_fixed = 0;
        $deposit->amount = $amount;
        $deposit->balance = $balance;
        $deposit->status = 'Success';
        $deposit->save();
        
        // Transaction
        $transaction = new Transaction();
        $transaction->user_id = $staffUserId;
        $transaction->end_user_id = $user_id;
        $transaction->currency_id = $currency_id;
        $transaction->payment_method_id = 1;
        $transaction->transaction_reference_id = $deposit->id;
        $transaction->transaction_type_id = Deposit;
        $transaction->uuid = $uuid;
        $transaction->subtotal = $amount;
        $transaction->percentage =  0;
        $transaction->charge_percentage = $deposit->charge_percentage;
        $transaction->charge_fixed = $deposit->charge_fixed;
        $transaction->total = $amount;
        $transaction->balance = $balance;
        $transaction->note = $note;
        $transaction->status = 'Success';
        $transaction->save();

        $wallet->balance += $amount;
        $wallet->save();

        $data['transInfo']['currency_id'] = $transaction->currency->id;
        $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
        $data['transInfo']['subtotal'] = $transaction->subtotal;
        $data['transInfo']['id'] = $transaction->id;
        $data['users'] = User::find($user_id, ['id']);
        $data['transactionDetails'] = $transaction;

        $status = 'Success';

        return $data;
    }
}
