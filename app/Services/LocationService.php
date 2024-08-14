<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LocationService {
    
   public function getLocation($ipAddress) {
        $accessKey = 'c5e0a6cb-bf48-4474-b447-7d6e0fc8c4f3';
        $url = "https://apiip.net/api/check?ip=$ipAddress&accessKey=$accessKey";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json_res = curl_exec($ch);
        curl_close($ch);
        $api_result = json_decode($json_res, true);
        return $api_result;
    }
}