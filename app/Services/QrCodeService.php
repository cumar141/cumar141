<?php



namespace App\Services;



use App\Models\{Currency,

    Merchant,

    MerchantPayment,

    MerchantOperator,

    QrCode,

    Transaction,

    User,

    Wallet

};

use App\Services\WithdrawalMoneyService;

use Exception;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;



class QrCodeService

{

    private $service;

    public $user_id = null;



    public function __construct(WithdrawalMoneyService $service)

    {

        $this->service = $service;

    }

    /**

     * Method getQrSecret

     *

     * @return void

     */

    public function getQrSecret()

    {

        $secret = QrCode::where([

            'object_id' => auth()->id(),

            'object_type' => 'user',

            'status' => 'Active',

        ])->first(['secret']);



        if (empty($secret)) {

            throw new Exception(__("QrCode not found"));

        }



        return $secret;

    }

       /**

     * Method getQrImage

     *

     * @return void

     */

    public function getQrImage()

    {

        $qrImage = QrCode::where([

            'object_id' => auth()->id(),

            'object_type' => 'user',

            'status' => 'Active',

        ])->first(['qr_image']);

    

            $path = '/public/uploads/qrcode/user/' . $qrImage->qr_image;

            $baseUrl  = config('app.url');

            $qrimg = $baseUrl . $path;

    

        return $qrimg;

    }



    /**

     * Method addUpdateQrSecret

     *

     */

    public function addUpdateQrSecret()

    {



        $userId = auth()->id();



        $user = User::where(['id' => $userId, 'status' => 'Active'])->first(['id', 'formattedPhone', 'email']);



        $qrCode = QrCode::updateQrCode($user);



        return [

            'secret' => $qrCode->secret

        ];



    }



    public function userSendRequestDetails($secretText)

    {



        $qrCode = QrCode::where(['secret' => $secretText, 'object_type' => 'user', 'status' => 'Active'])->first(['object_id', 'status']);

        

        if (empty($qrCode)) {

            throw new Exception(__("Invalid QR Code!"));

        }



        if ($qrCode->object_id == auth()->id()) {

            throw new Exception(__("You Cannot Send or Request Money To Yourself!"));

        }



        $result   = convert_string('decrypt', $secretText);

        $data     = explode('-', $result);

        $userType = $data[0];

        

        if ($userType !== 'user') {

            throw new Exception(__("Invalid QR Code!"));

        }



        if (preference('processed_by') == 'email_or_phone') {



            $userId = auth()->id();



            $user    = User::where(['id' => $userId, 'status' => 'Active'])->first(['carrierCode', 'phone', 'defaultCountry']);

           

            if(empty($user->phone) || empty($user->carrierCode) ||   empty($user->defaultCountry)){

                throw new Exception(__("Please set your phone number"));

            }



            if (count($data) < 6) {

                throw new Exception(__("User phone not available on this QrCode"));

            }



            return [

                'userType'      => $userType,

                'carrierCode' => $data[2],

                'phone' => $data[3],

                'defaultCountry' => $data[4]

            ];

        }



        return [

            'userType'      => $userType,

            'receiver_Phone' => $data[2],

        ];



    }



    public function merchantPaymentDetails($secretText)

    {

        $qrCode = QrCode::where(['secret' => $secretText, 'status' => 'Active'])

                          ->whereIn('object_type', ['standard_merchant', 'express_merchant'])

                          ->first(['status']);



        if (empty($qrCode)) {

            throw new Exception(__("Invalid QR Code!"));

        }



        $result   = convert_string('decrypt', $secretText);

        $data     = explode('-', $result);

        $userType = $data[0];

        $merchant = $this->getMerchant($data[1]);



        $paymentDetails = [

            'userType' => $userType,

            'merchantId' => $data[1],

            'merchantBusinessName' =>$merchant->business_name,

            'merchantNumber' =>$merchant->merchant_uuid,

            'merchantDefaultCurrencyCode' => $data[2],

            'merchantLogo' => $merchant->logo,

        ];



        if ($userType == 'standard_merchant') {

            $paymentDetails['merchantPaymentAmount'] =  $data[3];

        }



        return $paymentDetails;



    }

    

    public function merchantPaymentDetailsByNumber($merchantNumber, $operator_id=null)

    {

        if ($operator_id == 1 || $operator_id === null) {

            $merchant = Merchant::where(['merchant_uuid' => $merchantNumber, 'status' => 'Approved'])->first(['id', 'type', 'currency_id', 'business_name','merchant_uuid','logo']);

        

            if (!$merchant) {

                 return "Invalid Merchant!";

            }

        

            $paymentDetails = [

                'userType' => $merchant->type,

                'merchantId' => $merchant->id,

                'merchantBusinessName' =>$merchant->business_name,

                'merchantNumber' =>$merchant->merchant_uuid,

                'merchantDefaultCurrencyCode' =>  $this->getCurrencyCode($merchant->currency_id)->code,

                'merchantLogo' => $merchant->logo,

            ];

            

          

        }else{

            $MerchantOperator = MerchantOperator::where(['id' => $operator_id, 'is_active' => 1])->first(['name', 'logo', 'currency_id']);

            $paymentDetails = [

                'userType' => "MERCHANT",

                'merchantId' => null,

                'merchantBusinessName' =>$MerchantOperator->name . " " . "(MERCHANT)",

                'merchantNumber' =>$merchantNumber,

                'merchantDefaultCurrencyCode' =>   $this->getCurrencyCode($MerchantOperator->currency_id)->code,

                'merchantLogo' => $MerchantOperator->logo,

            ];

        }

         return $paymentDetails;

    }





    public function qrPaymentSubmit(

        $merchant_user_id, $merchant_id, $currency_id, $amount, $fee, $note, $operator_id=null

    )

    {

        $arr = $this->setPaymentArray(

            $merchant_user_id,

            $merchant_id,

            $currency_id,

            $amount,

            $fee,

            $note

        );

        

        try {

                DB::beginTransaction();

    

                $merchantPaymentId = $this->createMerchantPayment($arr);

    

                $this->paymentSentTransaction($arr, $merchantPaymentId);

    

                $this->paymentReceivedTransaction($arr, $merchantPaymentId);

    

                $this->senderWalletUpdate($currency_id, $amount);

    

                $this->merchantWalletUpdate($arr);

    

                DB::commit();

                

                return ['message' => 'Payment submitted successfully', 'payment_id' => $merchantPaymentId];



        } catch (Exception $e) {

            DB::rollBack();

            throw new Exception($e->getMessage());

        }



    }



    public function merchantPaymentReview($merchant_id="", $currency_code, $amount, $operator_id=null)

    {

            $currency = $this->getCurrency($currency_code);

            

            $wallet = $this->getWallet($currency);

            

        if ($operator_id == 1 || $operator_id === null){

            

            $merchant = $this->getMerchant($merchant_id);

            if (empty($merchant )) {

                return"Merchant does not exist.";

            }

    

            $this->checkMerchantDefaultWallet($merchant->user_id, $currency);

    

            $fees = $amount * ($merchant->fee / 100);

            

    

            if ($amount > $wallet->balance) {

                return "Sorry, not enough balance to perform the operation.";

            }

            

            if ($merchant->type== 'standard'){

                

                return [

                'status'                                => 200,

                'merchantType'                          => $merchant->type,

                'merchantBusinessName'                  => $merchant->business_name,

                'merchantCurrencyId'                    => $currency->id,

                'merchantPaymentCurrencySymbol'         => $currency->symbol,

                'merchantPaymentAmount'                 => $amount,

                'merchantCalculatedChargePercentageFee' => $fees,

                'merchantActualFee'                     => $merchant->fee,

                'merchantUserId'                        => $merchant->user_id,

                'merchantLogo'                          => $merchant->logo,

                ];

                

            }

            

            if ($merchant->type== 'express'){

                

                return [

                'status'                                => 200,

                'merchantType'                          => $merchant->type,

                'merchantBusinessName'                  => $merchant->business_name,

                'merchantCurrencyId'                    => $currency->id,

                'merchantPaymentCurrencySymbol'         => $currency->symbol,

                'merchantPaymentAmount'                 => $amount,

                'merchantActualFee'                     => $fees,

                'merchantUserId'                        => $merchant->user_id,

                'merchantLogo'                          => $merchant->logo,

                ];

          

            }

            

            }else{

                $MerchantOperator = MerchantOperator::where(['id' => $operator_id, 'is_active' => 1])->first(['name', 'logo', 'currency_id']);

                return [

                    'status'                                => 200,

                    'merchantType'                          => "MERCHANT",

                    'merchantBusinessName'                  => $MerchantOperator->name . " " ."UNDEFINED MERCHANT",

                    'merchantCurrencyId'                    => $currency->id,

                    'merchantPaymentCurrencySymbol'         => "$",

                    'merchantPaymentAmount'                 => $amount,

                    'merchantActualFee'                     => $amount,

                    'merchantUserId'                        => null,

                    'merchantLogo'                          => $MerchantOperator->logo,

                    ];

            }



    }



    public function getMerchant($merchant_id)

    {

        $merchant = Merchant::find($merchant_id, ['id', 'user_id', 'fee', 'business_name','type','merchant_uuid','logo']);



        if (empty($merchant )) {

            throw new Exception(__("Merchant does not exist."));

        }



        if ($merchant->user_id == auth()->id()) {

            throw new Exception(__("You can't make payment to yourself."));

        }



        return $merchant;



    }



    public function getCurrency($currency_code)

    {

        $currency = Currency::where('code', $currency_code)->first(['id', 'symbol', 'code']);



        if (empty($currency )) {

            throw new Exception(__("Currency :x not found", ['x' => $currency_code]));

        }



        return $currency;



    }

    

    public function getCurrencyCode($currency_id)

    {

        $currency = Currency::where('id', $currency_id)->first(['id', 'symbol', 'code']);



        if (empty($currency )) {

            throw new Exception(__("Currency :x not found", ['x' => $currency_id]));

        }



        return $currency;



    }



    public function getWallet($currency)

    {

        $wallet = Wallet::where([

            'currency_id' => $currency->id,

            'user_id' => $this->user_id ?? auth()->id()

        ])->first('balance');



        if (empty($wallet)) {

            throw new Exception(__("You do not have :x wallet.", ['x' => $currency->code]));

        }



        return $wallet;

    }



    public function setPaymentArray(

        $merchant_user_id,

        $merchant_id,

        $currency_id,

        $amount,

        $fee,

        $note

    )

    {



        $merchant = $this->getMerchant($merchant_id);

        $fee = $amount * ($merchant->fee / 100);



        return [

            'merchant_user_id' => $merchant_user_id,

            'merchant_id' => $merchant_id,

            'user_id' => $this->user_id ?? auth()->id(),

            'currency_id' => $currency_id,

            'amount' => $amount,

            'fee' =>  $fee,

            'unique_code' => unique_code(),

            'charge_percentage' => $merchant->fee,

            'note' => $note

            

        ];



    }



    public function createMerchantPayment($arr)

    {

        $merchantPayment                    = new MerchantPayment();

        $merchantPayment->merchant_id       = $arr['merchant_id'];

        $merchantPayment->currency_id       = $arr['currency_id'];

        $merchantPayment->payment_method_id = 1;

        $merchantPayment->user_id           = $arr['user_id'];

        $merchantPayment->gateway_reference = $arr['unique_code'];

        $merchantPayment->order_no          = '';

        $merchantPayment->item_name         = '';

        $merchantPayment->uuid              = $arr['unique_code'];

        $merchantPayment->charge_percentage = $arr['fee'];

        $merchantPayment->charge_fixed      = 0;

        $merchantPayment->amount            = $arr['amount'] - $arr['fee'];

        $merchantPayment->total             = $arr['amount'];

        $merchantPayment->status            = 'Success';

        $merchantPayment->save();



        return $merchantPayment->id;



    }



    public function paymentSentTransaction($arr, $merchantPaymentId)

    {

        $senderWallet = Wallet::where([

            'user_id' => $this->user_id ?? auth()->id(),

            'currency_id' => $arr['currency_id']

        ])->first(['id', 'balance', 'user_id']);

        

        $transaction                           = new Transaction();

        $transaction->user_id                  = $arr['user_id'];

        $transaction->end_user_id              = $arr['merchant_user_id'];

        $transaction->currency_id              = $arr['currency_id'];

        $transaction->payment_method_id        = 1;

        $transaction->merchant_id              = $arr['merchant_id'];

        $transaction->uuid                     = $arr['unique_code'];

        $transaction->transaction_reference_id = $merchantPaymentId;

        $transaction->transaction_type_id      = Payment_Sent;

        $transaction->subtotal                 = $arr['amount'];

        $transaction->percentage               = 0;

        $transaction->charge_percentage        = 0;

        $transaction->charge_fixed             = 0;

        $transaction->total                    = '-' . $arr['amount'];

        $transaction->note                     = $arr['note'];

        $transaction->balance                  = $senderWallet->balance - $arr['amount'];

        $transaction->status                   = 'Success';

        $transaction->save();

        return true;



    }



    public function paymentReceivedTransaction($arr, $merchantPaymentId)

    {

        $merchantWallet = Wallet::where([

            'user_id' => $arr['merchant_user_id'],

            'currency_id' => $arr['currency_id']

        ])->first(['id', 'balance']);

        

        $transaction                           = new Transaction();

        $transaction->user_id                  = $arr['merchant_user_id'];

        $transaction->end_user_id              = $arr['user_id'];

        $transaction->currency_id              = $arr['currency_id'];

        $transaction->payment_method_id        = 1;

        $transaction->merchant_id              = $arr['merchant_id'];

        $transaction->uuid                     = $arr['unique_code'];

        $transaction->transaction_reference_id = $merchantPaymentId;

        $transaction->transaction_type_id      = Payment_Received;

        $transaction->subtotal                 = $arr['amount'] - $arr['fee'];

        $transaction->percentage               = $arr['charge_percentage'];

        $transaction->charge_percentage        = $arr['fee'];

        $transaction->charge_fixed             = 0;

        $transaction->total                    = $arr['amount'];

        $transaction->note                     = $arr['note'];

        $transaction->balance                  = $merchantWallet->balance + $arr['amount'];

        $transaction->status                   = 'Success';

        $transaction->save();

        return true;



    }



    public function senderWalletUpdate($currency_id, $amount)

    {

        $senderWallet = Wallet::where([

            'user_id' => $this->user_id ?? auth()->id(),

            'currency_id' => $currency_id

        ])->first(['id', 'balance', 'user_id']);

        $senderWallet->balance = $senderWallet->balance - $amount;

        $senderWallet->save();



        return true;

    }



    public function merchantWalletUpdate($arr)

    {

        $merchantWallet = Wallet::where([

            'user_id' => $arr['merchant_user_id'],

            'currency_id' => $arr['currency_id']

        ])->first(['id', 'balance']);

        $merchantWallet->balance = $merchantWallet->balance + ($arr['amount'] - $arr['fee']);

        $merchantWallet->save();



        return true;

    }



    public function checkMerchantDefaultWallet($merchantId, $currency)

    {

        $merchantWallet =  Wallet::where([

            'user_id' => $merchantId,

            'currency_id' => $currency->id,

            'is_default'=> 'Yes'

        ])->first();

        if (empty($merchantWallet)) {

            throw new Exception(__("Currency :x is not supported by this merchant!", ['x' => $currency->code]));

        }



        return true;



    }



}

