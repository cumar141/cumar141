<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\CashOut\Withdrawals;
use Illuminate\Support\Facades\Validator;
use App\Models\StaffNotification;
use App\Exceptions\Api\V2\{
    AmountLimitException,
    WithdrawalException,
    PaymentFailedException,
    WalletException
};
use App\Http\Helpers\Common;
use App\Http\Requests\Api\V2\Payout\{
    AmountLimitRequest,
    ConfirmRequest
};
use DB;
use App\Services\PayoutMoneyService;

class CashoutController extends Controller
{
    protected $helper;
    protected $service;

    public function __construct(Common $helper, PayoutMoneyService $service)
    {
        $this->helper = $helper;
        $this->service = $service;
    }
    /**
     * Handle the cash out request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cashOut(Request $request)
    {
        $rules = [
            'currency_id' => 'required|integer',
            'teller_id' => 'required|integer|',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
            'payment_method_id' => 'nullable|integer',
        ];

        // Validation messages
        $messages = [
            'currency_id.required' => 'Currency ID is required.',
            'teller_id.required' => 'Teller ID is required.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be greater than or equal to 0.',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules, $messages);

        // Check for validation errors
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 400
            ], 400);
        }
        $currencyId = $request->input('currency_id');
        $tellerId = $request->input('teller_id');
        $amount = $request->input('amount');
        $note = $request->input('note', 'No description provided');
        $paymentMethodId = $request->input('payment_method_id', 1);
        $userId = auth()->id();
        if (!$userId) {
            return response()->json([
                'message' => 'user does not exist',
                'status' => 404
            ]);
        }
        try {
            DB::beginTransaction();
            $withdrawal = new Withdrawals();
            $withdrawalResult = $withdrawal->processWithdrawal($userId, $currencyId, $amount, $tellerId, $note, 1, null);


            if (isset($withdrawalResult['error'])) {
                return response()->json([
                    'message' => $withdrawalResult['error'],
                    'status' => 403
                ], 403);
            };

            $this->sendNotification($userId, $tellerId, $note, $withdrawalResult);
            DB::commit();
            // If both operations succeed, return a success response
            return response()->json([
                'message' => "Cash out successful",
                'status' => 200,
                'uuid' => $withdrawalResult['transactionDetails']['transaction_reference_id']
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred during cash out',
                'status' => 500
            ], 500);
        }
    }

    public function sendNotification($sender, $receiver, $note, $data)
    {

        // Retrieve sender and receiver information
        $senderInfo = User::where('id', $sender)->first();
        $receiverInfo = User::where('id', $receiver)->first();

        // Construct payload
        $payload = [
            'sender' => [
                'name' => $senderInfo->first_name . ' ' . $senderInfo->last_name,
                'phone' => $senderInfo->formattedPhone,
            ],
            'receiver' => [
                'name' => $receiverInfo->first_name . ' ' . $receiverInfo->last_name,
                'phone' => $receiverInfo->formattedPhone,
            ],
            'uuid' =>  $data['transactionDetails']['uuid'],
            'amount' =>  $data['transactionDetails']['total'],
            'currency_id' =>  $data['transactionDetails']['currency_id'],
            'transaction_type_id' =>  $data['transactionDetails']['transaction_type_id'],

        ];

        // Prepare notification data
        $notificationData = [
            'user_id' => $sender,
            'end_user_id' => $receiver,
            'note' => $note,
            'payload' => $payload
        ];

        // Create notification
        $notification = new StaffNotification();
        $notification->createNotification($notificationData);
    }
    
    public function verifyTeller(Request $request)
    {
        $status = "";
        $records = [];

        $request->validate([
            'teller' => 'required',
        ]);

        $teller = User::whereHas('role', function ($query) use ($request) {
            $query->where('name', 'Teller')->where('teller_uuid', $request->teller)
                ->where('status', 'Active');
        })
            ->with('role', 'branch')
            ->first();
        if (!$teller) {
            $status = false;
            $records = "No teller found";
        } else {
            $status = true;
            $records['id'] = $teller->id;
            $records['name'] = $teller->first_name . " " . $teller->last_name;
            $records['branch'] = $teller->branch->name;
        }
        return response()->json([
            'success' => $status,
            'records' => $records
        ]);
    }
    
    public function amountLimitCheck(Request $request)
    {
        try {

            extract($request->only(['currency_id', 'payment_method_id', 'amount']));
            
            return $this->successResponse(
                $this->service->validateAmountLimit(
                    $currency_id,
                    $payment_method_id,
                    $amount,
                    auth()->id()
                )
            );
        } catch (WithdrawalException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (AmountLimitException | WalletException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (\Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Validate withdrawal amount with currency id
     *
     * @param int $currencyId
     * @param int $paymentMethod
     * @param int $withdrawalSettingId
     * @param double $amount
     * @param int $userId
     * @return array
     * @throws WithdrawalException
     */

    public function validateAmountLimit($currencyId, $paymentMethod_id, $amount, $userId)
    {

        $feesDetails = $this->helper->transactionFees($currencyId, $amount, Withdrawal, $paymentMethod_id);

        $this->helper->amountIsInLimit($feesDetails, $amount);

        $this->helper->checkWalletAmount($userId, $currencyId, $feesDetails->total_amount);

        return array_merge((new FeesResource($feesDetails))->toArray(request()));
    }
}
