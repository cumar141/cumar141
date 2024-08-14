<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\AutoPayout;
use Carbon\Carbon;

class AutoPayoutService {
    public function pay($uuid, $sender, $receiver, $cleared_amount, $amount, $rate, $fee, $payment_method, $partner, $misc) {
        try {
            $autoPayout = new AutoPayout();
            $autoPayout->session = $uuid;
            $autoPayout->reference = "N/A";
            $autoPayout->trx_reference = $uuid;
            $autoPayout->sender = $sender;
            $autoPayout->receiver = $receiver;
            $autoPayout->cleared_amount = $cleared_amount;
            $autoPayout->amount = $amount;
            $autoPayout->rate = $rate;
            $autoPayout->fee = $fee;
            $autoPayout->platform = settings('name');
            $autoPayout->payment_method = $payment_method;
            $autoPayout->partner = $partner;
            $autoPayout->misc = json_encode($misc);
            $autoPayout->status = 1;
            $autoPayout->received_at = Carbon::now();
            $autoPayout->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}