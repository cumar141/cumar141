<?php

namespace App\Services\CashOut;

use App\Http\Helpers\Common;
use App\Models\FeesLimit;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\CashOut\Helper;

class Withdrawals
{
    private $helper;
    
    public function __construct()
    {
        $this->helper = new Helper();
    }

    public function processWithdrawal($user_id, $currencyId, $amount, $end_user_id, $note, $paymentMethodId, $status = null)
    {
        $data = [];
        $uuid = unique_code();
        $status = $status ?? 'Pending';
        if (!$this->checkBalance($user_id, $currencyId, $amount)) {
            $data['error'] = 'Insufficient balance';
            return $data;
        }
        $wallet = $this->helper->getWallet($user_id, $currencyId);
        if($wallet == false) {
            $data['error'] = 'Wallet not found';
            return $data;
        }

        $balance = $wallet->balance - $amount;

        try {
            DB::beginTransaction();
            // Withdrawal
            $withdrawal = new Withdrawal();
            $withdrawal->user_id = $user_id;
            $withdrawal->currency_id = $currencyId;
            $withdrawal->payment_method_id = $paymentMethodId;
            $withdrawal->uuid = $uuid;
            $withdrawal->charge_percentage = 0;
            $withdrawal->charge_fixed = 0;
            $withdrawal->subtotal = $amount;
            $withdrawal->amount = $amount;
            $withdrawal->balance = $balance;
            $withdrawal->status = $status;
            $withdrawal->save();

            // Transaction
            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->currency_id = $currencyId;
            $transaction->payment_method_id = $paymentMethodId;
            $transaction->transaction_reference_id = $withdrawal->id;
            $transaction->transaction_type_id = 2;
            $transaction->uuid = $uuid;
            $transaction->end_user_id = $end_user_id;
            $transaction->subtotal = $withdrawal->amount;
            $transaction->balance = $balance;
            $transaction->percentage = 0;
            $transaction->charge_percentage = $withdrawal->charge_percentage;
            $transaction->charge_fixed = $withdrawal->charge_fixed;
            $transaction->total = '-' . ($withdrawal->amount);
            $transaction->status = $status;
            $transaction->note = $note;
   
            $transaction->save();

            $wallet->balance -= $amount;
            $wallet->save();

            DB::commit();
            $data['transInfo']['currency_id'] = $transaction->currency->id;
            $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
            $data['transInfo']['subtotal'] = $transaction->subtotal;
            $data['transInfo']['id'] = $transaction->id;
            $data['users'] = User::find($user_id, ['id']);
            $data['transactionDetails'] = $transaction;

            return $data;
        } catch (\Exception $e) {
            DB::rollBack();
            $data['error'] = $e;
            return $data;
        }


    }

    public function checkBalance($userId, $currencyId, $amount)
    {
        $wallet = Wallet::where(['user_id' => $userId, 'currency_id' => $currencyId])->first(['id', 'currency_id', 'balance']);
        if ($wallet->balance < $amount) {
            return false;
        }
        return true;
    }
}