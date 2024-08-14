<?php

namespace App\Services;

use App\Models\{
    SmsQueue,
    PhoneOTP,
    SmsConfig,
    PaymentVerification,
    Deposit,
    Transaction,
    Wallet,
    PaymentMethod
};
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PaymentGatewayVerificationService {
    protected $request;
    
    public function process($request)
    {
        try{
            $this->request = $request;
            $verification_method = null;
            if (isset($request->platform)) {
                $verification_method = lcfirst($request->platform) . "Verify";
            } else {
                $payment_method = PaymentMethod::where("id", $request->payment_method_id)->firstOrFail()->name;
                if($payment_method) $verification_method = lcfirst($payment_method) . "Verify";
            }
            
            if(method_exists($this, $verification_method)) {
                return $this->$verification_method();
            }
            return [
                'status' => false,
                'success' => false,
                'message' => "You have selected invalid verification method",
            ];
        } catch (\Exception $ex) {
            return [
                'status' => false,
                'success' => false,
                'message' => $ex->getMessage(),
            ];
        }
    }
    
    public function premierWalletVerify() {
        try{
            $filter = isset($this->request->transaction) ? ["transaction_id" => $this->request->transaction] : ["uuid" => $this->request->reference];
            $transaction = PaymentVerification::where($filter)->first();
            $this->token = $this->verifyPremierloginMerchant();
            
            $url = "https://api.premierwallets.com/api/GetPaymentDetails";
            $headers = [
                'Content-Type: application/json',
                'MachineID: '.env("PREMIER.MACHINEID"),
                'ChannelID: 104',
                'DeviceType: 205',
                'Authorization: Bearer ' .$this->token['Token']
            ];
            
            $data = [
                "TransactionID" => $transaction->transaction_id,
                "LoginUserName" => "911808"
            ];
            
            $postfields = json_encode($data);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS,  $postfields);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($curl);
            $response = json_decode($result, true);
            if ($response['Response']['Code']==001){
                if ($response['Data']['Status']=="Executed"){
                    $status = true;
            
                    if($status) {
                        $deposit = Deposit::where('uuid', $transaction->uuid)->where('status', '!=', 'Success')->first();
                        if($deposit) {
                            $wallet = Wallet::firstOrCreate(
                                ['user_id' => $deposit->user_id, 'currency_id' => $deposit->currency_id],
                                ['balance' => 0]
                            );
                            $balance = $wallet->balance += $deposit->amount;
                            $wallet->save();
                            $deposit->update(["balance" => $balance,"status" => "Success"]);
                            $transaction->update(["balance" => $balance,"status" => "Success", "paid_at" => Carbon::now()]);
                            Transaction::where('uuid', $transaction->uuid)->update(["payment_status" => "Success", "status" => "Success"]);
                        }
                    }
                    
                }else{
                    $status = false;  
                }
                
                if ($response['Data']['Status']=="Rejected"){
                    $IsRejected = true;
                   
                }
                else{
                    $IsRejected = false; 
                }
                return [
                    'success' => $status,
                    'IsRejected' => $IsRejected,
                    'message' => $response['Data']['Status'],
                    'CustomerName' => $response['Data']['CustomerName'],
                    'TransactionId' => $response['Data']['TransactionId'],
                ];
                
            }else{
                
                return [
                    'success' => false,
                    'message' => $response['Response']['Errors'][0]['Message'],
                ];
            }
            
        } catch (\Exception $ex) {
           
            return [
                'success' => false,
                'message' => $ex->getMessage(),
            ];
        }
    }
    
    public function verifyPremierloginMerchant() {
        try {
            $url = "https://api.premierwallets.com/api/MerchantLogin";
            $headers = [
                'Content-Type: application/json',
                'MachineID: '.env("PREMIER.MACHINEID"),
                'ChannelID: 104',
                'DeviceType: 205',
                'Authorization: Basic '.env("PREMIER.MERCHANTTOKEN")
            ];
            
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($curl);
            $resObj = json_decode($result);
            curl_close($curl);
            
            $data = json_decode($result, true);
               
            return [
                'status' => true,
                'Token' => $data['Data']['Token'],
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    public function somtelVerify(){
        try{
            $filter = isset($this->request->transaction) ? ["transaction_id" => $this->request->transaction] : ["uuid" => $this->request->reference]; //this is not working so I make uncomment
            $transaction = PaymentVerification::where($filter)->first();
            $request_param = ["apiKey" => env("SOMTEL.APIKEY"), "invoiceId" => $transaction->transaction_id];
            $json = json_encode($request_param, JSON_UNESCAPED_SLASHES);
            $hashed = hash('SHA256', $json."sjBxisw1DViqeZGQdeUBfrhzSkxP7XfBTuVtM5");
            $url = "https://edahab.net/api/api/CheckInvoiceStatus?hash=".$hashed;
            $headers = [
                'Content-Type: application/json'
            ];
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS,  $json);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_VERBOSE, true); 
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($curl);
           
            $response = json_decode($result, true);
            $status = $response['InvoiceStatus'] == "Paid";
            
            if($status) {
                $deposit = Deposit::where('uuid', $transaction->uuid)->where('status', '!=', 'Success')->first();
                if($deposit) {
                    $wallet = Wallet::firstOrCreate(
                        ['user_id' => $deposit->user_id, 'currency_id' => $deposit->currency_id],
                        ['balance' => 0]
                    );
                    $balance = $wallet->balance += $deposit->amount;
                    $wallet->save();
                    $deposit->update(["balance" => $balance, "status" => "Success"]);
                    $transaction->update(["balance" => $balance, "status" => "Success", "paid_at" => Carbon::now()]);
                    Transaction::where('uuid', $transaction->uuid)->update(["balance" => $wallet->balance, "payment_status" => "Success", "status" => "Success"]);
                }
            }
            
            return [
                'success'    => $status,
                'message'   => 'Invoice ' .$response['InvoiceStatus'],
                'status'    => $response['InvoiceStatus'],
            ];
        } catch (\Exception $ex) {
            return [
                'success' => false,
                'message' => $ex->getMessage(),
            ];
        }
    }
    
    //TPlusWallet verify
    public function tplusWalletVerify(){
        try{
            $transaction = PaymentVerification::where("transaction_id", $this->request->transaction)->first();
            if($transaction) {
                $deposit = Deposit::where('uuid', $transaction->uuid)->where('status', '!=', 'Success')->first();
                if($deposit) {
                    $wallet = Wallet::firstOrCreate(
                        ['user_id' => $deposit->user_id, 'currency_id' => $deposit->currency_id],
                        ['balance' => 0]
                    );
                    $balance = $wallet->balance += $deposit->amount;
                    $wallet->save();
                    $deposit->update(["balance" => $balance, "status" => "Success"]);
                    $transaction->update(["balance" => $balance, "status" => "Success", "paid_at" => Carbon::now()]);
                    Transaction::where('uuid', $transaction->uuid)->update(["balance" => $wallet->balance, "payment_status" => "Success", "status" => "Success"]);
                
                    return [
                        'success'    => true,
                        'message'   => 'successful paid'
                    ];
                }
                return [
                    'success'    => false,
                    'message'   => 'already paid'
                ];
            }
                return [
                    'success'    => false,
                    'message'   => 'transaction not found'
                ];
            
        } catch (\Exception $ex) {
            return [
                'success' => false,
                'message' => $ex->getMessage(),
            ];
        }
    }

}