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

class ManagerDashboardController extends Controller
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

    public function dashboard($id)
    {
        $userid = $id;
        $today = Carbon::today();
        
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
            ->where(['status' => 'Success', 'user_id' => $userid])
            ->groupBy('id')->latest()->take(30)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Retrieve balances for all currencies
        $balances = Wallet::with('currency')->where('user_id', $userid)->get();
        
        // Retrieve today's transactions
        $DailyTransaction = Transaction::with('user', 'end_user')->select('transactions.*', DB::raw('SUM(CASE WHEN transaction_type_id = 1 THEN subtotal ELSE 0 END) - SUM(CASE WHEN transaction_type_id = 2 THEN subtotal ELSE 0 END) AS balance_at_transaction'))->whereDate('created_at', $today)->where('status', 'success')->where('user_id', $userid)->groupBy('id')->latest()->take(10)->orderBy('created_at', 'desc')->get();
        
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
        
        return view('staff.dashboard', compact('transactionsToday', 'financialData', 'balances', 'chartDataToday'));
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
            ->groupBy('id')->latest()->take(50)
            ->orderBy('created_at', 'desc')
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
            ->groupBy('id')->latest()->take(50)
            ->orderBy('created_at', 'desc')
            ->get();

        return $transactions;
    } 
}