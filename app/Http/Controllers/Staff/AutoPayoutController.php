<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\DataTables\PaymentsDataTable;
use App\Models\{
    Transaction,
    Withdrawal,
    Topup,
    AutoPayout
};
use Illuminate\Http\Request;
use Throwable;
use App\Services\CashOut\Helper;
use Carbon\Exceptions\Exception;
use Illuminate\Support\Js;
use PDO;

class AutoPayoutController extends Controller
{
    private $helper;
    
    // constructor
    public function __construct()
    {
        $this->helper = new Helper();
    }

    public function failed(PaymentsDataTable $dataTable)
    {
        return $dataTable->render('staff.autopayout.index');
    }

    public function retry(Request $request)
    {
        try{
            $response = '';
            $autopayout = AutoPayout::where(['trx_reference' => $request->transaction, 'status' => 4]);
            $autopayout->update(['status' => 1, 'attempts' => 0]);
            $response = $autopayout  ? ["status" => "success", "message" => "Operation was successful"] : ["status" => "warning", "message" => "There could be an issue, please contact developer team! {$request->transaction}"];
            return response()->json($response);
        } catch (Throwable $ex) {
            return response()->json(["status" => "error", "message" => "There's an error with this transaction, please contact developers. {$request->transaction}"]);
        }
    }
    
    public function approve(Request $request)
    {
        try {
            $response = '';
            $withdrawal = '';
            $topup = '';

            // Get the transaction
            $transaction = Transaction::where(['uuid' => $request->transaction])
                ->with('user', 'currency')
                ->first();
            $autopayout = AutoPayout::where('trx_reference', $request->transaction)->first();

            if (!$autopayout) {
                $response = response()->json(["status" => "error", "message" => "AutoPayout transaction not found."]);
            }
            $autopayout->status = 2;
            $autopayout->save();
            if ($autopayout->platfrom == settings('name')) {
                $transaction->status = "Success";
                $transaction->payment_status = "Success";
                $transaction->save();

                if ($transaction->transaction_type_id == 13) {
                    $topup = Topup::where(['uuid' => $autopayout->trx_reference])->first();
                    if (!$topup) {
                        response()->json(["status" => "error", "message" => "Topup transaction not found."]);
                    }
                    $topup->updateStatus($topup->id, "Success");
                }
                if ($transaction->transaction_type_id == 2) {
                    $withdrawal = Withdrawal::where(['uuid' => $autopayout->trx_reference])->first();
                    if (!$withdrawal) {
                        response()->json(["status" => "error", "message" => "Withdrawal transaction not found."]);
                    }
                    $withdrawal->updateStatus($withdrawal->id, "Success");
                }
                $response = $autopayout && $transaction && ($autopayout->payment_method == "TOPUP" ? $topup : $withdrawal) ?
                    ["status" => "success", "message" => "Operation was successful approved {$transaction->total} {$transaction->currency->code} to {$transaction->user->first_name} {$transaction->user->last_name}"] :
                    ["status" => "warning", "message" => "There could be an issue, please contact developer team! {$request->transaction}"];
            } else {
                $response = $autopayout ?
                    ["status" => "success", "message" => "Operation was successful"] :
                    ["status" => "warning", "message" => "There could be an issue, please contact developer team! {$request->transaction}"];
            }
            return response()->json($response);
        } catch (Throwable $ex) {
            return response()->json(["status" => "error", "message" => "There's an error with this transaction, please contact developers. {$request->transaction}"]);
        }
    }


    public function block(Request $request)
    {
        try {
            $withdrawal = '';
            $topup = '';
            $autopayout = AutoPayout::where('trx_reference', $request->transaction)->first();
            if (!$autopayout) {
                return response()->json(["status" => "error", "message" => "AutoPayout transaction not found."]);
            }
            $transaction = Transaction::where('uuid', $request->transaction)
                ->with('user', 'currency')
                ->first();

            if ($transaction && $transaction->status !== "Blocked") {
                $transaction->status = 'Blocked';
                $transaction->save();
            }
            if ($autopayout->platform == settings('name')) {
                if ($transaction->transaction_type_id == 13) {
                    $topup = Topup::where(['uuid' => $autopayout->trx_reference])->first();
                    if (!$topup) {
                        return response()->json(["status" => "error", "message" => "topup transaction not found."]);
                    }
                    $topup->updateStatus($topup->id, 'Blocked');
                }
                if ($transaction->transaction_type_id == 2) {
                    $withdrawal = Withdrawal::where(['uuid' => $autopayout->trx_reference])->first();
                    if (!$withdrawal) {
                        return response()->json(["status" => "error", "message" => "Withdrawal transaction not found."]);
                    }
                    $withdrawal->updateStatus($withdrawal->id, 'Blocked');
                }
                if (empty($transaction->user_id) || empty($transaction->currency_id) || empty($transaction->total)) {
                    return response()->json(["status" => "error", "message" => "No user payment Details found to refund."]);
                }

                // Increment wallet amount
                $this->helper->IncrementWalletAmount(
                    $transaction->user_id,
                    $transaction->currency_id,
                    str_replace('-', '', $transaction->total)
                );
                $autopayout->status = 5;
                $autopayout->save();
                return response()->json($autopayout && $transaction || $withdrawal || $topup ?
                    ["status" => "success", "message" => "Successfully refunded {$transaction->total} {$transaction->currency->code} to {$transaction->user->first_name} {$transaction->user->last_name}"] :
                    ["status" => "warning", "message" => "Successfully blocked this transaction but failed to refund! {$request->transaction}"]);
            } else {
                $autopayout->status = 5;
                $autopayout->save();
                return response()->json($autopayout ? ["status" => "success", "message" => "Operation was successful"] : ["status" => "warning", "message" => "There could be an issue, please contact developer team! {$request->transaction}"]);
            }
        } catch (Throwable $ex) {
            return response()->json([
                "status" => "error",
                "message" => "There's an error with this transaction, please contact developers. {$request->transaction}"
            ]);
        }
    }


    public function show()
    {
        return view('staff.autopayout.search');
    }

    public function searchAutoPayout(Request $request)
    {
        $searchQuery = $request->input('search_query');
        if (empty($searchQuery)) {
            return redirect()->back()->withErrors(['error', 'No search params Provided']);
        }
        $data = AutoPayout::where('trx_reference', 'like', '%' . $searchQuery . '%')->get();
        if (!$data) {
            return redirect()->back()->withErrors(['error', 'No data Found']);
        }

        return view('staff.autopayout.search', ['data' => $data]);
    }
    
}