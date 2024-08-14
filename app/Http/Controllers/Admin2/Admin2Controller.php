<?php

namespace App\Http\Controllers\Admin2;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Branch;
use App\Models\Wallet;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use App\Services\Reports\ReportService;

class Admin2Controller extends Controller
{
    protected $reportService;
    public function index()
    {
        $userId = auth()->guard('admin')->user()->id;
        $managers= $this->showManagers();
        $branch = Branch::all();

        $transactions = $this->getPeningtreasurerTransaction();

        return view('admin2.dashboard', compact('transactions', 'managers', 'branch'));
    }

    
    function getPeningtreasurerTransaction(){

        $treasurerIds = User::whereHas('role', function($query) {
            $query->where('name', 'Treasurer');
        })->pluck('id')->toArray();
        $pendingTransactions = Transaction::with('user', 'end_user', 'transaction_type', 'currency')
        ->whereIn('user_id', $treasurerIds)
        ->whereColumn('user_id', 'end_user_id')
        ->where('status', 'Pending')->get();
        return $pendingTransactions;
    }


    public function transactions()
    {
        $userId = auth()->guard('admin')->user()->id;
        $transactions = $this->getPeningtreasurerTransaction();

        return view('admin2.transactions', compact('transactions'));
    }

    public function profile()
    {
        $userId = auth()->guard('admin')->user()->id;
        $user = Admin::find($userId);

        return view('admin2.profile', compact('user'));
    }
    
    public function showManagers()
    {
        $user_id = auth()->guard('admin')->user()->id;
        
        $managers = User::whereHas('role', function($query) {
            $query->where('name', 'Manager');
        })->where('status', 'Active')->get();
    
        return $managers;
    }

    public function approve(Request $request)
    {
        $userId = auth()->guard('admin')->user()->id;
        $transaction = Transaction::find($request->id);
        $uuid = $transaction->uuid;
        $currency_id = $transaction->currency_id;
        $amount = $transaction->total;
        $wallet = Wallet::firstOrCreate(['user_id' => $transaction->user_id, 'currency_id' => $currency_id], ['balance' => 0]);
        $balance = $wallet->balance + $amount;
        $transaction->balance = $balance;
        $transaction->status = 'Success';
        $transaction->save();

        $deposit = Deposit::where('uuid', $uuid)->first();
        $deposit->balance = $balance;
        $deposit->status = 'Success';
        $deposit->save();

        $wallet->balance += $amount;
        $wallet->save();

        $data['transInfo']['currency_id'] = $transaction->currency->id;
        $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
        $data['transInfo']['subtotal'] = $transaction->subtotal;
        $data['transInfo']['id'] = $transaction->id;
        $data['transInfo']['note'] = $transaction->note;
        $data['users'] = User::find($transaction->user_id, ['id']);
        $data['transactionDetails'] = $transaction;

        return view('admin2.print', $data);
    }

    public function reject(Request $request)
    {
        $userId = auth()->guard('admin')->user()->id;
        $transaction = Transaction::find($request->id);
        $transaction->status = 'Cancelled';
        $transaction->save();

        return redirect()->route('admin2.dashboard');
    }

    public function reports($id)
    {
        $managers=$this->showBranchManagers($id);
        $tellers =$this->showBranchTellers($id);
        $branch = Branch::find($id);
        $currencies = Currency::all();

        return view('admin2.reports', compact('managers', 'branch', 'tellers', 'currencies'));
    }

    public function showBranchManagers($branch_id)
    {
        $user_id = auth()->guard('admin')->user()->id;
        
        // get all users whose role is manager
        $managers = User::whereHas('role', function($query) {
            $query->where('name', 'Manager');
        })->where('status', 'Active')->where('branch_id', $branch_id)->get();
    
        return $managers;
    }
    
    public function showBranchTellers($branch_id)
    {
        $user_id = auth()->guard('admin')->user()->id;
        
        $teller = User::whereHas('role', function($query) {
            $query->where('name', 'Teller');
        })->where('status', 'Active')->where('branch_id', $branch_id)->get();
    
        return $teller;
    }

    public function treasury()
    {
        $currencies = Currency::where(['status' => 'Active'])->get();
        $treasurers = User::whereHas('role', function($query) {
            $query->where('name', 'Treasurer');
        })->where('status', 'Active')->get();
        return view('admin2.treasureReport', compact('currencies', 'treasurers'));
    }

    public function approved()
    {
        $reportHolder = "Admin Approved Transactions Report";
        $treasurerIds = User::whereHas('role', function($query) {
            $query->where('name', 'Treasurer');
        })->pluck('id')->toArray();
        $users = User::whereHas('role', function($query) {
            $query->where('name', 'Treasurer');
        })->where('status', 'Active')->get();
        $transactions = Transaction::with('user', 'end_user', 'transaction_type', 'currency')
        ->whereIn('user_id', $treasurerIds)
        ->whereColumn('user_id', 'end_user_id')
        ->where('status', 'Success')->get();

        $branch = Branch::all();

        $reportType = "transactions";
      
        return view('admin2.approved', compact('transactions', 'reportHolder', 'users', 'branch', 'reportType'));
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

        if ($transactions->isEmpty() || $transactions->count() == 0){
            return redirect()->back()->with('error', 'No transactions found.');
        }

        $isAll = is_null($user_id);
        $allCustomer = false;
        $sdate = $startDate;
        $edate = $endDate;
        $balance = ($user_id) ? Wallet::firstOrCreate(['user_id' => $user_id, 'currency_id' => $currency], ['balance' => 0])->balance : null;

        if($transaction_type == 1){
           return view('Reports.DE_template', compact('transactions', 'user', 'sdate', 'edate', 'isAll', 'allCustomer', 'balance'));
        }
        elseif($transaction_type == 2){
            return view('Reports.WD_template', compact('transactions', 'user', 'sdate', 'edate', 'isAll', 'allCustomer', 'balance'));
        }
        else{
            return view('Reports.tr_template', compact('transactions', 'user', 'startDate', 'endDate', 'isAll', 'allCustomer'));
        }

    }
}
