<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    Organization,
    OrganizationWallet,
    Currency,
    Merchant,
    User,
    Wallet
};
use App\DataTables\Admin\OrganizationDataTable;
use App\DataTables\Admin\OrganizationUserDataTable;
use App\Http\Helpers\Common;
use App\Http\Controllers\Users\EmailController;
use Hash, Validator, Session, DB, Exception;

class OrganizationController extends Controller
{
    protected $helper;
    protected $email;
    protected $currency;
    protected $organization;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email = new EmailController();
        $this->currency = new Currency();
        $this->organization = new Organization();
    }

    public function index(OrganizationDataTable $dataTable)
    {
        $data['menu'] = 'organization';
        $data['sub_menu'] = 'organization_list';
        return $dataTable->render('admin.organization.index', $data);
    }

    public function addTransaction($id)
    {
        $data['menu'] = 'organization';
        $data['sub_menu'] = 'organization_transaction_list';
        $data['organization'] = Organization::find($id);

        return view('admin.organization.add_transaction', $data);
    }

    public function listUser($id)
    {
        $data['menu'] = 'organization';
        $data['sub_menu'] = 'organization_user_list';
        $data['organization_id'] = $id;


        $dataTable = new OrganizationUserDataTable($id);
        return $dataTable->render('admin.organization_user.index', $data);
    }

    public function create()
    {
        $data['menu'] = 'organization';
        $data['sub_menu'] = 'organization_list';



        return view('admin.organization.create', $data);
    }

    public function store(Request $request)
    {

        // Define validation rules
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required',

        ];

        $fieldNames = [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'address' => 'Address',


        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);


        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $organization = new Organization();
            $organization->name = $request->name;
            $organization->email = $request->email;
            $organization->phone = $request->phone;
            $organization->address = $request->address;
            $organization->is_white_label = $request->is_white_list;

            $organization->save();

            DB::commit();


            $this->helper->one_time_message('success', 'Organization Added Successfully');
            return redirect('admin/organization');
        } catch (Exception $e) {
            DB::rollBack();

            $this->helper->one_time_message('error', 'Something went wrong! Please try again.');
            return back();
        }
    }


    public function edit($id)
    {
        $data['menu'] = 'organization';
        $data['sub_menu'] = 'organization_list';
        $data['organization'] = Organization::find($id);

        return view('admin.organization.edit', $data);
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required',
        ];

        $fieldNames = [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'address' => 'Address',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $organization = Organization::find($request->id);
            $organization->name = $request->name;
            $organization->email = $request->email;
            $organization->phone = $request->phone;
            $organization->address = $request->address;
            $organization->user_id = $request->uuid ? null : $request->uuid;
            $organization->save();
            DB::commit();

            $this->helper->one_time_message('success', 'Organization Updated Successfully');
            return redirect('admin/organization');
        } catch (Exception $e) {
            DB::rollBack();
            $this->helper->one_time_message('error', 'Something went wrong! Please try again.');
            return back();
        }
    }

    public function assignOrganizationMerchant(Request $request)
    {

        $rules = [
            'organization_id' => 'required|exists:organizations,id',
            'merchant_user_id' => 'required|exists:users,id',
        ];

        $fieldNames = [
            'organization_id' => 'Organization ID',
            'merchant_user_id' => 'Merchant User ID',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }


        $organization = Organization::find($request->organization_id);
        if (!$organization) {
            return redirect()->back()->with('error', 'No organization found');
        }

        $organization->update(['merchant_uuid' => $request->uuid]);

        $merchant = Merchant::where('merchant_uuid', $request->uuid)->first();

        $this->helper->one_time_message('success', 'Merchant assigned to organization successfully' . $merchant->business_name);

        return redirect('admin/organization');
    }



    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $organization = Organization::find($id);
            $organization->delete();

            DB::commit();

            $this->helper->one_time_message('success', 'Organization Deleted Successfully');
            return redirect('admin/organization');
        } catch (Exception $e) {
            DB::rollBack();
            $this->helper->one_time_message('error', 'Something went wrong! Please try again.');
            return back();
        }
    }
}
