<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\{SmsService, OTPService};
use App\Models\User;

class OtpController extends Controller
{
    public function showOtp()
    {
        return view('staff.otp');
    }
    
    public function sendOtp(Request $request)
    {
        $userId = $request->user_id;
        $user = User::find($userId);
        $phone = $user->formattedPhone;
        
        $status = (new SmsService())->sendSMS($phone, 'OTP');
        return response()->json(["success" => $status], 200);
    }
    
    public function verifyOtps(Request $request)
    {
        $userId = $request->user_id;
        $otp = $request->otp;
        
        $user = User::find($userId);
        $phone = $user->formattedPhone;
       
        $status = (new OTPService())->verify($phone, $otp);
        return response()->json(["success" => $status], 200);
    }

    public function print(Request $request)
    {
        return view('staff.print.singlePrint', $request);
    }
}
