<?php

namespace App\Services;

use App\Jobs\smsJob;

use Exception;

class SmsService {
    public $otp;
    
    public function sendSMS($recipient, $type, $body = '') {
        smsJob::dispatch($recipient, $type, $body)->onQueue('main');
        return true;
    }
    
}