<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\FeesLimit;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Services\Mail\Deposit\DepositViaAdminMailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Session\Session as SessionSession;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
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

    public function showLoginForm()
    {
        return view('staff.login');
    }
    
    public function searchUser(Request $request)
    {
        $searchItem = $request->searchQuery;

        $user = User::whereHas('role', function ($query) {
            $query->where('customer_type', 'user');})->where(function ($query) use ($searchItem) {
            // Define the phone number columns to search in
            $phoneColumns = ['phone', 'phone1', 'phone2', 'phone3', 'formattedPhone'];
        
            // Iterate over the phone number columns
            foreach ($phoneColumns as $column) {
                // Use the like operator to search for the searchItem in each column
                $query->orWhere($column, 'like', '%' . $searchItem . '%');
            }
        })->first();
        
        if ($user) {
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'username' => trim($user->first_name . ' ' . $user->last_name),
                ],
            ]);
        } else {
            // User not found
            return response()->json(['error' => 'User not found or is a staff']);
        }
    }
   
    public function myReports(){

        $today = Carbon::today();
        $currencies = Currency::where('status', 'Active')->get();

        $user_id = auth()->guard('staff')->user()->id;
        $user = User::find($user_id);
        $transactions = $this->getTransactions($user_id, $today, $today);
        return view('staff.reports.myReports', compact('transactions', 'user', 'currencies'));
    }
    
    public function getTransactions($userId,  $startDate, $endDate)
    {
        $query = Transaction::with('user', 'currency', 'end_user', 'transaction_type')
            ->where(['user_id' => $userId, 'status' => 'Success'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc');
    
        $transactions = $query->get();
    
        if ($transactions->isEmpty()) {
            $transactions = null;
            return $transactions;
        }
    
        return $transactions;
    }
}
