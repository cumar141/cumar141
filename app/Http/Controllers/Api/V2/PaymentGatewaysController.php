<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\{
    PaymentGatewayService,
    PaymentGatewayVerificationService
    };

class PaymentGatewaysController extends Controller
{
    public function mobilePaymentWithdraw(Request $request) {
        $response = (new PaymentGatewayService)->mobilePaymentWithdraw($request);
        return response()->json($response);
    }
    
    public function verifyPayment(Request $request) {
        $response = (new PaymentGatewayVerificationService)->process($request);
        return response()->json($response);
    }
}
