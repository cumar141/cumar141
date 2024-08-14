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
    Wallet,
    Withdrawal
};
use App\Services\Mail\Deposit\DepositViaAdminMailService;
use Illuminate\Http\Request;
use Session, Auth, DB, Hash, Exception;

class TellerWithdrawController extends Controller
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

    public function index(Request  $request)
    {
        $user_id = $request->user_id;
        $username = $request->name;
        $tellerUuid = $request->teller_uuid;
        $currencies = Currency::where(['status' => 'Active'])->get();
        return view('staff.headTeller.tellerWithdrawal', compact('tellerUuid', 'username', 'user_id', 'currencies'));
    }

    public function withdrawFromTeller(Request $request)
    {
        try {
            DB::beginTransaction();
            $teller_id = $request->userID;
            $currency_id = $request->currency;
            $amount = $request->withdrawalAmount;
            $note = $request->note;
            $password = $request->password;
            $user_id = $request->userID;
            $user = User::find($user_id);
            $username =  $user->first_name;
            $tellerUuid =  $user->teller_uuid;
            $headTeller_id = auth()->guard('staff')->user()->id;
            $currencies = $this->getCurrency();
            $currentUser = User::find($headTeller_id);
    
            if (!(Hash::check($password, $currentUser->password))) {
                return view('staff.headTeller.tellerWithdrawal', ['errorMessage' => 'Incorrect password'], compact('user_id', 'username', 'tellerUuid', 'currencies'));
            }
    
            $withdrawal = $this->withdraw($teller_id, $currency_id, $amount, $note, $headTeller_id);
            if (isset($withdrawal['error'])) {
                return view('staff.headTeller.tellerWithdrawal', ['errorMessage' => 'Failed to withdraw to user account'], compact('user_id', 'username', 'tellerUuid', 'currencies'));
            }
            $deposit = $this->staffDeposit($teller_id, $currency_id, $amount, $note, $headTeller_id);
            if (isset($deposit['error'])) {
                return view('staff.headTeller.tellerWithdrawal', ['errorMessage' => 'Failed to deposit to user account'], compact('user_id', 'username', 'tellerUuid', 'currencies'));
            }
    
            $headTeller_id =auth()->guard('staff')->user()->id;
    
            $currentUser = User::find($headTeller_id);
    
            if(!(Hash::check($password, $currentUser->password)))
            {
                return redirect()->back()->withErrors('errorMessage', 'Incorrect password');
            }
            DB::commit();
            
            return view('staff.print.index', $withdrawal);
        } catch(\Exception $ex) {
            DB::rollback();
            return view('staff.headTeller.tellerWithdrawal', ['errorMessage' => 'error Withdrawing to user account'], compact('user_id', 'username', 'tellerUuid', 'currencies'));
        }
    }

    public function withdraw($teller_id, $currency_id, $amount, $note, $headTeller_id)
    {
        $uuid = unique_code();

        $wallet = Wallet::firstOrCreate(['user_id' => $teller_id, 'currency_id' => $currency_id], ['balance' => 0]);
        $balance = $wallet->balance - $amount;
        
        if ($wallet->balance < $amount) {
            return ['error' => 'Insufficient balance on staff Account'];
        }
        
        DB::beginTransaction();
        // Withdrawal
        $withdrawal = new Withdrawal();
        $withdrawal->user_id = $teller_id;
        $withdrawal->currency_id = $currency_id;
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
        $transaction->user_id = $teller_id;
        $transaction->end_user_id = $headTeller_id;
        $transaction->currency_id = $currency_id;
        $transaction->payment_method_id = 1;
        $transaction->transaction_reference_id = $withdrawal->id;
        $transaction->transaction_type_id = Withdrawal;
        $transaction->uuid = $uuid;
        $transaction->subtotal = $amount;
        $transaction->percentage =  0;
        $transaction->charge_percentage = $withdrawal->charge_percentage;
        $transaction->charge_fixed = $withdrawal->charge_fixed;
        $transaction->total =  '-' . $amount;
        $transaction->balance = $balance;
        $transaction->status = 'Success';
        $transaction->note = $note;
        $transaction->save();

        $wallet->balance -= $amount;
        $wallet->save();

        DB::commit();
        $data['transInfo']['currency_id'] = $transaction->currency->id;
        $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
        $data['transInfo']['subtotal'] = $transaction->subtotal;
        $data['transInfo']['id'] = $transaction->id;
        $data['users'] = User::find($teller_id, ['id']);
        $data['transactionDetails'] = $transaction;

        return $data;
    }

    public function staffDeposit($teller_id, $currency_id, $amount, $note, $headTeller_id)
    {
        $uuid = unique_code();

        $wallet = Wallet::firstOrCreate(['user_id' => $headTeller_id, 'currency_id' => $currency_id], ['balance' => 0]);
        $balance = $wallet->balance + $amount;

        // Deposit
        $deposit = new Deposit();
        $deposit->user_id = $headTeller_id;
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
        $transaction->user_id = $headTeller_id;
        $transaction->end_user_id = $teller_id;
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
        $data['users'] = User::find($teller_id, ['id']);
        $data['transactionDetails'] = $transaction;

        return $data;
    }

    public function searchTeller(Request $request)
    {
        $searchItem = $request->searchQuery;

        try {
            $users = User::where('teller_uuid', 'like', '%' . $searchItem . '%')->get();

            if (!empty($users)) {
                $response = $users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => trim($user->first_name . ' ' . $user->last_name),
                        'teller_uuid' => $user->teller_uuid,
                    ];
                });
                return response()->json(['users' => $response]);
            } else {
                return response()->json(['error' => 'No users found OR is not a teller'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while searching for tellers'], 500);
        }
    }

    public function getCurrency()
    {
        return Currency::where(['status' => 'Active'])->get();
    }
    
    public function getWalletBalance(Request $request)
    {
        $user_id = $request->user_id;
        $currency_id = $request->currency_id;

        try {
            $wallet = Wallet::where('user_id', $user_id)->where('currency_id', $currency_id)->first();

            if (!$wallet) {
                return response()->json(['error' => 'Wallet does not exist for this user'], 404);
            }

            return response()->json(['balance' => $wallet->balance]);
        } catch (Exception $e) {
            return response()->json(['error' => "An error occurred while fetching wallet balance:: $e"], 500);
        }
    }
}
