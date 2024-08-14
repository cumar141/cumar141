<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\StaffDataTable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\FeesLimit;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Mail\Deposit\DepositViaAdminMailService;
use App\Services\Mail\UserStatusChangeMailService;
use Illuminate\Http\Request;
use Session, DB, Validator;

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

    public function index(StaffDataTable $dataTable)
    {
        $data['menu'] = 'staff';
        $data['sub_menu'] = 'staff_list';

        return $dataTable->render('admin.staff.index', $data);
    }

    public function create()
    {
        $data['menu'] = 'staff';
        $data['sub_menu'] = 'staff_list';
        $data['branch'] = Branch::all();
        $data['roles'] = Role::select('id', 'display_name')->where(['customer_type' => 'Staff'])->get();

        return view('admin.users.createStaff', $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required',
            'branch_id' => 'required'
        ];

        $fieldNames = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'password' => 'Password',
            'role' => 'Role',
            'branch_id', 'Branch'
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Create user
            $user = $this->createNewUser($request);

            // Assign user type and role to new user
            RoleUser::insert(['user_id' => $user->id, 'role_id' => $user->role_id, 'user_type' => 'Staff']);

            // Create user detail
            $this->user->createUserDetail($user->id);

            DB::commit();
            $this->helper->one_time_message('success', __('Staff Added Successfully'));

            return redirect('admin/staff');
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            $this->helper->one_time_message('error', $e->getMessage());

            return redirect('admin/staff');
        }
    }

    public function Edit($id)
    {
        $data['menu'] = 'staff';
        $data['sub_menu'] = 'staff_list';

        $data['branch'] = Branch::all(); 
        $data['users'] = User::find($id);
        $data['roles'] = Role::select('id', 'display_name')->where(['customer_type' => 'Staff'])->get();

        return view('admin.users.editStaff', $data);
    }

    public function update(Request $request)
    {
        
            $rules = [
                'first_name' => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
                'last_name' => 'required|max:30',
                'email' => 'required|email|unique:users,email,'.$request->id,
                'password' => 'nullable|min:6|confirmed',
                'password_confirmation' => 'nullable|min:6',
                'status' => 'required',
                'branch_id' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                try {
                    DB::beginTransaction();
                    $user = User::find($request->id);
                    $user->first_name = $request->first_name;
                    $user->last_name = $request->last_name;
                    $user->email = $request->email;
                    $user->branch_id = $request->branch_id;
                    $user->role_id = $request->role;
                    $user->status = $request->status;

                    $formattedPhone = ltrim($request->phone, '0');
                    if (!empty($request->phone)) {
                        $user->phone = preg_replace("/[\s-]+/", '', $formattedPhone);
                        $user->defaultCountry = $request->user_defaultCountry;
                        $user->carrierCode = $request->user_carrierCode;
                        $user->formattedPhone = $request->formattedPhone;
                    } else {
                        $user->phone = null;
                        $user->defaultCountry = null;
                        $user->carrierCode = null;
                        $user->formattedPhone = null;
                    }

                    if (!is_null($request->password) && !is_null($request->password_confirmation)) {
                        $user->password = \Hash::make($request->password);
                    }
                    $user->save();

                    RoleUser::where(['user_id' => $request->id, 'user_type' => 'Staff'])->update(['role_id' => $request->role]);

                    DB::commit();

                    if ($request->status != $user->status) {
                        (new UserStatusChangeMailService())->send($user);
                    }
                    // dd($user);

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('staff')]));

                    return redirect(config('adminPrefix').'/staff');
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->helper->one_time_message('error', $e->getMessage());

                    return redirect(config('adminPrefix').'/staff');
                }
            }
        
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if ($user) {
            try {
                DB::beginTransaction();
                // Deleting Non-Relational Table Entries

                ActivityLog::where(['user_id' => $id])->delete();
                RoleUser::where(['user_id' => $id, 'user_type' => 'User'])->delete();

                $user->delete();

                DB::commit();

                $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('staff')]));

                return redirect(config('adminPrefix').'/staff');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->helper->one_time_message('error', $e->getMessage());

                return redirect(config('adminPrefix').'/staff');
            }
        }
    }

    public function showDepositForm($id)
    {
        $data['menu'] = 'staff';
        $data['sub_menu'] = 'staff_list';
        $data['user'] = User::find($id);
        $data['currency'] = $this->currency->where(['status' => 'Active'])->get();

        return view('admin.staff.depositForm', $data);
    }

    public function depositMoney(Request $request)
    {
        // dd($request);

        $data['menu'] = 'staff';
        $data['sub_menu'] = 'staff_list';

        $user_id = $request->userid;
        $amount = $request->amount;
        $currency = $request->currency;

        $uuid = unique_code();
        $feeInfo = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currency])
            ->first(['charge_percentage', 'charge_fixed']);
        // charge percentage calculation
        $p_calc = ($amount * (@$feeInfo->charge_percentage) / 100);

        try {
            DB::beginTransaction();
            // Deposit
            $deposit = new Deposit();
            $deposit->user_id = $user_id;
            $deposit->currency_id = $currency;
            $deposit->payment_method_id = 1;
            $deposit->uuid = $uuid;
            $deposit->charge_percentage = 0;
            $deposit->charge_fixed = 0;
            $deposit->amount = $amount;
            $deposit->status = 'Pending';
            $deposit->save();

            // Transaction
            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->currency_id = $currency;
            $transaction->payment_method_id = 1;
            $transaction->transaction_reference_id = $deposit->id;
            $transaction->transaction_type_id = Deposit;
            $transaction->uuid = $uuid;
            $transaction->subtotal = $amount;
            $transaction->percentage = 0;
            $transaction->charge_percentage = $deposit->charge_percentage;
            $transaction->charge_fixed = $deposit->charge_fixed;
            $transaction->total = $amount;
            $transaction->status = 'Pending';
            $transaction->save();

            if (module('Referral') && settings('referral_enabled') == 'Yes') {
                $refAwardData = [
                    'userId' => $deposit->user_id,
                    'currencyId' => $deposit->currency_id,
                    'currencyCode' => $deposit?->currency?->code,
                    'presentAmount' => $deposit->amount,
                    'paymentMethodId' => $deposit->payment_method_id,
                    'transactionType' => 'Deposit',
                ];

                $awardResponse = (new \Modules\Referral\Entities\ReferralAward())->checkReferralAward($refAwardData);
            }

            DB::commit();

            // Notification Email/SMS
            (new DepositViaAdminMailService())->send($deposit);
            if (module('Referral') && settings('referral_enabled') == 'Yes' && !empty($awardResponse)) {
                if (isset($awardResponse['email_status']) && $awardResponse['email_status'] === 200 && !empty($awardResponse['email_details'])) {
                    $awardInfo = (new \Modules\Referral\Services\Email\ReferralAwardMailService())->send($awardResponse['email_details']);
                    \Modules\Referral\Jobs\ProcessRewardEmail::dispatch($awardInfo);
                }
            }

            // Send deposit sms to admin
            if (checkAppSmsEnvironment()) {
                $payoutMessage = 'Amount '.moneyFormat(optional($deposit->currency)->symbol, formatNumber($deposit->amount)).' was deposited by System Administrator.';
                if (!empty($deposit->user->formattedPhone)) {
                    sendSMS($deposit->user->formattedPhone, $payoutMessage);
                }
            }

            $data['transInfo']['currency_id'] = $currency;
            $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
            $data['transInfo']['subtotal'] = $transaction->subtotal;
            $data['transInfo']['id'] = $transaction->id;
            $data['users'] = User::find($user_id, ['id']);
            $data['name'] = $data['users']->first_name.' '.$data['users']->last_name;

            // Session::forget('transInfo');
            // clearActionSession();
            return view('admin.staff.depositSuccess', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::forget('transInfo');
            clearActionSession();
            $this->helper->one_time_message('error', $e->getMessage());

            return redirect(config('adminPrefix').'/staff/depositForm/'.$user_id);
        }
    }

    public function createNewUser($request)
    {
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->branch_id = $request->branch_id;
        $user->type = 'Staff';
        $formattedPhone = str_replace('+'.$request->carrierCode, '', $request->formattedPhone);
        if (!empty($request->phone)) {
            $user->phone = preg_replace("/[\s-]+/", '', $formattedPhone);
            $user->defaultCountry = $request->defaultCountry;
            $user->carrierCode = $request->carrierCode;
            $user->formattedPhone = $request->formattedPhone;
        } else {
            $user->email = null;
            $user->defaultCountry = null;
            $user->carrierCode = null;
            $user->formattedPhone = null;
        }
        $user->password = \Hash::make($request->password);

        $user->role_id = $request->role;

        $roleName = $this->getRoleName($request->role);
        if ($roleName == 'Teller') {
            $user->teller_uuid = $this->unique_code();
        }

        $user->save();

        return $user;
    }

    public function unique_code()
    {
        $lastCode = User::whereHas('role', function ($query) {
            $query->where('name', 'Teller');
        })
            ->latest()
            ->value('teller_uuid');

        // Increment the last code by 1
        $newCode = ($lastCode ? $lastCode + 1 : 1);

        // Pad the number with leading zeros to ensure it has six digits
        $paddedCode = str_pad($newCode, 6, '0', STR_PAD_LEFT);

        return $paddedCode;
    }

    public function getRoleName($id)
    {
        $role = Role::find($id);

        return $role->name;
    }
}
