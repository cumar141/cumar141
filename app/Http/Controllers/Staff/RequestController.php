<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\{
    Currency,
    Deposit,
    RequestPayment,
    Transaction,
    User,
    Wallet,
    Withdrawal,
    StaffNotification
};
use DB, Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\CashOut\Approve;
use App\Services\CashOut\Reject;

class RequestController extends Controller
{
    public function getTreasury()
    {
        $treasurerUser = User::whereHas('role', function ($query) {
            $query->where('name', 'Treasurer');
        })->first();

        return $treasurerUser->id;
    }

    public function requestMoney(Request $request)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|exists:currencies,id',
            'note' => 'nullable|string',
            'manager_id' => 'required|exists:users,id',
            'password' => 'required',
        ]);

        // Extract validated data
        $amount = $validatedData['amount'];
        $currency_id = $validatedData['currency'];
        $note = $validatedData['note'];
        $manager_id = $validatedData['manager_id'];
        $password = $validatedData['password'];

        // Check password validity
        $user_id = auth()->guard('staff')->user()->id;
        $storedPassword = User::find($user_id)->password;
        if (!$storedPassword || !Hash::check($password, $storedPassword)) {
            return back()->with('error', 'Invalid password');
        }

        // Proceed with the rest of the code
        $data = $this->requestMoneyFromManager($user_id, $amount, $note, $manager_id, $currency_id);

        if (isset($data['error'])) {
            $data['type'] = 'request';
            $data['user_id'] = $user_id;
            return redirect()->route('show.money.form', $data)->withErrors(['error' => $data['error']]);
        }

        $this->sendNotification($user_id, $manager_id, $note, $data);

        return view('staff.print.index', $data);
    }

    public function managerRequestHandler(Request $request)
    {
        $rules = [
            'password' => 'required',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|exists:currencies,id',
            'note' => 'string|min:3',
        ];

        // Custom error messages
        $messages = [
            'password.required' => 'Password is required.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'currency.required' => 'Currency is required.',
            'currency.exists' => 'Selected currency is invalid.',
            'note.string' => 'Note must be a string.',
        ];

        // Perform validation
        $validator = Validator::make($request->all(), $rules, $messages);

        // Check if validation fails
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $pass = $request->password;
        $amount = $request->amount;
        $currency_id = $request->currency;
        $note = $request->note;
        $checkPassword = $this->checkPassword($pass);
        if ($checkPassword !== true) {
            return back()->with('error', $checkPassword);
        }

        $receiver = $this->getTreasury();
        $sender = auth()->guard('staff')->user()->id;
        $data = $this->requestMoneyFromManager($sender, $amount, $note, $receiver, $currency_id);
        if (!$data) {
            return back()->with('error', $data['error']);
        }
        
        $this->sendNotification($sender, $receiver, $note, $data);

        // If successful, return the view with the data
        return view('staff.print.index', $data);
    }

    public function TellerRequestHandler(Request $request)
    {
        // Validation rules
        $rules = [
            'password' => 'required',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|exists:currencies,id',
            'note' => 'nullable|string|min:3',
            'branch_id' => 'required|exists:branchs,id',
        ];

        // Custom error messages
        $messages = [
            'password.required' => 'Password is required.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'currency.required' => 'Currency is required.',
            'currency.exists' => 'Selected currency is invalid.',
            'note.string' => 'Note must be a string.',
            'branch_id.required' => 'Branch ID is required.',
            'branch_id.exists' => 'Selected branch is invalid.',
        ];
        // Perform validation
        $validator = Validator::make($request->all(), $rules, $messages);

        // Check if validation fails
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $pass = $request->password;
        $amount = $request->amount;
        $currency_id = $request->currency;
        $note = $request->note;
        $branch_id = $request->branch_id;
        $receiver = $this->getBranchManager($branch_id);
        $sender = auth()->guard('staff')->user()->id;

        $checkPassword = $this->checkPassword($pass);
        if ($checkPassword !== true) {
            return back()->with('error', $checkPassword);
        }

        $data = $this->requestMoneyFromManager($sender, $amount, $note, $receiver, $currency_id);

        if (isset($data['error'])) {
            return back()->with('error', $data['error']);
        }

        $this->sendNotification($sender, $receiver, $note, $data);

        return view('staff.print.index', $data);
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
            'amount' =>  $data['transactionDetails']['total'],
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

    public function getBranchManager($branch_id)
    {
        $user = auth()->guard('staff')->user()->id;
        if (empty($user)) {
            return redirect()->route('staff.login');
        }

        // get all users whose role is manager
        $managers = User::whereHas('role', function ($query) {
            $query->where('name', 'Manager');
        })->where('branch_id', $branch_id)->first();

        return $managers->id;
    }

    public function requestMoneyFromManager($sender, $amount, $note, $reciever, $currency_id)
    {
        try {
            DB::beginTransaction();
            $data = [];

            $wallet = Wallet::firstOrCreate(['user_id' => $sender, 'currency_id' => $currency_id], ['balance' => 0]);
    
            $balance = $wallet->balance + $amount;
    
            $sender_user = User::find($sender);
    
            // check if the end user exists
            $reciever_user = User::find($reciever);
    
            $uuid = unique_code();
            
            // Request
            $requestpayment = new RequestPayment();
            $requestpayment->user_id = $sender;
            $requestpayment->receiver_id = $reciever_user->id;
            $requestpayment->currency_id = $currency_id;
            $requestpayment->uuid = $uuid;
            $requestpayment->amount = $amount;
            $requestpayment->accept_amount = 0;
            $requestpayment->email = $sender_user->email;
            $requestpayment->phone = $sender_user->formattedPhone;
            $requestpayment->note = $note;
            $requestpayment->status = 'Pending';
            $requestpayment->save();

            // Transaction
            $transaction = new Transaction();
            $transaction->user_id = $sender;
            $transaction->end_user_id = $reciever;
            $transaction->currency_id = $currency_id;
            $transaction->payment_method_id = 1;
            $transaction->transaction_reference_id = $requestpayment->id;
            $transaction->transaction_type_id = Request_Sent;
            $transaction->note = $note;
            $transaction->uuid = $uuid;
            $transaction->subtotal = $amount;
            $transaction->percentage = 0;
            $transaction->charge_percentage = 0;
            $transaction->charge_fixed = 0;
            $transaction->total = $amount;
            $transaction->status = 'Pending';
            $transaction->balance = $balance;
            $transaction->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $data['error'] = $e->getMessage();
            return $data;
        }

        $data['transInfo']['currency_id'] = $transaction->currency->id;
        $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
        $data['transInfo']['subtotal'] = $transaction->subtotal;
        $data['transInfo']['id'] = $transaction->id;
        $data['transInfo']['note'] = $transaction->note;
        $data['users'] = User::find($sender, ['id']);
        $data['transactionDetails'] = $transaction;

        return $data;
    }
    
    public function PendingRequests()
    {
        $user_id = auth()->guard('staff')->user()->id;
        $pendingRequests = RequestPayment::where('status', 'Pending')
            ->where('receiver_id', $user_id)
            ->with('user', 'currency')
            ->get();

        return view('staff.ViewRequests', ['pendingRequests' => $pendingRequests]);
    }

    public function rejectRequest(Request $request)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validate([
                'transaction_uuid' => 'required|exists:transactions,uuid',
                'notificationId' => 'required|exists:staff_notification,id'
            ]);
    
            $uuid = $validatedData['transaction_uuid'];
            $notificationId = $validatedData['notificationId'];
            $rejectService = new Reject();
            $result = $rejectService->processRefund($uuid);
    
            if (isset($result['error'])) {
                return redirect()->back()->with('error', $result['error']);
            }
            else {
                $updateNotification = $this->updateNotification($notificationId);
                if($updateNotification == false){
                    return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update Notifications.']);
                }
                DB::commit();
                return redirect()->back()->with('message', 'Request rejected successfully!');
            }
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error occured!');
        }
    }
    public function approveRequest(Request $request)
    {
        try {
            DB::beginTransaction();
            // Validate the request
            $validatedData = $request->validate([
                'transaction_uuid' => 'required|exists:transactions,uuid',
                'notificationId' => 'required|exists:staff_notification,id'
            ]);
    
            $uuid = $validatedData['transaction_uuid'];
            $notificationId = $validatedData['notificationId'];
    
            // Process approval
            $approve = new Approve();
            $data = $approve->processApprove($uuid);
       
            if (isset($data['error'])) {
                return redirect()->back()->withInput()->withErrors(['error' => $data['error']]);
            } else {
                $updateNotification = $this->updateNotification($notificationId);
                if (!$updateNotification){
                    return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update Notifications.']);
                }
            }
        
            DB::commit();
            return view('staff.print.index', $data);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error occured!');
        }
    }

    private function updateNotification($id){
        $notification = StaffNotification::where('id', $id)->first();
        if (empty($notification)) {
            return false;
        }
        $notification->status = 'Success';
        $notification->save();
        return true;
    }

    public function checkWalletBalance($userid, $currency_id)
    {
        $wallet = Wallet::where(['user_id' => $userid, 'currency_id' => $currency_id])->first(['balance']);
        if (empty($wallet)) {
            return false;
        }
        return $wallet->balance;
    }

    public function showTellerRequestForm()
    {
        // Return the login user branchId and also all currencies
        $user_id = auth()->guard('staff')->user()->id;
        $user = User::find($user_id);

        if (!$user) {
            return redirect(route('staff.login'));
        }

        $branchID = $user->branch_id;
        $currencies = Currency::where(['status' => 'Active'])->get();

        // get branch manager
        $managers = $this->getBranchManager($branchID);
        $manager = User::find($managers);

        if ($currencies->isEmpty()) {
            // Handle when currencies data is not available
            return redirect(route('tellerRequestMoney'))->with('error', 'Currencies data is not available.');
        }

        return view('staff.TellerRequestMoney', compact('branchID', 'currencies', 'manager', 'user_id'));
    }

    public function showManagerRequestForm()
    {
        // Return the login user branchId and also all currencies
        $user_id = auth()->guard('staff')->user()->id;
        $user = User::find($user_id);

        if (!$user) {
            return redirect(route('staff.login'));
        }

        $branchID = $user->branch_id;
        $currencies = Currency::where(['status' => 'Active'])->get();

        $treasurer = User::whereHas('role', function ($query) {
            $query->where('name', 'Treasurer');
        })->first();

        if ($currencies->isEmpty()) {
            // Handle when currencies data is not available
            return redirect(route('tellerRequestMoney'))->with('error', 'Currencies data is not available.');
        }

        return view('staff.ManagerRequestMoney', compact('branchID', 'currencies', 'treasurer', 'user_id'));
    }

    public function checkPassword($password)
    {
        // Check password validity
        $user_id = auth()->guard('staff')->user()->id;
        $storedPassword = User::find($user_id)->password;

        if (!$storedPassword || !Hash::check($password, $storedPassword)) {
            // If the password is incorrect, return an error message
            return "The password is incorrect. Please try again.";
        }

        // If the password is correct, return true
        return true;
    }
}
