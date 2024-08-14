<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Http\{
    Request,
    JsonResponse
    };

class ValidateService {
    
    public function checkPhoneNumberFormat($phone)
{
    try {
        $phoneDigits = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phoneDigits) == 12 && strpos($phone, '+') === 0) {
            return [
                "code"    => 200,
                "message" => "Success",
                "valid"   => "True"
            ];
        }elseif ((strlen($phoneDigits) == 13) && ((strpos($phone, '+86') === 0) || (strpos($phone, '+43') === 0))) {
            return [
                "code"    => 200,
                "message" => "Success",
                "valid"   => "True"
            ];
        }

        $prefixes = ['61', '62', '63', '64', '65', '66', '67', '68', '69', '70', '71', '72', '77'];

        foreach ($prefixes as $prefix) {
            if (strpos($phoneDigits, $prefix) === 0 && strlen($phoneDigits) == 9) {
                return [
                    "code"    => 200,
                    "message" => "Success",
                    "valid"   => "True"
                ];
            }
        
        }

        return [
            "code"    => 422,
            "message" => "Please check your phone number!",
            "valid"   => "False"
        ];
    } catch (Exception $e) {
        return response()->json([
            "response" => [
                "status" => ["code" => 422, "message" => "Failed to process request"]
            ]
        ], 422);
    }
}



   
}