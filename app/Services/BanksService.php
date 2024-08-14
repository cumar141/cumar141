<?php

/**
 * @package DepositMoneyService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 21-12-2022
 */



namespace App\Services;

use App\Exceptions\Api\V2\{
    BanksException
};
use App\Http\Helpers\Common;
use App\Models\{
    Bank,
    OauthAccessBanks,
    BankAccounts,
    Currency
};
use Auth,DB;
use App\Services\Mail\Deposit\NotifyAdminOnDepositMailService;
class BanksService
{
    /**
     * @var Common
     */
    private $helper;

    public function __construct(Common $helper)
    {
        $this->helper = $helper;
    }
    
      /**
     * Get bank list into your account
     *
     * @return Bank
     *
     * @throws DepositMoneyException
     */
    public function getBanklist()
    {
        $userId = auth()->user()->id;
        $banks = BankAccounts::where(['user_id' => $userId])->get(['id', 'bank_name', 'account_name', 'account_number']);

        if (count($banks) == 0) {
            throw new BanksException(__("No banks does not exist for your account."));
        }
        return $banks;
    }
    
    /**
     * Get bank names we only accept
     *
     * @return Bank name list and will display into dropdown list
     *
     * @throws DepositMoneyException
     */
    public function getBankname()
    {
        $banks = Bank::all(['id', 'bank_name']);
    
        if ($banks->isEmpty()) {
            throw new BanksException("No banks exist.");
        }
    
        return $banks;
    }


    /**
     * @param int $bankId
     *
     * @return Bank
     *
     * @throws BanksException
     */
    public function getBankDetails($bankId)
    {
        $bank = BankAccounts::with("file:id,filename")->select("account_name", "account_number", "bank_name", "file_id")->firstWhere("id", $bankId);
        if (is_null($bank)) {
            throw new BanksException(__("Bank details not found."));
        }
        if ($bank->file_id && optional($bank->file)->filename && file_exists(public_path('uploads/files/bank_logos/' . $bank->file->filename))) {
            $bank->logo = $bank->file->filename;
        }
        return $bank;
    }
    
    public function addBank($bankData)
    {
        try {
            $bank_data = $this->saveBank($bankData);
            $bank_pin  = $this->OauthAccessBank($bankData);
            return $bank_data;
        } catch (Exception $e) {
            throw new BanksException($e->getMessage());
        }

    }
    
    public function deleteBank($bankData)
    {
        try {
            $bank_id = $this->delBank($bankData);
            $bank_pin  = $this->delBankOauth($bank_id);
            return $bank_pin;
        } catch (Exception $e) {
            throw new BanksException($e->getMessage());
        }

    }
    
    public function saveBank($bankData)
    {
    
        $bank = new BankAccounts();
        $bank->user_id = auth()->user()->id;
        $bank->currency_id = 1;
        $bank->country_id = 196;
        $bank->bank_name = $bankData['bank_name'];
        $bank->is_default = "No";
        $bank->account_name = $bankData['account_name'];
        $bank->account_number = $bankData['account_number'];
        $bank->save();
    
        return $bank;
    }
    
    public function OauthAccessBank($bankData)
    {
        $bank = BankAccounts::where('account_number', $bankData->account_number)->first();
    
        $oauthBank = new OauthAccessBanks;
        $oauthBank->bank_id = $bank->id;
        $oauthBank->user_id = auth()->user()->id;
        $oauthBank->token = \Hash::make($bankData->bank_pin);
        $oauthBank->created_at = now();
        $oauthBank->updated_at = now();
        $oauthBank->save();
    
        return $oauthBank;
    }
    
    public function delBank($accountNumber)
    {
        $bank = BankAccounts::where('account_number', $accountNumber)->first();
        if ($bank) {
            $bank->delete();
            return $bank->id;
        } else {
            return 'Bank record not found';
        }
    }
    
    public function delBankOauth($bank_id)
    {
        $OauthAccessBank = OauthAccessBanks::where('bank_id', $bank_id)->first();
        if ($OauthAccessBank) {
            $OauthAccessBank->delete();
            return 'Bank record deleted successfully';
        } else {
            return 'Bank record not found';
        }
    }
    
   public function verifyBankOauth($accountverify)
    {
        $bank_pin = $accountverify['account_pin'];
        $bank_id = $this->getBankid($accountverify['account_number']);
        $OauthAccessBank = OauthAccessBanks::where('bank_id', $bank_id)->first();
       
        if ($OauthAccessBank) {
            if (\Hash::check($bank_pin, $OauthAccessBank->token)) {
                return 'Successfully verified';
            } else {
                return 'Wrong PIN, please try again';
            }
        } else {
            return 'Bank access not found for the provided bank ID';
        }
    }

    
    public function getBankid($accountNumber)
    {
        $bank = BankAccounts::where('account_number', $accountNumber)->first();
        if ($bank) {
            return $bank->id;
        } else {
            return 'Bank record not found';
        }
    }



}
