<?php

namespace App\Services\CashOut;

use App\Models\Transaction;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Models\User;
use App\Models\RequestPayment;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;
use App\Services\CashOut\Helper;

class Reject
{
    private $helper;

    public function __construct()
    {
        $this->helper = new Helper();
    }
    public function processRefund($uuid)
    {
        $transaction = Transaction::where('uuid', $uuid)->first();

        if (!$transaction) {
            return $this->returnError('Transaction not found');
        }

        if ($transaction->status == 'Blocked') {
            return $this->returnError('Transaction already Blocked');
        }

        if($transaction->transaction_type_id == 3) {
            return $this->blockTransfer($transaction);
        } elseif($transaction->transaction_type_id == 2) {
            return $this->blockWithdrawal($transaction);  
        } elseif($transaction->transaction_type_id == 7) {
            return $this->blockRequestPayment($transaction);
        } else {
            return $this->returnError('Transaction type not found');
        }
    }

    private function returnTrue($transaction, $user_id)
    {
        return [
            'transInfo' => [
                'currency_id' => $transaction->currency->id,
                'currSymbol' => $transaction->currency->symbol,
                'subtotal' => $transaction->subtotal,
                'id' => $transaction->id,
            ],
            'users' => User::find($user_id, ['id']),
            'transactionDetails' => $transaction,
        ];
    }
    
    private function blockTransfer($transaction)
    {
        $transfer = Transfer::where('id', $transaction->transaction_reference_id)->first();
        if (!$transfer) return $this->returnError('transfer not found');
        if ($transfer->status == 'Blocked') return $this->returnError('Deposit already Blocked');

        // Block transfer 
        $this->helper->setTransactionStatus($transfer, 'Blocked');
        $this->helper->setTransactionStatus($transaction, 'Blocked');

        // $this->helper->IncrementWalletAmount($transfer->sender_id, $transfer->currency_id, $transfer->amount);

        return $this->returnTrue($transaction, $transaction->user_id);
    }

    private function blockWithdrawal($transaction)
    {
        $withdrawal = Withdrawal::where('id', $transaction->transaction_reference_id)->first();
        if (!$withdrawal) return $this->returnError('Withdrawal not found');
        if ($withdrawal->status == 'Blocked') return $this->returnError('Withdrawal already Blocked');
      
        $this->helper->setTransactionStatus($withdrawal, 'Blocked');
        $this->helper->setTransactionStatus($transaction, 'Blocked');
        $this->helper->IncrementWalletAmount($transaction->user_id, $transaction->currency_id, $transaction->total);
        return $this->returnTrue($transaction, $transaction->user_id);
    }

    private function blockRequestPayment($transaction)
    {
        $request = RequestPayment::where('id', $transaction->transaction_reference_id)->first();
        if (!$request) return $this->returnError('Request not found');
        if ($request->status == 'Blocked') return $this->returnError('Request already Blocked');

        $this->helper->setTransactionStatus($request, 'Blocked');
        $this->helper->setTransactionStatus($transaction, 'Blocked');
        return $this->returnTrue($transaction, $transaction->user_id);
    }

    public function returnError($message)
    {
        return ['error' => $message];
    }
}
