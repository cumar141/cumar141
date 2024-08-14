<?php

/**
 * @package ForgotPasswordService
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 11-1-2023
 */

namespace App\Services;

use DB, Hash, Password;
use App\Models\User;
use App\Exceptions\Api\V2\ForgotPasswordException;
use App\Services\Mail\PasswordResetMailService;
use App\Services\{SmsService, OTPService};

class ForgotPasswordService
{
    /**
     * send forgot password code
     *
     * @param string $email
     * @return void
     */
    public function resetCode($email)
    {
        $user  = User::where('email', $email)->orWhere('formattedPhone', $email)->orWhere('Phone', $email)->first();
        $user->email = $email;

        if (!$user) {
            throw new ForgotPasswordException(__("Email Address or Phone does not match."));
        }
        
        $otp_sms = (new SmsService());
        $otp_sms->sendSMS($email, 'OTP');

        $reset['email'] = $email;
        $reset['code'] = $user['code']  = "";
        $reset['created_at'] = date('Y-m-d H:i:s');
        $reset['token'] = base64_encode(Password::createToken($user));
        DB::table('password_resets')->where('email', $email)->delete();
        DB::table('password_resets')->insert($reset);

        $reset['resetUrl'] = url('password/resets', $reset['token']);

        // $response['email'] = (new PasswordResetMailService)->send($user, $reset);
        $response['code'] = $user['code'];
        

        return $response;
    }

    public function verifyCode($code, $email)
    {
        $reset = DB::table('phone_otp')->where(['otp' => $code, 'phone' => $email ])->first();
        if (!$reset) {
            throw new ForgotPasswordException(__("Verify code not valid. Please try again."));
        }

        return [
            'status'  => true,
            'message' => __('Reset code verified.')
        ];

    }

    public function confirmPassword($code, $email, $password)
    {
        $reset = DB::table('phone_otp')->where(['otp' => $code, 'phone' => $email, 'verified' => '0'])->first();
        if (!$reset) {
            throw new ForgotPasswordException(__("Verify code not valid. Please try again."));
        }

        $user = User::where('formattedPhone', $email)
                    ->Orwhere('phone', $email)
                    ->first();
        if (Hash::check($password, $user->password)) {
            throw new ForgotPasswordException(__("The new password you have entered is the same as your current password. Please choose a different password."));
        }

        $user->password = Hash::make($password);
        $user->save();

        DB::table('phone_otp')->where(['otp' => $code, 'phone' => $email])->update(['verified' => '1']);
        return [
            'status'  => true,
            'message' => __('Password changed successfully.')
        ];
    }



}
