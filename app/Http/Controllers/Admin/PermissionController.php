<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\DataTables\Admin\PermissionsDataTable2;
use App\Http\Helpers\Common;

class PermissionController extends Controller
{
    protected $helper;
    public function __construct()
    {
        $this->helper = new Common();
        $this->permission = new Permission();
    }

    public function index(PermissionsDataTable2 $dataTable)
    {
        $data['menu']     = 'staff';
        $data['sub_menu'] = 'perm_list';
        return $dataTable->render('admin.permissions.index', $data);
    }


    public function datatables()
    {
        return (new PermissionsDataTable2())->ajax();
    }

    public function create(Request $request)
    {
        $data['menu']     = 'staff';
        $data['sub_menu'] = 'perm_list';
        return view('admin.permissions.create', $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'group'        => 'required',
            'name'         => 'required|unique:permissions',
            'display_name' => 'required',
            'user_type'    => 'required',
        ];

        $fieldNames = [
            'group'        => __('Group'),
            'name'         => __('Name'),
            'display_name' => __('Display Name'),
            'user_type'    => __('User Type'), 
        ];

        $this->validate($request, $rules, [], $fieldNames);

        $permission              = new Permission();
        $permission->group       = $request->group;
        $permission->name        = $request->name;
        $permission->display_name= $request->display_name;
        $permission->user_type   = $request->user_type;
        $permission->description = $request->description;
        $permission->save();

        return redirect()->route('permissions')->with('success', __('Permission added successfully'));
    }
    public function edit($id)
    {
        $data['menu']     = 'staff';
        $data['sub_menu'] = 'perm_list';
        $permission = Permission::find($id);
        $data['permission'] = $permission;

        return view('admin.permissions.edit', $data);
    }

    public function update(Request $request)
    {

        $permission = Permission::find($request->id);

        $rules = [
            'group'        => 'required',
            'name'         => 'required|unique:permissions,name,'.$permission->id,
            'display_name' => 'required',
            'user_type'    => 'required',
        ];

        $fieldNames = [
            'group'        => __('Group'),
            'name'         => __('Name'),
            'display_name' => __('Display Name'),
            'user_type'    => __('User Type'),
        ];

        $this->validate($request, $rules, [], $fieldNames);
        
        $permission->group       = $request->group;
        $permission->name        = $request->name;
        $permission->display_name= $request->display_name;
        $permission->user_type   = $request->user_type;
        $permission->description = $request->description;
        $permission->save();

        $this->helper->one_time_message('success', __('The :x has been successfully Update.', ['x' => __('permission')]));
        return redirect()->route('permissions');
    }
    public function destroy($id)
    {
       
        $perm = Permission::find($id);
        if ($perm)
        {
        
            $perm->delete();
            $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('permission')]));
            return redirect()->intended(config('adminPrefix')."/permissions");
        }
    }

   
}
