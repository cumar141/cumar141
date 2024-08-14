<?php

/**
 * @package AuthService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 30-11-2022
 */

namespace App\Services;

use App\Services\Mail\SecurityAlertNotificationMailService;
use App\Services\Mail\UserVerificationMailService;
use App\Exceptions\Api\V2\LoginException;
use App\Models\{
    VerifyUser,
    Wallet,
    User,
    ActivityLog,
    OauthAccessTokens
    
};
use Auth, DB;
use Exception;
use Carbon\Carbon;
use Carbon\CarbonInterval;
class AuthService
{
    /**
     * Get User email by login method
     *
     * @param string $email
     * @return array
     */
    public function getUserEmailByLoginMethod($email)
    {
        $loginVia = settings('login_via');
        
        switch ($loginVia) {
            case 'phone_only':
                return $this->checkUserByPhone($email);
                break;

            case 'email_or_phone':
                if (strpos($email, '@') !== false) {
                    return $this->checkUserByEmail($email);
                } else {
                    return $this->checkUserByPhone($email);
                }
                break;

            default:
                return $this->checkUserByEmail($email);
                break;
        }
    }

    /**
     * Check user by phone number
     *
     * @param string $phone
     * @return array
     */
    public function checkUserByPhone($phone)
    {
        $formattedRequest = ltrim($phone, '0');
        $phnUser = User::where(['phone' => $formattedRequest])
        ->orWhere(['formattedPhone' => $formattedRequest])
        ->orWhere(['phone1' => $formattedRequest])
        ->orWhere(['phone2' => $formattedRequest])
        ->orWhere(['phone3' => $formattedRequest])
        ->first(['formattedPhone']);
        if (!$phnUser) {
            throw new LoginException(__("Number is ready to register"));
        }

        return $phnUser->formattedPhone;

    }

    /**
     * Check user by email address
     *
     * @param string $email
     * @return array
     */
    public function checkUserByEmail($email)
    {
        $user = User::where(['email' => $email])->first(['email']);

        if (!$user) {
            throw new LoginException(__("Invalid email & credentials"));
        }

        return $user->email;

    }

    /**
     * User login
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws LoginException
     */
    public function login($email, $password, $request)
    {
        try {
            DB::beginTransaction();
            $userdata = User::where(['formattedPhone' => $email])
                ->orWhere('phone1', $email)
                ->orWhere('phone2', $email)
                ->orWhere('phone3', $email)
                ->first();
            $email = $this->getUserEmailByLoginMethod($email);
            $user  = $this->getActiveUser($email);
            $this->emailVerification($user);
            $this->threshold($userdata);    //----->added this line
            if (!Auth::attempt(['formattedPhone' => $email, 'password' => $password])) {
                $hits = $this->update_user_hit_count($userdata, $request); //----->added this line
                throw new LoginException(__("Invalid phone & credentials ") . 'you have' . ' ' . $hits . ' ' . 'try left');
            }
            $this->attemptblocked($userdata);   //----->added this line
            $this->userWallet($user);
            DB::commit();
            return Auth::user();
        } catch (Exception $e) {
            DB::rollback();
            throw new LoginException($e->getMessage());
        }

    }

    public function getActiveUser($email)
    {
        $user = User::where('email', $email)
                ->orWhere('formattedPhone', $email)
                ->first(['id', 'first_name', 'type', 'last_name', 'formattedPhone', 'email', 'status']);
        if (!$user) {
            throw new LoginException(__("No user found, please try again"));
        }
        if ($user->type != "user") {
            throw new LoginException(__("Try again with user credentials"));
        }
        
        if ($user->status == 'Inactive') {
            throw new LoginException(__("Your account is inactivated. call customer care"));
        }

        return $user;

    }

    public function emailVerification($user)
    {
        if ('Enabled' == preference('verification_mail')) {
            if (0 == optional($user->user_detail)->email_verification) {
                (new VerifyUser())->createVerifyUser($user->id);
                (new UserVerificationMailService())->send($user);
            }
        }
    }

    public function userWallet($user)
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id, 'currency_id' => settings('default_currency')], ['balance' => 0]);
        return $wallet;
    }
    
    public function update_user_hit_count($user, $request, $max_login_hit = 3)
    {
        $data['phone'] = $request->email;
        $data['device'] = $request->header('device-model');
        $data['location'] = $request->ip();
        $user->login_hit_count += 1;
        if ($user->login_hit_count >= $max_login_hit) {
            $user->is_temp_blocked = 1;
            $user->temp_block_time = now();
            (new SecurityAlertNotificationMailService)->send($user, $data); //send alert email when blocked account
        }
        try {
            $user->save();
            DB::commit();
            return $max_login_hit - $user->login_hit_count;
        } catch (Exception $e) {
            DB::rollback();
            throw new LoginException($e->getMessage());
        }
    }
    
    public function threshold(User $user)
    {
        $temp_block_time = 600; // seconds
        if ($user->is_temp_blocked) {
            if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->diffInSeconds() <= $temp_block_time) {
                $time = $temp_block_time - Carbon::parse($user->temp_block_time)->diffInSeconds();
                throw new LoginException(__("Your account is temporarily blocked."). ' ' . CarbonInterval::seconds($time)->cascade()->forHumans());
            }
    
            $user->login_hit_count = 0;
            $user->is_temp_blocked = 0;
            $user->temp_block_time = null;
            $user->save();
        }
    }

    public function attemptblocked($user){
        //req within blocking
        $temp_block_time = 600; // seconds
        if(isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->diffInSeconds() <= $temp_block_time){
            $time = $temp_block_time - Carbon::parse($user->temp_block_time)->diffInSeconds();
            throw new LoginException(__("Please try again after"). ' ' . CarbonInterval::seconds($time)->cascade()->forHumans());
        }
    }
    
    public function clearLoginsession(){
        $userid = auth::user()->id;
        $user = auth::user();
        OauthAccessTokens::where('user_id', $userid)->delete();
        $user->login_hit_count = 0;
        $user->is_temp_blocked = 0;
        $user->temp_block_time = null;
        $user->save();
    }
            

}
