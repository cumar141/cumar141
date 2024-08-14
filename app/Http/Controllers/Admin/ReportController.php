<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\User;
use App\Models\UssdPayment;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Twilio\TwiML\Voice\Pay;
use App\Services\Reports\ReportService;

class ReportController extends Controller
{
    private $transactionService;
   

    public $report_types = [
        'CU' => 'Customer Statement',
        'CB' => 'Customer Balance',
        'DE' => 'Deposits',
        'WI' => 'Withdrawals', 
        'CO' => 'Commission',
        'TR' => 'Transactions',
        'TE' => 'Teller Report',
        'AG' => 'Agent',
        'AP' => 'Auto Payout',
    ]; 

    public function index()
    {
        $data['menu'] = 'reports';

        $data['from'] = $data['to'] = Carbon::now()->format('Y-m-d');

        $data['report_types'] = $this->report_types;

        $users = User::all();

        $currencies = Currency::where('status', 'Active')->get();

        return view('admin.reports.index', $data, compact('users', 'currencies'));
    } 

    public function params(Request $request)
    {
        $report = $request->report;
        $currencies = Currency::where('status', 'Active')->get();
        $statuses = Transaction::distinct()->pluck('status')->all();

        // Check if the report exists
        if (in_array($report, array_keys($this->report_types))) {
            return view('Reports.params.' . $report, compact('currencies', 'statuses'));
        }
        else {
            // Handle unknown report type
            return redirect()->back()->with('error', 'Invalid report type.');
        }

        // If report does not exist, you might want to handle this case or return an error response
    }
    public function generate(Request $request)
    {
        $transactionService = new ReportService();
        // dd($request->all());   
        // Validate the request data
        $request->validate([
            'report_type' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Get parameters from the validated request
        $reportType = $request->report_type;
        $params = [
            'phone' => $request->phone,
            'start_date' => date('Y-m-d', strtotime($request->start_date)),
            'end_date' => date('Y-m-d', strtotime($request->end_date)),
            'currency' => $request->currencyID,
            'status' => $request->status,
            'singall' => $request->singall,
            'payment_method' => $request->payment_method,
            'platform' => $request->platform,
            'partner' => $request->partner,
        ];

        // Check the report type and return the corresponding view
        switch ($reportType) {
            case 'WI':
                return  $transactionService->generateReportWithdrawal($params['phone'], $params['start_date'], $params['end_date'], $params['currency']);
            case 'DE':
                return $transactionService->generateReportDeposit($params['phone'], $params['start_date'], $params['end_date'], $params['currency']);
            case 'CU':
                return $transactionService->generateUserReport($params['phone'], $params['start_date'], $params['end_date'], $params['currency']);
            case 'CB':
                return  $transactionService->generateCustomerBalance($params['phone'], $params['start_date'], $params['end_date']);
            case 'TR':
                return $transactionService->generateTransactions($params['phone'], $params['start_date'], $params['end_date'], $params['status'], $params['currency']);
            case 'CO':
                return $transactionService->generateCommision($params['start_date'], $params['end_date'], $params['status'], $params['currency']);
            case 'TE':
                return $transactionService->generateTellerReport($params['phone'], $params['start_date'], $params['end_date'], $params['currency']);
            case 'AP':
                return $transactionService->generateAutoPayout($params['start_date'], $params['end_date'], $params['status'], $params['payment_method'], $params['platform'], $params['partner']);
            default:
                // Handle unknown report type
                return redirect()->back()->with('error', 'Invalid report type.');
        }
    }
}
