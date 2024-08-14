<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Models\PhoneOTP;
use Illuminate\Support\Facades\DB;
use App\Services\SmsService as ServicesSmsService;
use GuzzleHttp\Psr7\Message;

class otpServices
{




    public function generateOTP($userId)
    {

        $user = User::find($userId);
        $phone = $user->formattedPhone;

        $checkUser = $this->check_user($userId);
        $checkPhone = $this->check_phone($userId, $phone);

        if (!$checkPhone || !$checkUser) {
            return false;
        }

        try {
            $smsService = new SmsService();
            $smsService->sendSMS($user->formattedPhone, 'OTP', 'Your withdrawal OTP is: ');
            return true;
        } catch (\Exception $e) {
            return false;
            // throw new \Exception('Failed to send OTP via SMS.');
         
        }
    }


           // Check if the user exists
        function check_user($userId) { 
            $user = User::find($userId);
            
            return $user ? true : false;
        }

        // Check if the user has a phone number
        function check_phone($userId, $phone) {
            $user = User::find($userId)->where(function ($query) use ($phone) {
                $query->whereIn('formattedPhone', [$phone, $phone, $phone, $phone]);
            })->first();
            
            return $user ? true : false;
        }  
       

       
     

}
