<?php

/**
 * @package DepositMoneyController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 21-12-2022
 */

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\Api\V2\{
    AmountLimitException,
    BanksException,
    ApiException
};
use App\Http\Requests\Api\V2\Banks\{
    BanksRequest
};
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V2\Requests;
use App\Models\{
    BankAccounts

};
use App\Services\BanksService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Exception;

class BanksController extends Controller
{
    /**
     * @var DepositMoneyService
     */
    protected $service;

    public function __construct(BanksService $service)
    {
        $this->service = $service;
       
    }
    
    /**
     * Get available bank list
     *
     * @param PaymentMethodRequest $reqeust
     *
     * @return JsonResponse
     */
    public function getBankname(): JsonResponse
    {
        try {
            return $this->successResponse($this->service->getBankname());
        } catch (BanksException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Get available bank list In you account
     *
     * @return JsonResponse
     */
    public function getBankList(): JsonResponse
    {
        try {
            return $this->successResponse($this->service->getBanklist());
        } catch (BanksException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Get bank details
     *
     * @param GetBankDetailsRequest $reqeust
     *
     * @return JsonResponse
     */
    public function getBankDetails(BanksRequest $reqeust): JsonResponse
    {
        try {
            return $this->successResponse($this->service->getBankDetails($reqeust->bank_id));
        } catch (DepositMoneyException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }
    
    /**
     * Add bank details
     *
     * @param BanksRequest $reqeust
     *
     * @return JsonResponse
     */
    public function addBank(BanksRequest $reqeust): JsonResponse
    {
        try {
            $accountNumber = $reqeust['account_number'];
            $existingBank = BankAccounts::where('account_number', $accountNumber)->first();
            if ($existingBank) {
                throw new BanksException(__('Account number already exists.'), [], 422);
            }
            return $this->successResponse($this->service->addBank($reqeust));
        } catch (BanksException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }
    
        /**
     * delete bank details
     *
     * @return JsonResponse
     */
    public function delBank(Request $reqeust): JsonResponse
    {
        try {
            return $this->successResponse($this->service->deleteBank($reqeust->all()));
        } catch (BanksException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }
    
    /**
     * Verify bank pin
     *
     * @return JsonResponse
     */
    public function bankpin(Request $reqeust): JsonResponse
    {
        try {
            return $this->successResponse($this->service->verifyBankOauth($reqeust->all()));
        } catch (BanksException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }


    
 
}
