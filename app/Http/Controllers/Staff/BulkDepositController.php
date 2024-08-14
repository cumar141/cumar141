<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\Deposit;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\UserPermission;

class BulkDepositController extends Controller
{
    public function getCurrencyUsingId($id)
    {
        $currency = Currency::find($id);
        if (!$currency) {
            return redirect()->route('bulkDeposit')->withErrors('Currency not found');
        }
        return $currency->code;
    }

    public function checkPassword($password)
    {
        $user = User::find(auth()->guard('staff')->user()->id);
        if (!$user) {
            return redirect()->route('staff.login')->withErrors('User not found');
        }
        if (!password_verify($password, $user->password)) {
            return redirect()->route('bulkDeposit')->withErrors('Incorrect password');
        }
        return true;
    }

    public function showBulkDeposit(): \Illuminate\View\View
    {
        $userId =  auth()->guard('staff')->user()->id;

        $users = '';

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('staff.login')->withErrors('User not found');
        }

        $branch = $user->branch->name;

        $users = User::where(['branch_id' => $user->branch_id, 'status' => 'Active'])
            ->whereHas('role', function ($q) {
                $q->where('name', 'Teller');
            })
            ->get();
        if (UserPermission::has_permission(auth()->guard('staff')->user()->id, 'Treasurers')) {
            $users = User::where('status', 'Active')
                ->whereHas('role', function ($q) {
                    $q->where('name', 'Manager');
                })
                ->get();
        }

        $currencies = Currency::where(['status' => 'Active'])->get();

        return view('staff.bulkDeposit', compact('users', 'branch', 'currencies'));
    }

    public function userIDs($type, $branchId)
    {
        if ($type == 'Treasurers') {
            $users = User::where('status', 'Active')
                ->whereHas('role', function ($q) use ($type) {
                    $q->where('name', 'Manager');
                })
                ->get();
            return $users;
        }
        $users = User::where(['branch_id' => $branchId, 'status' => 'Active'])
            ->whereHas('role', function ($q) use ($type) {
                $q->where('name', $type);
            })
            ->get();
        return $users;
    }


    public function bulkDepositSubmit(Request $request): \Illuminate\Http\RedirectResponse
    {
        $currencies = $request->currencies;
        $amounts = $this->removeNullAmounts($request->amounts);
        $password = $this->checkPassword($request->password);
        if ($password !== true) {
            return $password;
        }
        $note = $request->note;

        $userId =  auth()->guard('staff')->user()->id;
        $branchId = User::find($userId)->branch_id;
        $userIDs = '';
        if (UserPermission::has_permission(auth()->guard('staff')->user()->id, 'Treasurers')) {
            $userIDs = $this->userIDs('Treasurers', $branchId);
        } else {
            $userIDs = $this->userIDs('Teller', $branchId);
        }

        // Get teller IDs
        $tellerIds = $userIDs->pluck('id')->toArray();

        $combined = $this->combineAmountsAndCurrencies($currencies, $amounts);

        $combinedWithTellerIds = $this->combineCurrencyAmountWithTellerIDS($combined, $tellerIds);

        $checkIfManagerHasEnoughBalance = $this->checkIfManagerHasEnoughBalance($combinedWithTellerIds);

        if ($checkIfManagerHasEnoughBalance !== true) {
            return $checkIfManagerHasEnoughBalance;
        }
        $transaction = $this->handleTransactions($combinedWithTellerIds, $note);
        if ($transaction === true) {
            return redirect()->route('bulkDeposit')->with('success', 'Bulk deposit successful');
        } else {
            return redirect()->route('bulkDeposit')->withErrors($transaction);
        }
    }

    public function removeNullAmounts($amounts)
    {
        $newAmounts = [];
        foreach ($amounts as $amount) {
            if ($amount != null) {
                $newAmounts[] = $amount;
            }
        }
        return $newAmounts;
    }

    public function combineAmountsAndCurrencies($currencies, $amounts)
    {
        $combined = [];
        for ($i = 0; $i < count($currencies); $i++) {
            $combined[] = [
                'currency' => $currencies[$i],
                'amount' => $amounts[$i]
            ];
        }
        return $combined;
    }

    public function combineCurrencyAmountWithTellerIDS($combined, $tellerIds)
    {
        $combinedWithTellerIds = [];

        foreach ($combined as $item) {
            foreach ($tellerIds as $tellerId) {
                $combinedWithTellerIds[] = [
                    'currency' => $item['currency'],
                    'amount' => $item['amount'],
                    'tellerId' => $tellerId
                ];
            }
        }

        return $combinedWithTellerIds;
    }

    public function handleTransactions($combinedWithTellerIds, $note)
    {
        $tranansaction = '';
        $user_id =  auth()->guard('staff')->user()->id;
        foreach ($combinedWithTellerIds as $combined) {
            $tellerId = $combined['tellerId'];
            $currency = (int)$combined['currency'];
            $amount = $combined['amount'];

            $tranansaction = $this->makeWithdrawal($user_id, $currency, $amount, $tellerId, $note);
            if ($tranansaction !== true) {
                return $tranansaction;
            }
            $tranansaction = $this->makeDeposit($user_id, $currency, $amount, $tellerId, $note);
            if ($tranansaction !== true) {
                return $tranansaction;
            }
        }
        return $tranansaction;
    }
    
    public function makeWithdrawal($user_id, $currency, $amount, $tellerId, $note)
    {
        try {
            $uuid = unique_code();
            $wallet = Wallet::firstOrCreate(['user_id' => $user_id, 'currency_id' => $currency], ['balance' => 0]);
            $balance = $wallet->balance - $amount;
            
            DB::beginTransaction();
            
            $withdrawal = new Withdrawal();
            $withdrawal->currency_id = $currency;
            $withdrawal->user_id = $user_id;
            $withdrawal->payment_method_id = 1;
            $withdrawal->uuid = $uuid;
            $withdrawal->subtotal =  $amount;
            $withdrawal->amount = $amount;
            $withdrawal->balance = $balance;
            $withdrawal->charge_percentage = 0;
            $withdrawal->charge_fixed = 0;
            $withdrawal->status = 'Success';
            $withdrawal->save();

            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->currency_id = $currency;
            $transaction->payment_method_id = 1;
            $transaction->transaction_reference_id = $withdrawal->id;
            $transaction->transaction_type_id = Withdrawal;
            $transaction->note = $note;
            $transaction->uuid = $uuid;
            $transaction->end_user_id =  $tellerId;
            $transaction->subtotal = $amount;
            $transaction->percentage = 0;
            $transaction->charge_percentage = $withdrawal->charge_percentage;
            $transaction->charge_fixed = $withdrawal->charge_fixed;
            $transaction->total = '-' . $amount;
            $transaction->balance = $balance;
            $transaction->status = 'Success';
            $transaction->save();

            $wallet->balance -=  $amount;
            $wallet->save();
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
        return true;
    }

    public function makeDeposit($user_id, $currency, $amount, $tellerIds, $note)
    {
        try {

            $uuid = unique_code();
            $wallet = Wallet::firstOrCreate(['user_id' => $tellerIds, 'currency_id' => $currency], ['balance' => 0]);
            $balance = $wallet->balance + $amount;
            
            DB::beginTransaction();
            $deposit = new Deposit();
            $deposit->currency_id = $currency;
            $deposit->user_id = $tellerIds;
            $deposit->payment_method_id = 1;
            $deposit->uuid = $uuid;
            $deposit->amount = $amount;
            $deposit->balance = $balance;
            $deposit->charge_percentage = 0;
            $deposit->charge_fixed = 0;
            $deposit->status = 'Success';
            $deposit->save();

            $transaction = new Transaction();
            $transaction->user_id = $tellerIds;
            $transaction->currency_id = $currency;
            $transaction->payment_method_id = 1;
            $transaction->transaction_reference_id = $deposit->id;
            $transaction->transaction_type_id = Deposit;
            $transaction->note = $note;
            $transaction->uuid = $uuid;
            $transaction->end_user_id = $user_id;
            $transaction->subtotal = $amount;
            $transaction->percentage = 0;
            $transaction->charge_percentage = $deposit->charge_percentage;
            $transaction->charge_fixed = $deposit->charge_fixed;
            $transaction->total = $amount;
            $transaction->balance = $balance;
            $transaction->status = 'Success';
            $transaction->save();

            $wallet->balance += $amount;
            $wallet->save();
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
        return true;
    }

    public function checkIfManagerHasEnoughBalance($combinedWithTellerIds)
    {
        $user_id = auth()->guard('staff')->user()->id;

        // Initialize an array to store total amounts for each currency
        $totalAmounts = [];

        // Calculate total amounts for each currency
        foreach ($combinedWithTellerIds as $combined) {
            $currency = $combined['currency'];
            $amount = $combined['amount'];
            $tellerId = $combined['tellerId'];

            // If the currency key doesn't exist in $totalAmounts array, initialize it
            if (!isset($totalAmounts[$currency])) {
                $totalAmounts[$currency] = 0;
            }

            // Increment total amount for the currency based on the number of tellers
            $totalAmounts[$currency] += $amount;
        }

        // Check if the manager has enough balance for each currency
        foreach ($totalAmounts as $currency => $totalAmount) {
            $managerWallet = Wallet::where(['user_id' => $user_id, 'currency_id' => $currency])->first();
            if (!$managerWallet) {
                return redirect()->route('bulkDeposit')->withErrors("Manager wallet not found for currency ". $this->getCurrencyUsingId($currency). "");
            }

            // Compare the manager's balance with the total amount for the currency
            if ($managerWallet->balance < $totalAmount) {
                return redirect()->route('bulkDeposit')->withErrors("Insufficient balance for currency ". $this->getCurrencyUsingId($currency). "");
            }
        }
        return true;
    }

}
