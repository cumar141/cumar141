<?php

namespace App\Services\Reports;
use App\Models\Transaction;
use App\Models\User;

class TransactionReport
{
    public function Transactions($phone=null, $userId=null, $currency=null, $startDate, $endDate)
    {
  
        $user = User::where('status', 'active');
        $transactions = Transaction::with('user', 'end_user', 'transaction_type', 'currency', 'payment_method')->where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'success');
        if(!empty($phone))
        {
            $user->where('phone', $phone);
            $userId = $user->first()->id;
        }

        if(!empty($currency))
        {
            $transactions->where('currency_id', $currency);
        }

        $transactions = $transactions->get();
        return $transactions;
    }
}