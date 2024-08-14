<?php

namespace App\Services\CashOut;
use App\Http\Helpers\Common;
use App\Models\FeesLimit;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Deposits
{

    public function processDeposit($user_id, $currency_id, $amount, $end_user_id, $note, $paymentMethodId,  $status = null)
    {
       
        $data = [];
        $uuid = unique_code();

        $status = $status ?? 'Pending';

        $wallet = Wallet::firstOrCreate(['user_id' => $user_id, 'currency_id' => $currency_id], ['balance' => 0]);

        $balance = $wallet->balance + $amount;
        try
        {
            DB::beginTransaction();

             // Deposit
             $deposit = new Deposit();
             $deposit->user_id = $user_id;
             $deposit->currency_id = $currency_id;
             $deposit->payment_method_id = $paymentMethodId;
             $deposit->uuid = $uuid;
             $deposit->charge_percentage =  0;
             $deposit->charge_fixed =  0;
             $deposit->amount = $amount;
             $deposit->balance = $balance;
             $deposit->status =  $status;
             $deposit->save();
            
             // Transaction
             $transaction = new Transaction();
             $transaction->user_id = $user_id;
             $transaction->currency_id = $currency_id;
             $transaction->payment_method_id = $paymentMethodId;
             $transaction->transaction_reference_id = $deposit->id;
             $transaction->transaction_type_id = 1;
             $transaction->note = $note;
             $transaction->uuid = $uuid;
             $transaction->end_user_id=$end_user_id;
             $transaction->subtotal = $amount;
             $transaction->balance = $balance;
             $transaction->percentage =  0;
             $transaction->charge_percentage = $deposit->charge_percentage;
             $transaction->charge_fixed = $deposit->charge_fixed;
             $transaction->total = $amount ;
             $transaction->status = $status;
             $transaction->save();

            if($status != null){
                $wallet->balance = $wallet->balance + $amount;
                $wallet->save();
            }
            
            DB::commit();

            $data['transInfo']['currency_id'] = $transaction->currency->id;
            $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
            $data['transInfo']['subtotal'] = $transaction->subtotal;
            $data['transInfo']['id'] = $transaction->id;
            $data['transInfo']['note'] = $transaction->note;
            $data['users'] = User::find($user_id, ['id']);
            $data['transactionDetails'] = $transaction;

            return $data;
        }
        catch(\Exception $e){
            DB::rollBack();
            $data['error'] = $e->getMessage();
            return $data;
        }
        
    }

}