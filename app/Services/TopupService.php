<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Http\Helpers\Common;
use App\Models\{Topup, Currency};
use DB;

class TopupService {
    public function purchase($uuid, $sender, $receiver, $cleared_amount, $amount, $rate, $fee, $payment_method, $payment_method_id, $partner, $misc)
    {
        $helper = new Common();
        $userId = auth()->user()->id;
        $currencyId = Currency::where(['code' => 'USD', 'status' => 'Active'])->first()->id;
        
        $wallet = $helper->getWallet($userId, $currencyId, ['id', 'currency_id', 'balance']);
        $helper->checkWalletAmount($userId, $currencyId, $amount);
        $arr = [
            'user_id'             => $userId,
            'wallet'              => $wallet,
            'currency_id'         => $wallet->currency_id,
            'payment_method_id'   => $payment_method_id,
            'uuid'                => $uuid,
            'receiver'            => $receiver,
            'percentage'          => $rate,
            'charge_percentage'   => $fee,
            'charge_fixed'        => $fee,
            'amount'              => $amount,
            'totalAmount'         => $amount,
            'subtotal'            => $amount - $fee,
            'payment_method_info' => $misc,
        ];
        try {

            DB::beginTransaction();

            $topup = new Topup();
            $topup = $topup->createTopup($arr);

            $arr['transaction_reference_id'] = $topup->id;
            
            $topup->createTopupTransaction($arr);

            $topup->updateWallet($arr);
            
            (new AutoPayoutService())->pay($uuid, $sender, $receiver, $cleared_amount, $amount, $rate, $fee, $payment_method, $partner, $misc);
            
            //send notification
            (new FirebaseService())->send_transaction_notification($userId, $amount, 'top_up',$currencyId,$receiver);
           

            DB::commit();

            return [
                'status' => true,
                'tr_ref_id' => $topup->id,
                'uuid' => $uuid
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw new PaymentFailedException($e->getMessage());
        }
    }
}