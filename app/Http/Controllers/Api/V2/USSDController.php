<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;

use App\Models\{User,
PaymentMethod,
Currency,
Transfer
};

use App\Services\{WalletService,
RegistrationService,
WithdrawalMoneyService,
SendMoneyService,
FirebaseService,
QrCodeService,
SmsService
};

use App\Jobs\waafiShowJob;
use App\Http\Helpers\Common;
use Illuminate\Http\Request;
use DB, Exception;

class USSDController extends Controller
{  
    public function check_balance(Request $request) {
        try{
            $user = User::where("id", $request->user_id)->firstOrFail();
            $wallet = (new WalletService)->defaultWalletBalance($request->user_id);
            $balance = str_replace("USD ", "", $wallet["defaultWalletBalance"]);
            return response()->json(["success" => true, "balance" => $balance]);
        }
        catch(Throwable $e) {
            return response()->json(["success" => false]);
        }
    }
    
    public function get_service_charge(Request $request) {
        $helper = new Common();
        $payment_method = PaymentMethod::where(["name" => $request->provider, "status" => "Active"])->firstOrFail();
        $fee = $helper->transactionFees(1, $request->amount, Withdrawal, $payment_method->id);
        return response()->json(["service_charge" => $fee]);
    }
    
    public function register_customer(Request $request) {
        try {
            $user = new \stdClass();
            $user->first_name = $request->customer_phone;
            $user->last_name = $request->customer_phone;
            $user->email = $request->customer_phone . "@somxchange.com";
            $user->formattedPhone = $request->customer_phone;
            $user->password = $request->password;
            $user->type = 'user';
            $user->defaultCountry = "so";
            $user->carrierCode = "252";
            $user->phone = str_replace("+252", "", $request->customer_phone);
            
            $response = (new RegistrationService())->userRegistration($user);
            
            return response()->json(["success" => $response["status"]]);
        }
        catch(Throwable $e) {
            return response()->json(["success" => false]);
        }
    }
    
    public function change_pin(Request $request) {
        try {
            $user = User::where("id", $request->user_id)->firstOrFail();
            
            $user->password = \Hash::make($request->password);
            
            $user->save();
            return response()->json(["success" => true]);
        }
        catch(Throwable $e) {
            return response()->json(["success" => false]);
        }
    }
    
    public function withdraw(Request $request) {
        try {
            DB::beginTransaction();
            $helper = new Common();
            $currency = Currency::where(["symbol" => "$"])->firstOrFail(); //USD OR DEFAULT
            $amount = $request->amount;
            $payment_method = PaymentMethod::where(["name" => $request->provider, "status" => "Active"])->firstOrFail();
            $fees = $helper->transactionFees($currency->id, $request->amount, Withdrawal, $payment_method->id);
            $totalAmount = $fees->total_amount;
            $user = User::where("id", $request->user_id)->firstOrFail();
            $helper->checkWalletAmount($user->id, $currency->id, $amount);
            $response = (new WithdrawalMoneyService($helper))->withdrawalConfirm(
                $user->id,
                $currency->id,
                $amount,
                $totalAmount,
                $payment_method->id,
                $request->details
            );
            
            DB::commit();
            
            return response()->json(["success" => true, "uuid" => $response["uuid"] ?? "N/A"]);
        }
        catch(Exception $e) {
            DB::rollBack();
            return response()->json(["success" => false, "error" => $e->getMessage()]);
        }
    }
    
    public function transfer(Request $request) {
        try {
            $helper = new Common();
            $currency = Currency::where(["symbol" => "$"])->firstOrFail(); //USD OR DEFAULT
            $user = User::where("id", $request->user_id)->firstOrFail();
            
            $receiver = User::where("formattedPhone", $request->receiver)
                ->orWhere("phone1", $request->receiver)
                ->orWhere("phone2", $request->receiver)
                ->orWhere("phone3", $request->receiver)
                ->firstOrFail();
                
            $amount = $request->amount;
            
            // Check if the receiver is not verified
            if (!$receiver->identity_verified) {
                throw new SendMoneyException(__("Xogta qofka lacagta loo dirayo ma dhameystirna"));
            }
            
            if($receiver->id == $user->id) {
                throw new SendMoneyException(__("Adiga lacag ma isku diri kartid."));
            }
            
            $currencyFee = $helper->transactionFees($currency->id, $amount, Transferred);
    
            $helper->amountIsInLimit($currencyFee, $amount);
    
            $helper->checkWalletAmount($user->id, $currency->id, $currencyFee->total_amount);
    
            $senderWallet = $helper->getWallet($user->id, $currency->id);
    
            $arr = [
                'emailFilterValidate' => null,
                'phoneRegex' => $request->receiver,
                'processedBy' => "phone",
                'user_id' => $user->id,
                'currency_id' => $currency->id,
                'uuid' => unique_code(),
                'fee' => $currencyFee->total_fees,
                'amount' => $amount,
                'note' => "Transfer through USSD",
                'receiver' => $request->receiver,
                'charge_percentage' => $currencyFee->charge_percentage,
                'charge_fixed' => $currencyFee->charge_fixed,
                'p_calc' => $currencyFee->fees_percentage,
                'total' => $currencyFee->total_amount,
                'senderWallet' => $senderWallet,
            ];
    
            if (!is_null($receiver)) {
                $arr['userInfo'] = $receiver;
            }

            DB::beginTransaction();
            //Create Transfer
            $transfer = new Transfer();
            $transfer = $transfer->createTransfer($arr);
            //Create Transferred Transaction
            $arr['transaction_reference_id'] = $transfer->id;
            $arr['status']                   = $transfer->status;
            $transfer->createTransferredTransaction($arr);
            //Create Received Transaction
            $transfer->createReceivedTransaction($arr);
            //Update Sender Wallet
            $SenderBalance = $transfer->updateSenderWallet($arr['senderWallet'], $arr['total']);
            //Create Or Update Receiver Wallet
            $arr['transfer_receiver_id'] = $transfer->receiver_id;
            $ReceiverBalance = $transfer->createOrUpdateReceiverWallet($arr);

            // Sms & Email
            // $this->notificationToSender($transfer, true, $SenderBalance);
            // $this->notificationToReceiver($transfer, true, $ReceiverBalance);
            // $this->notificationToAdmin($transfer);
            
            //send notification
            (new FirebaseService())->send_transaction_notification($transfer->receiver_id, $transfer->amount, 'received_money',$transfer->currency_id, $transfer->sender_id);
            (new FirebaseService())->send_transaction_notification($transfer->sender_id, $transfer->amount, 'send_money',$transfer->currency_id, $transfer->receiver_id);
            DB::commit();
            return response()->json(["success" => true, "uuid" => $arr['uuid']]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(["success" => false, "error" => $e->getMessage()]);
        }
    }
    
    public function merchant_payment(Request $request) {
        try {
            DB::beginTransaction();
            $helper = new Common();
            $service = new QrCodeService(new WithdrawalMoneyService($helper));
            $service->user_id = $request->user_id;
            $merchant = $service->merchantPaymentDetailsByNumber($request->merchant_id, 1);
            if(!is_array($merchant) && !isset($merchant["merchantId"])) return response()->json(["success" => false, "error" => "Merchant-iga ma ahan mid jira"]);
            $payment_info = $service->merchantPaymentReview($merchant["merchantId"], "USD", $request->amount, 1);
            if(!is_array($payment_info) && !isset($payment_info["status"])) return response()->json(["success" => false, "error" => $payment_info]);
            $fee = $payment_info["merchantCalculatedChargePercentageFee"] ?? $payment_info["merchantActualFee"];
            $payment = $service->qrPaymentSubmit($payment_info["merchantUserId"], $merchant["merchantId"], 1, $payment_info["merchantPaymentAmount"], $fee, "Payment made from USSD", 1);
            DB::commit();
            return response()->json(["success" => true, "uuid" => $payment["payment_id"]]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(["success" => false, "error" => $e->getMessage()]);
        }
        
    }
    
    public function waafi_show(Request $request) {
        try {
            waafiShowJob::dispatch($request->all())->onQueue("main");
            return response()->json(["success" => true]);
        } catch (Exception $e) {
            return response()->json(["success" => false]);
        }
    }
    
    public function send_sms(Request $request) {
        try {
            $smsService = (new SmsService);
            foreach($request->smsBatch as $sms) {
                $smsService->sendSMS($sms['recipient'], 'USSD', $sms['body']);
            }
            return response()->json(["success" => true]);
        } catch(\Exception $ex) {
            return response()->json(["success" => false]);
        }
    }
}

