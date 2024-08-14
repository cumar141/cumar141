<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Users\EmailController;
use Common, Config, Auth, DB, Exception, Validator, Session;
use App\DataTables\Admin\MerchantsDataTable;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MerchantsExport;
use App\Rules\CheckValidFile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\{
    Currency,
    MerchantPayment,
    MerchantGroup,
    MerchantApp,
    Merchant,
    Role,
    User,
    Wallet,
    QrCode
};

class MerchantController extends Controller
{
    protected $helper;
    protected $email;
    protected $merchant;

    public function __construct()
    {
        $this->helper   = new Common();
        $this->email    = new EmailController();
        $this->merchant = new Merchant();
    }

    public function index(MerchantsDataTable $dataTable)
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'merchant_details';

        $data['merchants_status'] = $this->merchant->select('status')->groupBy('status')->get();

        $data['from']     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';
        $data['user']     = $user    = isset(request()->user_id) ? request()->user_id : null;
        $data['getName']  = $this->merchant->getMerchantsUserName($user);

        return $dataTable->render('admin.merchants.list', $data);
    }

    public function merchantCsv()
    {
        return Excel::download(new MerchantsExport(), 'merchants_list_' . time() . '.xlsx');
    }

    public function merchantPdf()
    {
        $from = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;
        $user = isset(request()->user_id) ? request()->user_id : null;

        $data['merchants'] = $this->merchant->getMerchantsList($from, $to, $status, $user)->orderBy('merchants.id', 'desc')->get();

        if (isset($from) && isset($to)) {
            $data['date_range'] = $from . ' To ' . $to;
        } else {
            $data['date_range'] = 'N/A';
        }

        generatePDF('admin.merchants.merchants_report_pdf', 'merchants_report_', $data);
    }

    public function merchantsUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->merchant->getMerchantsUsersResponse($search);

        $res = [
            'status' => 'fail',
        ];
        if (count($user) > 0) {
            $res = [
                'status' => 'success',
                'data'   => $user,
            ];
        }
        return json_encode($res);
    }


    public function searchMerchantUser(Request $request)
    {
        $search = $request->search;

        $merchant = Merchant::with('user')->where('merchant_uuid', $search)->first();
        if ($merchant) {
            if ($merchant->status != 'Approved') {
                $res = [
                    'status' => 'fail',
                    'message' => 'Merchant not approved    ' . $request->search
                ];
                return response()->json($res);
            }
            $data = [
                'merchant_uuid' => $merchant->merchant_uuid,
                'business_name' => $merchant->business_name,
                'full_name'     => $merchant->user->first_name . ' ' . $merchant->user->last_name,
                'user_id' => $merchant->user_id,
            ];

            $res = [
                'status' => 'success',
                'data'   => $data,
            ];
        } else {
            $res = [
                'status' => 'fail',
                'message' => 'No Merchant found for code  ' . $request->search
            ];
        }
        return response()->json($res);
    }



    public function edit($id)
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'merchant_details';
        $data['merchant'] = Merchant::find($id);
        $data['merchantGroup'] = MerchantGroup::get(['id', 'name']);
        $data['activeCurrencies'] = Currency::where(['status' => 'Active', 'type' => 'fiat'])->get(['id', 'code', 'type']);


        return view('admin.merchants.edit', $data);
    }

    public function update(Request $request)
    {
        $rules = array(
            'business_name' => 'required',
            'site_url' => 'required|url',
            'fee' => 'required|numeric',
            'logo' => ['nullable', new CheckValidFile(getFileExtensions(3))],
        );

        $fieldNames = array(
            'business_name' => 'Business Name',
            'site_url'      => 'Site url',
            'fee'           => 'Fee',
            'logo'          => 'Logo',
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            $fileName = null;

            try {
                DB::beginTransaction();
                $merchant                    = Merchant::find($request->id);
                $merchant->currency_id       = $request->currency_id;
                $merchant->merchant_group_id = $request->merchantGroup;
                $merchant->type              = $request->type;

                if ($request->has('logo')) {
                    $response = uploadImage($request->logo, getDirectory('merchant'), '100*80', $merchant->logo, '70*70');
                    if (true === $response['status']) {
                        $fileName = $response['file_name'];
                    }
                }

                if ($request->type == 'express') {
                    $checkMerchantApp = MerchantApp::where(['merchant_id' => $request->id])->first();

                    if (empty($checkMerchantApp)) {
                        $merchant->appInfo()->create([
                            'client_id'     => Str::random(30),
                            'client_secret' => Str::random(100),
                        ]);
                    } else {
                        $merchantApp                = MerchantApp::find($checkMerchantApp->id);
                        $merchantApp->client_id     = $checkMerchantApp->client_id;
                        $merchantApp->client_secret = $checkMerchantApp->client_secret;
                        $merchant->save();
                    }
                }
                $merchant->business_name = $request->business_name;
                $merchant->site_url      = $request->site_url;
                $merchant->fee           = $request->fee;
                if ($fileName != null) {
                    $merchant->logo = $fileName;
                }
                $merchant->status = $request->status;
                $merchant->save();

                DB::commit();
                $this->helper->one_time_message('success', __('The :x has been successfully updated.', ['x' => __('merchant')]));
                return redirect(config('adminPrefix') . '/merchants');
            } catch (Exception $e) {
                DB::rollBack();
                $this->helper->one_time_message('error', $e->getMessage());
                return redirect(config('adminPrefix') . '/merchants');
            }
        }
    }

    public function deleteMerchantLogo(Request $request)
    {
        $logo = $request->logo;
        if (isset($logo)) {
            $merchant = Merchant::where(['id' => $request->merchant_id, 'logo' => $request->logo])->first();

            if ($merchant) {
                Merchant::where(['id' => $request->merchant_id, 'logo' => $request->logo])->update(['logo' => null]);

                if ($logo != null) {
                    $dir = public_path('user_dashboard/merchant/' . $logo);
                    if (file_exists($dir)) {
                        unlink($dir);
                    }
                }
                $data['success'] = 1;
                $data['message'] = __('The :x has been successfully deleted.', ['x' => __('logo')]);
            } else {
                $data['success'] = 0;
                $data['message'] = __('The :x does not exist.', ['x' => __('logo')]);
            }
        }
        echo json_encode($data);
        exit();
    }

    public function eachMerchantPayment($id)
    {
        $data['menu'] = 'users';
        $data['sub_menu'] = 'merchant_details';
        $data['merchant_payments'] = MerchantPayment::where(['merchant_id' => $id])->orderBy('id', 'desc')->get();
        $data['merchant'] = Merchant::find($id);
        return view('admin.merchants.eachMerchantPayment', $data);
    }

    public function changeMerchantFeeWithGroupChange(Request $request)
    {
        if ($request->merchant_group_id) {
            $merchantGroup = MerchantGroup::where(['id' => $request->merchant_group_id])->first(['fee']);
            if ($merchantGroup) {
                $data['status'] = true;
                $data['fee']    = $merchantGroup->fee;
            } else {
                $data['status'] = false;
            }
            return $data;
        }
    }

    // merchants create 
    public function Create()
    {
        // dd('create');
        $data['menu'] = 'users';
        $data['sub_menu'] = 'merchant_details';
        $data['activeCurrencies'] = Currency::where(['status' => 'Active', 'type' => 'fiat'])->get(['id', 'code']);
        $data['defaultWallet']    = Wallet::get();

        // return view('user.merchant.create', $data);

        return view('admin.merchants.create', $data);
    }
    public function Save(Request $request)
    {

        $data['menu']     = 'users';
        $data['sub_menu'] = 'merchant_details';

        $this->validate($request, [
            'business_name' => 'required|unique:merchants,business_name',
            'site_url'      => 'required|url',
            'type'          => 'required',
            'note'          => 'required',
            'logo' => ['nullable', new CheckValidFile(getFileExtensions(3), true)],
        ]);

        try {
            DB::beginTransaction();

            $picture  = $request->logo;
            $fileName = null;

            if (isset($picture)) {
                $response = uploadImage($picture, public_path("/uploads/merchant/"), '100*80', null, '70*70');

                if ($response['status'] === true) {
                    $fileName = $response['file_name'];
                } else {
                    DB::rollBack();
                    $this->helper->one_time_message('error', $response['message']);
                    return back()->withInput();
                }
            }

            $merchantGroup               = MerchantGroup::where(['is_default' => 'Yes'])->select('id', 'fee')->first();
            $merchant                    = new Merchant();
            $merchant->user_id           =  $request->user_id;
            $merchant->currency_id       = $request->currency_id;
            $merchant->merchant_group_id = isset($merchantGroup) ? $merchantGroup->id : null;
            $merchant->business_name     = $request->business_name;
            $merchant->site_url          = $request->site_url;
            $uuid                        = $this->unique_code();
            $merchant->merchant_uuid     = $uuid;
            $merchant->type              = $request->type;
            $merchant->note              = $request->note;
            $merchant->logo              = $fileName != null ? $fileName : '';
            $merchant->fee               = isset($merchantGroup) ? $merchantGroup->fee : 0.00;


            if (module('WithdrawalApi') && isActive('WithdrawalApi')) {
                $merchant->withdrawal_approval = $request->withdrawal_approval == 'on' ? 'Yes' : 'No';
            }

            $merchant->save();

            if (strtolower($request->type) == 'express') {
                try {
                    $merchantAppInfo = $merchant->appInfo()->create([
                        'client_id'     => Str::random(30),
                        'client_secret' => Str::random(100),
                    ]);
                } catch (Exception $ex) {
                    DB::rollBack();
                    $this->helper->one_time_message('error', __('Client id must be unique. Please try again!'));
                    return back();
                }

                $request->request->add([
                    'merchantId' => $merchant->id,
                    'merchantDefaultCurrencyId' => $merchant->currency_id,
                    'clientId' => $merchantAppInfo->client_id
                ]);

                $this->generateOrUpdateExpressMerchantQrCode($request);
            }

            DB::commit();

            // Notification Email/SMS
            (new  \App\Services\Mail\MerchantPayment\NotifyAdminOnMerchantCreationMailService())->send($merchant, ['type' => 'payment', 'medium' => 'email']);
            $this->helper->one_time_message('success', __('Merchant Created Successfully!'));
            return redirect(url(config('adminPrefix') . '/merchants'));
        } catch (Exception $e) {
            DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect(url(config('adminPrefix') . '/merchants'));
        }
    }

    public function generateOrUpdateExpressMerchantQrCode(Request $request)
    {
        $qrCode = QrCode::where(['object_id' => $request->merchantId, 'object_type' => 'express_merchant', 'status' => 'Active'])->first(['id', 'secret']);
        $merchantCurrency = Currency::where('id', $request->merchantDefaultCurrencyId)->first(['code']);

        if (!empty($qrCode)) {
            $qrCode->status = 'Inactive';
            $qrCode->save();
        }


        $secretCode = convert_string('encrypt', 'express_merchant' . '-' . $request->merchantId . '-' . $merchantCurrency->code . '-' . $request->clientId . Str::random(6));

        $imageName = time() . '.' . 'jpg';

        $createMerchantQrCode = new QrCode();
        $createMerchantQrCode->object_id   = $request->merchantId;
        $createMerchantQrCode->object_type = 'express_merchant';
        $createMerchantQrCode->secret = $secretCode;
        $createMerchantQrCode->qr_image = $imageName;
        $createMerchantQrCode->status = 'Active';
        $createMerchantQrCode->save();

        $secretCodeImage = generateQrcode($createMerchantQrCode->secret);
        Image::make($secretCodeImage)->save(getDirectory('merchant_qrcode') . $imageName);

        return response()->json([
            'status' => true,
            'imgSource' => image($imageName, 'merchant_qrcode')
        ]);
    }

    // handle search function
    public function searchUser(Request $request)
    {
        $search = $request->search_user;
        $strippedSearch = str_replace('+252', '', $search);
        $user = User::where(function ($query) use ($search, $strippedSearch) {
            $query->where('phone', $strippedSearch)
                ->orWhere('phone1', $strippedSearch)
                ->orWhere('phone3', $strippedSearch)
                ->orWhere('formattedPhone', $search);
        })->first();

        if ($user) {
            switch ($user->type) {
                case 'merchant':
                    $status = 200;
                    $message = 'User Found';
                    break;
                case 'user':
                case 'staff':
                    $status = 404;
                    $message = 'User Not Merchant';
                    break;
                default:
                    $status = 404;
                    $message = 'User Not Merchant';
                    break;
            }
        } else {
            $status = 404;
            $message = 'User Not Found';
        }

        $data = [
            'status' => $status,
            'message' => $message,
            'user' => $user ?? null,
        ];

        return response()->json($data);
    }

    function unique_code()
    {

        // Retrieve the last generated code from your database
        $lastCode = Merchant::latest()->value('merchant_uuid');

        $staticNewCode = 300000;

        if (!$lastCode) return  $staticNewCode;


        // Extract the numeric part from the last code
        $lastNumber = (int) substr($lastCode, 1); // Remove the starting '3' and convert to integer

        // Increment the last number by 1
        $newNumber = $lastNumber + 1;

        // Pad the number with leading zeros to ensure it has six digits
        $paddedNumber = str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        // Combine with '3' to form the new unique code
        $newCode = '3' . $paddedNumber;

        return $newCode;
    }
}
