<?php

namespace App\Http\Controllers\Staff;

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


class AdminReportsController extends Controller
{

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
        $user = auth()->user();
        // if user is treasurer or user is accountant
        if ($user->role->name == 'Treasurer' || $user->role->name == 'accountant') {
            $report_types = $this->report_types;

        }
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
                return $this->generateReportWithdrawal($params['phone'], $params['start_date'], $params['end_date'], $params['currency']);
            case 'DE':
                return $this->generateReportDeposit($params['phone'], $params['start_date'], $params['end_date'], $params['currency']);
            case 'CU':
                return $this->generateUserReport($params['phone'], $params['start_date'], $params['end_date'], $params['currency']);
            case 'CB':
                return $this->generateCustomerBalance($params['phone'], $params['start_date'], $params['end_date']);
            case 'TR':
                return $this->generateTransaction($params['phone'], $params['start_date'], $params['end_date'], $params['status'], $params['currency']);
            case 'CO':
                return $this->generateCommision($params['start_date'], $params['end_date'], $params['status'], $params['currency']);
            case 'TE':
                return $this->generateTellerReport($params['phone'], $params['start_date'], $params['end_date'], $params['currency']);
            case 'AP':
                return $this->generateAutoPayout($params['start_date'], $params['end_date'], $params['status'], $params['payment_method'], $params['platform'], $params['partner']);
            default:
                // Handle unknown report type
                return redirect()->back()->with('error', 'Invalid report type.');
        }
    }

    private function calculateAvailableBalance($transactions)
    {
        // Calculate the available balance based on the transactions
        $balance = 0;

        foreach ($transactions as $transaction) {
            // Determine if the transaction is debit or credit
            $transactionType = $transaction->total >= 0 ? 'credit' : 'debit';

            // Update the balance based on the transaction type
            if ($transactionType === 'credit') {
                $balance += $transaction->total;
            } else {
                $balance -= abs($transaction->total);
            }

            // Add the transaction type to the transaction object (optional)
            $transaction->transactionType = $transactionType;
        }

        return $balance;
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
            $transactions = Transaction::with('user')
            ->where('status', 'Success')
                // ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->orderBy('created_at', 'desc')
                ->get();

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
            $user = User::whereHas('role', function ($query) {
                $query->where('customer_type', 'user');
            })->where(function ($query) use ($phone) {
                $query->where('phone1', 'like', '%' . $phone . '%')
                    ->orWhere('phone2', 'like', '%' . $phone . '%')
                    ->orWhere('phone3', 'like', '%' . $phone . '%')
                    ->orWhere('formattedPhone', 'like', '%' . $phone . '%');
            })->first();

            if (!$user) {
                return back()->with('error', 'User not found or is a stuff.');
            }

            $userId = $user->id;

            $transactions = Transaction::with('currency', 'transaction_type', 'user')
            ->where('status', 'Success')
                ->where('user_id', $userId)
                // ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->orderBy('created_at', 'desc')
                ->get();

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

        // dd($userDetails);

        return view('Reports.cb_template', compact('userDetails', 'users', 'user', 'startDate', 'endDate', 'isAll', 'wallets'));
    }


    private function getInitialBalance($userId, $currency, $startDate)
    {
        // Fetch the initial balance at the beginning of the statement period
        $initialBalance = Transaction::where('user_id', $userId)
            ->when($currency, function ($query) use ($currency) {
                return $query->where('currency_id', $currency);
            })
            ->where('status', 'Success')
            ->whereDate('created_at', '<', $startDate)
            ->orderBy('created_at', 'desc')
            ->value('balance');

        return $initialBalance ?: 0;
    }

    public function generateTransaction($phone, $startDate, $endDate, $status, $currency)
    {
        $user = '';
        $transactions = '';
        $user = User::whereHas('role', function ($query) {
            $query->where('customer_type', 'user');
        })->where(function ($query) use ($phone) {
            $query->where('phone1', 'like', '%' . $phone . '%')
                ->orWhere('phone2', 'like', '%' . $phone . '%')
                ->orWhere('phone3', 'like', '%' . $phone . '%')
                ->orWhere('formattedPhone', 'like', '%' . $phone . '%');
        })->first();



        if ($user) {
            $userId = $user->id;

            if (!empty($currency)) {
                $transactions = Transaction::where('user_id', $userId)
                    ->with('currency', 'transaction_type', 'user')
                    ->where('status', $status)
                    ->where('currency_id', $currency)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $transactions = Transaction::with('currency', 'transaction_type', 'user')
                    ->whereHas('user.role', function ($query) {
                        $query->where('customer_type', '=', 'user');
                    })
                    ->where('status', $status)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } else {
            return back()->with('error', 'User not found or is a stuff.');
        }
        if (empty($phone)) {
            $user = User::whereHas('role', function ($query) {
                $query->where('customer_type', 'user');
            })->first();
            if (!empty($currency)) {
                $transactions = Transaction::with('currency', 'transaction_type', 'user')
                    ->whereHas('user.role', function ($query) {
                        $query->where('customer_type', '=', 'user');
                    })
                    ->where('status', $status)
                    ->where('currency_id', $currency)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $transactions = Transaction::with('currency', 'transaction_type', 'user')
                    ->whereHas('user.role', function ($query) {
                        $query->where('customer_type', '=', 'user');
                    })
                    ->where('status', $status)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        $isAll = is_null($phone);

        return view('Reports.tr_template', compact('transactions', 'user', 'startDate', 'endDate', 'isAll'));
    }

    public function generateCommision($startDate, $endDate, $status, $currency)
    {
        // dd($currency);
        $transactions = Transaction::with('currency', 'user', 'end_user', 'transaction_type')
            ->where('status', $status)
            ->where('currency_id', $currency)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereHas('user.role', function ($query) {
                $query->where('customer_type', 'user');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Reports.co_template', compact('transactions', 'startDate', 'endDate'));
    }

    public function generateReportDeposit($phone, $startDate, $endDate, $currency)
    {
        // Initialize variables
        $users = '';
        $allCustomer = false;
        $userId = null;
        $transactions = '';

        // Check if "All Customer" is selected
        if (empty($phone)) {
            // Handle the case for "All Customer"
            // Fetch all deposits without filtering by user phone
            $deposits = Transaction::with(['currency', 'user', 'transaction_type', 'end_user'])
                ->whereHas('user.role', function ($query) {
                    $query->where('customer_type', '=', 'user');
                })
                ->where('transaction_type_id', 1)
                ->where('currency_id', $currency)
                ->where('status', 'Success')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->get();

            $users = User::whereHas('role', function ($query) {
                $query->where('customer_type', 'user');
            })->get();

            $allCustomer = true;
        } else {
            // "Single Customer" is selected, find the user based on the provided phone number
            $users = User::whereHas('role', function ($query) {
                $query->where('customer_type', 'user');
            })->where('phone1', 'like', '%' . $phone . '%')
                ->orWhere('phone2', 'like', '%' . $phone . '%')
                ->orWhere('phone3', 'like', '%' . $phone . '%')
                ->orWhere('formattedPhone', 'like', '%' . $phone . '%')
                ->first();

            // Handle the case where the user is not found
            if (!$users) {
                return back()->with('error', 'User not foundor is a stuff.');
            }



            // Retrieve the user ID
            $userId = $users->id;

            // Query deposit transactions based on the user ID, date range, and currency
            $deposits = Transaction::with(['currency', 'user', 'transaction_type', 'end_user'])
                ->where('user_id', $userId)
                ->where('transaction_type_id', 1)
                ->where('currency_id', $currency)
                ->where('status', 'Success')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->get();
        }

        if ($deposits->isEmpty()) {
            // Handle the case where no deposit transactions are found for the provided conditions.
            return back()->with('error', 'User not found or is a stuff.');
        }

        // Calculate available balance for deposits
        $availableBalance = $this->calculateAvailableBalance($deposits);

        // Query wallet balance (inside the condition where a user is found)
        $walletBalance = ($userId) ? Wallet::where('user_id', $userId)
            ->where('currency_id', $currency)
            ->value('balance') : null;

        $transactions = Transaction::where('user_id', $userId)
        ->where('transaction_type_id', 1)
            ->with('currency', 'transaction_type', 'user')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->get();

        // Pass data to the view
        return view('Reports.DE_template', [
            'allCustomer' => $allCustomer,
            'user' => $users,
            'transaction' => $transactions,
            'deposits' => $deposits,
            'availableBalance' => $availableBalance,
            'walletBalance' => $walletBalance,
            'sdate' => $startDate,
            'edate' => $endDate,
        ]);
    }

    public function generateReportWithdrawal($phone, $startDate, $endDate, $currency)
    {
        // Initialize variables
        $users = '';
        $allCustomer = true;
        $userId = null;

        // Check if "All Customer" is selected
        if (empty($phone)) {
            // Handle the case for "All Customer"
            // Fetch all withdrawals without filtering by user phone
            $withdrawals = Transaction::with(['currency', 'user', 'transaction_type', 'end_user'])
                ->whereHas('user.role', function ($query) {
                    $query->where('customer_type', '=', 'user');
                })
                ->where('transaction_type_id', 2)
                ->where('currency_id', $currency)
                ->where('status', 'Success')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->get();

            $users = User::whereHas('role', function ($query) {
                $query->where('customer_type', 'user');
            })->get();
        } else {
            // "Single Customer" is selected, find the user based on the provided phone number
            $users = User::whereHas('role', function ($query) {
                $query->where('customer_type', 'user');
            })->where('phone1', 'like', '%' . $phone . '%')
                ->orWhere('phone2', 'like', '%' . $phone . '%')
                ->orWhere('phone3', 'like', '%' . $phone . '%')
                ->orWhere('formattedPhone', 'like', '%' . $phone . '%')
                ->first();

            // Handle the case where the user is not found
            if (!$users) {
                return back()->with('error', 'User not foundor is a stuff.');
            }

            $allCustomer = false;

            // Retrieve the user ID
            $userId = $users->id;

            // Query withdrawal transactions based on the user ID, date range, and currency
            $withdrawals = Transaction::with(['currency', 'user', 'transaction_type', 'end_user'])
                ->where('user_id', $userId)
                ->where('currency_id', $currency)
                ->where('transaction_type_id', 2)
                ->where('status', 'Success')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->get();
        }

        if ($withdrawals->isEmpty()) {
            // Handle the case where no withdrawal transactions are found for the provided conditions.
            return back()->with('error', 'User not foundor is a stuff.');
        }

        // Calculate available balance for withdrawals
        $availableBalance = $this->calculateAvailableBalance($withdrawals);

        // Query wallet balance (inside the condition where a user is found)
        $walletBalance = ($userId) ? Wallet::where('user_id', $userId)
            ->where('currency_id', $currency)
            ->value('balance') : null;

        // dd($withdrawals, $users, $availableBalance, $walletBalance, $startDate, $endDate);

        // Pass data to the view
        return view('Reports.WD_template', [
            'allCustomer' => $allCustomer,
            'user' => $users,
            'withdrawals' => $withdrawals,
            'availableBalance' => $availableBalance,
            'walletBalance' => $walletBalance,
            'sdate' => $startDate,
            'edate' => $endDate,
        ]);
    }

    public function generateUserReport($phone, $startDate, $endDate, $currency)
    {
        // Find the user based on the provided phone number

        if(empty($phone)){
            return back()->with('error', 'User phone is required.');
        }

        $user = User::whereHas('role', function ($query) {
            $query->where('customer_type', 'user');
        })->where(function ($query) use ($phone) {
            $query->where('phone1', 'like', '%' . $phone . '%')
                ->orWhere('phone2', 'like', '%' . $phone . '%')
                ->orWhere('phone3', 'like', '%' . $phone . '%')
                ->orWhere('formattedPhone', 'like', '%' . $phone . '%');
        })->first();

        if (!$user) {
            // Handle the case where the user is not found
            return back()->with('error', 'User not foundor is a stuff.');
        }

        // Retrieve the user ID
        $userId = $user->id;

        // Query all transaction types
        $transactionTypes = TransactionType::all();

        // Query transactions based on the user ID, date range, and all transaction types
        $allTransactionTypes = $transactionTypes->pluck('id')->toArray();

        $transactions = Transaction::with('transaction_type', 'user', 'end_user')->where('user_id', $userId)
            ->where('currency_id', $currency)
            ->where('status', 'Success')
            ->whereIn('transaction_type_id', $allTransactionTypes)
            ->whereDate('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        if ($transactions->isEmpty()) {
            // Handle the case where no transactions are found for the provided conditions.
            return back()->with('error', 'No transaction found for the provided conditions.');
        }

        // Initialize opening balance
        $openingBalance = $this->initializeOpeningBalance($transactions);

        // Calculate available balance
        $availableBalance = $this->calculateAvailableBalance($transactions, $openingBalance);

        // Query wallet balance
        $walletBalance = Wallet::where('user_id', $userId)
            ->where('currency_id', $currency)
            ->value('balance');

        // Pass data to the view
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

    // Add this function to calculate the opening balance
    private function initializeOpeningBalance($transactions)
    {
        // Query transactions for the user and currency before the start date
        return $transactions[0]->balance;
    }

    public function generateTellerReport($phone, $startDate, $endDate, $currency)
    {
        $transactions = '';
        $openingBalance = 0;
        $userId = '';
        $user = User::whereHas('role', function ($query) {
            $query->where('name', 'Teller');
        })->where(function ($query) use ($phone) {
            $query->where('teller_uuid', 'like', '%' . $phone . '%');

        })->first();

        if (!$user) {
            return redirect()->back()->with('error', 'User not found is a Customer.');
        } else {
            $userId = $user->id;
        }


        if (!empty($currency)) {
            $transactions = Transaction::with('currency', 'end_user', 'user', 'transaction_type')
                ->where('user_id', $userId)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->whereHas('user.role', function ($query) {
                    $query->where('name', 'Teller');
                })
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $transactions = Transaction::with('user', 'end_user', 'transaction_type')
                ->where('user_id', $userId)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->whereHas('user.role', function ($query) {
                    $query->where('name', 'Teller');
                })
                ->orderBy('created_at', 'asc')
                ->get();
        }
        // if user is empty
        if ($transactions->isEmpty()) {
            return back()->with('error', 'No transaction found for the provided conditions.');
        }

        $openingBalance = $this->initializeOpeningBalance($transactions);

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

}