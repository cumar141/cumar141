<?php

namespace App\Services\CashOut;

use App\Models\Transaction;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\Transfer;
use App\Models\Wallet;
use App\Models\Currency;
use App\Models\User;
use App\Models\RequestPayment;
use Illuminate\Support\Facades\DB;
use App\Services\CashOut\Deposits;
use App\Services\CashOut\Requests;
use App\Services\CashOut\Withdrawals;
use App\Services\CashOut\Received;
use App\Services\CashOut\Helper;

class Approve
{
    private $helper;
    
    public function __construct()
    {
        $this->helper = new Helper();
    }
    
    public function processApprove($uuid)
    {
        $transaction = Transaction::where('uuid', $uuid)->first();

        if (!$transaction) {
            return $this->returnError('Transaction not found with UUID: ' . $uuid);
        }

        if ($transaction->status == 'Success') {
            return $this->returnError('Transaction already approved for UUID: ' . $uuid);
        }

        switch ($transaction->transaction_type_id) {
            case 1:
                $data = $this->approveDeposit($transaction);
                if (is_array($data)) {
                    return $data;
                } else {
                    return $this->returnError('Failed to generate receipt for transaction: ');
                }
            case 2:
                $data = $this->approveWithdrawal($transaction);
                if (is_array($data)) {
                    return $data;
                } else {
                    return $this->returnError('Failed to generate receipt for transaction: ');
                }
            case 7:
                $data = $this->approveRequestPayment($transaction);
                if (is_array($data)) {
                    return $data;
                } else {
                    return $this->returnError('Failed to generate receipt for transaction: ');
                }
            case 3:
                $data = $this->approveTransfer($transaction);
                if (is_array($data)) {
                    return $data;
                } else {
                    return $this->returnError('Failed to process transfer for transaction: ');
                }
            default:
                return $this->returnError('Transaction type not found for UUID: ' . $uuid);
        }
    }

    private function returnError($message)
    {
        return ['error' => $message];
    }
    
    private function approveDeposit($transaction)
    {
        $deposit = Deposit::find($transaction->transaction_reference_id);
        if (!$deposit) return $this->returnError('Deposit not found');
        if ($deposit->status == 'Success') return $this->returnError('Deposit already approved');


        $this->helper->setTransactionStatus($deposit, 'Success');
        $this->helper->setTransactionStatus($transaction, 'Success');

        $user = User::find($deposit->user_id);
        if (!$user) return $this->returnError('User not found');
        return $deposit;
    }

    private function approveWithdrawal($transaction)
    {
        $withdrawal = Withdrawal::find($transaction->transaction_reference_id);
        if (!$withdrawal) return $this->returnError('Withdrawal not found');
        if ($withdrawal->status == 'Success') return $this->returnError('Withdrawal already approved');
       
        $user_id = $transaction->end_user_id;
        $currency_id = $transaction->currency_id;
        $amount = str_replace("-", '' ,$transaction->total);
        $end_user_id = $transaction->user_id;
        $note = $transaction->note;
        $paymentMethodId = $transaction->payment_method_id;
        $status = $transaction->status;

        $deposits = new Deposits();
        $result = $deposits->processDeposit($user_id, $currency_id, $amount, $end_user_id, $note, $paymentMethodId,  'Success');

        if (isset($result['error'])) {
            return  $this->returnError($result['error']);
        } 
        $this->helper->setTransactionStatus($withdrawal, 'Success');
        $walletBalance = $this->helper->getWalletBalance($transaction->user_id, $transaction->currency_id);
        $balance = $walletBalance - abs($transaction->total);
        $transaction->balance = $balance;
        $transaction->status = "Success";
        $transaction->save();
        return $result;
    }

    private function approveRequestPayment($transaction)
    {
        $request = RequestPayment::find($transaction->transaction_reference_id);
        if (!$request) return $this->returnError('Request not found');
        if ($request->status == 'Success') return $this->returnError('Request already approved');

        $requests = new Requests();
        $result = $requests->RequestReceived($request, $transaction);
        if (isset($result['error'])) {
          return  $this->returnError($result['error']);
        }

        return $result;
    }

    public function approveTransfer($transaction)
    {
        $transfer = Transfer::find($transaction->transaction_reference_id);
        if (!$transfer) return $this->returnError('Transfer not found');
        if ($transfer->status == 'Success') return $this->returnError('Transfer already approved');
        if (!$this->helper->checkBalanceAganistAmount($transaction->user_id, $transaction->currency_id, $transfer->amount)) return $this->returnError('Not enough balance');
        
        $received = new Received();
        $result = $received->createReceived($transfer, $transaction);

        if (isset($result['error'])) {
            return $this->returnError($result['error']);
        }
        
        return $result;

    }
}
