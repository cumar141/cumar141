<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\Deposit;
use App\Models\Wallet;
use App\Models\Branch;
use App\Models\Currency;

use Illuminate\Support\Facades\Session;

class TreasuryReportController extends Controller
{
    // Add your methods here



    public function treasuryReport($id)
    {
        $branch_id = $id;

        $branch = Branch::find($branch_id);

        if(!$branch){
            return redirect()->back()->with('error', 'Branch not found.');
        }

        $managers = User::whereHas('role', function($query) {
            $query->where('name', 'Manager');
        })->where('status', 'Active')->where('branch_id', $branch_id)->get();
        
        $tellers = User::whereHas('role', function($query) {
            $query->where('name', 'Teller'); 
        })->where('status', 'Active')->where('branch_id', $branch_id)->get();

        $currencies = Currency::all();

        return view('staff.treasurer.treasuryReport.index', compact('managers', 'tellers', 'branch', 'currencies'));
    }


    // =================================================================================
    // Generate button click
    // =================================================================================

    public function generate(Request $request){

        // dd($request->all());
        // Validate the request data
        // $request->validate([
        //     'type' => 'required',
        // ]);


        // extract the validated data

        $type = $request->type;
        $start = $request->start_date;
        $end = $request->end_date;
        $currency = $request->currency;
        $teller_id = $request->teller_id;
        $manager_id = $request->manager_id;
        $branch_id = $request->branch_id;
        $reportType = $request->reportType;
        $treasurer_id = $request->treasurer_id;
        
        $startDate = date('Y-m-d', strtotime($start));
        $endDate = date('Y-m-d', strtotime($end));



        // Check the report type and return the corresponding view

        // dd($request);
  
        if($type == 'manager'){

            return $this->generateManagerReport($manager_id, $startDate, $endDate, $currency, $branch_id, $reportType);
        }
        elseif ($type == 'teller') {
            return $this->generateTellerReport($teller_id, $startDate, $endDate, $currency, $branch_id, $reportType);
        }
        elseif($type == 'treasurer')
        {
            return $this->generateTreasurerReport($treasurer_id, $startDate, $endDate, $currency,  $reportType);
        }
        else {
            // Handle unknown report type
            return redirect()->back()->with('error', 'Invalid report type.');
        }
    }



    // =================================================================================
    // Generate the manager report
    // =================================================================================



    public function generateManagerReport($manager_id, $startDate, $endDate, $currency, $branch_id, $reportType)
    {

        // get branch name
        $branch = Branch::find($branch_id)->name;


        $reportHolder = '';
        // Get the manager
        $user = User::find($manager_id);

        $wallets = null;
        $openingBalance = 0;
        $availlableBalance = 0;

        // Get the Teller's opening balance
        if($currency != 'all' && !empty($currency)){
          $openingBalance = $this->calculateOpeningBalance($manager_id, $currency, $startDate, $endDate);
          $availlableBalance = $this->getCurrentBalance($manager_id, $currency);
      }
      if($currency == 'all' || empty($currency)){
          $wallets = Wallet::with('currency')->where('user_id', $manager_id)->get();
      }

        // dd($transactions, $startDate, $endDate, $user, $openingBalance, $availlableBalance,  $reportHolder, $branch);

        if($reportType == 'transaction'){
            $reportHolder = 'Manager Transaction Report';

            // Get the manager's transactions
            $transactions = $this->getTransactions($manager_id, $currency, $startDate, $endDate);

        }
        elseif ($reportType == 'deposit') {
            $reportHolder = 'Manager Deposit Report';
            // Get the manager's deposits
            $transactions = $this->getDeposits($manager_id, $currency, $startDate, $endDate);
        }
        elseif ($reportType == 'withdrawal') {
            $reportHolder = 'Manager Withdrawal Report';
            // Get the manager's withdrawals
            $transactions = $this->getWithdrawals($manager_id, $currency, $startDate, $endDate);
        }
        else
        {
            return redirect()->back()->with('error', 'Invalid report type.');
        }
        return view('staff.treasurer.treasuryReport.view_report', compact('transactions', 'startDate', 'endDate', 'user', 'openingBalance', 'availlableBalance',  'reportHolder', 'branch', 'reportType', 'wallets'));

    }


    // =================================================================================
    // Generate the teller report
    // =================================================================================

    public function generateTellerReport($teller_id, $startDate, $endDate, $currency, $branch_id, $reportType)
    {
        // get branch name
        $branch = Branch::find($branch_id)->name;


        $reportHolder = '';
        // Get the Teller
        $user = User::find($teller_id);
        $wallets = null;
        $openingBalance = 0;
        $availlableBalance = 0;

          // Get the Teller's opening balance
          if($currency != 'all' && !empty($currency)){
            $openingBalance = $this->calculateOpeningBalance($teller_id, $currency, $startDate, $endDate);
            $availlableBalance = $this->getCurrentBalance($teller_id, $currency);
        }
        if($currency == 'all' || empty($currency)){
            $wallets = Wallet::with('currency')->where('user_id', $teller_id)->get();
        }


        if($reportType == 'transaction'){
            $reportHolder = 'Teller Transaction Report';

            // Get the Teller's transactions 
            $transactions = $this->getTransactions($teller_id, $currency, $startDate, $endDate);

        }
        elseif ($reportType == 'deposit') {
            $reportHolder = 'Teller Deposit Report';
            // Get the Teller's deposits
            $transactions = $this->getDeposits($teller_id, $currency, $startDate, $endDate);

        }
        elseif ($reportType == 'withdrawal') {
            $reportHolder = 'Teller Withdrawal Report';
            
            $transactions = $this->getWithdrawals($teller_id, $currency, $startDate, $endDate);

           

        }
        else
        {
            return redirect()->back()->with('error', 'Invalid report type.');
        }

        return view('staff.treasurer.treasuryReport.view_report', compact('transactions', 'startDate', 'endDate', 'user', 'openingBalance', 'availlableBalance',  'reportHolder', 'branch', 'reportType', 'wallets'));
        
    }


    // =================================================================================
    // Generate the treasurer report
    // =================================================================================

    public function generateTreasurerReport($treasurer_id, $startDate, $endDate, $currency, $reportType)
    {
         // get branch name
         $branch = 'All Branches';


         $reportHolder = '';
         // Get the manager
         $user = User::find($treasurer_id);
 
         $wallets = null;
         $openingBalance = 0;
         $availlableBalance = 0;
 
         // Get the Teller's opening balance
         if($currency != 'all' && !empty($currency)){
           $openingBalance = $this->calculateOpeningBalance($treasurer_id, $currency, $startDate, $endDate);
           $availlableBalance = $this->getCurrentBalance($treasurer_id, $currency);
       }
       if($currency == 'all' || empty($currency)){
           $wallets = Wallet::with('currency')->where('user_id', $treasurer_id)->get();
       }
 
         // dd($transactions, $startDate, $endDate, $user, $openingBalance, $availlableBalance,  $reportHolder, $branch);
 
         if($reportType == 'transaction'){
             $reportHolder = 'Manager Transaction Report';
 
             // Get the manager's transactions
             $transactions = $this->getTransactions($treasurer_id, $currency, $startDate, $endDate);
 
         }
         elseif ($reportType == 'deposit') {
             $reportHolder = 'Manager Deposit Report';
             // Get the manager's deposits
             $transactions = $this->getDeposits($treasurer_id, $currency, $startDate, $endDate);
         }
         elseif ($reportType == 'withdrawal') {
             $reportHolder = 'Manager Withdrawal Report';
             // Get the manager's withdrawals
             $transactions = $this->getWithdrawals($treasurer_id, $currency, $startDate, $endDate);
         }
         else
         {
             return redirect()->back()->with('error', 'Invalid report type.');
         }

        return view('staff.treasurer.treasuryReport.view_report', compact('transactions', 'startDate', 'endDate', 'user', 'openingBalance', 'availlableBalance',  'reportHolder', 'branch', 'reportType', 'wallets'));
    }


    // =================================================================================
    // End of Generate the treasurer report
    // =================================================================================


    // =================================================================================
    // return treasurer view
    // =================================================================================

    public function treasurer()
    {
        $currencies = Currency::where(['status' => 'Active'])->get();
        $treasurers = User::whereHas('role', function($query) {
            $query->where('name', 'Treasurer');
        })->where('status', 'Active')->get();
        return view('staff.accountant.report', compact('currencies', 'treasurers'));
    }




    // =================================================================================
    // Helper methods
    // =================================================================================


    private function calculateOpeningBalance($userId, $currency, $startDate, $endDate)
    {
        // Query transactions for the user and currency before the start date
        $openingTransactions = Transaction::where('user_id', $userId)
            ->where('currency_id', $currency)
            ->where('status', 'Success')
            ->whereDate('created_at', '<', $startDate)
            ->orderBy('created_at', 'asc')
            ->get();
    
        // Calculate the opening balance based on the transactions
        $openingBalance = 0;
    
        foreach ($openingTransactions as $transaction) {
            $openingBalance += $transaction->total;
        }
    
        return $openingBalance;
    }




    // =================================================================================
    // Get the current balance of a user
    // =================================================================================


    public function getCurrentBalance($userId, $currency)
    {
        $wallet = Wallet::where('user_id', $userId)
            ->where('currency_id', $currency)
            ->first();
    
        if ($wallet) {
            return $wallet->balance;
        }
        // dd($wallet);
    
        return 0;
    }


    // =================================================================================
    // End of Helper methods
    // =================================================================================


 //================================================
    //   Transaction functions
    //==================================================

    public function getTransactions($userId, $currency, $startDate, $endDate)
    {
        $query = Transaction::with('user', 'end_user', 'transaction_type')
            ->where('user_id', $userId)
            ->where('status', 'Success')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc');
    
        if (!empty($currency) && $currency != 'all') {
            $query->where('currency_id', $currency);
        }
    
        $transactions = $query->get();
    
        // if ($transactions->isEmpty()) {
        //     $transactions = '';
        // }
    
        return $transactions;
    }
    

    // =================================================================================
    // Get Deposit
    // =================================================================================

    public function getDeposits($userId, $currency, $startDate, $endDate)
    {
        $query = Deposit::with(['currency', 'user', 'transaction'])
            ->where('user_id', $userId)
            ->where('status', 'Success')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc');

            if (!empty($currency) && $currency != 'all') {
            $query->where('currency_id', $currency);
        }

        $deposits = $query->get();

        // if ($deposits->isEmpty()) {
        //     return back()->with('error', 'No deposits found for the provided conditions.');
        // }

        return $deposits;
    }


    // =================================================================================
    // End of Get Deposit
    // =================================================================================


    // =================================================================================
    // Get Withdrawals
    // =================================================================================

    public function getWithdrawals($userId, $currency, $startDate, $endDate)
    {
        $query = Withdrawal::with(['currency', 'user', 'transaction'])
            ->where('user_id', $userId)
            ->where('status', 'Success')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc');
    
            if (!empty($currency) && $currency != 'all') {
            $query->where('currency_id', $currency);
        }
    
        $withdrawals = $query->get();
    
        // if (!empty($currency) && $currency != 'all') {
        //     return back()->with('error', 'No withdrawals found for the provided conditions.');
        // }
    
        return $withdrawals;
    }
    

    // =================================================================================
    // End of Get Withdrawals
    // =================================================================================


    // =================================================================================
    // End of Get Withdrawals
    // =================================================================================


    // =================================================================================
    // Get Transfers
    // =================================================================================

    public function getTransfers($userId, $currency, $startDate, $endDate)
    {
        $transfers = Transaction::with('currency', 'transaction_type', 'user')
            ->where('status', 'Success')
            ->where('currency_id', $currency)
            ->where('user_id', $userId)
            ->where('transaction_type_id', 3)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc')
            ->get();
    
        return $transfers;
    }


}
