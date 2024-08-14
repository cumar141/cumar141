<?php

namespace App\Models;

use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\PaymentMethod;
use App\Models\Transaction;
// use App\Services\Mail\Topup\NotifyAdminOnWithdrawMailService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class Topup extends Model
{
    public $timestamps  = true;
    protected $fillable = ['user_id', 'currency_id', 'payment_method_id', 'uuid', 'charge_percentage', 'charge_fixed', 'subtotal', 'amount', 'payment_method_info', 'status'];
    protected $casts = ["payment_method_info" => "object"];

    //
    protected $email;
    protected $helper;
    public function __construct()
    {
        $this->email  = new EmailController(); //needed to send email notification
        $this->helper = new Common();
    }
    //

    public function payment_method()
    {
        return $this->belongsTo(TopupOperator::class, 'payment_method_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transaction_reference_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user      [id]
     * @return [string]  [firstname and lastname]
     */
    public function getTopupsUserName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'topups.user_id')
            ->where(['user_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search   [query string]
     * @return [string] [distinct firstname and lastname]
     */
    public function getTopupsUsersResponse($search)
    {
        return $this->leftJoin('users', 'users.id', '=', 'topups.user_id')
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->distinct('users.first_name')
            ->select('users.first_name', 'users.last_name', 'topups.user_id')
            ->get();
    }

    /**
     * [Topups Filtering Results]
     * @param  [null/date] $from   [start date]
     * @param  [null/date] $to     [end date]
     * @param  [string]    $status [Status]
     * @param  [string]    $pm     [Payment Methods]
     * @param  [null/id]   $user   [User ID]
     * @return [query]     [All Query Results]
     */
    public function getTopupsList($from, $to, $status, $currency, $pm, $user)
    {
        $conditions = [];

        if (empty($from) || empty($to))
        {
            $date_range = null;
        }
        else if (empty($from))
        {
            $date_range = null;
        }
        else if (empty($to))
        {
            $date_range = null;
        }
        else
        {
            $date_range = 'Available';
        }

        if (!empty($status) && $status != 'all')
        {
            $conditions['topups.status'] = $status;
        }
        if (!empty($currency) && $currency != 'all')
        {
            $conditions['topups.currency_id'] = $currency;
        }
        if (!empty($pm) && $pm != 'all')
        {
            $conditions['topups.payment_method_id'] = $pm;
        }
        if (!empty($user))
        {
            $conditions['topups.user_id'] = $user;
        }

        $topups = $this->with([
            'user:id,first_name,last_name',
            'currency:id,code',
            'payment_method:id,name',
        ])->where($conditions);

        if (!empty($date_range))
        {
            $topups->where(function ($query) use ($from, $to)
            {
                $query->whereDate('topups.created_at', '>=', $from)->whereDate('topups.created_at', '<=', $to);
            })
            ->select('topups.*');
        }
        else
        {
            $topups->select('topups.*');
        }
        //
        return $topups;
    }

    //for front-end - TopupController.php - common functions (need to reuse in mobile app too) - starts
    public function createTopup($arr)
    {
        $topup                      = new Topup();
        $topup->user_id             = $arr['user_id'];
        $topup->currency_id         = $arr['currency_id'];
        $topup->payment_method_id   = $arr['payment_method_id'];
        $topup->uuid                = $arr['uuid'];
        $topup->charge_percentage   = $arr['charge_percentage'];
        $topup->charge_fixed        = $arr['charge_fixed'];
        $topup->subtotal            = $arr['subtotal'];
        $topup->amount              = $arr['amount'];
        $topup->payment_method_info = $arr['payment_method_info'];
        $topup->status              = 'Pending';
        $topup->save();

        return $topup;
    }

    public function createTopupTransaction($arr)
    {
        $transaction                           = new Transaction();
        $transaction->user_id                  = $arr['user_id'];
        $transaction->currency_id              = $arr['currency_id'];
        $transaction->payment_method_id        = $arr['payment_method_id'];
        $transaction->uuid                     = $arr['uuid'];
        $transaction->transaction_reference_id = $arr['transaction_reference_id'];
        $transaction->reference_number         = $arr["receiver"] ?? "N/A";
        $transaction->transaction_type_id      = Topup;
        $transaction->subtotal                 = $arr['amount'];
        $transaction->percentage               = $arr['percentage'];
        $transaction->charge_percentage        = $arr['charge_percentage'];
        $transaction->charge_fixed             = $arr['charge_fixed'];
        $transaction->total                    = '-' . ($transaction->subtotal + $transaction->charge_percentage + $transaction->charge_fixed);
        $transaction->balance                  = ($arr['wallet']->balance - $arr['totalAmount']);
        $transaction->status                   = 'Pending';
        $transaction->save();

        return $transaction->id;
    }

    public function updateWallet($arr)
    {
        $arr['wallet']->balance = ($arr['wallet']->balance - $arr['totalAmount']);
        $arr['wallet']->save();
    }

    public function processPayoutMoneyConfirmation($arr = [])
    {
        $response = ['status' => 401];

        try {
            //Backend Validation - Wallet Balance Again Amount Check - Starts here
            $checkWalletBalance = $this->helper->checkWalletBalanceAgainstAmount($arr['totalAmount'], $arr['currency_id'], $arr['user_id']);
            if ($checkWalletBalance == true) {
                $response['topupTransactionId'] = null;
                $response['message'] = __("Sorry, not enough funds to perform the operation.");
                return $response;
                //Backend Validation - Wallet Balance Again Amount Check - Ends here
            } else {
                DB::beginTransaction();

                //Create Topup
                $topup = self::createTopup($arr);
                
                $arr['transaction_reference_id'] = $topup->id;

                //Create Topup Transaction
                $transactionId = self::createTopupTransaction($arr);

                //Update Wallet
                self::updateWallet($arr);

                DB::commit();

                // Notificaton email/SMS
                // (new NotifyAdminOnWithdrawMailService)->send($topup, ['type' => 'topup', 'medium' => 'email']);

                $response['status'] = 200;
                $response['topupTransactionId'] = $transactionId;

                return $response;
            }
        } catch (Exception $e) {
            DB::rollBack();
            $response['topupTransactionId'] = null;
            $response['message'] = $e->getMessage();
            return $response;
        }
    }
    //for front-end - TopupController.php - common functions (need to reuse in mobile app too) - ends
    
    /**
     * updateStatus
     *
     * @param  int $topupId
     * @param  string $status
     * @return object
     */
    public static function updateStatus(int $topupId, string $status): object
    {
        $topup = Topup::find($topupId);
        
        if ($topup) {
            $topup->status = $status;
            $topup->save();
        }

        return $topup;
    }

}
