<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\{
    TopupOperator,
    TopupProduct,
    TopupPackage,
    PaymentMethod
};

use App\Services\{
    TopupService,
    ValidateService
    };

class TopupController extends Controller
{
    public function operators() {
        $operators = TopupOperator::where('is_active', 1)->get(['id', 'name', 'logo']);
        return $this->successResponse($operators);
    }

    
    public function products(Request $request) {
        $products = TopupProduct::where('operator_id', $request->operator_id)->select('id', 'name')->get();
        return $this->successResponse($products);
    }
    
    public function packages(Request $request) {
        $packages = TopupPackage::where('product_id', $request->product_id)->select('id', 'description', 'amount')->get();
        return $this->successResponse($packages);
    }
    
    public function purchase(Request $request) {
        try {
            $phoneValid = (new ValidateService())->checkPhoneNumberFormat($request->receiver);
            
            if ($phoneValid['valid']!='True'){
                return response()->json([
                    "response" => [
                        "status"    => ["code" => 422, "message" => "Phone is not a valid"],
                    ]
                ], 422);
            }
            $uuid = unique_code();
            $sender = auth()->user()->formattedPhone;
            $receiver = str_replace('+252', '', $request->receiver);
            $payment_method = "TOPUP";
            $package = TopupPackage::where("id", $request->package)->first();
            $partner = "{$package->product->operator->name} (Reseller)";
            $misc = ["TOPUP_PACKAGE" => $package->product->name];
            $amount = $cleared_amount = $package->amount;
            $rate = $fee = 0;
            $payment_method_id = PaymentMethod::where('name', $package->product->operator->name)->first()->id;
            
            $response = (new TopupService())->purchase($uuid, $sender, $receiver, $cleared_amount, $amount, $rate, $fee, $payment_method, $payment_method_id, $partner, $misc);
        
            return response()->json([
                "response" => [
                    "status"    => ["code" => 200, "message" => "Success"],
                ]
            ], 200);
        } catch(Exception $e) {
            return response()->json([
                "response" => [
                    "status"    => ["code" => 422, "message" => "Failed to process request"]
                ]
            ], 422);
        }
    }
}