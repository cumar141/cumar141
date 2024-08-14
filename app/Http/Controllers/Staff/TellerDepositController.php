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
use Illuminate\Http\Request;
use Session, Hash, DB;

class TellerDepositController extends Controller
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
            $password = $request->password;
            $tellerManagerID = auth()->guard('staff')->user()->id;
            $note = $request->note;
    
            $staffID = $request->userID;
            $amount = $request->amount;
            $currency = $request->currency;
            //this data is used to send back with the erros
            $user_id = $request->userID;
            $user = User::find($user_id);
            $username =  $user->first_name;
            $tellerUuid =  $user->teller_uuid;
            $currentUser = User::find($tellerManagerID);
            $currencies = $this->getCurrency();
            if (!Hash::check($password, $currentUser->password)) {
                return view('staff.headTeller.tellerDeposit', ['errorMessage' => 'Password is incorrect'], compact('user_id', 'username', 'tellerUuid', 'currencies'));
            }
            $withdrawal = $this->makeWithdrawal($tellerManagerID, $currency, $amount, $staffID, $note);
    
            if (isset($withdrawal['error'])) {
                return view('staff.headTeller.tellerDeposit', ['errorMessage' => 'error Withdrawing from staff account check you wallet balance'], compact('user_id', 'username', 'tellerUuid', 'currencies'));
            }
            $deposit = $this->staffDeposit($staffID, $currency, $amount, $tellerManagerID, $note);
    
            if (isset($deposit['error'])) {
                return view('staff.headTeller.tellerDeposit', ['errorMessage' => 'error Withdrawing from staff account check you wallet balance'], compact('user_id', 'username', 'tellerUuid', 'currencies'));
            }
            DB::commit();
            
            return view('staff.print.index', $withdrawal);
        } catch(\Exception $ex) {
            DB::rollback();
            return view('staff.headTeller.tellerDeposit', ['errorMessage' => 'error Withdrawing from staff account'], compact('user_id', 'username', 'tellerUuid', 'currencies'));
        }
    }

    public function makeWithdrawal($tellerManagerID, $currency, $amount, $staffID, $note)
    {
        $uuid = unique_code();
        
        $wallet = Wallet::firstOrCreate(['user_id' => $tellerManagerID, 'currency_id' => $currency], ['balance' => 0]);
        $balance = $wallet->balance - $amount;

        if ($wallet->balance < $amount) {
            return ['error' => 'The Manager balance is not enough'];
        }

        $withdrawal = new Withdrawal();
        $withdrawal->user_id = $tellerManagerID;
        $withdrawal->currency_id = $currency;
        $withdrawal->payment_method_id = 1;
        $withdrawal->uuid = $uuid;
        $withdrawal->charge_percentage = 0;
        $withdrawal->charge_fixed = 0;
        $withdrawal->subtotal = $amount;
        $withdrawal->amount = $amount;
        $withdrawal->status = 'Success';
        $withdrawal->balance = $balance;
        $withdrawal->save();

        // Transaction
        $transaction = new Transaction();
        $transaction->user_id = $tellerManagerID;
        $transaction->end_user_id = $staffID;
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
        $transaction->total = '-' . ($amount + 0);
        $transaction->balance = $balance;
        $transaction->status = 'Success';
        $transaction->save();
        
        $wallet->balance -= $amount;
        $wallet->save();

        $data['transInfo']['currency_id'] = $transaction->currency->id;
        $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
        $data['transInfo']['subtotal'] = $transaction->subtotal;
        $data['transInfo']['id'] = $transaction->id;
        $data['transInfo']['note'] = $transaction->note;
        $data['users'] = User::find($staffID, ['id']);
        $data['transactionDetails'] = $transaction;

        return $data;
    }
    public function staffDeposit($staffID, $currency_id, $amount, $tellerManagerID, $note)
    {
        $uuid = unique_code();

        $wallet = Wallet::firstOrCreate(['user_id' => $staffID, 'currency_id' => $currency_id], ['balance' => 0]);
        $balance = $wallet->balance + $amount;

        // Deposit
        $deposit = new Deposit();
        $deposit->user_id = $staffID;
        $deposit->currency_id = $currency_id;
        $deposit->payment_method_id = 1;
        $deposit->uuid = $uuid;
        $deposit->charge_percentage = 0;
        $deposit->charge_fixed = 0;
        $deposit->amount = $amount;
        $deposit->status = 'Success';
        $deposit->balance = $balance;
        $deposit->save();

        // Transaction
        $transaction = new Transaction();
        $transaction->user_id = $staffID;
        $transaction->end_user_id = $tellerManagerID;
        $transaction->currency_id = $currency_id;
        $transaction->payment_method_id = 1;
        $transaction->transaction_reference_id = $deposit->id;
        $transaction->transaction_type_id = Deposit;
        $transaction->note = $note;
        $transaction->uuid = $uuid;
        $transaction->subtotal = $amount;
        $transaction->percentage = 0;
        $transaction->charge_percentage = $deposit->charge_percentage;
        $transaction->charge_fixed = $deposit->charge_fixed;
        $transaction->total = $amount;
        $transaction->balance = $balance;
        $transaction->status = 'Success';
        $transaction->save();

        $wallet->balance += $amount;
        $wallet->save();

        $data['transInfo']['currency_id'] = $transaction->currency->id;
        $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
        $data['transInfo']['subtotal'] = $transaction->subtotal;
        $data['transInfo']['id'] = $transaction->id;
        $data['transInfo']['note'] = $transaction->note;
        $data['users'] = User::find($staffID, ['id']);
        $data['transactionDetails'] = $transaction;

        return $data;
    }

    public function getTellerUser(Request $request)
    {
        $searchItem = $request->searchQuery;

        $user = User::where(function ($query) use ($searchItem) {
            $query->where('teller_uuid', 'like', '%' . $searchItem . '%');
        })->first();

        if ($user) {
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'username' => trim($user->first_name . ' ' . $user->last_name),
                    'teller_uuid' => $user->teller_uuid,
                ],
            ]);
        } else {
            // User not found
            return response()->json(['error' => 'User not found']);
        }
    }

    public function showTellerInfo()
    {
        $branchId = auth()->guard('staff')->user()->branch_id;

        $tellers = User::whereHas('role', function ($query) {
            $query->where('name', 'Teller');
        })->where(['status' => 'Active', 'branch_id' => $branchId])->get();

        return view('staff.headTeller.showTellerInfo', compact('tellers'));
    }

    public function showTellerDepositForm(Request $request)
    {
        $user_id = $request->user_id;
        $username = $request->name;
        $tellerUuid = $request->teller_uuid;
        $currencies  = $this->getCurrency();

        return view('staff.headTeller.tellerDeposit', compact('user_id', 'username', 'tellerUuid', 'currencies'));
    }

    public function getCurrency()
    {
        return Currency::where(['status' => 'Active'])->get();
    }
}
