<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Models\AutoPayout;

class waafiShowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;

    /**
     * Create a new job instance.
     *
     * @param array $payment Data to send with the request.
     */
    public function __construct(array $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $payment = $this->payment;
            $account = str_replace("+", "", $payment["account"]);
            $timestamp = Carbon::now();
            
            $response = Http::post("https://api.waafipay.net/asm", [
                "schemaVersion" => "1.0",
    			"requestId" => $payment["session"],
    			"timestamp" => $timestamp,
    			"channelName" => "WEB",
    			"serviceName" => "API_PURCHASE",
    			"serviceParams" => [
        			"merchantUid" => "M0912362",
        			"apiUserId" => "1005203",
        			"apiKey" => "API-1081628967AHX",
        			"paymentMethod" => "mwallet_account",
        			"payerInfo" => ["accountNo" => $account],
        			"transactionInfo"=>[
            			"referenceId" => $payment["reference"],
            			"invoiceId" => $payment["reference"],
            			"amount" => $payment["amount"],
            			"currency" => "USD",
            			"description" => "DESCRIPTION? NAHHH"
        			],
    			]
            ]);
            $response = json_decode($response);
            $autopayout = AutoPayout::where(["session" => $payment["session"]]);
            
            if($response->responseCode == "2001" && $response->params->state == "APPROVED") {
                $autopayout->update([
                    "status"        => 1,
                    "attempts"      => 0,
                    "received_at"   => $timestamp,
                    "reference"     => $response->params->transactionId
                ]);
            }else {
                $autopayout->update([
                    "status"    => 0,
                    "attempts"  => 0
                ]);
            }
            
        } catch(\Exception $ex) {
            Log::error($ex->getMessage());
        }
    }
}
