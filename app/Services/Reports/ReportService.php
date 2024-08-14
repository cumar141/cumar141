<?php

namespace App\Services\Reports;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Currency;
use App\Models\TransactionType;
use App\Models\UssdPayment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportService
{
    // Generate transaction report service
    public function generateTransaction($phone, $startDate, $endDate, $status, $currency, $transaction_type_id = null)
    {
        // dd($phone, $startDate, $endDate, $status, $currency, $transaction_type_id);
       
        $query = Transaction::with(['currency', 'transaction_type', 'user', 'end_user'])
            ->orderBy('created_at', 'asc');

        if ($phone) {
            $user = $this->getUserByPhone($phone);
            if ($user && $user !== true) {
                $query->where('user_id', $user->id);
            }
        }
        if(isset($startDate) && isset($endDate)){
            $startDates = Carbon::parse($startDate)->startOfDay()->toDateTimeString();
            $endDates = Carbon::parse($endDate)->endOfDay()->toDateTimeString();
            $query->whereBetween('created_at', [$startDates, $endDates]);
        }
        elseif(isset($startDate)){
            $startDates = Carbon::parse($startDate)->startOfDay()->toDateTimeString();
            $query->where('created_at', '>=', $startDates);
        }
        elseif(isset($endDate)){
            $endDates = Carbon::parse($endDate)->endOfDay()->toDateTimeString();
            $query->where('created_at', '<=', $endDates);
        }
        else{ 
            $query->where('created_at', '>=', now()->subDays(30));
        }

        if ($currency) {
            $query->where('currency_id', $currency);
        }

        if ($transaction_type_id) {
           if($transaction_type_id == 1)
           {
            $query->whereIn('transaction_type_id', [1, 4, 8]);
           }
            elseif($transaction_type_id == 2)
            {
            $query->whereIn('transaction_type_id', [2, 3, 7]);
            }
            else
            {
            $query->where('transaction_type_id', $transaction_type_id);
            }
        }

        $statusFilter = $status ?? 'Success';
        $query->where('status', $statusFilter);
        return $query->get();
    }

    public function generateTransactions($phone, $startDate, $endDate, $status, $currency,)
    {
        $user = User::all();
        $wallets = '';
        $isAll = false;
        if (!empty($phone)) {
            $user = $this->getUserByPhone($phone);
           
            if($user === false)
            {
                $data=['message'=>'User not found.'];
                return view('Reports.error',  $data);
            }
           
            $transactions = $this->generateTransaction($phone, $startDate, $endDate, $status, $currency, null);
   
        } elseif (empty($phone )) {
            $transactions = $this->generateTransaction(null, $startDate, $endDate, $status, $currency, null);
            $isAll = true;
        } else {
            $transactions = $this->generateTransaction(null, $startDate, $endDate, $status, $currency, null);
            $isAll = true;
        }

        if($transactions->isEmpty())
        {
            $data =['message'=>'No transaction found for the provided conditions.'];
            return view('Reports.error',  $data);
        }

        return view('Reports.tr_template', compact('transactions', 'user', 'startDate', 'endDate', 'isAll'));
    }

    public function generateReportDeposit($phone, $startDate, $endDate, $currency){
        $user = $this->getUserByPhone($phone);
        if ($user === false) {
            $data=['message'=>'User not found.'];
            return view('Reports.error',  $data);
        }

        $transactions = $this->generateTransaction($phone, $startDate, $endDate, null, $currency, 1);

        if ($transactions->isEmpty()) {
           $data =['message'=>'No transaction found for the provided conditions.'];
            return view('Reports.error',  $data);
        }

        $walletBalance = $this->getWalletBalance($phone, $currency);

            return view('Reports.DE_template', [
                'allCustomer' => $user === null,
                'user' => $user === null ? User::all() : $user,
                'transactions' => $transactions,
                'walletBalance' => $walletBalance,
                'sdate' => $startDate,
                'edate' => $endDate,
            ]);
    }

    public function generateReportWithdrawal($phone, $startDate, $endDate, $currency)
    {
        $user = $this->getUserByPhone($phone);

        if ($user === false) {
            $data=['message'=>'User not found.'];
            return view('Reports.error',  $data);
        }

        $transactions = $this->generateTransaction($phone, $startDate, $endDate, null, $currency, 2);

        if ($transactions->isEmpty()) {
            $data =['message'=>'No transaction found for the provided conditions.'];
            return view('Reports.error',  $data);
        }

        $walletBalance =  $this->getWalletBalance($phone, $currency);

        return view('Reports.WD_template', [
            'allCustomer' => $user === null,
            'user' => $user === null ? User::all() : $user,
            'transactions' => $transactions,
            'walletBalance' => $walletBalance,
            'sdate' => $startDate,
            'edate' => $endDate,
        ]);
    }

    public function generateUserReport($phone, $startDate, $endDate, $currency)
    {
        $user = $this->getUserByPhone($phone);

        if ($user === false) {
            $data=['message'=>'User not found.'];
            return view('Reports.error',  $data);
        }
       
        $transactions = $this->generateTransaction($phone, $startDate, $endDate, null, $currency);
        if ($transactions->isEmpty()) {
            $data =['message'=>'No transaction found for the provided conditions.'];
            return view('Reports.error',  $data);
        }

        $openingBalance = $transactions->first()->balance;
        $availableBalance = $transactions->last()->balance;

        $walletBalance =  $this->getWalletBalance($phone, $currency);

        $transactionTypes = TransactionType::all();

        return view('Reports.user', [
            'user' => $user,
            'transactions' => $transactions,
            'transactionTypes' => $transactionTypes,
            'availableBalance' => $availableBalance,
            'walletBalance' => $walletBalance,
            'openingBalance' => $openingBalance,
            'sdate' => $startDate,
            'edate' => $endDate,
        ]);
    }

    public function generateCustomerBalance($phone, $startDate, $endDate)
    {
        $userDetails = [];
        $user = '';
        $users = User::with('wallets')->get();
        $isAll = '';
        $currencies = Currency::where('status', 'Active')->get();
        $wallets = Wallet::with('currency')->get();

        if (empty($phone)) {
            $transactions = $this->generateTransaction(null, null, $endDate, 'Success', null, null);

            foreach ($users as $user) {
                $userBalances = []; // Array to store balances for each currency
                $latestTransactions = []; // Array to store latest transactions for each currency

                foreach ($currencies as $currency) {
                    $latestTransactions[$currency->id] = null;
                }

                foreach ($transactions as $transaction) {
                    if ($transaction->user->id === $user->id) {
                        $currencyCode = $transaction->currency->id;

                        if (!$latestTransactions[$currencyCode] || $transaction->created_at > $latestTransactions[$currencyCode]->created_at) {
                            $latestTransactions[$currencyCode] = $transaction;
                        }
                    }
                }

                foreach ($currencies as $currency) {
                    $latestTransaction = $latestTransactions[$currency->id];
                    $userBalances[$currency->id] = $latestTransaction ? $latestTransaction->balance : 0;
                }

                $userDetails[] = [
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'phone' => $user->formattedPhone,
                    'balances' => $userBalances,
                ];
            }

            $isAll = true;
        } elseif (!empty($phone)) {
            

            $user = $this->getUserByPhone($phone);
            if ($user === false) {
                $data=['message'=>'User not found.'];
                return view('Reports.error',  $data);
            }
            $userId = $user->id;

            $transactions = $this->generateTransaction($phone, null, $endDate, 'Success', null, null);

            $userBalances = []; // Array to store balances for each currency
            $latestTransactions = []; // Array to store latest transactions for each currency

            foreach ($currencies as $currency) {
                $latestTransactions[$currency->id] = null;
            }

            foreach ($transactions as $transaction) {
                $currencyCode = $transaction->currency->id;

                if (!$latestTransactions[$currencyCode] || $transaction->created_at > $latestTransactions[$currencyCode]->created_at) {
                    $latestTransactions[$currencyCode] = $transaction;
                }
            }

            foreach ($currencies as $currency) {
                $latestTransaction = $latestTransactions[$currency->id];
                $userBalances[$currency->id] = $latestTransaction ? $latestTransaction->balance : '0.00';
            }
            $wallets = Wallet::where('user_id', $userId)->with('currency')->get();

            $userDetails[] = [
                'name' => $user->first_name . ' ' . $user->last_name,
                'phone' => $user->formattedPhone,
                'balances' => $userBalances,
            ];

            $isAll = false;
        }
        return view('Reports.cb_template', compact('userDetails', 'users', 'user', 'startDate', 'endDate', 'isAll', 'wallets'));
    }

    public function generateCommision($startDate, $endDate, $status, $currency)
    {
        $transactions = $this->generateTransaction(null, $startDate, $endDate, $status, $currency, null);
        return view('Reports.co_template', compact('transactions', 'startDate', 'endDate'));
    }

    public function generateTellerReport($phone, $startDate, $endDate, $currency)
    {
        $transactions = '';
        $openingBalance = 0;
        $userId = '';
        $user = User::where(function ($query) use ($phone) {
            $query->where('teller_uuid', 'like', '%' . $phone . '%');

        })->first();

        if (!$user) {
            $data =['message'=>'Teller Not found.'];
            return view('Reports.error',  $data);
        } else {
            $userId = $user->id;
        }

        $transactions = $this->generateTransaction($phone, $startDate, $endDate, null, $currency, null);
        // if user is empty
        if ($transactions->isEmpty()) {
            $data =['message'=>'No transaction found for the provided conditions.'];
            return view('Reports.error',  $data);
        }

        $openingBalance = $transactions->first()->balance;

        return view('Reports.te_template', compact('transactions', 'startDate', 'endDate', 'user', 'openingBalance'));
    }

    public function generateAutoPayout($startDate, $endDate, $status, $payment_method, $platform, $partner)
    {
        // dd($startDate, $endDate, $status, $payment_method, $platform);
        $query = UssdPayment::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc');

        if ($status != 'all') {
            $query->where('status', $status);
        }

        if ($platform != 'all') {
            $query->where('platform', $platform);
        }

        if ($payment_method != 'all') {
            $query->where('payment_method', $payment_method);
        }

        if ($partner != 'all') {
            $query->where('partner', $partner);
        }

        $ussdPayments = $query->get();

        return view('Reports.ap_template', compact('ussdPayments', 'startDate', 'endDate'));
    }

    private function getUserByPhone($phone)
    {
        if (empty($phone)) {
            return null; 
        }

        $user = User::where(function ($query) use ($phone) {
            $query->where('phone1', 'like', '%' . $phone . '%')
                ->orWhere('phone2', 'like', '%' . $phone . '%')
                ->orWhere('phone3', 'like', '%' . $phone . '%')
                ->orWhere('formattedPhone', 'like', '%' . $phone . '%');
        })->first();

        return $user ? $user : false;
    }

    private function getWalletBalance($phone, $currency)
    {
        $user = $this->getUserByPhone($phone);
        if (!$user) {
            return null;
        }

        $wallet = Wallet::where('user_id', $user->id)
            ->where('currency_id', $currency)
            ->first();

        return $wallet ? $wallet->balance : null;
    }
}
