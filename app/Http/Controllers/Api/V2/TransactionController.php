<?php

/**
 * @package TransactionController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 08-12-2022
 */

namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\GetTransactionListRequest;
use App\Exceptions\Api\V2\TransactionException;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class TransactionController extends Controller
{

    /**
     * All transaction list
     *
     * @param TransactionService $service
     * @return JsonResponse
     */
    public function list(GetTransactionListRequest $request, TransactionService $service)
    {
        try {
            $type    = $request->has('type') ? $request->type: 'allTransactions'; // TODO::(Optimization) type will be more dynamic in future
            $offset  = $request->has('offset') ? $request->offset: 0;
            $limit   = $request->has('limit') ? $request->limit: 10;
            $order   = $request->has('order') ? $request->order: "desc";
            $filters = [];
            if(!empty($request->filters)) {
                if(!empty($request->filters["user"])) {
                    $filters['transactions.end_user_id'] = User::where(['formattedPhone' => $request->filters["user"]])
                    ->orWhere('phone1', $request->filters["user"])
                    ->orWhere('phone2', $request->filters["user"])
                    ->orWhere('phone3', $request->filters["user"])
                    ->first()->id;
                }
                
                if(!empty($request->filters["date"])) {
                    $filters['date'] = $request->filters["date"];
                }
            }
            $data    = $service->list($type, auth()->user()->id, $offset, $limit, $order, $filters);
            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }
        

    /**
     * Get details of a transaction
     *
     * @param Request $request
     * @param TransactionService $service
     * @return JsonResponse
     * @throws TransactionException
     */
    public function details(Request $request, TransactionService $service)
    {
        try {
            $transaction = $service->details($request->tr_id, auth()->user()->id);
            return $this->successResponse($transaction);
        } catch (TransactionException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (\Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }
    
    public function types(TransactionService $service)
    {
        try {
            return $this->successResponse($service->types());
        } catch (TransactionException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (\Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }
}
