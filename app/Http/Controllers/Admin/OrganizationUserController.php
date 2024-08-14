<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    OrganizationUser,
    Organization,
    Currency
};
use App\DataTables\Admin\OrganizationUserDataTable;
use App\Http\Helpers\Common;
use App\Http\Controllers\Users\EmailController;
use Hash, Validator, Session, DB, Exception;


class OrganizationUserController extends Controller
{
    protected $helper;
    protected $email;
    protected $currency;
    protected $organization_user;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email = new EmailController();
        $this->currency = new Currency();
        $this->organization_user = new OrganizationUser();
    }

    public function index(OrganizationUserDataTable $dataTable)
    {
        $data['menu'] = 'organization';
        $data['sub_menu'] = 'organization_user_list';
        return $dataTable->render('admin.organization_user.index', $data);
    }

    public function create($id)
    {
        $organizations = Organization::find($id);
        $data['menu'] = 'organization';
        $data['sub_menu'] = 'organization_user_list';
        $data['organizations'] = $organizations;

        return view('admin.organization_user.create', $data);
    }

    public function store(Request $request)
    {
        $rules = array(
            'organization_id' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        );

        $fieldNames = array(
            'organization_id' => 'Organization',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
        );


        //check if organization has other user or if this is the firt user
        $other_user = OrganizationUser::where('organization_id', $request->organization_id)->first();


        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            $organizationUser = new OrganizationUser();
            $organizationUser->organization_id = $request->organization_id;
            $organizationUser->username = $request->username;
            $organizationUser->email = $request->email;
            $organizationUser->password = Hash::make($request->password);
            $organizationUser->save();

            //if this is the first user, then make him admin using spaties
            if (!$other_user) {
                DB::table('org_model_has_roles')->insert([
                    'role_id' => 1,
                    'model_type' => 'App\\Models\\OrganizationUser',
                    'model_id' => $organizationUser->id
                ]);
            }


            $this->helper->one_time_message('success', 'Organization User Created Successfully');
            return redirect('admin/organization/list/user/' . $request->organization_id);
        }
    }

    public function edit($id)
    {
        $data['menu'] = 'organization';
        $data['sub_menu'] = 'organization_user_list';
        $data['organizationUser'] = OrganizationUser::with('organization')->find($id);

        return view('admin.organization_user.edit', $data);
    }

    public function update(Request $request)
    {
        $rules = array(
            'organization_id' => 'required',
            'email' => 'required|email',
            'username' => 'required',

        );

        $fieldNames = array(
            'organization_id' => 'Organization',
            'email' => 'Email',
            'username' => 'Username',
        );

        if ($request->password != '') {
            $rules['password'] = 'required|confirmed';
            $fieldNames['password'] = 'Password';
            $fieldNames['password_confirmation'] = 'Confirm Password';
        }

        // check passwords match
        if ($request->password != '' && $request->password != $request->password_confirmation) {
            $this->helper->one_time_message('error', 'Password and Confirm Password do not match');
            return back();
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            $organizationUser = OrganizationUser::find($request->id);
            $organizationUser->organization_id = $request->organization_id;
            $organizationUser->username = $request->username;
            $organizationUser->email = $request->email;
            if ($request->password != '') {
                $organizationUser->password = Hash::make($request->password);
            }

            $organizationUser->save();

            $this->helper->one_time_message('success', 'Organization User Updated Successfully');
            return redirect('admin/organization/user');
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $organization_user = OrganizationUser::find($id);
            $organization_user->delete();

            DB::commit();

            $this->helper->one_time_message('success', 'Organization Deleted Successfully');
            return redirect('admin/organization/user');
        } catch (Exception $e) {
            DB::rollBack();
            $this->helper->one_time_message('error', 'Something went wrong! Please try again.');
            return back();
        }
    }

    public function search(Request $request)
    {
        $searchItem = $request->searchInput;
        $data['menu'] = 'organization';
        $data['sub_menu'] = 'organization_user_list';
        $organizations = Organization::where(function ($query) use ($searchItem) {
            // Define the columns to search in
            $columns = ['name', 'email', 'phone'];

            // Iterate over the columns
            foreach ($columns as $column) {
                // Use the like operator to search for the searchItem in each column
                $query->orWhere($column, 'like', '%' . $searchItem . '%');
            }
        })->first();

        if (empty($organizations)) {
            return view('admin.organization_user.search')->with('message', 'No results found');
        }
        $data['organizations'] = $organizations;

        return view('admin.organization_user.create', $data);
    }

}
