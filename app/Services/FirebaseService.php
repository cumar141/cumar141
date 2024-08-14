<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\{
    Preference,
    Currency,
    User,
    NotificationMessages,
    TransactionType
    };
use Carbon\Carbon;

class FirebaseService {
    
    public static function curl_token($postdata)
    {
        $key = Preference::where(['field' => 'push_notification_key'])->first()->value;
         
        $url = "https://fcm.googleapis.com/fcm/send";
       
        $header = array(
            'Authorization: key='.$key,
            'Content-Type: application/json'
        );
        
         $ch = curl_init();
         $timeout = 120;
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
         curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
         curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
         $result = curl_exec($ch);
         $response = json_decode($result, true);
         curl_close($ch);

         return $response;
        
    }
   
    public static function send_push_notif_to_device($fcm_token, $data)
    {
        $postdata = '{
            "to": "'. $fcm_token .'",
            "notification": {
              "title":"' . $data['title'] . '",
              "body": "' . $data['description'] . '",
              "mutable_content": true,
              "sound": "Tri-tone"
              }
        }';
        
        return self::curl_token($postdata);
        
    }

    public static function send_push_notif_to_topic($data)
    {
        $image = asset('storage/app/public/notification') . '/' . $data['image'];
        $postdata = '{
            "to" : "/topics/' . $data['receiver'] . '",
            "mutable-content": "true",
            "data" : {
                "title" :"' . $data['title'] . '",
                "body" : "' . $data['description'] . '",
                "image" : "' . $image . '",
                "is_read": 0
              },
              "notification" : {
                "title" :"' . $data['title'] . '",
                "body" : "' . $data['description'] . '",
                "image" : "' . $image . '",
                "is_read": 0,
                "icon" : "new",
                "sound" : "default"
              }
        }';
        
        return self::curl_token($postdata);

    }

    public static function send_push_notif_to_customers($data)
    {
        $image = asset('storage/app/public/notification') . '/' . $data['image'];
        $postdata = '{
            "to" : "/topics/notify_customers",
            "mutable-content": "true",
            "data" : {
                "title" :"' . $data['title'] . '",
                "body" : "' . $data['description'] . '",
                "image" : "' . $image . '",
                "is_read": 0
              },
              "notification" : {
                "title" :"' . $data['title'] . '",
                "body" : "' . $data['description'] . '",
                "image" : "' . $image . '",
                "is_read": 0,
                "icon" : "new",
                "sound" : "default"
              }
        }';

        return self::curl_token($postdata);
    }
    
    public static function send_transaction_notification($user_id, $amount, $transaction_type, $currencyId, $end_user_=null)
    {
        //send notification [receiver]
        $userId =  auth()->id();
        $user = User::where(['id' => $userId ?? $end_user_])->first();
        $end_user = User::where(['id' => $user_id])->first();
        $currency = Currency::where(['id' => $currencyId])->first();
        $value = FirebaseService::order_status_update_message($transaction_type);
        $type = json_decode($value,true);
        
        $message = json_decode($value['value'],true);
        
        $Transactiontype = TransactionType::where(['id' => $type['type']])->first();
        
        if(isset($end_user) && $end_user->fcm_token && $value)
        {
            $fcm_token = $end_user->fcm_token;
            //Receiver Money
            if ($type['type']==4){
            $data = [
                'title' => $Transactiontype->name . ' ' . 'money',
                'description' => $currency->code . ' ' . $amount . ' ' . $message['message'] . ' ' . $user->formattedPhone,
            ];
            }
            //Withdrawal Money
            if ($type['type']==2){
            $data = [
                'title' => $Transactiontype->name . ' ' . 'money',
                'description' => $currency->code . ' ' . $amount . ' ' . $message['message'] . ' ' . $end_user_,
            ];
            }
            //Sender Money
            if ($type['type']==3){
            $data = [
                'title' => $Transactiontype->name . ' ' . 'money',
                'description' => $currency->code  . ' ' . $amount . ' ' . $message['message'],
            ];
            }
            //Top-up
            if ($type['type']==13){
            $data = [
                'title' => $Transactiontype->name . ' ' . 'Recharge',
                'description' => $currency->code  . ' ' . $amount . ' ' . $message['message'] . ' ' . $end_user_,
            ];
            }
            //idententy Proof
            if ($type['key']=='identity_proof'){
            $identityStatus = $currencyId;
            $data = [
                'title' => "Identity" . ' ' . $identityStatus,
                'description' => $message['message'] . ' ' . $identityStatus,
            ];
            }
          
            try {
                
                FirebaseService::send_push_notif_to_device($fcm_token, $data);
                
                return true;
            } catch (\Exception $exception) {
                return false;
            }
        }

    }
    
    public static function order_status_update_message($status)
    {
        
        if ($status == 'top_up') {
            $data = NotificationMessages::where(['key' => 'top_up'])->first();
            
        } elseif ($status == "withdrawal_money") {
            $data = NotificationMessages::where(['key' => 'withdrawal_money'])->first();

        }  elseif ($status == "send_money") {
            $data = NotificationMessages::where(['key' => 'send_money'])->first();
            
        }  elseif ($status == 'request_money') {
            $data = NotificationMessages::where(['key' => 'request_money'])->first()->value;
        
        }  elseif ($status == 'denied_money') {
            $data = NotificationMessages::where(['key' => 'denied_money'])->first()->value;

        }  elseif ($status == 'approved_money') {
            $data = NotificationMessages::where(['key' => 'approved_money'])->first()->value;

        } elseif ($status == "ADD_MONEY") {
            $data = NotificationMessages::where(['key' => 'ADD_MONEY'])->first()->value;

        } elseif ($status == "received_money") {
            $data = NotificationMessages::where(['key' => 'received_money'])->first();

        }elseif ($status == 'Top_up') {
            $data = NotificationMessages::where(['key' => 'Top_up'])->first()->value;

        }elseif ($status == 'identity_proof') {
            $data = NotificationMessages::where(['key' => 'identity_proof'])->first();

        }else {
            $data['status'] = 0;
            $data['message'] = "";
        }

        if ($data == null && $data['status'] == 0) {
            return 0;
        }
        //$json = json_decode($data, true);
        return $data;//$json['message'];
    }

}