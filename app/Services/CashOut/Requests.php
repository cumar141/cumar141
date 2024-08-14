<?php

namespace App\Services\CashOut;

use App\Http\Helpers\Common;
use App\Models\FeesLimit;
use App\Models\RequestPayment;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\CashOut\Helper;

class Requests
{
    private $helper;
    
    public function __construct()
    {
        $this->helper = new Helper();
    }

    public function RequestSent($user_id, $receiver_id, $currencyId, $amount, $accept_amount, $phone, $email, $note, $paymentMethodId, $status = null)
    {
        $data = [];
        $uuid = unique_code();
        $status = $status ?? 'Pending';

        try {
            DB::beginTransaction();
            // Withdrawal
            $requests = new RequestPayment();
            $requests->user_id = $user_id;
            $requests->receiver_id = $receiver_id;
            $requests->currency_id = $currencyId;
            $requests->uuid = $uuid;
            $requests->phone = $phone;
            $requests->amount = $amount;
            $requests->accept_amount = $accept_amount;
            $requests->status = $status;
            $requests->note = $note;
            $requests->email = $email;
            $requests->save();

            // Transaction
            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->currency_id = $currencyId;
            $transaction->payment_method_id = $paymentMethodId;
            $transaction->transaction_reference_id = $requests->id;
            $transaction->transaction_type_id = Request_Sent;
            $transaction->note = $note;
            $transaction->status = $status;
            $transaction->end_user_id = $receiver_id;
            $transaction->uuid = $uuid;
            $transaction->save();

            // Update wallet
            $this->helper->DecrementWalletAmount($user_id, $currencyId, $amount);

            DB::commit();
            $data['transaction'] = $transaction;
            $data['user_id'] = $user_id;
            return $data;
        } catch (\Exception $e) {
            DB::rollBack();
            $data['error'] = 'An error occurred while processing the transactions.';
            return $data;
        }
    }
    public function RequestReceived(RequestPayment $requestPayment, $transaction)
    {
        $amount = $requestPayment->amount;
        $currencyId = $requestPayment->currency_id;
        $user_id = $requestPayment->user_id;
        $receiver_id = $requestPayment->receiver_id;
        $note = $requestPayment->note;
        $status = 'Success';
        $uuid = $requestPayment->uuid;
        $amount = $requestPayment->amount;

        $sender = User::find($receiver_id);
        $receiver = User::find($user_id);

        $res = $this->helper->checkBalanceAganistAmount($sender->id, $currencyId, $amount);
        if (!$res) {
            $data['error'] = 'Insufficient balance';
            return $data;
        }

        $wallet = $this->helper->getWallet($sender->id, $currencyId);
        $walletBalance = $wallet->balance;
        $receiverBalance = $walletBalance - $amount;

        try {
            DB::beginTransaction();
            
            // Update wallet
            $this->helper->IncrementWalletAmount($receiver->id, $currencyId, $amount);
            $this->helper->DecrementWalletAmount($sender->id, $currencyId, $amount);
            
            $transaction->balance = $this->helper->getWalletBalance($transaction->user_id, $transaction->currency_id);;
            $transaction->status = "Success";
            $transaction->save();
            
            // Withdrawal
            $requestPayment->status = "Success";
            $requestPayment->accept_amount = $amount;
            $requestPayment->save();

            // Transaction
            $transaction = new Transaction();
            $transaction->user_id = $sender->id;
            $transaction->end_user_id = $receiver->id;
            $transaction->currency_id = $currencyId;
            $transaction->uuid = $uuid;
            $transaction->transaction_type_id = 8;
            $transaction->transaction_reference_id = $requestPayment->id;
            $transaction->note = $note;
            $transaction->status = $status;
            $transaction->subtotal = $amount;
            $transaction->balance = $receiverBalance;
            $transaction->percentage = 0;
            $transaction->payment_method_id = 1;
            $transaction->charge_percentage = 0;
            $transaction->charge_fixed = 0;
            $transaction->total = '-' . $amount;
            $transaction->status = 'Success';
            $transaction->save();

            $data['transInfo']['currency_id'] = $transaction->currency->id;
            $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
            $data['transInfo']['subtotal'] = $transaction->subtotal;
            $data['transInfo']['id'] = $transaction->id;
            $data['transInfo']['note'] = $transaction->note;
            $data['users'] = User::find($sender->id);
            $data['transactionDetails'] = $transaction;

            DB::commit();
            return $data;
        } catch (\Exception $e) {
            DB::rollBack();
            $data['error'] = $e->getMessage();
            return $data;
        }
    }
}