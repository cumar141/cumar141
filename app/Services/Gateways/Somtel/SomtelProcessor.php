<?php

/**
 * @package somtelProcessor
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 01-08-2023
 */


namespace App\Services\Gateways\somtel;

use App\Services\Gateways\Gateway\Exceptions\{
    GatewayInitializeFailedException,
    PaymentFailedException
};
use App\Services\Gateways\Gateway\PaymentProcessor;
use Exception;



/**
 * @method array pay()
 */
class somtelProcessor extends PaymentProcessor
{
    protected $data;

    protected $somtel;

    protected $baseurl;

    protected $accessToken;

    protected $uniqid;


    /**
     * Boot stripe payment processor
     *
     * @param array $data
     *
     * @return void
     */
    protected function boot($data)
    {
        $this->data = $data;

        $this->paymentCurrency();

        $this->uniqid = $this->data['uuid'];

        $this->somtel = $this->paymentMethodCredentials();

        if (!$this->somtel->merchant_id || !$this->somtel->secret_key || !$this->somtel->encryption_key || !$this->somtel->merchant_domain
        ) {
            throw new GatewayInitializeFailedException(__("somtel initialize failed."));
        }

    }


    /**
     * Confirm payment for stripe
     *
     * @param array $data
     *
     * @return mixed
     *
     * @throws PaymentFailedException
     */
    public function pay(array $data): array
    {
        try {
            $this->boot($data);

            $this->validateInitiatePaymentRequest($data);

           $paymentData =  $this->setPaymentData();

            return [
                'data' => $paymentData,
            ];

        } catch (Exception $th) {
            throw new PaymentFailedException($th->getMessage(), ["response" => $response ?? null]);
        }
    }

    public function paymentView()
    {
        return 'gateways.'.$this->gateway();
    }


    /**
     * Get gateway alias name
     *
     * @return string
     */
    public function gateway(): string
    {
        return "somtel";
    }


    /**
     * Validate initialization request
     *
     * @param array $data
     *
     * @return array
     */
    private function validateInitiatePaymentRequest($data)
    {
        $rules = [
            'amount' => 'required',
            'currency_id' => 'required',
            'payment_method_id' => 'required', 'exists:payment_methods,id',
            'redirect_url' => 'required',
            'transaction_type' =>'required',
            'payment_type' => 'required',
            'uuid' => 'required'
        ];
        return $this->validateData($data, $rules);
    }

    public function setPaymentData()
    {
        // Prepare the request data as an array
        $data = [
            "schemaVersion" => "1.0",
			"requestId" => $this->uniqid,
			"timestamp" => $timestamp,
			"channelName" => "WEB",
			"serviceName" => "API_PURCHASE",
			"serviceParams" => [
    			"merchantUid" => $this->somtel->merchantUid,
    			"apiUserId" => $this->somtel->apiUserId,
    			"apiKey" => $this->somtel->apiKey,
    			"paymentMethod" => "mwallet_account",
    			"payerInfo" => ["accountNo" => auth()->user()->formattedPhone],
    			"transactionInfo"=>[
        			"referenceId" => $this->uniqid,
        			"invoiceId" => $this->uniqid,
        			"amount" => $this->amount,
        			"currency" => "USD",
        			"description" => "DESCRIPTION? NAHHH"
                ],
			],
		];

        return $data;

    }

    public function verify($request)
    {
        try {
            $data = getPaymentParam($request->params);
            $data['payment_method_id'] = somtel;
            $this->setPaymentType($data['payment_type']);
            $this->boot($data);

            return $data;
        } catch (Exception $e) {
            
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }


    public function callBack($request)
    {
        $status = $request->status;
        $this->uniqid = $request->uid;
        if (!empty( $this->uniqid) && ($status == 'cancel')) {
            $this->transactionUpdate('Blocked');
        }
    }

}





