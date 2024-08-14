<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UssdPayment;
use App\Models\Wallet;
use App\Models\Branch;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\TryCatch;
use Session;
use App\Services\Reports\ReportService;

class ReportController extends Controller
{
    private $reportService;

    public $report_types = [
        'CU' => 'Customer Statement',
        'CB' => 'Customer Balance',
        'DE' => 'Deposits', 
        'WI' => 'Withdrawals',
        'CO' => 'Commission',
        'TR' => 'Transactions',
        'TE' => 'Teller Report',
        'AP' => 'Auto Payout',
    ];

    public function index()
    {
        $report_types = [];
        $user = auth()->guard('staff')->user();
        // if user is treasurer or user is accountant
        if (!($user->role->name == 'Treasurer' || $user->role->name == 'accountant' || $user->role->name == 'Manager')) {
            return redirect()->back()->with('error', "You Don't have permission to view this page");  
        }

        $report_types = $this->report_types;
        
        // if user is mamager
        if ($user->role->name == 'Manager') {
            $report_types = [
                'CU' => 'Customer Statement',
                'CB' => 'Customer Balance',
                'DE' => 'Deposits', 
                'WI' => 'Withdrawals',
                'TR' => 'Transactions',
            ];
        }

        return view('staff.admin_reports.index', compact('report_types'));
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

    public function managerTellerReport()
    {
        $user = auth()->guard('staff')->user();
        if(!($user->role->name == 'Treasurer' || $user->role->name == 'Manager' || $user->role->name == 'accountant' ))
        {
            return redirect()->back()->with('error', "You Don't have permission to view this page");
        }
        $today = Carbon::today();


        $branch_id = auth()->guard('staff')->user()->branch_id;

        $branch = Branch::find($branch_id);

        if (!$branch) {
            return redirect()->back()->with('error', 'Branch not found.');
        }
        $transactions = Transaction::with('user', 'end_user', 'transaction_type')
            ->whereHas('user', function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id)
                    ->whereHas('role', function ($roleQuery) {
                        $roleQuery->where('name', 'Teller');
                    });
            })
            ->where('status', 'Success')
            ->whereDate('created_at', '>=', $today)
            ->whereDate('created_at', '<=', $today)
            ->orderBy('created_at', 'asc')->get();

        if (empty($transactions)) {
            $transactions = null;
        }


        $tellers = User::whereHas('role', function ($query) {
            $query->where('name', 'Teller');
        })->where('status', 'Active')->where('branch_id', $branch_id)->get();

        $currencies = Currency::where(['status' => 'Active'])->get();

        return view('staff.reports.tellers.index', compact('tellers', 'currencies', 'branch', 'transactions'));
    }
    public function managerReport()
    {
        $user = auth()->guard('staff')->user();
        if(!($user->role->name == 'Treasurer'  || $user->role->name == 'accountant' ))
        {
            return redirect()->back()->with('error', "You Don't have permission to view this page");
        }
      
        $today = Carbon::today();


        $branch_id = auth()->guard('staff')->user()->branch_id;

        $branch = Branch::find($branch_id);

        if (!$branch) {
            return redirect()->back()->with('error', 'Branch not found.');
        }
        $transactions = Transaction::with('user', 'end_user', 'transaction_type')
            ->whereHas('user', function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id)
                    ->whereHas('role', function ($roleQuery) {
                        $roleQuery->where('name', 'Manager');
                    });
            })
            ->where('status', 'Success')
            ->whereDate('created_at', '>=', $today)
            ->whereDate('created_at', '<=', $today)
            ->orderBy('created_at', 'asc')->get();

        if (empty($transactions)) {
            $transactions = null;
        }


        $manager = User::whereHas('role', function ($query) {
            $query->where('name', 'Manager');
        })->where('status', 'Active')->where('branch_id', $branch_id)->get();

        $currencies = Currency::where(['status' => 'Active'])->get();

        return view('staff.reports.manager.index', compact('manager', 'currencies', 'branch', 'transactions'));
    }


    public function myReports()
    {

        $today = Carbon::today();

        $currencies = Currency::where('status', 'Active')->get();

        $user_id = auth()->guard('staff')->user()->id;
        $user = User::find($user_id);
        $transactions = Transaction::with('user', 'end_user', 'transaction_type')
            ->where('user_id', $user_id)
            ->where('status', 'Success')
            ->whereDate('created_at', '>=', $today)
            ->whereDate('created_at', '<=', $today)
            ->orderBy('created_at', 'asc')->get();
        return view('staff.reports.myReports.index', compact('transactions', 'user', 'currencies'));
    }


    public function treasurer()
    {
        $user = auth()->guard('staff')->user();
        
        $currencies = Currency::where(['status' => 'Active'])->get();
        $treasurers = User::whereHas('role', function ($query) {
            $query->where('name', 'Treasurer');
        })->where('status', 'Active')->get();
        if($user->role->name == 'Treasurer' || $user->role->name == 'Manager' || $user->role->name == 'accountant' )
        {
        return view('staff.accountant.report', compact('currencies', 'treasurers'));
        }
        else{
            return redirect()->back()->with('error', "You Don't have permission to view this page");
        }
    }

    public function AutoPayout()
    {
        $currencies = Currency::where(['status' => 'Active'])->get();
        return view('staff.reports.autoPayout.index');
    }



    public function mr(Request $request)
    {
        $user = auth()->guard('staff')->user();
        return $this->generateReport($request, $user->id);
    }

    public function generateTeller(Request $request)
    {
        $user = auth()->guard('staff')->user();
        if($user->role->name == 'Treasurer' || $user->role->name == 'Manager' || $user->role->name == 'accountant' )
        {

            return $this->generateReport($request, $request->user_id);
        }
        else{
            return redirect()->back()->with('error', "You Don't have permission to view this page");
        }
    }
    public function generateAdminTreasurer(Request $request)
    {
        $user = auth()->guard('admin')->user();
        if($user->role->name == 'Admin')
        {

            return $this->generateReport($request, $request->user_id);
        }
        else{
            return redirect()->back()->with('error', "You Don't have permission to view this page");
        }
    }

    public function generateTreasurer(Request $request)
    {
        $user = auth()->guard('staff')->user();
        if( $user->role->name == 'accountant' )
        {
        return $this->generateReport($request, $request->user_id);
        }
        else{
            return redirect()->back()->with('error', "You Don't have permission to view this page");
        }
    }

    public function generateManager(Request $request)
    {
        $user = auth()->guard('staff')->user();
        if($user->role->name == 'Treasurer' || $user->role->name == 'accountant' )
        {
        return $this->generateReport($request, $request->user_id);
        }
        else{
            return redirect()->back()->with('error', "You Don't have permission to view this page");
        }
    }

    private function generateReport(Request $request, $user_id)
    {
        $startDate = date('Y-m-d', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));
        $currency = $request->currencyID;
        $transaction_type = $request->transaction_type;

        $transaction_type_id = $this->getTransactionTypeId($transaction_type);
        
        return $this->generateTransactions($user_id, $startDate, $endDate, $currency, $transaction_type_id);
    }

    private function getTransactionTypeId($transaction_type)
    {
        switch ($transaction_type) {
            case 'deposit':
                return 1;
            case 'withdrawal':
                return 2;
            case 'transaction':
            default:
                return null;
        }
    }

    private function generateTransactions($user_id, $startDate, $endDate, $currency, $transaction_type = null)
    {
        $user = User::find($user_id);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }
        $phone = $user->formattedPhone;

        $reportService = new ReportService();
        $transactions = $reportService->generateTransaction($phone, $startDate, $endDate, 'Success', $currency, $transaction_type);
        
        if ($transactions->isEmpty()){
            return redirect()->back()->with('error', 'No transactions found.');
        }

        $isAll = is_null($user_id);
        $allCustomer = false;
        $sdate = $startDate;
        $edate = $endDate;
        $walletBalance = ($user_id) ? Wallet::where('user_id', $user_id)
            ->where('currency_id', $currency)
            ->value('balance') : null;

        if($transaction_type == 1){
           return view('Reports.DE_template', compact('transactions', 'user', 'sdate', 'edate', 'isAll', 'allCustomer', 'walletBalance'));
        }
        elseif($transaction_type == 2){
            return view('Reports.WD_template', compact('transactions', 'user', 'sdate', 'edate', 'isAll', 'allCustomer', 'walletBalance'));
        }
        else{
            return view('Reports.tr_template', compact('transactions', 'user', 'startDate', 'endDate', 'isAll', 'allCustomer'));
        }
    }

}
