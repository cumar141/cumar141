<?php

namespace App\Services\CashOut;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transfer;
use App\Services\CashOut\Helper;
use Illuminate\Support\Facades\DB;

class Received
{
    private $helper;
    
    public function __construct()
    {
        $this->helper = new Helper();
    }

    public function createReceived(Transfer $transfer, $senderTransaction)
    {
        $sender_id = $transfer->sender_id;
        $receiver_id = $transfer->receiver_id;
        $currency_id = $transfer->currency_id;
        $amount = $transfer->amount;
        $note = $transfer->note;
        $uuid = unique_code();
        $status = "Success";
        $user = User::find($receiver_id);
        $email = $user->email ?? '';
        $phone = $user->formattedPhone ?? '';
        
        try {
            DB::beginTransaction();
            $senderWallet = Wallet::firstOrCreate(['user_id' => $sender_id, 'currency_id' => $currency_id], ['balance' => 0]);
            $senderWallet->balance = $senderWallet->balance - $amount;
            $senderWallet->save();
            
            $receiverWallet = Wallet::firstOrCreate(['user_id' => $receiver_id, 'currency_id' => $currency_id], ['balance' => 0]);
            $receiverWallet->balance = $receiverWallet->balance + $amount;
            $receiverWallet->save();
            
            $transfer->email = $email;
            $transfer->phone = $phone;
            $transfer->status = 'Success';
            $transfer->save();
            
            $received = new Transfer();
            $received->sender_id = $sender_id;
            $received->receiver_id = $receiver_id;
            $received->currency_id = $currency_id;
            $received->uuid = $uuid;
            $received->amount = $amount;
            $received->note = $note;
            $received->status = $status;
            $received->save();

            // Transaction
            $transaction = new Transaction();
            $transaction->user_id = $receiver_id;
            $transaction->end_user_id = $sender_id;
            $transaction->currency_id = $currency_id;
            $transaction->uuid = $uuid;
            $transaction->transaction_type_id = 4;
            $transaction->transaction_reference_id = $transfer->id;
            $transaction->note = $note;
            $transaction->status = $status;
            $transaction->subtotal = $amount;
            $transaction->balance = $receiverWallet->balance;
            $transaction->percentage = 0;
            $transaction->payment_method_id = 1;
            $transaction->charge_percentage = 0;
            $transaction->charge_fixed = 0;
            $transaction->total = $amount;
            $transaction->status = 'Success';
            $transaction->save();

            // Sender Transaction Update
            
            $senderTransaction->balance = $senderWallet->balance;
            $senderTransaction->status = "Success";
            $senderTransaction->save();
            
            $data['transInfo']['currency_id'] = $transaction->currency->id;
            $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
            $data['transInfo']['subtotal'] = $transaction->subtotal;
            $data['transInfo']['id'] = $transaction->id;
            $data['transInfo']['note'] = $transaction->note;
            $data['users'] = User::find($sender_id);
            $data['transactionDetails'] = $transaction;

            DB::commit();
            return $data;
        } catch (\Exception $e) {
            DB::rollBack();
            $data['error'] =  $e->getMessage();
            return $data;
        }
    }
}