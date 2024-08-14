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
use App\Services\OTPService;
use App\Models\StaffNotification;
use App\Models\TransactionType;
use Exception;
use Illuminate\Contracts\Session\Session as SessionSession;
use Illuminate\Http\Request;
use Session, Hash;
// use Illuminate\Support\Facades\Auth;


class profileController extends Controller
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

    public function showProfile() {
        $user_id =  auth()->guard('staff')->user()->id;
        $user = User::where('id', $user_id)->first();
      
        if (!$user) {
            return redirect()->route('staff.login'); 
        }
        return view('staff.profile', ['user' => $user]);
    }

    public function wallets()
    {
        $userid =  auth()->guard('staff')->user()->id;

        // Fetching currency IDs and codes
        $currencies = Currency::where(['status' => 'Active'])->pluck('code', 'id')->toArray();

        $balances = [];
        
        foreach ($currencies as $currencyId => $currencyCode) {
            $balance = Wallet::firstOrCreate(['user_id' => $userid, 'currency_id' => $currencyId], ['balance' => 0])->balance;
            $balances[$currencyCode] = $balance;
        }

        return view('staff.wallets', compact('balances'));
    }

    public function getPendingDeposits()
    {
        $user_id =  auth()->guard('staff')->user()->id;

        try {
            $pendingTransactions = Transaction::with('currency', 'user', 'end_user')
                ->where(['status' => 'Pending', 'user_id' => $user_id])
                ->whereIn('transaction_type_id', [1, 2, 8])
                ->get();
            return response()->json([
                'count' => $pendingTransactions->count(),
                'details' => $pendingTransactions,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching pending deposits: '.$e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching pending deposits.'], 500);
        }
    }
    
    function getNotifications()
    {
        $user = auth()->guard('staff')->user()->id;
        try {
            $notifications = StaffNotification::where('receiver_id', $user)
            ->where(['is_read' => 0, 'status' => 'Pending'])
            ->get();
        
            if ($notifications->isEmpty()) {
                return response()->json([
                    'count' => 0,
                    'details' => 'No notifications found',
                ]);
            }
    
            return response()->json([
                'count' => $notifications->count(),
                'details' => $notifications,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching notifications: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching notifications.'], 500);
        }
    }

   public function ViewMoreTransactions(){
        $user_id = auth()->guard('staff')->user()->id;
        $notifications = StaffNotification::where('receiver_id', $user_id)
            ->where(['is_read' => 0, 'status' => 'Pending'])
            ->get();
    
        $transactions = [];
        foreach ($notifications as $notification) {
            $payload = $notification->payload; 
    
            if (is_array($payload) && isset($payload['uuid'])) {
                $uuid = $payload['uuid'];
    
                // Query transaction based on UUID
                $transaction = Transaction::with('currency', 'user', 'end_user','transaction_type')
                    ->where('uuid', $uuid)
                    ->first();
            
                if ($transaction) {
                    $transactions[] = [
                        'notification_info' => [
                            'id' => $notification->id,
                            'created_at' => $notification->created_at,
                            'payload' => $payload,
                        ],
                        'transaction_info' => $transaction,
                    ];
                }
            }
        }
        return view('staff.viewTransactions', compact('notifications', 'transactions'));
    }
    
    public function changePassword(Request $request) {
        try {
            $user_id = auth()->guard('staff')->user()->id;
            $user = User::where("id", $user_id)->firstOrFail();
       
            if(!(new OTPService())->verify($user->formattedPhone, $request->otp)) {
                return ["status" => "error", "message" => "OTP Invalid!"];
            }
            
            if(!(Hash::check($request->old_password, $user->password))) {
                return ["status" => "error", "message" => "Wrong Password!"];
            }
            
            $user->password = Hash::make($request->password);
            $user->save();
            
            return ["status" => "success", "message" => "Operation was successful", "request" => $request->all()];
        } catch(\Exception $e) {
            return ["status" => "error", "message" => "Operation was unsuccessful"];
        }
    }
}
