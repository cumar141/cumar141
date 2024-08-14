<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;

class FirebaseController extends Controller
{
    public function sendToDevice(Request $request) {
        $status = (new FirebaseService())->send_transaction_notification($request->user_id, $request->amount,$request->transaction_type);
        return response()->json(["success" => $status], 200);
    }

}
