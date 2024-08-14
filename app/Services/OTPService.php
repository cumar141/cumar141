<?php

namespace App\Services;

use App\Models\PhoneOTP;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class OTPService {
    
    public function generate($recipient) {
        $otp = 123456; #rand(100000, 999999);
        $phoneOTP = PhoneOTP::updateOrCreate(['phone' => $recipient], ['phone' => $recipient, 'otp'=> $otp, 'verified' => 0, 'expires_at' => Carbon::now()->addMinutes(3)]);
        return $otp;
    }
    
    public function verify($recipient, $otp) {
        $otp = PhoneOTP::where("phone", $recipient)->where("otp", $otp)->where("verified", 0)->where("expires_at", ">", Carbon::now())->first();
        $status = false;
        if($otp) {
            $otp->verified = 1;
            $otp->save();
            $status = true;
        }
        return $status;
    }
}