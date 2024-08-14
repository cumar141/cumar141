<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Jobs\SendSMS;
use App\Models\{MerchantGroup, Merchant, MerchantOperator};
use Illuminate\Http\{Request,JsonResponse};
use App\Services\{SmsService, OTPService, QrCodeService, WithdrawalMoneyService};
use App\Http\Requests\Api\V2\QrCode\{
    QrCodeExpressRequest,
    QrCodePaymentRequest,
    QrCodePaymentSubmitRequest,
    QrCodeRequest
};


class MerchantController extends Controller
{
     private $service;

    public function __construct(QrCodeService $service, WithdrawalMoneyService $withdrawalService)
    {
        $this->service = $service;
        $this->withdrawalService = $withdrawalService;
    }
    
    public function getGroup(Request $request) 
    {
        return $this->successResponse(
            Merchant::where("merchant_group_id", $request->merchant_group_id)->select('id', 'merchant_uuid', 'business_name', 'logo')->get()
        );
    }
    
    public function groups() 
    {
        return $this->successResponse(
            MerchantGroup::where(['is_active' => 1])->select('id', 'name', 'icon')->get()
        );
    }
    
    public function getOperators() 
    {
        return $this->successResponse(
            MerchantOperator::where('is_active', 1)->get(['id', 'name', 'logo'])
        );
    }
    
    public function getMerchantByNumber(QrCodeRequest $request)
    {
        return $this->successResponse(
            $this->service->merchantPaymentDetailsByNumber($request->merchant_number, $request->operator_id)
        );
    }
    
    public function MerchantPaymentConfirm(QrCodeExpressRequest $request)
    {
        extract($request->all());
        return $this->successResponse(
            $this->service->merchantPaymentReview($merchant_id, $currency_code, $amount, $request->operator_id)
        );
    }
    
    public function MerchantPaymentComplete(Request $request)
    {
        extract($request->all());
        $payment_method_id = $this->get_payment_method_id($request->operator_id);
        if ($request->operator_id == 1){
        return $this->successResponse(
            $this->service->qrPaymentSubmit($request->merchant_user_id, $request->merchant_id, $request->currency_id, $request->amount, $request->fee, $request->note, $request->operator_id)
            );
        }else{
            $totalAmount = $request->amount + $request->fee;
            $details = [
                    "account_no" => $request->merchant_number,
                    "type" => "MERCHANT",
                    "partner" => $request->partner
                ];
            return $this->successResponse(
                $this->withdrawalService->withdrawalConfirm(auth()->id(), $request->currency_id, $request->amount, $totalAmount, $payment_method_id, $details, $note=NULL));
        }
    }
    
     public function get_payment_method_id($operator_id)
    {
        if ($operator_id == 2){
            $payment_method_id = 12; //Hormuud
            return $payment_method_id;
        }
        if ($operator_id == 3){
            $payment_method_id = 13; //Somtel
            return $payment_method_id;
        }
        if ($operator_id == 4){
            $payment_method_id = 14; //Premier bank
            return $payment_method_id;
        }
         if ($operator_id == 5){
            $payment_method_id = 17; //Amtel
            return $payment_method_id;
        }
    }
    
}
