<?php

namespace App\Services\PayoutGateway;

use Illuminate\Support\Facades\Http;
use App\Models\{
    User,
    Wallet,
    Deposit,
    Withdrawal,
    Transaction,
    Organization
};

use App\Services\SmsService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use DB, Exception;

class TrueShilling {
    protected $AutoTellerIDs = [1];
    
    public function send($data) {
        try {
            DB::beginTransaction();
            $currency = 1; //USD
            $amount = $data["amount"];
            $accountPhone = $data["account"]; 
            $note = $data["description"];
            
            $organization = Organization::where("id", $data["organization"])->first();
            if(!$organization) throw new Exception("Organization must be valid");
            
            $customer = User::where("formattedPhone", $accountPhone)
                ->orWhere("phone", $accountPhone)
                ->orWhere("phone1", $accountPhone)
                ->orWhere("phone2", $accountPhone)
                ->orWhere("phone3", $accountPhone)
            ->first();
            if(!$customer) throw new Exception("Customer must be valid");
            
            $teller = User::where("id", $this->AutoTellerIDs[0])->first();
            if(!$teller) throw new Exception("Teller must be valid");
            
            $customerWallet = Wallet::firstOrCreate(['user_id' => $customer->id, 'currency_id' => $currency], ['balance' => 0]);
            $tellerWallet = Wallet::firstOrCreate(['user_id' => $teller->id, 'currency_id' => $currency], ['balance' => 0]);
            if($tellerWallet->balance < $amount) throw new Exception('Teller balance not enough!');
            
            $status = $this->tellerWithdrawal($tellerWallet, $customerWallet, $currency, $amount, $note, $organization) && $this->customerDeposit($tellerWallet, $customerWallet, $currency, $amount, $note, $organization);
            if(!$status) throw new Exception('Either of the transactions failed!');
            $date = date("d/m/Y H:i:s", time());
            (new SmsService)->sendSMS($accountPhone, 'Deposit', "[-somxchange-] waxaad \${$amount} ka heshay {$organization->name}, Tar: {$date} oo ah {$note}");
            DB::commit();
            
            return ["status" => "success", "message" => "Transaction was successful"];
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            return ["status" => "failed", "message" => "Information not found."];
        } catch(\Exception $ex) {
            DB::rollback();
            return ["status" => "failed", "message" => $ex->getMessage()];
        }
        
    }

    protected function tellerWithdrawal($tellerWallet, $customerWallet, $currency, $amount, $note, $organization)
    {
        $uuid = unique_code();
        $teller = $tellerWallet->user_id;
        $customer = $customerWallet->user_id;
        $balance = $tellerWallet->balance - $amount;
    
        $withdrawal = new Withdrawal();
        $withdrawal->user_id = $teller;
        $withdrawal->currency_id = $currency;
        $withdrawal->payment_method_id = 1;
        $withdrawal->uuid = $uuid;
        $withdrawal->charge_percentage = 0;
        $withdrawal->charge_fixed =  0;
        $withdrawal->subtotal = $amount;
        $withdrawal->amount = $amount;
        $withdrawal->balance = $balance;
        $withdrawal->status = 'Success';
        $withdrawal->save();
    
        // Transaction
        $transaction = new Transaction();
        $transaction->user_id = $teller;
        $transaction->end_user_id = $customer; 
        $transaction->currency_id = $currency;
        $transaction->payment_method_id = 1;
        $transaction->transaction_reference_id = $withdrawal->id;
        $transaction->transaction_type_id = Withdrawal;
        $transaction->uuid = $uuid;
        $transaction->subtotal = $amount;
        $transaction->percentage = 0;
        $transaction->charge_percentage = $withdrawal->charge_percentage;
        $transaction->charge_fixed = $withdrawal->charge_fixed;
        $transaction->note = "{$note} FROM {$organization->name}";
        $transaction->total = '-'.$amount ;
        $transaction->balance = $balance;
        $transaction->status = 'Success';
        $transaction->save();
        
        $tellerWallet->balance -= $amount;
        $tellerWallet->save();
        
        return true;
    }
    
    protected function customerDeposit($tellerWallet, $customerWallet, $currency, $amount, $note, $organization)
    {
        $uuid = unique_code();
        $teller = $tellerWallet->user_id;
        $customer = $customerWallet->user_id;
        $balance = $customerWallet->balance + $amount;

        // Deposit
        $deposit = new Deposit();
        $deposit->user_id = $customer;
        $deposit->currency_id = $currency;
        $deposit->payment_method_id = 1;
        $deposit->uuid = $uuid;
        $deposit->charge_percentage = 0;
        $deposit->charge_fixed = 0;
        $deposit->amount = $amount;
        $deposit->balance = $balance;
        $deposit->status = 'Success';
        $deposit->save();

        // Transaction
        $transaction = new Transaction();
        $transaction->user_id = $customer;
        $transaction->end_user_id = $teller;
        $transaction->currency_id = $currency;
        $transaction->payment_method_id = 1;
        $transaction->transaction_reference_id = $deposit->id;
        $transaction->transaction_type_id = Deposit;
        $transaction->note = "{$note} FROM {$organization->name}";
        $transaction->uuid = $uuid;
        $transaction->subtotal = $amount;
        $transaction->percentage =  0;
        $transaction->charge_percentage = $deposit->charge_percentage;
        $transaction->charge_fixed = $deposit->charge_fixed;
        $transaction->total = $amount;
        $transaction->balance = $balance;
        $transaction->status = 'Success';
        $transaction->save();
        
        $customerWallet->balance += $amount;
        $customerWallet->save();

        return true;
    }
}