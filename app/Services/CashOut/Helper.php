<?php

namespace App\Services\CashOut;
use App\Models\{
    Transaction,
    Wallet,
    Withdrawal,
    User
};
use DB;

class Helper
{
    public function checkBalanceAganistAmount($user_id, $currencyId, $amount)
    {
        $wallet = $this->getWallet($user_id, $currencyId);
        if (!$wallet || $wallet->balance < $amount) {
            return false;
        }
        return true;
    }

    public function getWallet($user_id, $currencyId)
    {
        $wallet =  Wallet::firstOrCreate(['user_id' => $user_id, 'currency_id' => $currencyId], ['balance' => 0]);

        if ($wallet) {
            return $wallet;
        }
        return null;
    }

    public function CheckIfUserIsSendingToHimself($user_id, $end_user_id)
    {
        if ($user_id == $end_user_id) {
            return true;
        }
        return false;
    }

    public function checkIfUserHasWallet($user_id, $currencyId)
    {
        $wallet = $this->getWallet($user_id, $currencyId);
        if ($wallet) {
            return true;
        }
        return false;
    }

    public function CheckIfUserIsActive($user_id)
    {
        $user = User::find($user_id);
        if ($user->status == 'active') {
            return true;
        }
        return false;
    }

    public function IncrementWalletAmount($user_id, $currencyId, $amount)
    {
       
        try {
            $wallet = $this->getWallet($user_id, $currencyId);
            DB::beginTransaction();
            $wallet->balance += $amount;
            $wallet->save();
            DB::commit();
           
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function DecrementWalletAmount($user_id, $currencyId, $amount)
    {
        
        try {
            $wallet = $this->getWallet($user_id, $currencyId);
            DB::beginTransaction();
            $wallet->balance -= $amount;
            $wallet->save();

            DB::commit();
           
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }

    public function setTransactionStatus($transaction, $status)
    {
        try {
            DB::beginTransaction();
            $transaction->update(['status'=> $status]);
            DB::commit();
           
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function CheckAmountInLimit($amount)
    {
        if ($amount >= 0.1) {
            return true;
        }
        return false;
    }

    public function getWalletBalance($user_id, $currencyId)
    {
        $wallet = $this->getWallet($user_id, $currencyId);
        if ($wallet) {
            return $wallet->balance;
        }
        
        return 0;
    }
}