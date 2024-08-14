<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    User,
    Wallet,
    Transfer,
    Transaction,
    StaffNotification
};
use DB, session, Hash;

class TransferController extends Controller
{
    public function transferMoney(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0', 
            'currency' => 'required|exists:currencies,id', 
            'note' => 'nullable|string', 
            'manager_id' => 'required|exists:users,id', 
            'password' => 'required|string|min:6',
        ]);
    
        // Extract validated data
        $amount = $validatedData['amount'];
        $currency_id = $validatedData['currency'];
        $note = $validatedData['note'];
        $manager_id = $validatedData['manager_id'];
        $password = $validatedData['password'];
    
        $user_id = auth()->guard('staff')->user()->id;
        $user = User::find($user_id);
        
        if (!$user || !Hash::check($password, $user->password)) {
            return redirect()->route('show.money.form')->with('error', 'Invalid password.');
        }
    
        $data = $this->transfer($user_id, $amount, $note, $manager_id, $currency_id);
    
        if (isset($data['error'])) {
            $data['type'] = 'transfer';
            $data['user_id'] = $user_id;
            return redirect()->route('show.money.form', $data)->withErrors(['error' => $data['error']]);
        } else {
            return view('staff.print.index', $data);
        }
    }
    
    public function transfer($user_id, $amount, $note, $end_user_id, $currency_id)
    {
        $uuid = unique_code();
        $data = [];
        
        // first check if the user has enough balance
        $wallet = Wallet::firstOrCreate(['user_id' => $user_id, 'currency_id' => $currency_id], ['balance' => 0]);
        if($wallet->balance < $amount) {
            return ['error' => 'Insufficient balance'];
        }
        $balance = $wallet->balance - $amount;

        // check if the end user exists
        $end_user = User::find($end_user_id);
        if(!$end_user) {
            return ['error' => 'End user does not exist'];
        }
       
        $senderInfo = User::find($user_id);

        $arr = [
            'user_id' =>$user_id,
            'end_user_id' => $end_user_id,
            'currency_id' => $currency_id,
            'uuid' => $uuid,
            'fee' => 0,
            'amount' =>$amount,
            'note' => $note,
            'emailFilterValidate' =>$senderInfo->email,
            'phoneRegex' => $senderInfo->phone,
            'balance' => $balance,
        ];
        
        $data = $this->createTransfer($arr);
        $this->sendNotification($user_id,$end_user_id,$note,$data);
        
        return $data;
    }

    public function createTransfer($arr)
    {
        DB::beginTransaction();
        try {
            $transfer = new Transfer();
            $transfer->sender_id = $arr['user_id'];
            $transfer->receiver_id = $arr['end_user_id'];
            $transfer->currency_id = $arr['currency_id'];
            $transfer->uuid = $arr['uuid'];
            $transfer->fee = $arr['fee'];
            $transfer->amount = $arr['amount'];
            $transfer->note = $arr['note'];
            $transfer->email = $arr['emailFilterValidate'] ?? null;
            $transfer->phone = $arr['phoneRegex'] ?? null;
            $transfer->status = 'Pending';
            $transfer->save();
    
            $transaction = new Transaction();
            $transaction->user_id = $arr['user_id'];
            $transaction->end_user_id = $arr['end_user_id'];
            $transaction->currency_id = $arr['currency_id'];
            $transaction->payment_method_id = 1; 
            $transaction->transaction_reference_id = $transfer->id;
            $transaction->transaction_type_id = Transferred; 
            $transaction->note = $arr['note'];
            $transaction->uuid = $arr['uuid'];
            $transaction->subtotal = $arr['amount'];
            $transaction->percentage = 0;
            $transaction->charge_percentage = 0;
            $transaction->charge_fixed = 0;
            $transaction->total = '-'.$arr['amount'];
            $transaction->status = 'Pending';
            $transaction->save();
    
            DB::commit();
    
            $data['transInfo']['currency_id'] = $transaction->currency->id;
            $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
            $data['transInfo']['subtotal'] = $transaction->subtotal;
            $data['transInfo']['id'] = $transaction->id;
            $data['transInfo']['note'] = $transaction->note;
            $data['users'] = User::find($arr['user_id'], ['id']);
            $data['transactionDetails'] = $transaction;
    
            return $data;
    
        } catch (\Exception $e) {
            DB::rollback();
            $data['error'] = $e->getMessage();
            return $data;
        }
    }
    
    public function sendNotification($sender, $receiver, $note, $data)
    {
        // Retrieve sender and receiver information
        $senderInfo = User::where('id', $sender)->first();
        $receiverInfo = User::where('id', $receiver)->first();
    
        // Construct payload
        $payload = [
            'sender' => [
                'name' => $senderInfo->first_name . ' ' . $senderInfo->last_name,
                'phone' => $senderInfo->formattedPhone,
            ],
            'receiver' => [
                'name' => $receiverInfo->first_name . ' ' . $receiverInfo->last_name,
                'phone' => $receiverInfo->formattedPhone,
            ],
            'uuid' =>  $data['transactionDetails']['uuid'],
            'amount' =>  abs($data['transactionDetails']['total']),
            'currency_id' =>  $data['transactionDetails']['currency_id'],
            'transaction_type_id' =>  $data['transactionDetails']['transaction_type_id'],
    
        ];

        // Prepare notification data
        $notificationData = [
            'user_id' => $sender,
            'end_user_id' => $receiver,
            'note' => $note,
            'payload' => $payload
        ];

        // Create notification
        $notification = new StaffNotification();
        $notification->createNotification($notificationData);
    }
}
