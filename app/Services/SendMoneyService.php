<?php

/**
 * @package SendMoneyService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 20-11-2022
 */

namespace App\Services;

use App\Http\Helpers\Common;
use App\Enums\Status;
use App\Exceptions\Api\V2\{
    PaymentFailedException,
    SendMoneyException
};
use App\Models\{
    Transfer,
    Wallet,
    User
};
use App\Services\{
    Mail\ReceiveMoneyMailService,
    Mail\SendMoneyMailService,
    Sms\ReceiveMoneySmsService,
    Sms\SendMoneySmsService,
    Sms\SmsTemplateService,
    SmsService,
    FirebaseService
};
use Exception, DB;
use App\Services\Mail\SendMoney\NotifyAdminOnSendMoneyMailService;

class SendMoneyService
{
    /**
     * @var Common;
     */
    protected $helper;
    protected $transactionType;
    protected $transfer;


    /**
     * Construct the service class
     *
     * @param Common $helper
     *
     * @return void
     */
    public function __construct(Common $helper)
    {
        $this->helper = $helper;
        $this->transactionType = Transferred;
        $this->transfer = new Transfer;
    }


    /**
     * Validates if the payable request can be processed or not
     *
     * @param string $email
     *
     * @return bool
     */
    public function validateEmail($email)
    {
        $receiver = User::whereEmail($email)->first();

        // Check if receiver exists
        if (is_null($receiver)) {
            throw new SendMoneyException(__("Receiver email address does not exist."));
        }

        return $this->validateReceiverUserEmail($receiver);
    }


    /**
     * Validates receiver email for sending money
     *
     * @param User|null $receiver
     *
     * @return bool
     *
     * @throws SendMoneyException
     */
    protected function validateReceiverUserEmail($receiver)
    {
        if (is_null($receiver)) {
             throw new SendMoneyException(__("Receiver email address does not exist."));
        }

        $user = auth()->user();

        // Check if both user and receiver email addresses are same
        if ($user->email ==  $receiver->email) {
            throw new SendMoneyException(__("You cannot send money to yourself."));
        }

        // Check if receiver is a suspended user
        if (in_array($receiver->status, [Status::SUSPENDED, Status::INACTIVE])) {
            throw new SendMoneyException(__("You cannot send money to a :x user.", ["x" => $receiver->status]));
        }

         $userData = [
            'fullName' => $receiver->first_name . ' ' . $receiver->last_name,
            'phone' => $receiver->formattedPhone,
            'email' => $receiver->email
        ];

        return $userData;
    }



    /**
     * Validates if the payable request can be processed or not
     *
     * @param string $phone
     *
     * @return bool
     *
     * @throws SendMoneyException
     */
    public function validatePhoneNumber($phone)
    {
        $user = auth()->user();

        if (is_null($user->formattedPhone) || empty($user->formattedPhone)) {
            throw new SendMoneyException(__("Please set your phone number first."));
        }
        
        $receiver = User::select('formattedPhone', 'status', 'first_Name', 'last_Name','email', 'identity_verified')->where("formattedPhone", $phone)->first();
       
        if (is_null($receiver)) {
            throw new SendMoneyException(__("Receiver phone number does not exist."));
        }
        
        // Check if the receiver is not verified
        if (!$receiver->identity_verified) {
            throw new SendMoneyException(__("Receiver account is not verified yet."));
        }

        return $this->validateReceiverUserPhone($receiver);
    }


    /**
     * Validate receiver phone number for sending money
     *
     * @param User $receiver
     * @return bool
     *
     * @throws SendMoneyException
     */
    protected function validateReceiverUserPhone($receiver)
    {
        // Check if receiver exists
        if (is_null($receiver)) {
            throw new SendMoneyException(__("Receiver phone number does not exist."));
        }

        $user = auth()->user();

        // Check if both user and receiver email addresses are same
        if ($user->formattedPhone ==  $receiver->formattedPhone) {
            throw new SendMoneyException(__("You cannot send money to yourself."));
        }

        // Check if receiver is a suspended user
        if (in_array($receiver->status, [Status::SUSPENDED, Status::INACTIVE])) {
            throw new SendMoneyException(__("You cannot send money to a :x user.", ["x" => $receiver->status]));
        }
        
        
        $userData = [
            'fullName' => $receiver->first_Name . ' ' . $receiver->last_Name,
            'phone' => $receiver->formattedPhone,
            'email' => $receiver->email
        ];
        return $userData;
    }


    /**
     * Get available currencies of the user
     *
     * @return array
     */
    public function getSelfCurrencies()
    {
        $result = [];

        Wallet::with('currency:id,code,type')
            ->where("user_id", auth()->id())
            ->whereHas("active_currency")
            ->join('fees_limits', 'fees_limits.currency_id', 'wallets.currency_id')
            ->where('fees_limits.has_transaction', 'Yes')
            ->where('fees_limits.transaction_type_id', $this->transactionType)
            ->get()
            ->map(function ($item) use (&$result) {
                $result[$item->currency_id] = [
                    'id' => $item->currency_id,
                    'code' => optional($item->currency)->code,
                    'is_default' => $item->is_default,
                    'type' => optional($item->currency)->type
                ];
            });

        return array_values($result);
    }



    /**
     * Check the requested amount and currency in user wallet and Fees limit
     *
     * @param int $currencyId Currency Id
     * @param float $amount
     *
     * @return array
     *
     * @throws SendMoneyException
     */
    public function validateAmountLimit($currencyId, $amount)
    {
        $userId = auth()->id();

        $currencyFee = $this->helper->transactionFees($currencyId, $amount, $this->transactionType);

        $this->helper->amountIsInLimit($currencyFee, $amount);

        $this->helper->checkWalletAmount($userId, $currencyId, $currencyFee->total_amount);

        return $currencyFee;

    }


    public function sendMoneyConfirm($identifier, $currencyId, $amount, $totalFees, $note)
    {
        $userId =  auth()->id();
        $identifier = trim($identifier);
        $email = $this->helper->validateEmailInput($identifier);
        $phone = $this->helper->validatePhoneInput($identifier);
        if (!$email && !$phone) {
            throw new SendMoneyException(__("Invalid send money request."));
        }
        
        $receiver = User::where('email', $identifier)->orWhere('formattedPhone', $identifier)->first();
        
        // Check if the receiver is not verified
        if (!$receiver->identity_verified) {
            throw new SendMoneyException(__("Receiver account is not verified yet."));
        }
        
        $currencyFee = $this->helper->transactionFees($currencyId, $amount, $this->transactionType);

        $this->helper->amountIsInLimit($currencyFee, $amount);

        $this->helper->checkWalletAmount($userId, $currencyId, $currencyFee->total_amount);

        $senderWallet = $this->helper->getWallet($userId, $currencyId);


        $arr = [
            'emailFilterValidate' => $email,
            'phoneRegex' => $phone,
            'processedBy' => preference("processed_by"),
            'user_id' => $userId,
            'currency_id' => $currencyId,
            'uuid' => unique_code(),
            'fee' => $totalFees,
            'amount' => $amount,
            'note' => trim($note),
            'receiver' => $identifier,
            'charge_percentage' => $currencyFee->charge_percentage,
            'charge_fixed' => $currencyFee->charge_fixed,
            'p_calc' => $currencyFee->fees_percentage,
            'total' => $currencyFee->total_amount,
            'senderWallet' => $senderWallet,
        ];

        if (!is_null($receiver)) {
            $arr['userInfo'] = $receiver;
        }

        try {
            DB::beginTransaction();
            //Create Transfer
            $transfer = $this->transfer->createTransfer($arr);
            //Create Transferred Transaction
            $arr['transaction_reference_id'] = $transfer->id;
            $arr['status']                   = $transfer->status;
            $this->transfer->createTransferredTransaction($arr);
            //Create Received Transaction
            $this->transfer->createReceivedTransaction($arr);
            //Update Sender Wallet
            $SenderBalance = $this->transfer->updateSenderWallet($arr['senderWallet'], $arr['total']);
            //Create Or Update Receiver Wallet
            $arr['transfer_receiver_id'] = $transfer->receiver_id;
            $ReceiverBalance = $this->transfer->createOrUpdateReceiverWallet($arr);

            DB::commit();

            // Sms & Email
            $this->notificationToSender($transfer, true, $SenderBalance);
            $this->notificationToReceiver($transfer, true, $ReceiverBalance);
            $this->notificationToAdmin($transfer);
            
            //send notification
            (new FirebaseService())->send_transaction_notification($transfer->receiver_id, $transfer->amount, 'received_money',$transfer->currency_id, $transfer->sender_id);
            (new FirebaseService())->send_transaction_notification($transfer->sender_id, $transfer->amount, 'send_money',$transfer->currency_id, $transfer->receiver_id);
            
            return [
                'status' => true,
                'tr_ref_id' => $transfer->id,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw new SendMoneyException($e->getMessage());
        }

    }

    public function notificationToSender($transfer, $new = false, $SenderBalance=0)
    {
        if ($new) {
            $recipient = str_replace("+", "", $transfer->sender->formattedPhone);
            $data = [
                "{receiver_phone}" => is_null($transfer->receiver->formattedPhone) ? $transfer->receiver->email : $transfer->receiver->formattedPhone, 
                "{amount}" => moneyFormat(optional($transfer->currency)->symbol, formatNumber($transfer->amount, $transfer->currency_id)),
                "{sender_phone}" => is_null($transfer->sender->formattedPhone) ? $transfer->sender->email : $transfer->sender->formattedPhone,
                "{sender_balance}" => moneyFormat(optional($transfer->currency)->symbol, formatNumber($SenderBalance, $transfer->currency_id)),
                "{uuid}" => $transfer->uuid,
                "{created_at}" => dateFormat($transfer->created_at),
                "{soft_name}" => settings('name')
            ];
            $template = (new SmsTemplateService())->getSmsTemplate('notify-money-sender');
            $template = (object) $template["template"];
            $body = str_replace(array_keys($data), $data, $template->body);
            $type = "MoneyTransfer";
            return (new SmsService())->sendSms($recipient, $type, $body);
        }
        
        $processedBy         = preference('processed_by');
        $emailFilterValidate = $this->helper->validateEmailInput($transfer->email);
        $phoneRegex          = $this->helper->validatePhoneInput($transfer->phone);

        if ($emailFilterValidate && "email" == $processedBy) {
            return (new SendMoneyMailService())->send($transfer);
        } elseif ($phoneRegex && "phone" == $processedBy) {
            return (new SendMoneySmsService())->send($transfer);
        } elseif ("email_or_phone" == $processedBy) {
            if ($emailFilterValidate) {
                return (new SendMoneyMailService())->send($transfer);
            } elseif ($phoneRegex) {
                return (new SendMoneySmsService())->send($transfer);
            }
        }
    }

    public function notificationToReceiver($transfer, $new = false, $ReceiverBalance=0)
    {
        if ($new) {
            $recipient = str_replace("+", "", $transfer->receiver->formattedPhone);
            $data = [
                "{receiver_phone}" => is_null($transfer->receiver->formattedPhone) ? $transfer->receiver->email : $transfer->receiver->formattedPhone,
                "{amount}" => moneyFormat(optional($transfer->currency)->symbol, formatNumber($transfer->amount, $transfer->currency_id)),
                "{sender_phone}" => is_null($transfer->sender->formattedPhone) ? $transfer->sender->email : $transfer->sender->formattedPhone,
                "{receiver_balance}" => moneyFormat(optional($transfer->currency)->symbol, formatNumber($ReceiverBalance, $transfer->currency_id)),
                "{uuid}" => $transfer->uuid,
                "{created_at}" => dateFormat($transfer->created_at),
                "{soft_name}" => settings('name')
            ];
            $template = (new SmsTemplateService())->getSmsTemplate('notify-money-receiver');
            $template = (object) $template["template"];
            $body = str_replace(array_keys($data), $data, $template->body);
            $type = "MoneyTransfer";
            return (new SmsService())->sendSms($recipient, $type, $body);
        }
        
        $processedBy         = preference('processed_by');
        $emailFilterValidate = $this->helper->validateEmailInput($transfer->email);
        $phoneRegex          = $this->helper->validatePhoneInput($transfer->phone);

        if ($emailFilterValidate && "email" == $processedBy) {
            return (new ReceiveMoneyMailService())->send($transfer);
        } elseif ($phoneRegex && "phone" == $processedBy) {
            return (new ReceiveMoneySmsService())->send($transfer);
        } elseif ("email_or_phone" == $processedBy) {
            if ($emailFilterValidate) {
                return (new ReceiveMoneyMailService())->send($transfer);
            } elseif ($phoneRegex) {
                return (new ReceiveMoneySmsService())->send($transfer);
            }
        }
    }


    //Admin Notification
    public function notificationToAdmin($transfer)
    {
        (new NotifyAdminOnSendMoneyMailService)->send($transfer, ['type' => 'send', 'medium' => 'email']);

        return true;
    }
}
