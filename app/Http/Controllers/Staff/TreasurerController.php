<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Currency;
use DB, session, Hash;

class TreasurerController extends Controller
{
    public function showTreasurerCreateMoney()
    {
        // session users
        $user_id =auth()->guard('staff')->user()->id;

        $transactions = Transaction::with('user', 'currency', 'transaction_type')
                        ->where(['user_id' => $user_id, 'status' => 'Success'])->whereColumn('user_id', 'end_user_id')
                        ->orderBy('created_at', 'desc')->get();

        $currencies = Currency::where(['status' => 'Active'])->get();

        return view('staff.treasurer.create_money', compact('transactions', 'user_id', 'currencies'));
    }
    
    public function createMoney(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0', 
            'currency' => 'required|exists:currencies,id', 
            'note' => 'nullable|string', 
            'password' => 'required|string',
        ]);

        // Extract validated data
        $amount = $validatedData['amount'];
        $currency_id = $validatedData['currency'];
        $note = $validatedData['note'];
        $password = $validatedData['password'];

        // Proceed with the rest of the code
        $user_id =auth()->guard('staff')->user()->id;

        // check user password 
        $user = auth()->guard('staff')->user();

        if (!Hash::check($password, $user->password)) {
            return redirect()->back()->with('error', 'Invalid password');
        }

        $data = $this->depositMoneyTreasurer($user_id, $amount, $currency_id, $note);

        if(isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        } else {
            return view('staff.print.singlePrint', $data);
        }
    }
    
    public function showManagers()
    {
        // session users
        $user_id = auth()->guard('staff')->user()->id;
        
        // get all users whose role is manager
        $managers = User::whereHas('role', function($query) {
            $query->where('name', 'Manager');
        })->where('status', 'Active')->get();

        return view('staff.treasurer.show_managers', compact('managers'));
    }

    public function showMoneyForm(Request $request)
    {
        $currencies = Currency::where(['status' => 'Active'])->get();
        $user_id = $request->user_id;
        $type = $request->type;
        $user = User::find($user_id);

        if($type == 'transfer') {
            return view('staff.treasurer.transfer_money_form', compact('user', 'user_id','currencies'));
        }else if($type == 'request'){
            return view('staff.treasurer.request_money_form', compact('user', 'user_id','currencies'));
        }else {
            return redirect()->back()->with('error', 'Invalid request');
        }
    }

    public function depositMoneyTreasurer($user_id, $amount, $currency_id, $note)
    {
        $uuid = unique_code();
        try {
            DB::beginTransaction();

            // Deposit
            $deposit = new Deposit();
            $deposit->user_id = $user_id;
            $deposit->currency_id = $currency_id;
            $deposit->payment_method_id = 1;
            $deposit->uuid = $uuid;
            $deposit->charge_percentage =  0;
            $deposit->charge_fixed =  0;
            $deposit->amount = $amount;
            $deposit->status = 'Pending';
            $deposit->save();

            // Transaction
            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->currency_id = $currency_id;
            $transaction->payment_method_id = 1;
            $transaction->transaction_reference_id = $deposit->id;
            $transaction->transaction_type_id = Deposit;
            $transaction->note = $note;
            $transaction->uuid = $uuid;
            $transaction->end_user_id = $user_id;
            $transaction->subtotal = $amount;
            $transaction->percentage =  0;
            $transaction->charge_percentage = $deposit->charge_percentage;
            $transaction->charge_fixed = $deposit->charge_fixed;
            $transaction->total = $amount;
            $transaction->status = 'Pending';
            $transaction->save();

            // check if user has wallet for the currency and create if not
            $wallet = Wallet::firstOrCreate(['user_id' => $user_id, 'currency_id' => $currency_id], ['balance' => 0]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return ['error' => $e->getMessage()];
        }
        
        $data['transInfo']['currency_id'] = $transaction->currency->id;
        $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
        $data['transInfo']['subtotal'] = $transaction->subtotal;
        $data['transInfo']['id'] = $transaction->id;
        $data['transInfo']['note'] = $transaction->note;
        $data['users'] = User::find($user_id, ['id']);
        $data['transactionDetails'] = $transaction;

        return $data;
    }

}
