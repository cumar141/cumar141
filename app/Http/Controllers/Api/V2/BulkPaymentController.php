<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;

use App\Services\BulkPaymentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Models\AccountProvider;

class BulkPaymentController extends Controller
{
    protected $service;

    public function __construct(BulkPaymentService $service)
    {
        $this->service = $service;
       
    }
    
    public function process(Request $request)
    {
        try{
            $response = $this->service->process($request);
            return response()->json($response);
        } catch (Exception $e) {
            return response()->json(["status" => "failed", "message" => $e->getMessage()]);
        }
    }
}
