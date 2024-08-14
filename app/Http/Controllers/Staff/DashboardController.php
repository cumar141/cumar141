<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Branch;
use App\Models\Permission;
use App\Models\TransactionType;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Session, DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Helpers\UserPermission;

class DashboardController extends Controller
{
    protected $helper;
    protected $email;
    protected $currency;
    protected $user;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email = new EmailController();
        $this->currency = new Currency();
        $this->user = new User();
    }
    
    public function dashboard()
    {
        $userid = auth()->guard('staff')->user()->id;

        // Get today's date
        $today = Carbon::today();

        // Retrieve deposits, withdrawals, and commissions made today

        // loop throuth all currencies and get the total deposit, withdraw, commission and balance
        $currencies = Currency::where('status', 'Active')->get();
        $financialData = [];
        foreach ($currencies as $currency) {
            $totalDeposit = $this->getTotal('deposit', $userid, $currency->id);
            $totalWithdraw = $this->getTotal('withdraw', $userid, $currency->id);

            $financialData[$currency->code] = [
                'Deposit' => $totalDeposit,
                'Withdraw' => $totalWithdraw,
            ];
        }
        
        $transactionsToday = Transaction::with('user', 'end_user', 'transaction_type', 'currency')
            ->whereDate('created_at', $today)
            ->where(['status' => 'Success', 'user_id' => auth()->guard('staff')->user()->id])
            ->groupBy('id')->latest()->take(30)
            ->orderBy('created_at', 'desc')
            ->get();

        // Retrieve balances for all currencies
        $balances = Wallet::with('currency')->where('user_id', $userid)->get();

        // Retrieve today's transactions
        $DailyTransaction = Transaction::with('user', 'end_user')->select('transactions.*', DB::raw('SUM(CASE WHEN transaction_type_id = 1 THEN subtotal ELSE 0 END) - SUM(CASE WHEN transaction_type_id = 2 THEN subtotal ELSE 0 END) AS balance_at_transaction'))->whereDate('created_at', $today)->where(['status' => 'success', 'user_id' => $userid])->groupBy('id')->latest()->take(10)->orderBy('created_at', 'desc')->get();

        // Prepare data for the chart
        $chartDataToday = [];
        foreach ($DailyTransaction as $transaction) {
            $chartDataToday[] = ['x' => $transaction->created_at->format('Y-m-d H:i:s'), 'y' => $transaction->subtotal];
        }

        $data = [
            'transactionsSummary' =>  $this->TransactionReportSummary(), 
            'treasurersSummary' =>  $this->TreasurerReportSummary(),
            'adminApprovesTreasurerReports' =>  $this->adminApprovesTreasurerReports(),
            'adminCancelTreasurerReports' =>  $this->adminCancelTreasurerReports()
        ];
        
        return view('staff.dashboard', compact('userid', 'transactionsToday', 'financialData', 'balances', 'chartDataToday', 'data'));
    }


    public function GetWalletTransactions(Request $request)
    {
      
        $today = Carbon::today();
        $currencyCode = $request->currencyCode;
        $userId = auth()->guard('staff')->user()->id;

        $transactions = Transaction::with('user', 'end_user', 'transaction_type', 'currency')
            ->whereHas('user', function ($query) use ($userId) {
                $query->where('id', $userId);
            })
            ->whereHas('currency', function ($query) use ($currencyCode) {
                $query->where('code', $currencyCode);
            })
            ->whereDate('created_at', $today)
            ->where('status', 'Success')
            ->get();

        // return json response
        return response()->json($transactions);
    }

    public function showReceipt()
    {
        $today = Carbon::today();
        $user_id =   auth()->guard('staff')->user()->id;

        $transactions = Transaction::with('user', 'end_user', 'transaction_type', 'currency')
            ->where(['user_id' => $user_id, 'status' => 'Success'])
            ->whereDate('created_at', $today)
            ->get();

        if ($transactions->isEmpty()) {
            $error = 'No Receipts (transactions) found for the user';
            return view('staff.receipt.index', compact('error'));
        }
        return view('staff.receipt.index', compact('transactions'));
    }

    public function searchReceipt(Request $request)
    {

        $phone = $request->phone;

        $user = User::where(function ($query) use ($phone) {
            $query
                ->where('phone1', 'like', '%' . $phone . '%')
                ->orWhere('phone2', 'like', '%' . $phone . '%')
                ->orWhere('phone3', 'like', '%' . $phone . '%')
                ->orWhere('formattedPhone', 'like', '%' . $phone . '%');
        })->first();

        if (!$user) {
            return back()->with('error', 'No user found with the phone number');
        }

        $user_id =auth()->guard('staff')->user()->id;

        $transactions = Transaction::with('user', 'end_user', 'transaction_type', 'currency')->where('user_id', $user_id)->get();

        if ($transactions->isEmpty()) {
            return back()->with('error', 'No Transaction found with the phone number');
        }

        return view('staff.receipt.show', compact('transactions'));
    }

    public function printRoute($id)
    {
        $type = 'Deposit';
        $user_id = $id;

        // Fetch transaction details
        $transactionDetails = Transaction::with('user', 'end_user', 'transaction_type', 'currency')
            ->where('id', $id)
            ->first();

        if (!$transactionDetails) {
            // Handle the case where transaction details are not found
            return  redirect()->back()->withErrors('error', 'Transaction details not found');
        }

        // Determine the type based on transaction type ID
        if ($transactionDetails->transaction_type_id == 2) {
            $type = 'Withdrawal';
        }

        // Access currency relationship on the transaction object
        $currency = $transactionDetails->currency;

        // Fetch user details associated with the transaction
        $user = $transactionDetails->user;

        // Pass data to the view
        return view('staff.receipt.print', compact('transactionDetails', 'type', 'user'));
    }
    public function showSelectionForm()
    {
        $managers = $this->showManagers();
        $branch = Branch::all();

        // Add logic here if needed
        return view('staff.dashboard_selection', compact('managers', 'branch'));
    }

    public function showManagers()
    {
        // get all users whose role is manager
        $managers = User::whereHas('role', function ($query) {
            $query->where('name', 'Manager');
        })->get();

        return $managers;
    }
    
    public function selectDashboard(Request $request)
    {
        $request->validate([
            'manager_id' => 'required',
        ]);


        // Get the selected dashboard value from the form
        $selectedDashboard = $request->input('manager_id');


        if ($selectedDashboard === 'my_dashboard') {
            return redirect(route('staff.dashboard'));
        }
        // Redirect to the managerDashboard route with the id parameter
        return redirect()->route('managerDashboard', ['id' => $selectedDashboard]);
    }

    public function TreasurerReportSummary()
    {
        $treasurers = User::whereHas('role', function ($query) {
            $query->where('name', 'Treasurer');
        })->where('status', 'Active')->get();

        $treasurersIDs = $treasurers->pluck('id')->toArray();

        $today = Carbon::today();

        $transactions = Transaction::with('user', 'end_user', 'transaction_type', 'currency')
            ->whereDate('created_at', $today)
            ->whereIn('user_id', $treasurersIDs)
            ->groupBy('id')->latest()->take(50)
            ->orderBy('created_at', 'desc')
            ->get();

        return $transactions;
    }

    public function TransactionReportSummary()
    {
        $today = Carbon::today();

        $transactions = Transaction::with('user', 'end_user', 'transaction_type', 'currency')
            ->whereDate('created_at', $today)
            ->groupBy('id')->latest()
            ->take(50)->orderBy('created_at', 'desc')
            ->get();

        return $transactions;
    }

    public function adminApprovesTreasurerReports()
    {
        $treasurers = User::whereHas('role', function ($query) {
            $query->where('name', 'Treasurer');
        })->where('status', 'Active')->get();

        $treasurersIDs = $treasurers->pluck('id')->toArray();

        $today = Carbon::today();

        $transactions = Transaction::with('user', 'end_user', 'transaction_type', 'currency')
            ->whereDate('created_at', $today)
            ->where('status', 'Success')
            ->whereIn('user_id', $treasurersIDs)
            ->whereColumn('user_id', 'end_user_id')
            ->groupBy('id') ->latest()->take(50)
            ->orderBy('created_at', 'desc')
            ->get();

        return $transactions;
    }

    public function adminCancelTreasurerReports()
    {
        $treasurers = User::whereHas('role', function ($query) {
            $query->where('name', 'Treasurer');
        })->where('status', 'Active')->get();

        $treasurersIDs = $treasurers->pluck('id')->toArray();

        $today = Carbon::today();

        $transactions = Transaction::with('user', 'end_user', 'transaction_type', 'currency')
            ->whereDate('created_at', $today)
            ->where('status', 'Cancelled')
            ->whereIn('user_id', $treasurersIDs)
            ->groupBy('id')->latest()
            ->take(50)->orderBy('created_at', 'desc')
            ->get();

        return $transactions;
    } 

    public function getTotal($type, $user_id, $currency_id)
    {
        $today = Carbon::today();

        if($type == 'deposit') {
            $total = Deposit::whereDate('created_at', $today)
                ->where(['status' => 'Success', 'user_id' => $user_id, 'currency_id' => $currency_id])
                ->sum('amount');
        }else if($type == 'withdraw') {
            $total = Withdrawal::whereDate('created_at', $today)
                ->where(['status' => 'Success', 'user_id' => $user_id, 'currency_id' => $currency_id])
                ->sum('amount');
        }else if($type == 'commission') {
            $total = Transaction::whereDate('created_at', $today)
                ->where(['status' => 'Success', 'user_id' => $user_id, 'currency_id' => $currency_id])
                ->sum('charge_percentage');
        }else {
            $total = Transaction::whereDate('created_at', $today)
                ->where(['status' => 'Success', 'user_id' => $user_id, 'currency_id' => $currency_id])
                ->sum('total');
        }
        
        return $total;
    }
}
