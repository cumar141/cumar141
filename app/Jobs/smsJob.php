<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\SmsConfig;
use App\Services\OTPService;
use Illuminate\Support\Facades\Http;
use Exception;

class smsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipient;
    protected $type;
    protected $body;

    public function __construct($recipient, $type, $body)
    {
        $this->recipient = $recipient;
        $this->type = $type;
        $this->body = $body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->channel = $this->getChannel();
        if ($this->channel->status !== "Active") {
            return false;
        } else if ($this->channel->config->requiresBearerToken) {
            $token = $this->getToken();
            $response = Http::withToken($token)->withBody(json_encode($this->getBody()))->post($this->channel->config->sendEndpoint);
            $body = json_decode($response->body(), true);
            $status = ($response->successful() ? ($body["Result"]["Status"] ?? $body["ResponseCode"]) : false) == 200;
            if(!$status) throw new Exception("Couldn't send sms");
            return $status;
        }
    }

    private function getBody() {
        $body = $this->body;
        if ($this->type == "OTP") {
            $body = $this->getBodyOTP();
        }
        return ["mobile" => $this->_recipient, "message" => $body];
    }
    
    private function getToken() {
        $response;
        if (strtolower($this->channel->config->tokenContentType) == "application/x-www-form-urlencoded") {
            $response = Http::asForm()->post($this->channel->config->tokenEndpoint, (array) $this->channel->config->tokenBody);
        } else if (strtolower($this->channel->config->tokenContentType) == "application/json") {
            $response = Http::withBody(json_encode($this->channel->config->tokenBody), $this->channel->config->tokenContentType)->post($this->channel->config->tokenEndpoint);
        }
        if ($response->successful()) return $response["access_token"];
        throw new Exception("Couldn't get valid token");
    }
    
    private function getChannel() {
        $this->_recipient = substr($this->recipient, -9);
        if (preg_match('/^(61|68|77|63|9)\d{7}$/', $this->_recipient, $matches)) {
            return SmsConfig::where(['provider' => 'hormuud'])->first();
        }
        
        if (preg_match('/^(62|65|66)\d{7}$/', $this->_recipient, $matches)) {
            return SmsConfig::where(['provider' => 'somtel'])->first();
        }
        return SmsConfig::where(['status' => 'Active'])->first();
    }
    
    private function getBodyOTP() {
        $this->otp = (new OTPService())->generate($this->recipient);
        return "Your OTP code is {$this->otp}";
    }
}
